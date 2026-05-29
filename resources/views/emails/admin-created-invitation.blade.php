<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso administrativo criado</title>
</head>
<body style="font-family: Arial, sans-serif; color: #171717; line-height: 1.5;">
    <div style="margin-bottom: 22px;">
        @if($logoPath)
            <img
                src="{{ $message->embed($logoPath) }}"
                alt="Projeto Casulo"
                style="display: block; max-width: 180px; max-height: 72px; width: auto; height: auto;"
            >
        @elseif($logoUrl)
            <img
                src="{{ $logoUrl }}"
                alt="Projeto Casulo"
                style="display: block; max-width: 180px; max-height: 72px; width: auto; height: auto;"
            >
        @else
            <div style="font-size: 20px; font-weight: bold; color: #bf4f00;">
                Projeto Casulo
            </div>
        @endif
    </div>

    <h2>Seu acesso administrativo foi criado</h2>

    <p>Olá, {{ $admin->name }}.</p>

    <p>
        Um acesso administrativo foi criado para este e-mail no painel do Projeto Casulo.
    </p>

    <div style="border: 1px solid #f2c08f; background: #fff7ed; padding: 14px; border-radius: 8px; margin: 18px 0;">
        <p style="margin: 0 0 8px;">
            <strong>E-mail:</strong> {{ $admin->email }}
        </p>

        <p style="margin: 0 0 8px;">
            <strong>Nível:</strong> {{ ucfirst($admin->role) }}
        </p>

        <p style="margin: 0;">
            <strong>Status:</strong> {{ $admin->is_active ? 'Ativo' : 'Inativo' }}
        </p>
    </div>

    <p>
        Por segurança, sua senha não foi definida pelo painel.
        Para criar sua senha, acesse a tela de login e clique em
        <strong>“Esqueceu sua senha?”</strong>.
    </p>

    <p>
        <a
            href="{{ $loginUrl }}"
            style="
                display: inline-block;
                background: #bf4f00;
                color: #ffffff;
                padding: 12px 18px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
            "
        >
            Ir para o painel
        </a>
    </p>

    <p>
        Se você não esperava este acesso, ignore esta mensagem e avise a equipe responsável.
    </p>
</body>
</html>
