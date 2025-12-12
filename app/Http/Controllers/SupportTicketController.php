<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Optimize: Add eager loading to prevent N+1 queries
        $tickets = auth()->user()->supportTickets()
            ->with(['user', 'replies.user', 'attachments'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->priority, function ($query) use ($request) {
                return $query->where('priority', $request->priority);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('support-tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('support-tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'links' => ['nullable', 'string', 'max:1000'],
        ]);

        // Parse links if provided (comma-separated)
        $linksArray = [];
        if ($request->links) {
            $linksArray = array_filter(array_map('trim', explode(',', $request->links)));
        }

        // Auto-upgrade priority for premium buyers
        $priority = $request->priority;
        if (auth()->user()->hasPremium()) {
            // Upgrade priority: low -> medium, medium -> high, high -> urgent, urgent stays urgent
            $priorityMap = [
                'low' => 'medium',
                'medium' => 'high',
                'high' => 'urgent',
                'urgent' => 'urgent',
            ];
            $priority = $priorityMap[$priority] ?? $priority;
        }

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $priority,
            'status' => 'open',
            'links' => $linksArray,
        ]);

        // Notify admin users about new ticket
        $admins = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $isPremium = auth()->user()->hasPremium();
        $notificationTitle = $isPremium ? '⭐ Premium Support Ticket' : '🎫 New Support Ticket';
        $notificationMessage = auth()->user()->name . ($isPremium ? ' (Premium)' : '') . ' created a new support ticket: ' . $ticket->title;

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'ticket_new',
                $notificationTitle,
                $notificationMessage,
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }

        return redirect()->route('support-tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $supportTicket): View
    {
        // Ensure user owns this ticket
        if ($supportTicket->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $supportTicket->load(['user', 'assignedAdmin', 'closedByUser', 'replies.user']);

        return view('support-tickets.show', compact('supportTicket'));
    }

    /**
     * Store a reply to the ticket.
     */
    public function reply(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        // Ensure user owns this ticket or is admin
        if ($supportTicket->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // Don't allow replies to closed tickets
        if ($supportTicket->status === 'closed') {
            return redirect()->route('support-tickets.show', $supportTicket)
                ->with('error', 'You cannot reply to a closed ticket.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10'],
        ]);

        $isAdmin = auth()->user()->hasRole('admin');

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => $isAdmin,
        ]);

        // Update ticket status
        if (!$isAdmin && $supportTicket->status === 'resolved') {
            // User replied to resolved ticket, reopen it
            $supportTicket->update(['status' => 'open']);
        } elseif ($isAdmin && $supportTicket->status === 'open') {
            // Admin replied, mark as in progress
            $supportTicket->update(['status' => 'in_progress']);
        }

        // Notify the other party
        if ($isAdmin) {
            // Admin replied, notify user
            $this->notificationService->create(
                $supportTicket->user,
                'ticket_reply',
                '💬 New Reply on Your Ticket',
                auth()->user()->name . ' replied to your ticket: ' . $supportTicket->title,
                route('support-tickets.show', $supportTicket),
                ['ticket_id' => $supportTicket->id]
            );
        } else {
            // User replied, notify assigned admin or all admins
            if ($supportTicket->assigned_to) {
                $this->notificationService->create(
                    $supportTicket->assignedAdmin,
                    'ticket_reply',
                    '💬 New Reply on Ticket',
                    auth()->user()->name . ' replied to ticket: ' . $supportTicket->title,
                    route('admin.tickets.show', $supportTicket),
                    ['ticket_id' => $supportTicket->id]
                );
            } else {
                // Notify all admins
                $admins = \App\Models\User::whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })->get();

                foreach ($admins as $admin) {
                    $this->notificationService->create(
                        $admin,
                        'ticket_reply',
                        '💬 New Reply on Ticket',
                        auth()->user()->name . ' replied to ticket: ' . $supportTicket->title,
                        route('admin.tickets.show', $supportTicket),
                        ['ticket_id' => $supportTicket->id]
                    );
                }
            }
        }

        return redirect()->route('support-tickets.show', $supportTicket)
            ->with('success', 'Reply sent successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupportTicket $supportTicket): View
    {
        // Only allow editing of open tickets owned by user
        if ($supportTicket->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$supportTicket->isOpen()) {
            return redirect()->route('support-tickets.show', $supportTicket)
                ->with('error', 'You can only edit open tickets.');
        }

        return view('support-tickets.edit', compact('supportTicket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        // Only allow editing of open tickets owned by user
        if ($supportTicket->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$supportTicket->isOpen()) {
            return redirect()->route('support-tickets.show', $supportTicket)
                ->with('error', 'You can only edit open tickets.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'links' => ['nullable', 'string', 'max:1000'],
        ]);

        // Parse links if provided
        $linksArray = [];
        if ($request->links) {
            $linksArray = array_filter(array_map('trim', explode(',', $request->links)));
        }

        $supportTicket->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'links' => $linksArray,
        ]);

        return redirect()->route('support-tickets.show', $supportTicket)
            ->with('success', 'Support ticket updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupportTicket $supportTicket): RedirectResponse
    {
        // Only allow deletion of open tickets owned by user
        if ($supportTicket->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$supportTicket->isOpen()) {
            return redirect()->route('support-tickets.show', $supportTicket)
                ->with('error', 'You can only delete open tickets.');
        }

        $supportTicket->delete();

        return redirect()->route('support-tickets.index')
            ->with('success', 'Support ticket deleted successfully!');
    }
}
