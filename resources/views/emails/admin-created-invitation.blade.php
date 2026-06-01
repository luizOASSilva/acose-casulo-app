<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso administrativo criado</title>
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
                                Seu acesso administrativo foi criado
                            </h2>

                            <p style="margin: 0 0 14px;">
                                Olá, {{ $admin->name }}.
                            </p>

                            <p style="margin: 0 0 14px;">
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

                            <p style="margin: 0 0 14px;">
                                Por segurança, sua senha não foi definida pelo painel.
                                Para criar sua senha, acesse a tela de login e clique em
                                <strong>“Esqueceu sua senha?”</strong>.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 18px 0;">
                                <tr>
                                    <td align="center" bgcolor="#bf4f00" style="border-radius: 8px; background: #bf4f00;">
                                        <a
                                            href="{{ $loginUrl }}"
                                            target="_blank"
                                            style="display: inline-block; padding: 12px 18px; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 8px;"
                                        >
                                            Ir para o painel
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px; font-size: 13px; color: #525252;">
                                Se o botão não abrir no celular, copie e cole este link no navegador:
                            </p>

                            <p style="margin: 0 0 18px; font-size: 13px; word-break: break-all;">
                                <a
                                    href="{{ $loginUrl }}"
                                    target="_blank"
                                    style="color: #bf4f00; text-decoration: underline;"
                                >
                                    {{ $loginUrl }}
                                </a>
                            </p>

                            <p style="margin: 0 0 14px; color: #991b1b;">
                                <strong>IMPORTANTE:</strong>
                                se você não esperava este acesso, ignore esta mensagem e avise a equipe responsável.
                            </p>

                            <p style="margin: 0; font-size: 13px; color: #737373;">
                                Este e-mail foi enviado automaticamente pelo Projeto Casulo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
