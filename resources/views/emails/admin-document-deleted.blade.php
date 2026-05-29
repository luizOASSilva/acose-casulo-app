<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Documento de transparência removido</title>
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

    <h2>Documento de transparência removido</h2>

    <p>Olá.</p>

    <p>
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

    <p style="color: #991b1b;">
        <strong>IMPORTANTE:</strong>
        documentos de transparência são considerados informações sensíveis.
        Caso esta remoção não tenha sido intencional, revise imediatamente o log de auditoria no painel.
    </p>

    <p>
        Se esta ação foi realizada corretamente, nenhuma ação adicional é necessária.
    </p>
</body>
</html>
