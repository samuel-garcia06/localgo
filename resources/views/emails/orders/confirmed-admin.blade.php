<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo pedido confirmado</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#14211b;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #dfe7df;">
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <p style="margin:0 0 8px;color:#17945b;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">LOCALGO · Panel Admin</p>
                            <h1 style="margin:0;color:#14211b;font-size:26px;line-height:1.2;">¡Nuevo pedido confirmado!</h1>
                            <p style="margin:14px 0 0;color:#51625a;font-size:15px;line-height:1.6;">
                                El cliente ha confirmado su pedido. Aquí tienes el resumen:
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Cliente:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Teléfono:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->customer_phone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Email:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->customer_email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Tipo:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->delivery_type?->label() ?? '—' }}</td>
                                </tr>
                                @if($order->delivery_type?->value === 'domicilio' && filled($order->customer_address))
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Dirección:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->customer_address }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Pago:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->payment_method?->label() ?? $order->payment_method }}</td>
                                </tr>
                                @if(filled($order->notes))
                                <tr>
                                    <td style="padding:4px 0;color:#51625a;font-size:14px;"><strong>Notas:</strong></td>
                                    <td style="padding:4px 0;color:#14211b;font-size:14px;">{{ $order->notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eee8;border-radius:14px;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:12px 14px;border-bottom:1px solid #edf2ed;color:#14211b;font-size:14px;">
                                            {{ $item->quantity }} x {{ $item->product_name }}
                                            @if(filled($item->drink_choice))
                                                <span style="color:#718078;font-size:12px;"> · {{ $item->drink_choice }}</span>
                                            @endif
                                            @if(filled($item->sauce_choice))
                                                <span style="color:#718078;font-size:12px;"> · {{ $item->sauce_choice }}</span>
                                            @endif
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
                            <p style="margin:0;color:#14211b;background:#e8f5ef;border:1px solid #b2dfcb;border-radius:12px;padding:12px 14px;font-size:14px;line-height:1.5;">
                                El pedido está <strong>confirmado por el cliente</strong> y esperando tu aceptación en el panel.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
