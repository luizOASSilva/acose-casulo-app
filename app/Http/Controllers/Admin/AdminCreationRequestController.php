<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCreationTokenRequest;
use App\Http\Requests\Admin\StoreAdminCreationRequest;
use App\Mail\AdminCreatedInvitationMail;
use App\Mail\ConfirmAdminCreationMail;
use App\Models\Admin;
use App\Models\AdminCreationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminCreationRequestController extends Controller
{
    public function request(StoreAdminCreationRequest $request): JsonResponse
    {
        $master = $request->user();
        $validated = $request->validated();

        $email = strtolower(trim($validated['email']));

        $pendingExists = AdminCreationRequest::query()
            ->where('email', $email)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingExists) {
            return response()->json([
                'message' => 'Já existe uma solicitação pendente para este e-mail.',
            ], 422);
        }

        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $creationRequest = AdminCreationRequest::create([
            'requested_by_admin_id' => $master->id,
            'name' => trim($validated['name']),
            'email' => $email,
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(30),
        ]);

        $confirmationUrl = config('app.frontend_url', config('app.url'))
            . '/admin/configuracoes/confirmar-criacao-admin?token='
            . urlencode($plainToken);

        Mail::to($master->email)->send(
            new ConfirmAdminCreationMail(
                creationRequest: $creationRequest,
                masterAdmin: $master,
                confirmationUrl: $confirmationUrl
            )
        );

        return response()->json([
            'message' => 'Enviamos um e-mail para o master confirmar a criação deste usuário. O novo administrador ainda não tem acesso ao painel.',
        ]);
    }

    public function show(AdminCreationTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $creationRequest = $this->findByToken($validated['token']);

        if (!$creationRequest) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 404);
        }

        if ($creationRequest->isConfirmed()) {
            return response()->json([
                'message' => 'Essa solicitação já foi confirmada.',
            ], 422);
        }

        if ($creationRequest->isExpired()) {
            return response()->json([
                'message' => 'Esse link expirou. Solicite a criação novamente.',
            ], 422);
        }

        return response()->json([
            'data' => [
                'name' => $creationRequest->name,
                'email' => $creationRequest->email,
                'role' => $creationRequest->role,
                'is_active' => $creationRequest->is_active,
                'expires_at' => $creationRequest->expires_at?->toISOString(),
                'requested_by' => [
                    'name' => $creationRequest->requestedByAdmin?->name,
                    'email' => $creationRequest->requestedByAdmin?->email,
                ],
            ],
        ]);
    }

    public function confirm(AdminCreationTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $creationRequest = $this->findByToken($validated['token']);

        if (!$creationRequest) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 404);
        }

        if ($creationRequest->isConfirmed()) {
            return response()->json([
                'message' => 'Essa solicitação já foi confirmada.',
            ], 422);
        }

        if ($creationRequest->isExpired()) {
            return response()->json([
                'message' => 'Esse link expirou. Solicite a criação novamente.',
            ], 422);
        }

        if (
            Admin::query()
                ->where('email', $creationRequest->email)
                ->exists()
        ) {
            return response()->json([
                'message' => 'Este e-mail já está em uso por outro administrador.',
            ], 422);
        }

        $admin = DB::transaction(function () use ($creationRequest) {
            $admin = Admin::create([
                'name' => $creationRequest->name,
                'email' => $creationRequest->email,
                'role' => $creationRequest->role,
                'is_active' => $creationRequest->is_active,
                'password' => Hash::make(Str::random(64)),
            ]);

            $creationRequest->forceFill([
                'confirmed_at' => now(),
            ])->save();

            return $admin;
        });

        Mail::to($admin->email)->send(
            new AdminCreatedInvitationMail($admin)
        );

        return response()->json([
            'message' => 'Administrador criado com sucesso. Enviamos um e-mail para o novo usuário com instruções para criar a senha.',
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'is_active' => $admin->is_active,
            ],
        ]);
    }

    private function findByToken(string $token): ?AdminCreationRequest
    {
        return AdminCreationRequest::query()
            ->with('requestedByAdmin')
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }
}
