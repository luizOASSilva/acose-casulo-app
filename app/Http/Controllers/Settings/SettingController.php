<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function public(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Setting::publicCached(),
        ]);
    }

    public function index(Request $request)
    {
        return SettingResource::collection(
            Setting::adminCached()
        );
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        foreach ($validated['settings'] as $item) {
            $setting = Setting::query()
                ->where('key', $item['key'])
                ->first();

            if (! $setting) {
                continue;
            }

            $setting->update([
                'value' => $item['value'] ?? null,
            ]);

            $setting->refresh();
        }

        Setting::clearCache();

        return response()->json([
            'message' => 'Configurações atualizadas com sucesso.',
            'data' => Setting::publicCached(),
        ]);
    }

    public function clearCache(Request $request): JsonResponse
    {
        abort_unless(
            $request->user('admin')?->role === 'master',
            403,
            'Apenas usuários master podem limpar o cache.'
        );

        Setting::clearCache();

        return response()->json([
            'message' => 'Cache de configurações limpo com sucesso.',
        ]);
    }
}
