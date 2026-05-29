<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirme a criação de administrador</title>
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

    <h2>Confirme a criação de um novo administrador</h2>

    <p>Olá, {{ $masterAdmin->name }}.</p>

    <p>
        Uma solicitação de criação de administrador foi feita no painel.
        Revise com muita atenção os dados abaixo antes de confirmar.
    </p>

    <div style="border: 1px solid #f2c08f; background: #fff7ed; padding: 14px; border-radius: 8px; margin: 18px 0;">
        <p style="margin: 0 0 8px;">
            <strong>Nome:</strong> {{ $creationRequest->name }}
        </p>

        <p style="margin: 0 0 8px;">
            <strong>E-mail:</strong> {{ $creationRequest->email }}
        </p>

        <p style="margin: 0 0 8px;">
            <strong>Nível:</strong> {{ ucfirst($creationRequest->role) }}
        </p>

        <p style="margin: 0;">
            <strong>Status inicial:</strong> {{ $creationRequest->is_active ? 'Ativo' : 'Inativo' }}
        </p>
    </div>

    <p style="color: #991b1b;">
        <strong>IMPORTANTE:</strong>
        ao confirmar, este e-mail receberá instruções para criar uma senha e poderá acessar
        o painel administrativo conforme o nível informado.
    </p>

    <p style="color: #991b1b;">
        Se o e-mail estiver incorreto ou não pertencer à pessoa correta, não confirme esta solicitação.
    </p>

    <p>
        Para revisar e confirmar, clique no botão abaixo:
    </p>

    <p>
        <a
            href="{{ $confirmationUrl }}"
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
            Revisar e confirmar criação
        </a>
    </p>

    <p>
        Este link expira em 30 minutos. Se você não reconhece esta solicitação
        ou percebeu erro no e-mail, ignore esta mensagem.
    </p>
</body>
</html>
