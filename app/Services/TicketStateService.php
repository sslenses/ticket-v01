<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Support\Facades\Auth;

class TicketStateService
{
    /**
     * Transition the ticket to a new state and log the change.
     *
     * @param Ticket $ticket
     * @param string $toState
     * @return void
     */
    public function transition(Ticket $ticket, string $toState): void
    {
        $fromState = $ticket->status;

        $ticket->update([
            'status' => $toState,
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'from_state' => $fromState,
            'to_state' => $toState,
        ]);
    }
}
