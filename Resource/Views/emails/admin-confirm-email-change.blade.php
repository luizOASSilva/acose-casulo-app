public function requestEmailChange(Request $request, Admin $admin): JsonResponse
{
    $master = $request->user();

    if (!$master || $master->role !== 'master') {
        return response()->json([
            'message' => 'Apenas master pode solicitar alteração de e-mail.',
        ], 403);
    }

    $validated = $request->validate([
        'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
    ]);

    $newEmail = strtolower(trim($validated['email']));
    $oldEmail = strtolower(trim($admin->email));

    if ($newEmail === $oldEmail) {
        return response()->json([
            'message' => 'O novo e-mail é igual ao e-mail atual.',
        ], 422);
    }

    $plainToken = Str::random(64);
    $tokenHash = hash('sha256', $plainToken);

    $changeRequest = DB::transaction(function () use ($admin, $master, $oldEmail, $newEmail, $tokenHash) {
        AdminEmailChangeRequest::query()
            ->where('target_admin_id', $admin->id)
            ->whereNull('confirmed_at')
            ->delete();

        return AdminEmailChangeRequest::create([
            'target_admin_id' => $admin->id,
            'requested_by_admin_id' => $master->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(30),
        ]);
    });

    $confirmationUrl = config('app.frontend_url', config('app.url'))
        . '/admin/configuracoes/confirmar-email-admin?token='
        . urlencode($plainToken);

    Mail::to($master->email)->send(
        new ConfirmAdminEmailChangeMail(
            $changeRequest,
            $admin,
            $master,
            $confirmationUrl
        )
    );

    return response()->json([
        'message' => 'Enviamos um e-mail de confirmação para o master. O e-mail do administrador só será alterado após a confirmação.',
    ]);
}

public function confirmEmailChange(Request $request): JsonResponse
{
    $validated = $request->validate([
        'token' => ['required', 'string'],
    ]);

    $tokenHash = hash('sha256', $validated['token']);

    $changeRequest = AdminEmailChangeRequest::query()
        ->with(['targetAdmin', 'requestedByAdmin'])
        ->where('token_hash', $tokenHash)
        ->first();

    if (!$changeRequest) {
        return response()->json([
            'message' => 'Token inválido.',
        ], 404);
    }

    if ($changeRequest->isConfirmed()) {
        return response()->json([
            'message' => 'Essa alteração já foi confirmada.',
        ], 422);
    }

    if ($changeRequest->isExpired()) {
        return response()->json([
            'message' => 'Esse link expirou. Solicite uma nova alteração.',
        ], 422);
    }

    $targetAdmin = $changeRequest->targetAdmin;

    if (!$targetAdmin) {
        return response()->json([
            'message' => 'Administrador não encontrado.',
        ], 404);
    }

    if (
        Admin::query()
            ->where('email', $changeRequest->new_email)
            ->where('id', '!=', $targetAdmin->id)
            ->exists()
    ) {
        return response()->json([
            'message' => 'Este e-mail já está em uso por outro administrador.',
        ], 422);
    }

    DB::transaction(function () use ($targetAdmin, $changeRequest) {
        $targetAdmin->forceFill([
            'email' => $changeRequest->new_email,
        ])->save();

        $changeRequest->forceFill([
            'confirmed_at' => now(),
        ])->save();
    });

    return response()->json([
        'message' => 'E-mail do administrador alterado com sucesso.',
    ]);
}
