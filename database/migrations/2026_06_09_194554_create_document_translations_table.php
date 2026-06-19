<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('document_id')
                ->constrained('documents')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');

            $table->string('title');

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['document_id', 'locale']);
            $table->index(['locale', 'translation_status']);
        });

        $documents = DB::table('documents')
            ->orderBy('id')
            ->get();

        foreach ($documents as $document) {
            DB::table('document_translations')->insert([
                'document_id' => $document->id,
                'locale' => 'pt-BR',
                'title' => $document->title,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $document->created_at ?? now(),
                'updated_at' => $document->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_translations');
    }
};
