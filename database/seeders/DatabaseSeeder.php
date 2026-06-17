<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $roles = [
            'admin' => 'admin@example.com',
            'dest_manager' => 'manager@example.com',
            'staff' => 'staff@example.com',
            'user' => 'user@example.com'
        ];

        foreach ($roles as $role => $email) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => ucfirst(str_replace('_', ' ', $role)) . ' User',
                    'password' => bcrypt('password'),
                    'role' => $role,
                ]
            );
        }

        \App\Models\Ticket::updateOrCreate(
            ['label' => 'TICKET-2026-001'],
            [
                'source_device' => 'Jkt-Core-Sw01',
                'destination_device' => 'Sg-Dist-Sw02',
                'source_tenant_id' => \Illuminate\Support\Str::uuid(),
                'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
                'connector_type' => 'LC-LC',
                'cable_details' => [
                    'length' => 15,
                    'color' => 'Yellow',
                    'type' => 'Single-Mode OS2'
                ],
                'status' => \App\Models\Ticket::STATUS_WAITING_DESTINATION,
            ]
        );
    }
}
