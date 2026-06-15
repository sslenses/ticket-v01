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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->string('source_device');
            $table->string('destination_device');
            $table->uuid('source_tenant_id');
            $table->uuid('destination_tenant_id');
            $table->string('connector_type');
            $table->jsonb('cable_details')->nullable();
            $table->string('status')->default('waiting_destination');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
