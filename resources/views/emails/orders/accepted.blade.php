@php
    $prepTime = (int) ($order->preparation_time ?? 20);
    $eta = $order->updated_at->addMinutes($prepTime)->format('H:i');
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tu pedido ha sido aceptado</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#14211b;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #dfe7df;">
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <p style="margin:0 0 8px;color:#17945b;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Urban Bites · Pedido #{{ $order->id }}</p>
                            <h1 style="margin:0;color:#14211b;font-size:26px;line-height:1.2;">Tu pedido ha sido aceptado</h1>
                            <p style="margin:14px 0 0;color:#51625a;font-size:15px;line-height:1.6;">
                                @if(filled($order->customer_name))
                                    Gracias, {{ $order->customer_name }}. Tu pedido ha sido aceptado y el restaurante ya lo está preparando.
                                @else
                                    Tu pedido ha sido aceptado y el restaurante ya lo está preparando.
                                @endif
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 6px;color:#14211b;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Tiempo estimado</p>
                                        <p style="margin:0;color:#14211b;font-size:28px;font-weight:800;line-height:1;">{{ $prepTime }} minutos</p>
                                        <p style="margin:6px 0 0;color:#51625a;font-size:14px;">Hora aproximada de finalización: <strong>{{ $eta }}</strong></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if($order->delivery_type)
                        <tr>
                            <td style="padding:0 24px 8px;">
                                <p style="margin:0;color:#51625a;font-size:14px;">
                                    <strong>Entrega:</strong> {{ $order->delivery_type->label() }}
                                    @if($order->delivery_type->value === 'domicilio' && filled($order->customer_address))
                                        — {{ $order->customer_address }}
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endif

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
                            <p style="margin:0;color:#17945b;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px;font-size:14px;line-height:1.5;">
                                Gracias por confiar en Urban Bites. Te avisaremos cuando tu pedido esté listo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
