<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_category_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('document_category_id')
                ->constrained('document_categories')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['document_category_id', 'locale'], 'doc_cat_translation_unique');
            $table->index(['locale', 'translation_status'], 'doc_cat_translation_locale_status_idx');
        });

        $categories = DB::table('document_categories')
            ->orderBy('id')
            ->get();

        foreach ($categories as $category) {
            DB::table('document_category_translations')->insert([
                'document_category_id' => $category->id,
                'locale' => 'pt-BR',
                'name' => $category->name,
                'description' => $category->description,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $category->created_at ?? now(),
                'updated_at' => $category->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_category_translations');
    }
};
