<div class="fastbite-app min-h-screen" wire:poll.30000ms="$refresh">
    @include('partials.store-header', ['cartTotal' => $total])

    {{-- Operational status banners --}}
    @if($restaurantSettings->isClosed())
        <div class="fixed inset-x-0 top-[72px] z-30 flex items-center justify-center gap-3 bg-red-600 px-4 py-3 text-sm font-semibold text-white shadow-lg sm:top-[88px]">
            <span>🔴</span>
            <span>El restaurante no está aceptando pedidos en este momento.</span>
        </div>
    @elseif($restaurantSettings->isBusy())
        <div class="fixed inset-x-0 top-[72px] z-30 flex items-center justify-center gap-3 bg-amber-500 px-4 py-3 text-sm font-semibold text-amber-950 shadow-lg sm:top-[88px]">
            <span>🟠</span>
            <span>Alta demanda. El tiempo de espera puede ser superior al habitual.</span>
        </div>
    @endif

    <main class="mx-auto w-full max-w-[1520px] px-3 pb-20 {{ $restaurantSettings->isOpen() ? 'pt-24 sm:pt-28' : 'pt-36 sm:pt-40' }} sm:px-6 lg:px-8">
        @if (session('order-success'))
            <div class="notice-success mb-6 rounded-[24px] px-5 py-4 text-sm font-medium text-emerald-950">
                {{ session('order-success') }}
            </div>
        @endif

        @if ($pendingOrderPublicId && ! $pendingOrderNoticeDismissed && $cartItems->isEmpty())
            <section class="mb-6 rounded-[24px] border {{ $pendingOrderExpired ? 'border-amber-200 bg-amber-50 text-amber-950' : 'border-emerald-200 bg-emerald-50 text-emerald-950' }} px-4 py-4 text-sm shadow-sm sm:px-5">
                <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="font-bold">
                            {{ $pendingOrderExpired ? 'El email de confirmación ha caducado' : 'Te hemos enviado un email para confirmar tu pedido.' }}
                        </p>
                        <p class="mt-1 leading-6">{{ $verificationMessage }}</p>
                        @if ($verificationEmail)
                            <p class="mt-2 break-words font-semibold">{{ $verificationEmail }}</p>
                        @endif
                        <p class="mt-2 leading-6">El restaurante no preparará el pedido hasta que pulses el enlace de confirmación.</p>
                    </div>

                    <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                        @if (! $pendingOrderExpired)
                            <button type="button" wire:click="resendConfirmationEmail" wire:loading.attr="disabled" wire:target="resendConfirmationEmail" class="checkout-button inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-full px-5 text-sm font-semibold text-white sm:w-auto">
                                <svg wire:loading wire:target="resendConfirmationEmail" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="resendConfirmationEmail">Reenviar email</span>
                                <span wire:loading wire:target="resendConfirmationEmail">Enviando...</span>
                            </button>
                        @endif
                        <button type="button" wire:click="dismissPendingOrderNotice" class="nav-ghost-button inline-flex min-h-[52px] w-full items-center justify-center rounded-full px-5 text-sm font-semibold sm:w-auto">
                            Entendido
                        </button>
                    </div>
                </div>
            </section>
        @endif

        @if ($cartItems->isEmpty())
            <section class="surface-panel rounded-[24px] px-4 py-12 text-center sm:rounded-[30px] sm:px-6 sm:py-14">
                <div class="mx-auto flex h-18 w-18 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fa-solid fa-cart-shopping text-2xl" aria-hidden="true"></i>
                </div>
                <h1 class="mt-5 text-2xl font-black tracking-tight text-[var(--fastbite-ink)] sm:text-3xl">Tu carrito está vacío</h1>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-[var(--fastbite-muted)]">Todavía no has añadido productos. Vuelve a la carta, explora las categorías y crea tu pedido en Urban Bites.</p>
                <a href="{{ $homeUrl }}" class="checkout-button mt-7 inline-flex min-h-[52px] items-center justify-center rounded-full px-6 text-sm font-semibold text-white">
                    Volver a la carta
                </a>
            </section>
        @else
            <section class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,430px)]">
                <div class="min-w-0 space-y-5">
                    <div class="surface-panel rounded-[24px] p-4 sm:rounded-[30px] sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[var(--fastbite-accent)]">Checkout Urban Bites</p>
                                <h1 class="mt-1 text-2xl font-black tracking-tight text-[var(--fastbite-ink)] sm:text-3xl">Revisa tu pedido</h1>
                                <p class="mt-2 text-sm text-[var(--fastbite-muted)]">{{ $cartQuantity }} {{ $cartQuantity == 1 ? 'artículo añadido' : 'artículos añadidos' }} · entrega rápida o recogida</p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="delivery-toggle inline-flex w-full rounded-full p-1 sm:w-auto">
                                    <button wire:click="setFulfillment('delivery')" class="delivery-pill {{ $fulfillment === 'delivery' ? 'delivery-pill-active' : '' }} min-h-[44px] flex-1 rounded-full px-4 py-2 text-sm font-semibold sm:flex-none">
                                        Entrega
                                    </button>
                                    <button wire:click="setFulfillment('pickup')" class="delivery-pill {{ $fulfillment === 'pickup' ? 'delivery-pill-active' : '' }} min-h-[44px] flex-1 rounded-full px-4 py-2 text-sm font-semibold sm:flex-none">
                                        Recogida
                                    </button>
                                </div>
                                <button wire:click="clearCart" wire:confirm="¿Seguro que quieres vaciar el carrito? Se eliminarán todos los productos." class="nav-ghost-button inline-flex min-h-[48px] w-full items-center justify-center rounded-full px-4 text-sm font-semibold sm:w-auto">
                                    Vaciar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        @foreach ($cartItems as $item)
                            <article class="cart-item rounded-[24px] p-4 sm:rounded-[28px] sm:p-5">
                                <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                                    <div class="aspect-[16/10] w-full shrink-0 overflow-hidden rounded-[20px] bg-[var(--fastbite-surface-alt)] sm:h-24 sm:w-24 sm:rounded-[22px]">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Support\LocalgoStore::placeholderImageUrl() }}';">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h2 class="break-words text-lg font-bold tracking-tight text-[var(--fastbite-ink)]">{{ $item['name'] }}</h2>
                                                <p class="mt-2 text-sm leading-6 text-[var(--fastbite-muted)]">{{ $item['description'] }}</p>
                                            </div>
                                            <button wire:click="remove({{ $item['id'] }})" class="min-h-[40px] w-fit text-xs font-semibold uppercase tracking-[0.16em] text-[var(--fastbite-muted)] transition hover:text-red-600">
                                                Quitar
                                            </button>
                                        </div>

                                        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                                            <div class="quantity-stepper inline-flex items-center rounded-full p-1">
                                                <button wire:click="decrement({{ $item['id'] }})" aria-label="Reducir cantidad de {{ $item['name'] }}" class="stepper-button inline-flex h-10 w-10 items-center justify-center rounded-full">-</button>
                                                <span class="min-w-12 text-center text-sm font-bold text-[var(--fastbite-ink)]" aria-label="Cantidad: {{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                                                <button wire:click="increment({{ $item['id'] }})" aria-label="Aumentar cantidad de {{ $item['name'] }}" class="stepper-button inline-flex h-10 w-10 items-center justify-center rounded-full">+</button>
                                            </div>
                                            <div class="text-left sm:text-right">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--fastbite-muted)]">{{ number_format($item['price'], 2) }} € unidad</p>
                                                <p class="mt-1 text-lg font-bold text-[var(--fastbite-ink)]">{{ number_format($item['line_total'], 2) }} €</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <aside class="min-w-0 xl:sticky xl:top-28 xl:self-start">
                    <div class="cart-panel rounded-[24px] p-4 sm:rounded-[30px] sm:p-6">
                        <div class="border-b border-[var(--fastbite-border)] pb-5">
                            <p class="text-sm font-semibold text-[var(--fastbite-accent)]">Resumen</p>
                            <h2 class="mt-1 text-2xl font-bold text-[var(--fastbite-ink)]">Finaliza tu pedido</h2>
                        </div>

                        <div class="mt-5 space-y-2 text-sm">
                            <div class="flex items-center justify-between text-[var(--fastbite-muted)]">
                                <span>Subtotal</span>
                                <span>{{ number_format($subtotal, 2) }} €</span>
                            </div>
                            <div class="flex items-center justify-between text-[var(--fastbite-muted)]">
                                <span>Envío</span>
                                <span>{{ number_format($deliveryFee, 2) }} €</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[var(--fastbite-border)] pt-3 text-lg font-bold text-[var(--fastbite-ink)]">
                                <span>Total</span>
                                <span>{{ number_format($total, 2) }} €</span>
                            </div>
                        </div>

                        @if($restaurantSettings->isClosed())
                            {{-- Closed: replace form with clear message --}}
                            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-center">
                                <p class="text-2xl mb-3">🔴</p>
                                <p class="font-bold text-red-800 text-base">No aceptamos pedidos ahora mismo</p>
                                <p class="mt-2 text-sm text-red-600 leading-relaxed">
                                    El restaurante ha pausado temporalmente los pedidos. Puedes volver a intentarlo en unos minutos.
                                </p>
                                <a href="{{ $homeUrl }}" class="mt-5 inline-flex min-h-[48px] items-center justify-center rounded-full border border-red-300 px-5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors">
                                    Volver a la carta
                                </a>
                            </div>
                        @else
                            <form wire:submit="submitOrder" class="mt-6 space-y-4">
                                @if($restaurantSettings->isBusy())
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                        <p class="text-sm font-semibold text-amber-800">🟠 Alta demanda</p>
                                        <p class="mt-0.5 text-sm text-amber-700">El tiempo de espera puede ser superior al habitual.</p>
                                    </div>
                                @endif

                                @error('cart') <p class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p> @enderror

                                <div>
                                    <label for="customerName" class="mb-1.5 block text-sm font-medium text-[var(--fastbite-ink)]">Nombre</label>
                                    <input id="customerName" wire:model="customerName" type="text" autocomplete="name" placeholder="Tu nombre completo" class="field-control w-full rounded-2xl px-4 py-3 text-sm">
                                    @error('customerName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="customerPhone" class="mb-1.5 block text-sm font-medium text-[var(--fastbite-ink)]">Teléfono</label>
                                    <input id="customerPhone" wire:model="customerPhone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+34 612 345 678" class="field-control w-full rounded-2xl px-4 py-3 text-sm">
                                    @error('customerPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="customerEmail" class="mb-1.5 block text-sm font-medium text-[var(--fastbite-ink)]">Email</label>
                                    <input id="customerEmail" wire:model="customerEmail" type="email" inputmode="email" autocomplete="email" placeholder="tu@email.com" class="field-control w-full rounded-2xl px-4 py-3 text-sm">
                                    @error('customerEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                @if ($fulfillment === 'delivery')
                                    <div>
                                        <label for="customerAddress" class="mb-1.5 block text-sm font-medium text-[var(--fastbite-ink)]">Dirección</label>
                                        <input id="customerAddress" wire:model="customerAddress" type="text" autocomplete="street-address" placeholder="Calle, número, piso o referencia" class="field-control w-full rounded-2xl px-4 py-3 text-sm">
                                        @error('customerAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif

                                <div>
                                    <label for="notes" class="mb-1.5 block text-sm font-medium text-[var(--fastbite-ink)]">Notas</label>
                                    <textarea id="notes" wire:model="notes" autocomplete="off" placeholder="Sin pepinillo, salsa aparte, llamar al llegar..." class="field-control min-h-24 w-full rounded-2xl px-4 py-3 text-sm"></textarea>
                                </div>

                                {{-- ── Aviso del restaurante ──────────────────── --}}
                                @if(filled($restaurantSettings->customer_notice))
                                    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="leading-relaxed">{{ $restaurantSettings->customer_notice }}</p>
                                    </div>
                                @endif

                                <button class="checkout-button inline-flex min-h-[54px] w-full items-center justify-center gap-2 rounded-full px-5 text-sm font-semibold text-white"
                                    wire:target="submitOrder"
                                    wire:loading.attr="disabled"
                                    @disabled($cartItems->isEmpty())>
                                    <svg wire:loading wire:target="submitOrder" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading wire:target="submitOrder">Procesando...</span>
                                    <span wire:loading.remove wire:target="submitOrder">Finalizar pedido</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </aside>
            </section>
        @endif
    </main>
</div>
