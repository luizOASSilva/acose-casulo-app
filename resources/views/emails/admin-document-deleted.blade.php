<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Documento removido</title>
</head>
<body style="margin: 0; padding: 0; background: #f6f6f6; font-family: Arial, Helvetica, sans-serif; color: #27272a;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #f6f6f6; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 620px; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="background: #b91c1c; padding: 24px 28px;">
                            <p style="margin: 0; color: #fecaca; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em;">
                                Ação crítica
                            </p>

                            <h1 style="margin: 8px 0 0; color: #ffffff; font-size: 24px; line-height: 1.25;">
                                Documento removido
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.65; color: #3f3f46;">
                                Um documento da área de transparência foi removido no painel administrativo.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 22px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 14px 16px; border: 1px solid #fee2e2; background: #fef2f2; border-radius: 12px;">
                                        <p style="margin: 0 0 10px; font-size: 13px; color: #991b1b; font-weight: 700;">
                                            Detalhes da exclusão
                                        </p>

                                        <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                            <strong>Documento:</strong>
                                            {{ $documentName }}
                                        </p>

                                        @if($documentId)
                                            <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                                <strong>ID:</strong>
                                                {{ $documentId }}
                                            </p>
                                        @endif

                                        @if($categoryName)
                                            <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                                <strong>Categoria:</strong>
                                                {{ $categoryName }}
                                            </p>
                                        @elseif($categoryId)
                                            <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                                <strong>Categoria ID:</strong>
                                                {{ $categoryId }}
                                            </p>
                                        @endif

                                        @if($year)
                                            <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                                <strong>Ano:</strong>
                                                {{ $year }}
                                            </p>
                                        @endif

                                        <p style="margin: 0 0 8px; font-size: 14px; color: #27272a;">
                                            <strong>Removido por:</strong>
                                            {{ $deletedByName }}
                                            @if($deletedByEmail)
                                                &lt;{{ $deletedByEmail }}&gt;
                                            @endif
                                        </p>

                                        <p style="margin: 0; font-size: 14px; color: #27272a;">
                                            <strong>Data e horário:</strong>
                                            {{ $deletedAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.65; color: #52525b;">
                                Esta mensagem foi enviada porque documentos de transparência são considerados itens sensíveis.
                                Verifique o log de auditoria do painel caso precise revisar a ação.
                            </p>

                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #71717a;">
                                Se esta remoção foi intencional, nenhuma ação adicional é necessária.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 18px 28px; background: #fafafa; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #71717a;">
                                Projeto Casulo — Painel administrativo
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
