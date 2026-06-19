<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('publication_id')
                ->constrained('publications')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');

            $table->string('title');
            $table->string('slug');
            $table->longText('content');
            $table->string('summary', 160)->nullable();

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['publication_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'translation_status']);
        });

        $publications = DB::table('publications')
            ->leftJoin('articles', 'articles.publication_id', '=', 'publications.id')
            ->select([
                'publications.id',
                'publications.title',
                'publications.slug',
                'publications.content',
                'articles.summary',
                'publications.created_at',
                'publications.updated_at',
            ])
            ->orderBy('publications.id')
            ->get();

        foreach ($publications as $publication) {
            DB::table('publication_translations')->insert([
                'publication_id' => $publication->id,
                'locale' => 'pt-BR',
                'title' => $publication->title,
                'slug' => $publication->slug,
                'content' => $publication->content,
                'summary' => $publication->summary,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $publication->created_at ?? now(),
                'updated_at' => $publication->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_translations');
    }
};
