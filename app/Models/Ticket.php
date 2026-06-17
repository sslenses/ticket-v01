<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'label',
    'source_device',
    'destination_device',
    'source_tenant_id',
    'destination_tenant_id',
    'connector_type',
    'cable_details',
    'status',
])]
class Ticket extends Model
{
    const STATUS_WAITING_DESTINATION = 'waiting_destination';
    const STATUS_APPROVED_DESTINATION = 'approved_destination';
    const STATUS_APPROVED_ADMIN = 'approved_admin';
    const STATUS_SENDED_CABLE = 'sended_cable';
    const STATUS_RECEIVED_CABLE = 'received_cable';
    const STATUS_DONE = 'done';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the logs for the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cable_details' => 'array',
        ];
    }
}
