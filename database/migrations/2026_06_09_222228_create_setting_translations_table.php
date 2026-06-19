<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_translations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('setting_id')
                ->constrained('settings')
                ->cascadeOnDelete();

            $table->string('locale', 10)->default('pt-BR');
            $table->text('value')->nullable();

            $table->string('translation_status')->default('original');
            $table->timestamp('translated_at')->nullable();

            $table->timestamps();

            $table->unique(['setting_id', 'locale']);
            $table->index(['locale', 'translation_status']);
        });

        $settings = DB::table('settings')
            ->where('is_public', true)
            ->orderBy('id')
            ->get();

        $nonTranslatableKeys = [
            'site_logo_url',
            'site_footer_logo_url',
            'site_og_image_url',
            'og_image_url',

            'contact_email',
            'contact_phone',
            'contact_whatsapp',

            'google_maps_embed_url',
            'google_maps_url',

            'facebook_url',
            'instagram_url',
            'youtube_url',

            'donation_enabled',
        ];

        foreach ($settings as $setting) {
            if (in_array($setting->key, $nonTranslatableKeys, true)) {
                continue;
            }

            if (! in_array($setting->type, ['text', 'textarea'], true)) {
                continue;
            }

            DB::table('setting_translations')->insert([
                'setting_id' => $setting->id,
                'locale' => 'pt-BR',
                'value' => $setting->value,
                'translation_status' => 'original',
                'translated_at' => null,
                'created_at' => $setting->created_at ?? now(),
                'updated_at' => $setting->updated_at ?? now(),
            ]);
        }

        Setting::clearCache();
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_translations');

        Setting::clearCache();
    }
};
