<?php

namespace App\Http\Controllers;

use App\Models\GiftNote;
use App\Models\Note;
use App\Models\Transaction;
use App\Services\CommissionService;
use App\Services\NotificationService;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GiftNoteController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private CommissionService $commissionService,
        private TaxService $taxService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of user's gifts (sent and received).
     */
    public function index(): View
    {
        $sentGifts = GiftNote::where('gifter_id', auth()->id())
            ->with(['recipient', 'note', 'transaction'])
            ->latest()
            ->paginate(10, ['*'], 'sent');

        $receivedGifts = GiftNote::where('recipient_id', auth()->id())
            ->where('status', 'sent')
            ->with(['gifter', 'note', 'transaction'])
            ->latest()
            ->paginate(10, ['*'], 'received');

        return view('gifts.index', compact('sentGifts', 'receivedGifts'));
    }

    /**
     * Show the form for sending a gift.
     */
    public function create(Note $note): View
    {
        // Check if note is available for purchase
        if (!$note->is_public || $note->status !== 'active') {
            abort(404);
        }

        return view('gifts.create', compact('note'));
    }

    /**
     * Store a newly created gift.
     */
    public function store(Request $request, Note $note): RedirectResponse
    {
        // Check if note is available
        if (!$note->is_public || $note->status !== 'active') {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'This note is not available for gifting.');
        }

        $validated = $request->validate([
            'recipient_email' => ['required', 'email', 'exists:users,email'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $recipient = \App\Models\User::where('email', $validated['recipient_email'])->first();

        if ($recipient->id === auth()->id()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You cannot gift a note to yourself.');
        }

        // Check if recipient already owns this note
        $alreadyOwned = \App\Models\PurchasedNote::where('user_id', $recipient->id)
            ->where('note_id', $note->id)
            ->exists();

        if ($alreadyOwned) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The recipient already owns this note.');
        }

        $user = auth()->user();
        $wallet = $user->wallet;
        $giftPrice = $note->price;

        if (!$wallet || $wallet->balance < $giftPrice) {
            return redirect()->route('wallet.index')
                ->with('error', 'Insufficient wallet balance. Please top up first.');
        }

        DB::transaction(function () use ($note, $recipient, $validated, $user, $wallet, $giftPrice) {
            // Calculate tax
            $taxData = $this->taxService->calculateTax($giftPrice, $user);
            $totalAmount = $taxData['total_amount'];

            // Deduct from wallet
            $user->decrement('wallet_balance', $totalAmount);
            $wallet->balance = $user->wallet_balance;
            $wallet->save();

            // Create transaction
            $transaction = Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $note->user_id,
                'note_id' => $note->id,
                'amount' => $giftPrice,
                'commission' => $this->commissionService->calculateCommission($giftPrice),
                'currency' => config('currency.base_currency', 'IDR'),
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => "Gift purchase for: {$recipient->name}",
                'tax_percent' => $taxData['tax_percent'],
                'tax_amount' => $taxData['tax_amount'],
                'tax_inclusive' => $taxData['tax_inclusive'],
                'tax_country_code' => $taxData['country_code'] ?? null,
            ]);

            // Create gift note
            $giftNote = GiftNote::create([
                'gifter_id' => $user->id,
                'recipient_id' => $recipient->id,
                'note_id' => $note->id,
                'transaction_id' => $transaction->id,
                'message' => $validated['message'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
                'expires_at' => now()->addDays(30), // Gift expires in 30 days
            ]);

            // Notify recipient
            $this->notificationService->create(
                $recipient,
                'gift_received',
                '🎁 You Received a Gift!',
                $user->name . ' sent you a gift: ' . $note->title,
                route('gifts.show', $giftNote),
                ['gift_id' => $giftNote->id, 'note_id' => $note->id]
            );

            // Send email notification
            try {
                \Illuminate\Support\Facades\Mail::to($recipient->email)->send(
                    new \App\Mail\GiftNoteReceivedMail($giftNote)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send gift email: ' . $e->getMessage());
            }

            // Notify seller
            $this->notificationService->create(
                $note->user,
                'note_gifted',
                '🎁 Note Gifted',
                $user->name . ' gifted your note "' . $note->title . '" to ' . $recipient->name,
                route('marketplace.show', $note),
                ['gift_id' => $giftNote->id]
            );
        });

        return redirect()->route('gifts.show', GiftNote::latest()->first())
            ->with('success', 'Gift sent successfully!');
    }

    /**
     * Display the specified gift.
     */
    public function show(GiftNote $giftNote): View
    {
        // Ensure user is gifter or recipient
        if ($giftNote->gifter_id !== auth()->id() && $giftNote->recipient_id !== auth()->id()) {
            abort(403);
        }

        $giftNote->load(['gifter', 'recipient', 'note', 'transaction']);

        return view('gifts.show', compact('giftNote'));
    }

    /**
     * Claim a gift note.
     */
    public function claim(GiftNote $giftNote): RedirectResponse
    {
        // Ensure user is recipient
        if ($giftNote->recipient_id !== auth()->id()) {
            abort(403);
        }

        if (!$giftNote->canBeClaimed()) {
            return redirect()->route('gifts.show', $giftNote)
                ->with('error', 'This gift cannot be claimed. It may have expired or already been claimed.');
        }

        DB::transaction(function () use ($giftNote) {
            // Create purchased note
            \App\Models\PurchasedNote::create([
                'user_id' => $giftNote->recipient_id,
                'note_id' => $giftNote->note_id,
                'purchase_price' => $giftNote->transaction->amount ?? 0,
                'purchased_at' => now(),
                'transaction_id' => $giftNote->transaction_id,
            ]);

            // Update gift status
            $giftNote->update([
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);

            // Notify gifter
            $this->notificationService->create(
                $giftNote->gifter,
                'gift_claimed',
                '✅ Gift Claimed',
                $giftNote->recipient->name . ' has claimed your gift: ' . $giftNote->note->title,
                route('gifts.show', $giftNote),
                ['gift_id' => $giftNote->id]
            );
        });

        return redirect()->route('gifts.show', $giftNote)
            ->with('success', 'Gift claimed successfully! The note has been added to your library.');
    }
}
