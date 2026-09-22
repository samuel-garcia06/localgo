@php
    $customerName = trim((string) ($order->customer_name ?? ''));
    $heading = $customerName !== ''
        ? "Lo sentimos, {$customerName}"
        : 'Lo sentimos';
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tu pedido no ha podido ser aceptado</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#14211b;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #dfe7df;">
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <p style="margin:0 0 8px;color:#17945b;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Urban Bites</p>
                            <h1 style="margin:0;color:#14211b;font-size:26px;line-height:1.2;">{{ $heading }}</h1>
                            <p style="margin:14px 0 0;color:#51625a;font-size:15px;line-height:1.6;">
                                El restaurante no ha podido aceptar tu pedido en este momento.
                            </p>
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
                        <td style="padding:0 24px 28px;">
                            <p style="margin:0;color:#7a4b00;background:#fff7df;border:1px solid #ffe3a3;border-radius:12px;padding:12px 14px;font-size:14px;line-height:1.5;">
                                Disculpa las molestias. Si tienes alguna duda, contacta directamente con el restaurante.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
