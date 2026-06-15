<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_id',
    'user_id',
    'from_state',
    'to_state',
])]
class TicketLog extends Model
{
    /**
     * Get the ticket that owns the log.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that created the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
