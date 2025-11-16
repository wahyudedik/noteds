<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteBundle;
use App\Models\Transaction;
use App\Services\CommissionService;
use App\Services\NotificationService;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NoteBundleController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private CommissionService $commissionService,
        private TaxService $taxService
    ) {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of bundles.
     */
    public function index(Request $request): View
    {
        $bundles = NoteBundle::where('is_active', true)
            ->with(['user', 'items.note'])
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('bundles.index', compact('bundles'));
    }

    /**
     * Show the form for creating a new bundle.
     */
    public function create(): View
    {
        $userNotes = Note::where('user_id', auth()->id())
            ->where('is_public', true)
            ->where('status', 'active')
            ->where('is_draft', false)
            ->with('tags')
            ->latest()
            ->get();

        return view('bundles.create', compact('userNotes'));
    }

    /**
     * Store a newly created bundle.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note_ids' => ['required', 'array', 'min:2'],
            'note_ids.*' => ['exists:notes,id'],
        ]);

        // Verify all notes belong to user
        $userNotes = Note::where('user_id', auth()->id())
            ->whereIn('id', $validated['note_ids'])
            ->count();

        if ($userNotes !== count($validated['note_ids'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Some selected notes do not belong to you.');
        }

        DB::transaction(function () use ($validated) {
            $bundle = NoteBundle::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'is_active' => true,
            ]);

            // Add notes to bundle
            foreach ($validated['note_ids'] as $index => $noteId) {
                $bundle->items()->create([
                    'note_id' => $noteId,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('bundles.index')
            ->with('success', 'Bundle created successfully.');
    }

    /**
     * Display the specified bundle.
     */
    public function show(NoteBundle $bundle): View
    {
        $bundle->load(['user', 'items.note.tags', 'items.note.reviews']);

        return view('bundles.show', compact('bundle'));
    }

    /**
     * Purchase a bundle.
     */
    public function purchase(Request $request, NoteBundle $bundle): RedirectResponse
    {
        if (!$bundle->is_active) {
            return redirect()->route('bundles.show', $bundle)
                ->with('error', 'This bundle is not available for purchase.');
        }

        $user = auth()->user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $bundle->price) {
            return redirect()->route('wallet.index')
                ->with('error', 'Insufficient wallet balance. Please top up first.');
        }

        DB::transaction(function () use ($bundle, $user, $wallet) {
            // Calculate tax
            $taxData = $this->taxService->calculateTax($bundle->price, $user);
            $totalAmount = $taxData['total_amount'];

            // Deduct from wallet
            $user->decrement('wallet_balance', $totalAmount);
            $wallet->balance = $user->wallet_balance;
            $wallet->save();

            // Create transaction
            $transaction = Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $bundle->user_id,
                'note_id' => null, // Bundle purchase
                'amount' => $bundle->price,
                'commission' => $this->commissionService->calculateCommission($bundle->price),
                'currency' => config('currency.base_currency', 'IDR'),
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => "Bundle purchase: {$bundle->title}",
                'tax_percent' => $taxData['tax_percent'],
                'tax_amount' => $taxData['tax_amount'],
                'tax_inclusive' => $taxData['tax_inclusive'],
                'tax_country_code' => $taxData['country_code'] ?? null,
            ]);

            // Create purchased notes for each note in bundle
            foreach ($bundle->items as $item) {
                \App\Models\PurchasedNote::create([
                    'user_id' => $user->id,
                    'note_id' => $item->note_id,
                    'purchase_price' => $item->note->price ?? 0,
                    'purchased_at' => now(),
                    'transaction_id' => $transaction->id,
                ]);
            }

            // Increment bundle purchase count
            $bundle->increment('purchase_count');

            // Notify seller
            $this->notificationService->create(
                $bundle->user,
                'bundle_purchased',
                '💰 Bundle Purchased',
                $user->name . ' purchased your bundle: ' . $bundle->title,
                route('bundles.show', $bundle),
                ['bundle_id' => $bundle->id, 'transaction_id' => $transaction->id]
            );
        });

        return redirect()->route('bundles.show', $bundle)
            ->with('success', 'Bundle purchased successfully! All notes have been added to your library.');
    }
}
