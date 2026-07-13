<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
        });

        // Seed UUIDs for existing tickets
        foreach (\Illuminate\Support\Facades\DB::table('tickets')->get() as $ticket) {
            \Illuminate\Support\Facades\DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
