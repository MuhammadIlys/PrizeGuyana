
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->string('provider', 30)->default('stripe');

            $table->string('stripe_checkout_session_id')
                ->nullable()
                ->unique();

            $table->string('stripe_payment_intent_id')
                ->nullable()
                ->unique();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');

            $table->string('status', 30)->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
