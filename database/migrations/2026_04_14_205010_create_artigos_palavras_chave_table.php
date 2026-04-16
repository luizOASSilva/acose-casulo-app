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
        Schema::create('artigos_palavras_chave', function (Blueprint $table) {
            $table->foreignId('palavra_chave_id')->constrained('palavras_chave');
            $table->foreignId('artigo_id')->constrained('artigos');
            $table->primary(['palavras_chave_id', 'artigo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artigos_palavras_chave');
    }
};
