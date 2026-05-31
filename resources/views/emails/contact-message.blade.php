<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo contato pelo site</title>
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

    <h2>Nova mensagem enviada pelo site</h2>

    <p>Olá.</p>

    <p>
        Uma nova mensagem foi enviada pelo formulário de contato do site.
        Confira os dados abaixo e responda diretamente este e-mail para falar com a pessoa.
    </p>

    <div style="border: 1px solid #f2c08f; background: #fff7ed; padding: 14px; border-radius: 8px; margin: 18px 0;">
        <p style="margin: 0 0 8px;">
            <strong>Nome:</strong> {{ $data['name'] }}
        </p>

        <p style="margin: 0 0 8px;">
            <strong>E-mail:</strong> {{ $data['email'] }}
        </p>

        @if(! empty($data['phone']))
            <p style="margin: 0 0 8px;">
                <strong>Telefone / WhatsApp:</strong> {{ $data['phone'] }}
            </p>
        @endif

        <p style="margin: 0;">
            <strong>Assunto:</strong> {{ $data['subject'] }}
        </p>
    </div>

    <p>
        <strong>Mensagem:</strong>
    </p>

    <div style="border: 1px solid #e5e5e5; background: #fafafa; padding: 14px; border-radius: 8px; margin: 12px 0 18px;">
        <p style="margin: 0; white-space: pre-line;">{{ $data['message'] }}</p>
    </div>

    <p style="color: #991b1b;">
        <strong>IMPORTANTE:</strong>
        antes de abrir links, anexos ou responder com informações sensíveis,
        confirme se a mensagem parece legítima.
    </p>

    <div style="border-top: 1px solid #e5e5e5; margin-top: 22px; padding-top: 14px;">
        <p style="margin: 0 0 6px; color: #737373; font-size: 13px;">
            <strong>Dados técnicos do envio</strong>
        </p>

        <p style="margin: 0; color: #737373; font-size: 13px;">
            <strong>IP:</strong> {{ $data['ip'] ?? 'Não informado' }}<br>
            <strong>User Agent:</strong> {{ $data['user_agent'] ?? 'Não informado' }}
        </p>
    </div>

    <p style="margin-top: 22px; color: #737373; font-size: 13px;">
        Este e-mail foi enviado automaticamente pelo site Projeto Casulo.
    </p>
</body>
</html>
