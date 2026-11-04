<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_terms_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')
                ->constrained('my_competitions')
                ->cascadeOnDelete();

            $table->string('version', 50);
            $table->string('title');
            $table->longText('content');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'competition_id',
                'version',
            ]);

            $table->index([
                'competition_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_terms_versions');
    }
};