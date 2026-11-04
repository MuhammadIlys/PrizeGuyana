
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_competitions', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('internal_reference')->nullable()->unique();

            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('competition_categories')
                ->nullOnDelete();

            $table->boolean('is_featured')->default(false);

            $table->string('main_image')->nullable();
            $table->string('video_path')->nullable();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('draw_at')->nullable();

            $table->decimal('entry_price', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            $table->unsignedInteger('total_entries');
            $table->unsignedInteger('max_entries_per_customer')->nullable();

            $table->string('entry_allocation_method')->default('random');

            $table->boolean('qualification_enabled')->default(false);
            $table->boolean('instant_win_enabled')->default(false);

            $table->string('draw_method')->nullable();

            $table->unsignedTinyInteger('minimum_age')->default(18);

            $table->foreignId('terms_id')
                ->nullable()
                ->constrained('competition_terms_versions')
                ->nullOnDelete();

            $table->string('status')->default('draft');
            $table->dateTime('published_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_competitions');
    }
};
