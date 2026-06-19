<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('partner_id')
                ->constrained('partners')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');

            $table->string('logo_alt')->nullable();

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['partner_id', 'locale']);
            $table->index(['locale', 'translation_status']);
        });

        $partners = DB::table('partners')
            ->orderBy('id')
            ->get();

        foreach ($partners as $partner) {
            DB::table('partner_translations')->insert([
                'partner_id' => $partner->id,
                'locale' => 'pt-BR',
                'logo_alt' => $partner->logo_alt,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $partner->created_at ?? now(),
                'updated_at' => $partner->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_translations');
    }
};
