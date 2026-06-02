<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'branding',
                'key' => 'site_logo_url',
                'label' => 'Logo do site',
                'description' => 'URL ou caminho público da logo exibida no site.',
                'type' => 'url',
                'value' => '/logo.svg',
                'is_public' => true,
                'sort_order' => 1,
            ],

            [
                'group' => 'contact',
                'key' => 'contact_email',
                'label' => 'E-mail de contato',
                'description' => 'E-mail público da instituição.',
                'type' => 'email',
                'value' => 'luizotavioassilva@gmail.com',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_phone',
                'label' => 'Telefone',
                'description' => 'Telefone público da instituição.',
                'type' => 'text',
                'value' => '',
                'is_public' => true,
                'sort_order' => 11,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_whatsapp',
                'label' => 'WhatsApp',
                'description' => 'Número de WhatsApp para contato.',
                'type' => 'text',
                'value' => '',
                'is_public' => true,
                'sort_order' => 12,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_address',
                'label' => 'Endereço',
                'description' => 'Endereço físico exibido no site.',
                'type' => 'textarea',
                'value' => "Rua Francisco Rodrigues Dias, 80\nUberaba — Bragança Paulista/SP\nCEP: 12908-843",
                'is_public' => true,
                'sort_order' => 13,
            ],
            [
                'group' => 'contact',
                'key' => 'business_hours',
                'label' => 'Horário de atendimento',
                'description' => 'Horário público de funcionamento.',
                'type' => 'textarea',
                'value' => 'Segunda a sexta, das 8h às 17h',
                'is_public' => true,
                'sort_order' => 14,
            ],
            [
                'group' => 'contact',
                'key' => 'google_maps_embed_url',
                'label' => 'Mapa incorporado',
                'description' => 'URL usada no iframe do Google Maps.',
                'type' => 'url',
                'value' => 'https://maps.google.com/maps?q=Rua+Francisco+Rodrigues+Dias,+80,+Bragança+Paulista,+SP&z=15&output=embed',
                'is_public' => true,
                'sort_order' => 15,
            ],
            [
                'group' => 'contact',
                'key' => 'google_maps_url',
                'label' => 'Link do Google Maps',
                'description' => 'URL pública para abrir a localização no Google Maps.',
                'type' => 'url',
                'value' => 'https://www.google.com/maps?q=Rua+Francisco+Rodrigues+Dias,+80,+Bragança+Paulista,+SP',
                'is_public' => true,
                'sort_order' => 16,
            ],
            [
                'group' => 'contact',
                'key' => 'location_title',
                'label' => 'Nome do local',
                'description' => 'Nome exibido na seção de localização.',
                'type' => 'text',
                'value' => 'Acose Casulo',
                'is_public' => true,
                'sort_order' => 17,
            ],

            [
                'group' => 'social',
                'key' => 'facebook_url',
                'label' => 'Facebook',
                'description' => 'URL da página no Facebook.',
                'type' => 'url',
                'value' => '',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'social',
                'key' => 'instagram_url',
                'label' => 'Instagram',
                'description' => 'URL do perfil no Instagram.',
                'type' => 'url',
                'value' => '',
                'is_public' => true,
                'sort_order' => 21,
            ],

            [
                'group' => 'donation',
                'key' => 'donation_enabled',
                'label' => 'Doações ativas',
                'description' => 'Define se o fluxo público de doações está ativo.',
                'type' => 'boolean',
                'value' => '1',
                'is_public' => true,
                'sort_order' => 30,
            ],
            [
                'group' => 'donation',
                'key' => 'donation_message',
                'label' => 'Mensagem de doação',
                'description' => 'Texto exibido na área pública de doações.',
                'type' => 'textarea',
                'value' => 'Sua doação ajuda a manter as atividades, o acolhimento e o cuidado oferecidos pela Acose Casulo.',
                'is_public' => true,
                'sort_order' => 31,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        Setting::clearCache();
    }
}
