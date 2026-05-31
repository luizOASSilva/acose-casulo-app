<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirme a criação de administrador</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; color: #171717; line-height: 1.5; background: #ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; background: #ffffff;">
        <tr>
            <td style="padding: 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 640px;">
                    <tr>
                        <td style="padding-bottom: 22px;">
                            @if($logoPath)
                                <img
                                    src="{{ $message->embed($logoPath) }}"
                                    alt="Projeto Casulo"
                                    style="display: block; max-width: 180px; max-height: 72px; width: auto; height: auto; border: 0;"
                                >
                            @elseif($logoUrl)
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="Projeto Casulo"
                                    style="display: block; max-width: 180px; max-height: 72px; width: auto; height: auto; border: 0;"
                                >
                            @else
                                <div style="font-size: 20px; font-weight: bold; color: #bf4f00;">
                                    Projeto Casulo
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <h2 style="margin: 0 0 16px; font-size: 24px; line-height: 1.3; color: #171717;">
                                Confirme a criação de um novo administrador
                            </h2>

                            <p style="margin: 0 0 14px;">
                                Olá, {{ $masterAdmin->name }}.
                            </p>

                            <p style="margin: 0 0 14px;">
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

                            <p style="margin: 0 0 14px; color: #991b1b;">
                                <strong>IMPORTANTE:</strong>
                                ao confirmar, este e-mail receberá instruções para criar uma senha e poderá acessar
                                o painel administrativo conforme o nível informado.
                            </p>

                            <p style="margin: 0 0 14px; color: #991b1b;">
                                Se o e-mail estiver incorreto ou não pertencer à pessoa correta, não confirme esta solicitação.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Para revisar e confirmar, clique no botão abaixo:
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 18px 0;">
                                <tr>
                                    <td align="center" bgcolor="#bf4f00" style="border-radius: 8px; background: #bf4f00;">
                                        <a
                                            href="{{ $confirmationUrl }}"
                                            target="_blank"
                                            style="display: inline-block; padding: 12px 18px; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 8px;"
                                        >
                                            Revisar e confirmar criação
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px; font-size: 13px; color: #525252;">
                                Se o botão não abrir no celular, copie e cole este link no navegador:
                            </p>

                            <p style="margin: 0 0 18px; font-size: 13px; word-break: break-all;">
                                <a
                                    href="{{ $confirmationUrl }}"
                                    target="_blank"
                                    style="color: #bf4f00; text-decoration: underline;"
                                >
                                    {{ $confirmationUrl }}
                                </a>
                            </p>

                            <p style="margin: 0;">
                                Este link expira em 30 minutos. Se você não reconhece esta solicitação
                                ou percebeu erro no e-mail, ignore esta mensagem.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
