<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Confirmación de pedido en Urban Bites.">
    <title>Confirmación de pedido | Urban Bites</title>
    <link rel="icon" type="image/png" href="{{ asset('branding/localgo-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('branding/localgo-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('branding/localgo-logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="fastbite-app flex min-h-screen items-center justify-center px-4 py-10">
        <section class="surface-panel w-full max-w-xl rounded-[28px] p-5 text-center sm:p-8">

            @if ($status === 'pending')
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fa-solid fa-envelope-circle-check text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mt-5 text-sm font-semibold text-[var(--fastbite-accent)]">Urban Bites</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-[var(--fastbite-ink)] sm:text-3xl">
                    Email reenviado
                </h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--fastbite-muted)]">{{ $message }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--fastbite-muted)]">
                    El restaurante no preparará el pedido hasta que pulses el enlace de confirmación.
                </p>

            @else
                {{-- error / token inválido / caducado --}}
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-triangle-exclamation text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mt-5 text-sm font-semibold text-[var(--fastbite-accent)]">Urban Bites</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-[var(--fastbite-ink)] sm:text-3xl">
                    No se pudo confirmar
                </h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--fastbite-muted)]">{{ $message }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--fastbite-muted)]">
                    El restaurante no preparará el pedido hasta que confirmes el email.
                </p>
            @endif

            @if ($canResend)
                <form method="POST" action="{{ route('orders.resend-confirmation-email', $order->public_id) }}" class="mt-6 space-y-3">
                    @csrf
                    <p class="text-sm font-medium text-[var(--fastbite-ink)]">Solicita un nuevo enlace de confirmación:</p>
                    <input name="customer_email" type="email" autocomplete="email" value="{{ old('customer_email', $order->customer_email) }}" class="field-control w-full rounded-2xl px-4 py-3 text-sm" required>
                    @error('customer_email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <button class="checkout-button inline-flex min-h-[52px] w-full items-center justify-center rounded-full px-5 text-sm font-semibold text-white">
                        Reenviar email
                    </button>
                </form>
            @endif

            <a href="{{ route('home') }}" class="nav-ghost-button mt-5 inline-flex min-h-[48px] items-center justify-center rounded-full px-5 text-sm font-semibold">
                Volver a la carta
            </a>
        </section>
    </main>
</body>
</html>
