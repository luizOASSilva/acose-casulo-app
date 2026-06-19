<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('media_id')
                ->constrained('media')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');

            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['media_id', 'locale']);
            $table->index(['locale', 'translation_status']);
        });

        $mediaItems = DB::table('media')
            ->orderBy('id')
            ->get();

        foreach ($mediaItems as $media) {
            DB::table('media_translations')->insert([
                'media_id' => $media->id,
                'locale' => 'pt-BR',
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $media->created_at ?? now(),
                'updated_at' => $media->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_translations');
    }
};
