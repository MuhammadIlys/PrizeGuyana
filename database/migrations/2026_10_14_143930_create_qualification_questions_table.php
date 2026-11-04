
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualification_questions', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('question');

            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();

            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualification_questions');
    }
};
