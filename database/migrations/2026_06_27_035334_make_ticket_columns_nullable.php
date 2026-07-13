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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('source_device')->nullable()->change();
            $table->string('destination_device')->nullable()->change();
            $table->uuid('source_tenant_id')->nullable()->change();
            $table->uuid('destination_tenant_id')->nullable()->change();
            $table->string('connector_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('source_device')->nullable(false)->change();
            $table->string('destination_device')->nullable(false)->change();
            $table->uuid('source_tenant_id')->nullable(false)->change();
            $table->uuid('destination_tenant_id')->nullable(false)->change();
            $table->string('connector_type')->nullable(false)->change();
        });
    }
};
