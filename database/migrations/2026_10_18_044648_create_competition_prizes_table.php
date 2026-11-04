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
        Schema::create('competition_prizes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('competition_id')->index();

            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('prize_role', ['main', 'small']);
            $table->string('prize_type', 50); // physical or cash
            $table->string('image_path')->nullable();

            $table->decimal('prize_value', 12, 2)->nullable();
            $table->char('currency', 3)->default('USD');

            $table->unsignedInteger('quantity')->default(1);

            $table->enum('winning_method', ['draw', 'instant_win']);
            $table->string('status', 20)->default('active');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_prizes');
    }
};
