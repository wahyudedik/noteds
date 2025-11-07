<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $tickets = SupportTicket::with(['user', 'assignedAdmin'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->priority, function ($query) use ($request) {
                return $query->where('priority', $request->priority);
            })
            ->when($request->premium_only, function ($query) {
                return $query->whereHas('user', function ($q) {
                    $q->where('premium_expires_at', '>', now());
                });
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            })
            ->orderByRaw("CASE 
                WHEN EXISTS (SELECT 1 FROM users WHERE users.id = support_tickets.user_id AND users.premium_expires_at > NOW()) THEN 0 
                ELSE 1 
            END")
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'assignedAdmin', 'closedByUser', 'replies.user']);
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        return view('admin.tickets.show', compact('ticket', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $ticket->update($validated);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Assign ticket to admin.
     */
    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully!');
    }

    /**
     * Admin reply to ticket.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if ($ticket->status === 'closed') {
            return redirect()->route('admin.tickets.show', $ticket)
                ->with('error', 'You cannot reply to a closed ticket.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10'],
        ]);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => true,
        ]);

        // Update ticket status to in_progress if it's open
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Notify user about admin reply
        $this->notificationService->create(
            $ticket->user,
            'ticket_reply',
            '💬 New Reply on Your Ticket',
            auth()->user()->name . ' replied to your ticket: ' . $ticket->title,
            route('support-tickets.show', $ticket),
            ['ticket_id' => $ticket->id]
        );

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Reply sent successfully!');
    }
}
