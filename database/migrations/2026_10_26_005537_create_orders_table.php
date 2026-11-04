
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('order_number', 50)->unique();

            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('pending');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('USD');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
