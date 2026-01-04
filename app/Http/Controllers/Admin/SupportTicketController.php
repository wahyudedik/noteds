<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use App\Models\User;
use App\Services\SupportTicketService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function __construct(
        private SupportTicketService $supportTicketService,
        private NotificationService $notificationService
    ) {
        // Middleware is applied in routes/web.php via admin middleware group
    }

    /**
     * Display a listing of all tickets.
     */
    public function index(Request $request): Response
    {
        $query = SupportTicket::with([
            'user:id,name,email',
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

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned admin
        if ($request->has('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                  ->orWhere('message', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $tickets = $query->latest()->paginate(20);

        // Get unique categories for filter
        $categories = SupportTicket::distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        // Get admin users for assignment filter
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Admin/SupportTickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $request->status ?? 'all',
                'category' => $request->category ?? '',
                'priority' => $request->priority ?? 'all',
                'assigned_to' => $request->assigned_to ?? 'all',
                'search' => $request->search ?? '',
            ],
            'categories' => $categories,
            'admins' => $admins,
        ]);
    }

    /**
     * Display the specified ticket.
     */
    public function show(SupportTicket $ticket): Response
    {
        // Eager load relationships with proper error handling
        $ticket->loadMissing([
            'user' => function ($query) {
                $query->select('id', 'name', 'email', 'avatar');
            },
            'assignedAdmin' => function ($query) {
                $query->select('id', 'name', 'email');
            },
            'responses' => function ($q) {
                $q->with(['user' => function ($userQuery) {
                    $userQuery->select('id', 'name', 'email');
                }])->orderBy('created_at');
            },
        ]);

        // Get admin users for assignment
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Admin/SupportTickets/Show', [
            'ticket' => $ticket,
            'admins' => $admins,
        ]);
    }

    /**
     * Assign ticket to an admin.
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $admin = User::findOrFail($validated['assigned_to']);
        
        if (!$admin->isAdmin()) {
            return back()->withErrors(['error' => 'User must be an admin.']);
        }

        $oldStatus = $ticket->status;
        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);

        // Notify about status change if status was updated
        if ($oldStatus !== 'in_progress') {
            $this->notificationService->notifySupportTicketStatusUpdate($ticket, $oldStatus, 'in_progress');
        }

        return redirect()->route('admin.support-tickets.show', $ticket->id)
            ->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        $status = $validated['status'];

        switch ($status) {
            case 'in_progress':
                $ticket->markAsInProgress(auth()->id());
                break;
            case 'resolved':
                $ticket->markAsResolved(auth()->id());
                break;
            case 'closed':
                $ticket->markAsClosed(auth()->id());
                break;
            case 'open':
                $ticket->reopen();
                break;
        }

        // Notify user and admin about status change
        if ($oldStatus !== $status) {
            $this->notificationService->notifySupportTicketStatusUpdate($ticket, $oldStatus, $status);
        }

        return redirect()->route('admin.support-tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return redirect()->route('admin.support-tickets.show', $ticket->id)
            ->with('success', 'Ticket priority updated successfully.');
    }

    /**
     * Add admin response to ticket.
     */
    public function addResponse(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpeg,png,gif,webp,pdf,txt,doc,docx',
            'is_internal_note' => 'nullable|boolean',
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
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_admin_response' => true,
            'is_internal_note' => $validated['is_internal_note'] ?? false,
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

        // Auto-assign if not assigned
        if (!$ticket->assigned_to) {
            $ticket->update(['assigned_to' => auth()->id()]);
        }

        // Update status to in_progress if it's open
        $oldStatus = $ticket->status;
        if ($ticket->status === 'open') {
            $ticket->markAsInProgress(auth()->id());
        }

        // Notify user about admin response
        $this->notificationService->notifySupportTicketResponse($ticket, $response);

        // Notify about status change if status was updated
        if ($oldStatus !== $ticket->status) {
            $this->notificationService->notifySupportTicketStatusUpdate($ticket, $oldStatus, $ticket->status);
        }

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
                $q->with(['user' => function ($userQuery) {
                    $userQuery->select('id', 'name', 'email');
                }])->orderBy('created_at');
            },
        ]);

        return redirect()->route('admin.support-tickets.show', $ticket->id)
            ->with('success', 'Response added successfully.');
    }

    /**
     * Delete a ticket.
     */
    public function destroy(SupportTicket $ticket)
    {
        // Delete attachments
        if ($ticket->attachments) {
            $this->supportTicketService->deleteAttachments($ticket->attachments);
        }

        // Delete response attachments
        foreach ($ticket->responses as $response) {
            if ($response->attachments) {
                $this->supportTicketService->deleteAttachments($response->attachments);
            }
        }

        $ticket->delete();

        return redirect()->route('admin.support-tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }
}
