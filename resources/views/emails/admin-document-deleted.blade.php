<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Documento de transparência removido</title>
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
                                Documento de transparência removido
                            </h2>

                            <p style="margin: 0 0 14px;">
                                Olá.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Um documento da área de transparência foi removido no painel administrativo.
                                Como este é um item sensível, estamos enviando este aviso para registro.
                            </p>

                            <div style="border: 1px solid #fecaca; background: #fef2f2; padding: 14px; border-radius: 8px; margin: 18px 0;">
                                <p style="margin: 0 0 8px;">
                                    <strong>Documento:</strong> {{ $documentName }}
                                </p>

                                @if($documentId)
                                    <p style="margin: 0 0 8px;">
                                        <strong>ID:</strong> {{ $documentId }}
                                    </p>
                                @endif

                                @if($categoryName)
                                    <p style="margin: 0 0 8px;">
                                        <strong>Categoria:</strong> {{ $categoryName }}
                                    </p>
                                @elseif($categoryId)
                                    <p style="margin: 0 0 8px;">
                                        <strong>Categoria ID:</strong> {{ $categoryId }}
                                    </p>
                                @endif

                                @if($year)
                                    <p style="margin: 0 0 8px;">
                                        <strong>Ano:</strong> {{ $year }}
                                    </p>
                                @endif

                                <p style="margin: 0 0 8px;">
                                    <strong>Removido por:</strong> {{ $deletedByName }}
                                    @if($deletedByEmail)
                                        &lt;{{ $deletedByEmail }}&gt;
                                    @endif
                                </p>

                                <p style="margin: 0;">
                                    <strong>Data e horário:</strong>
                                    {{ $deletedAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <p style="margin: 0 0 14px; color: #991b1b;">
                                <strong>IMPORTANTE:</strong>
                                documentos de transparência são considerados informações sensíveis.
                                Caso esta remoção não tenha sido intencional, revise imediatamente o log de auditoria no painel.
                            </p>

                            @if(! empty($auditUrl))
                                <p style="margin: 0 0 14px;">
                                    Para revisar os detalhes técnicos desta ação, clique no botão abaixo:
                                </p>

                                <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 18px 0;">
                                    <tr>
                                        <td align="center" bgcolor="#bf4f00" style="border-radius: 8px; background: #bf4f00;">
                                            <a
                                                href="{{ $auditUrl }}"
                                                target="_blank"
                                                style="display: inline-block; padding: 12px 18px; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 8px;"
                                            >
                                                Abrir detalhe da auditoria
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 0 0 10px; font-size: 13px; color: #525252;">
                                    Se o botão não abrir no celular, copie e cole este link no navegador:
                                </p>

                                <p style="margin: 0 0 18px; font-size: 13px; word-break: break-all;">
                                    <a
                                        href="{{ $auditUrl }}"
                                        target="_blank"
                                        style="color: #bf4f00; text-decoration: underline;"
                                    >
                                        {{ $auditUrl }}
                                    </a>
                                </p>
                            @endif

                            <p style="margin: 0 0 14px;">
                                Se esta ação foi realizada corretamente, nenhuma ação adicional é necessária.
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
