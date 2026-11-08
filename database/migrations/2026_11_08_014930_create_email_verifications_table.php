<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();

            // Temporary registration details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');

            $table->string('country', 100);
            $table->date('date_of_birth');
            $table->string('phone_number', 30)->nullable();

            // OTP verification
            $table->string('otp_hash');
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
