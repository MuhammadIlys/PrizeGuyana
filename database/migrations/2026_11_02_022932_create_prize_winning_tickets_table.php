<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prize_winning_tickets', function (Blueprint $table) {
            $table->id();

            // Competition reference
            $table->foreignId('competition_id')
                ->constrained('my_competitions')
                ->restrictOnDelete();

            // Prize reference
            $table->foreignId('prize_id')
                ->constrained('competition_prizes')
                ->restrictOnDelete();

            // Winning ticket reference
            // A ticket can win only one prize across the entire competition.
            $table->foreignId('ticket_id')
                ->unique()
                ->constrained('competition_tickets')
                ->restrictOnDelete();

            // Winning method
            $table->enum('winning_method', [
                'instant_win',
                'draw',
            ]);

            // Winning status
            $table->enum('status', [
                'pending',
                'won',
                'claimed',
                'fulfilled',
                'cancelled',
            ])->default('pending');

            // Winner information
            $table->foreignId('winner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->restrictOnDelete();

            // Winning and fulfillment timestamps
            $table->timestamp('won_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();

            // Admin notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['competition_id', 'winning_method']);
            $table->index(['competition_id', 'status']);
            $table->index('prize_id');
            $table->index('winner_user_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prize_winning_tickets');
    }
};
