<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use App\Models\Faq;
use App\Services\SupportTicketService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private SupportTicketService $supportTicketService,
        private NotificationService $notificationService
    ) {
        // Middleware is applied in routes/web.php
    }

    /**
     * Display help center with FAQs.
     */
    public function helpCenter(): Response
    {
        $faqs = Faq::published()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('category');

        $categories = Faq::published()
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('Support/HelpCenter', [
            'faqs' => $faqs,
            'categories' => $categories,
        ]);
    }

    /**
     * Search knowledge base (FAQs).
     */
    public function searchKnowledgeBase(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $faqs = Faq::published()
            ->where(function ($q) use ($query) {
                $q->where('question', 'like', '%' . $query . '%')
                    ->orWhere('answer', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'category' => $faq->category,
                ];
            });

        return response()->json(['results' => $faqs]);
    }

    /**
     * Display a listing of user's tickets.
     */
    public function index(Request $request): Response
    {
        $query = SupportTicket::where('user_id', Auth::id())
            ->with([
                'assignedAdmin:id,name,email',
                'responses' => function ($q) {
                    $q->where('is_internal_note', false)->latest()->limit(1);
                }
            ]);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                    ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                    ->orWhere('message', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->latest()->paginate(15);

        // Get unique categories for filter
        $categories = SupportTicket::where('user_id', Auth::id())
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('Support/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $request->status ?? 'all',
                'category' => $request->category ?? '',
                'search' => $request->search ?? '',
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create(): Response
    {
        $categories = [
            'general' => 'General Inquiry',
            'account' => 'Account Issues',
            'technical' => 'Technical Support',
            'other' => 'Other',
        ];

        return Inertia::render('Support/Tickets/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'category' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpeg,png,gif,webp,pdf,txt,doc,docx',
        ]);

        // Validate files
        if ($request->hasFile('attachments')) {
            $fileErrors = $this->supportTicketService->validateFiles($request->file('attachments'));
            if (!empty($fileErrors)) {
                return back()->withErrors(['attachments' => $fileErrors])->withInput();
            }
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'category' => $validated['category'] ?? 'general',
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        // Store attachments
        if ($request->hasFile('attachments')) {
            $attachmentPaths = $this->supportTicketService->storeAttachments(
                $request->file('attachments'),
                $ticket->id
            );
            $ticket->update(['attachments' => $attachmentPaths]);
        }

        // Notify admins about new ticket
        $this->notificationService->notifyNewSupportTicket($ticket);

        return redirect()->route('support.tickets.show', $ticket->id)
            ->with('success', 'Support ticket created successfully. Ticket number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified ticket.
     */
    public function show(SupportTicket $ticket): Response
    {
        // Ensure user can only view their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Eager load relationships with proper error handling
        $ticket->loadMissing([
            'user' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar');
            },
            'assignedAdmin' => function ($query) {
                $query->select('id', 'name', 'email');
            },
            'responses' => function ($q) {
                $q->where('is_internal_note', false)
                    ->with(['user' => function ($userQuery) {
                        $userQuery->select('id', 'name', 'email');
                    }])
                    ->orderBy('created_at');
            },
        ]);

        return Inertia::render('Support/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Add a response to a ticket.
     */
    public function addResponse(Request $request, SupportTicket $ticket)
    {
        // Ensure user can only respond to their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Can't respond to closed tickets
        if ($ticket->isClosed()) {
            return back()->withErrors(['error' => 'Cannot respond to a closed ticket.']);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpeg,png,gif,webp,pdf,txt,doc,docx',
        ]);

        // Validate files
        if ($request->hasFile('attachments')) {
            $fileErrors = $this->supportTicketService->validateFiles($request->file('attachments'));
            if (!empty($fileErrors)) {
                return back()->withErrors(['attachments' => $fileErrors])->withInput();
            }
        }

        $response = SupportTicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin_response' => false,
            'is_internal_note' => false,
        ]);

        // Store attachments
        if ($request->hasFile('attachments')) {
            $attachmentPaths = $this->supportTicketService->storeResponseAttachments(
                $request->file('attachments'),
                $ticket->id,
                $response->id
            );
            $response->update(['attachments' => $attachmentPaths]);
        }

        // If ticket was resolved, reopen it when user responds
        if ($ticket->isResolved()) {
            $ticket->reopen();
        }

        // Notify admins about new response
        $this->notificationService->notifySupportTicketResponse($ticket, $response);

        // Reload ticket with fresh data
        $ticket->refresh();
        $ticket->loadMissing([
            'user' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar');
            },
            'assignedAdmin' => function ($query) {
                $query->select('id', 'name', 'email');
            },
            'responses' => function ($q) {
                $q->where('is_internal_note', false)
                    ->with(['user' => function ($userQuery) {
                        $userQuery->select('id', 'name', 'email');
                    }])
                    ->orderBy('created_at');
            },
        ]);

        return redirect()->route('support.tickets.show', $ticket->id)
            ->with('success', 'Response added successfully.');
    }
}
