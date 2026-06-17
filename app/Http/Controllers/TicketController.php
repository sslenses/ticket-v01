<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Services\TicketStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    protected TicketStateService $stateService;

    /**
     * Create a new controller instance.
     */
    public function __construct(TicketStateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * Display a listing of tickets.
     */
    public function index()
    {
        $tickets = Ticket::latest()->get();
        return view('dashboard', compact('tickets'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        Gate::authorize('create', Ticket::class);

        $ticket = Ticket::create($request->validated() + [
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load('logs.user');
        
        $isPublic = !auth()->check();

        if ($isPublic && in_array($ticket->status, [Ticket::STATUS_DONE, Ticket::STATUS_CANCELLED])) {
            abort(403, 'Access to this ticket is restricted. Completed or cancelled tickets are not viewable publicly.');
        }

        return view('ticket-detail', compact('ticket', 'isPublic'));
    }

    /**
     * Update the status of the ticket.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $status = $request->input('status');
        
        $statusToPolicyMap = [
            Ticket::STATUS_APPROVED_DESTINATION => 'approveDestination',
            Ticket::STATUS_APPROVED_ADMIN       => 'approveAdmin',
            Ticket::STATUS_SENDED_CABLE         => 'sendCable',
            Ticket::STATUS_RECEIVED_CABLE       => 'receiveCable',
            Ticket::STATUS_DONE                 => 'markDone',
            Ticket::STATUS_CANCELLED            => 'cancel',
        ];

        $policyMethod = $statusToPolicyMap[$status] ?? null;

        if (!$policyMethod || !method_exists(\App\Policies\TicketPolicy::class, $policyMethod)) {
            return response()->json(['message' => "Invalid transition action for status '{$status}'."], 422);
        }

        Gate::authorize($policyMethod, $ticket);

        try {
            $this->stateService->transition($ticket, $status);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($ticket);
    }

    /**
     * Update the specified ticket details.
     */
    public function update(Request $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'label' => 'required|string|unique:tickets,label,' . $ticket->id,
            'source_device' => 'required|string',
            'destination_device' => 'required|string',
            'source_tenant_id' => 'required|uuid',
            'destination_tenant_id' => 'required|uuid',
            'connector_type' => 'required|string',
            'cable_details' => 'nullable|array',
        ]);

        $ticket->update($validated);

        return response()->json($ticket);
    }
}
