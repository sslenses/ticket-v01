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
     * @throws \InvalidArgumentException
     */
    public function transition(Ticket $ticket, string $toState): void
    {
        $fromState = $ticket->status;

        $validTransitions = [
            Ticket::STATUS_WAITING_DESTINATION => Ticket::STATUS_APPROVED_DESTINATION,
            Ticket::STATUS_APPROVED_DESTINATION => Ticket::STATUS_APPROVED_ADMIN,
            Ticket::STATUS_APPROVED_ADMIN => Ticket::STATUS_SENDED_CABLE,
            Ticket::STATUS_SENDED_CABLE => Ticket::STATUS_RECEIVED_CABLE,
            Ticket::STATUS_RECEIVED_CABLE => Ticket::STATUS_DONE,
        ];

        if (!isset($validTransitions[$fromState]) || $validTransitions[$fromState] !== $toState) {
            throw new \InvalidArgumentException("Invalid state transition from '{$fromState}' to '{$toState}'.");
        }

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
