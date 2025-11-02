<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
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
        $tickets = auth()->user()->supportTickets()
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

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
            'links' => $linksArray,
        ]);

        // Notify admin users about new ticket
        $admins = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'ticket_new',
                '🎫 New Support Ticket',
                auth()->user()->name . ' created a new support ticket: ' . $ticket->title,
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

        $supportTicket->load(['user', 'assignedAdmin', 'closedByUser']);

        return view('support-tickets.show', compact('supportTicket'));
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
