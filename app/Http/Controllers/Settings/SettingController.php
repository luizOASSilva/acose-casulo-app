<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Mail\ConfirmAdminEmailChangeMail;
use App\Models\Admin;
use App\Models\AdminEmailChangeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function public(): JsonResponse
    {
        return response()->json([
            'data' => Setting::publicCached(),
        ]);
    }

    public function index()
    {
        return SettingResource::collection(
            Setting::adminCached()
        );
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        foreach ($validated['settings'] as $item) {
            Setting::query()
                ->where('key', $item['key'])
                ->update([
                    'value' => $item['value'] ?? null,
                ]);
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
