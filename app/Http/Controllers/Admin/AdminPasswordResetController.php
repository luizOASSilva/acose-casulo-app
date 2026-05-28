<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ForgotAdminPasswordRequest;
use App\Http\Requests\Admin\ResetAdminPasswordRequest;
use App\Mail\AdminResetPasswordMail;
use App\Models\Admin;
use App\Models\AdminPasswordResetToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminPasswordResetController extends Controller
{
    public function forgot(ForgotAdminPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $email = strtolower(trim($validated['email']));

        $admin = Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$admin || !$admin->is_active) {
            return response()->json([
                'message' => 'Se este e-mail estiver cadastrado, enviaremos um link para redefinir a senha.',
            ]);
        }

        $panelSlug = config('app.panel_slug');

        if (!$panelSlug) {
            return response()->json([
                'message' => 'Não foi possível gerar o link de redefinição agora.',
            ], 500);
        }

        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        DB::transaction(function () use ($admin, $email, $tokenHash) {
            AdminPasswordResetToken::query()
                ->where('admin_id', $admin->id)
                ->whereNull('used_at')
                ->delete();

            AdminPasswordResetToken::create([
                'admin_id' => $admin->id,
                'email' => $email,
                'token_hash' => $tokenHash,
                'expires_at' => now()->addMinutes(30),
            ]);
        });

        $resetUrl = rtrim(config('app.frontend_url', config('app.url')), '/')
            . '/acesso/'
            . urlencode((string) $panelSlug)
            . '/redefinir-senha?token='
            . urlencode($plainToken);

        Mail::to($admin->email)->send(
            new AdminResetPasswordMail(
                admin: $admin,
                resetUrl: $resetUrl
            )
        );

        return response()->json([
            'message' => 'Se este e-mail estiver cadastrado, enviaremos um link para redefinir a senha.',
        ]);
    }

    public function reset(ResetAdminPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tokenHash = hash('sha256', $validated['token']);

        $resetToken = AdminPasswordResetToken::query()
            ->with('admin')
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 404);
        }

        if ($resetToken->isUsed()) {
            return response()->json([
                'message' => 'Este link já foi utilizado.',
            ], 422);
        }

        if ($resetToken->isExpired()) {
            return response()->json([
                'message' => 'Este link expirou. Solicite uma nova redefinição de senha.',
            ], 422);
        }

        $admin = $resetToken->admin;

        if (!$admin) {
            return response()->json([
                'message' => 'Administrador não encontrado.',
            ], 404);
        }

        if (!$admin->is_active) {
            return response()->json([
                'message' => 'Este usuário está inativo.',
            ], 403);
        }

        DB::transaction(function () use ($admin, $resetToken, $validated) {
            $admin->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();

            $resetToken->forceFill([
                'used_at' => now(),
            ])->save();

            AdminPasswordResetToken::query()
                ->where('admin_id', $admin->id)
                ->whereNull('used_at')
                ->where('id', '!=', $resetToken->id)
                ->delete();
        });

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ]);
    }
}
