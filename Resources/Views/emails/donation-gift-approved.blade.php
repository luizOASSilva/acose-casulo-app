<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Doação confirmada e brinde em preparação</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; color: #171717; line-height: 1.5; background: #ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; background: #ffffff;">
        <tr>
            <td style="padding: 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 640px;">
                    <tr>
                        <td style="padding-bottom: 22px;">
                            @if(! empty($logoPath))
                                <img
                                    src="{{ $message->embed($logoPath) }}"
                                    alt="Projeto Casulo"
                                    style="display: block; max-width: 180px; max-height: 72px; width: auto; height: auto; border: 0;"
                                >
                            @elseif(! empty($logoUrl))
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
                                Sua doação foi confirmada
                            </h2>

                            <p style="margin: 0 0 14px;">
                                Olá, {{ $donation->name ?: 'doador(a)' }}.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Recebemos com muita alegria a confirmação da sua doação para o Projeto Casulo.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Agradecemos imensamente pela sua contribuição e por acreditar no nosso trabalho.
                                Seu apoio nos ajuda a continuar oferecendo acolhimento, cuidado, autonomia e novas
                                oportunidades às pessoas atendidas pelo projeto.
                            </p>

                            <div style="border: 1px solid #f2c08f; background: #fff7ed; padding: 14px; border-radius: 8px; margin: 18px 0;">
                                <p style="margin: 0 0 8px;">
                                    <strong>Valor da doação:</strong>
                                    R$ {{ number_format((float) $donation->amount, 2, ',', '.') }}
                                </p>

                                @if($donation->size)
                                    <p style="margin: 0 0 8px;">
                                        <strong>Tamanho do brinde:</strong>
                                        {{ strtoupper($donation->size) }}
                                    </p>
                                @endif

                                <p style="margin: 0;">
                                    <strong>Situação do brinde:</strong>
                                    Em preparação
                                </p>
                            </div>

                            <h3 style="margin: 22px 0 12px; font-size: 18px; line-height: 1.3; color: #171717;">
                                Seu brinde está sendo preparado
                            </h3>

                            <p style="margin: 0 0 14px;">
                                Sua doação dá direito a um brinde especial, que está sendo preparado com muito
                                carinho pela nossa equipe.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Assim que o brinde estiver pronto para envio, entraremos em contato novamente
                                por este e-mail com as próximas informações.
                            </p>

                            @if(
                                $donation->street ||
                                $donation->number ||
                                $donation->neighborhood ||
                                $donation->city ||
                                $donation->state ||
                                $donation->zip_code
                            )
                                <h3 style="margin: 22px 0 12px; font-size: 18px; line-height: 1.3; color: #171717;">
                                    Endereço informado para entrega
                                </h3>

                                <div style="border: 1px solid #e5e5e5; background: #fafafa; padding: 14px; border-radius: 8px; margin: 0 0 18px;">
                                    @if($donation->street)
                                        <p style="margin: 0 0 8px;">
                                            <strong>Endereço:</strong>
                                            {{ $donation->street }}

                                            @if($donation->number)
                                                , {{ $donation->number }}
                                            @endif
                                        </p>
                                    @endif

                                    @if($donation->complement)
                                        <p style="margin: 0 0 8px;">
                                            <strong>Complemento:</strong>
                                            {{ $donation->complement }}
                                        </p>
                                    @endif

                                    @if($donation->neighborhood)
                                        <p style="margin: 0 0 8px;">
                                            <strong>Bairro:</strong>
                                            {{ $donation->neighborhood }}
                                        </p>
                                    @endif

                                    @if($donation->city || $donation->state)
                                        <p style="margin: 0 0 8px;">
                                            <strong>Cidade:</strong>

                                            {{ $donation->city }}

                                            @if($donation->city && $donation->state)
                                                /
                                            @endif

                                            {{ $donation->state }}
                                        </p>
                                    @endif

                                    @if($donation->zip_code)
                                        <p style="margin: 0;">
                                            <strong>CEP:</strong>
                                            {{ $donation->zip_code }}
                                        </p>
                                    @endif
                                </div>

                                <p style="margin: 0 0 14px;">
                                    Caso algum dado de entrega esteja incorreto, responda a este e-mail para
                                    que nossa equipe possa realizar a atualização.
                                </p>
                            @endif

                            <p style="margin: 0 0 14px;">
                                Somos muito gratos pela sua generosidade e por fazer parte dessa causa.
                            </p>

                            <p style="margin: 0 0 14px;">
                                Com carinho,<br>
                                <strong>Equipe Projeto Casulo</strong>
                            </p>

                            <p style="margin: 0; font-size: 13px; color: #737373;">
                                Este e-mail foi enviado automaticamente após a confirmação da sua doação.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
