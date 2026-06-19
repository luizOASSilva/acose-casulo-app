<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('keyword_id')
                ->constrained('keywords')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');
            $table->string('word');

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['keyword_id', 'locale']);
            $table->unique(['locale', 'word']);
            $table->index(['locale', 'translation_status']);
        });

        $keywords = DB::table('keywords')
            ->orderBy('id')
            ->get();

        foreach ($keywords as $keyword) {
            DB::table('keyword_translations')->insert([
                'keyword_id' => $keyword->id,
                'locale' => 'pt-BR',
                'word' => $keyword->word,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $keyword->created_at ?? now(),
                'updated_at' => $keyword->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_translations');
    }
};
