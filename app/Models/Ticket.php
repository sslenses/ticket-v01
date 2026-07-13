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

    /**
     * Get a nested value from cable_details array safely.
     */
    public function getCableDetail(string $key, $default = '')
    {
        return is_array($this->cable_details) ? ($this->cable_details[$key] ?? $default) : $default;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (!\Illuminate\Support\Str::isUuid($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
