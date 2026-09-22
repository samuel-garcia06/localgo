@php
    $customerName = trim((string) ($order->customer_name ?? ''));
    $heading = $customerName !== ''
        ? "Confirma tu pedido, {$customerName}"
        : 'Confirma tu pedido';
    $intro = $customerName !== ''
        ? "Hola {$customerName}, confirma tu pedido para que podamos enviarlo al restaurante."
        : 'Confirma tu pedido para que podamos enviarlo al restaurante.';
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Confirma tu pedido</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#14211b;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #dfe7df;">
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <p style="margin:0 0 8px;color:#17945b;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">LOCALGO</p>
                            <h1 style="margin:0;color:#14211b;font-size:26px;line-height:1.2;">{{ $heading }}</h1>
                            <p style="margin:14px 0 0;color:#51625a;font-size:15px;line-height:1.6;">{{ $intro }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eee8;border-radius:14px;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:12px 14px;border-bottom:1px solid #edf2ed;color:#14211b;font-size:14px;">
                                            {{ $item->quantity }} x {{ $item->product_name }}
                                        </td>
                                        <td align="right" style="padding:12px 14px;border-bottom:1px solid #edf2ed;color:#14211b;font-size:14px;font-weight:700;">
                                            {{ number_format((float) $item->subtotal, 2) }} €
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:14px;color:#14211b;font-size:16px;font-weight:700;">Total</td>
                                    <td align="right" style="padding:14px;color:#14211b;font-size:16px;font-weight:800;">{{ number_format((float) $order->total, 2) }} €</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:4px 24px 24px;">
                            <a href="{{ $confirmationUrl }}" style="display:inline-block;background:#1fb86a;color:#ffffff;text-decoration:none;border-radius:999px;padding:14px 24px;font-size:15px;font-weight:800;">
                                Confirmar pedido
                            </a>
                            <p style="margin:18px 0 0;color:#7a4b00;background:#fff7df;border:1px solid #ffe3a3;border-radius:12px;padding:12px 14px;font-size:14px;line-height:1.5;text-align:left;">
                                El restaurante no preparará el pedido hasta que lo confirmes. Este enlace caduca en 15 minutos.
                            </p>
                            <p style="margin:16px 0 0;color:#718078;font-size:12px;line-height:1.5;">
                                Si el botón no funciona, abre este enlace en tu navegador:<br>
                                <span style="word-break:break-all;">{{ $confirmationUrl }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
