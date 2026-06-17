<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('staff') || $user->hasRole('dest_manager') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can approve destination.
     */
    public function approveDestination(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('dest_manager') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can approve admin.
     */
    public function approveAdmin(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can send cable.
     */
    public function sendCable(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can receive cable.
     */
    public function receiveCable(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can mark as done.
     */
    public function markDone(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }
}
