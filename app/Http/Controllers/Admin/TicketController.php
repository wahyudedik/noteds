<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
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
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'assignedAdmin', 'closedByUser']);
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
     * Admin response to ticket.
     */
    public function response(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'min:10'],
        ]);

        $ticket->update([
            'admin_response' => $request->admin_response,
            'status' => 'in_progress',
        ]);

        // Notify user about admin response
        $this->notificationService->notifyTicketResponse($ticket->user, $ticket->title);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Response sent successfully!');
    }
}
