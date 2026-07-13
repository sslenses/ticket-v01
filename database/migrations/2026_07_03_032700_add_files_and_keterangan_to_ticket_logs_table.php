<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->string('fab_file')->nullable();
            $table->string('ba_file')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropColumn(['fab_file', 'ba_file', 'keterangan']);
        });
    }
};
