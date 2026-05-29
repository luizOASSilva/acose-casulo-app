<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinição de senha</title>
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

    <h2>Redefinição de senha</h2>

    <p>Olá, {{ $admin->name }}.</p>

    <p>
        Recebemos uma solicitação para redefinir a senha do seu acesso ao painel administrativo.
    </p>

    <p>
        Para criar uma nova senha, clique no botão abaixo:
    </p>

    <p>
        <a
            href="{{ $resetUrl }}"
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
            Redefinir senha
        </a>
    </p>

    <p style="color: #991b1b;">
        <strong>IMPORTANTE:</strong>
        este link expira em 30 minutos. Se você não solicitou essa redefinição,
        ignore este e-mail.
    </p>
</body>
</html>
