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
        Schema::create('transfer_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('station_id');
            $table->decimal('amount', 12, 2);
            $table->string('status');
            $table->timestamp('created_at');

            $table->index(['station_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_events');
    }
};
