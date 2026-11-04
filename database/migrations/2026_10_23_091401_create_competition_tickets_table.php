<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_tickets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('competition_id');
            $table->string('ticket_number', 50);

            $table->enum('status', [
                'available',
                'assigned',
                'unavailable',
            ])->default('available');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();

            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();

            $table->unique([
                'competition_id',
                'ticket_number',
            ]);

            $table->index('user_id');
            $table->index('order_id');
            $table->index([
                'competition_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_tickets');
    }
};
