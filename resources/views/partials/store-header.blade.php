<header class="fastbite-header fixed inset-x-0 top-0 z-40">
    <div class="mx-auto flex w-full max-w-[1520px] items-center gap-2 px-3 py-3 sm:gap-3 sm:px-6 lg:px-8">
        <a href="{{ $homeUrl }}" class="brand-mark brand-mark-image shrink-0 rounded-2xl px-2.5 py-2 sm:px-3">
            <img
                src="{{ asset('branding/localgo-logo.png') }}"
                alt="{{ config('app.name') }}"
                class="brand-mark-logo h-10 w-auto sm:h-14"
            >
        </a>

        <div class="hidden min-w-0 flex-1 lg:block">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-[var(--fastbite-muted)]">{{ $headerTitle }}</p>
                <p class="mt-1 text-sm text-[var(--fastbite-muted)]">{{ $headerSubtitle }}</p>
            </div>
        </div>

        <nav class="ml-auto flex min-w-0 items-center gap-1.5 sm:gap-3">
            <a href="{{ $homeUrl }}" class="nav-ghost-button {{ $currentRoute === 'home' ? 'nav-ghost-button-active' : '' }} inline-flex min-h-[48px] shrink-0 items-center justify-center rounded-full px-3 text-sm font-semibold sm:min-h-[52px] sm:px-4">
                Carta
            </a>
            <a href="{{ $cartUrl }}" class="cart-button inline-flex min-h-[52px] min-w-0 shrink-0 items-center gap-1.5 rounded-full px-2.5 sm:min-h-[56px] sm:gap-3 sm:px-4 lg:px-5" data-cart-button>
                <span class="cart-badge inline-flex h-8 min-w-8 shrink-0 items-center justify-center rounded-full bg-[var(--fastbite-accent)] px-2 text-sm font-bold text-white" data-cart-count>
                    {{ $cartQuantity }}
                </span>
                <span class="min-w-0 text-left">
                    <span class="hidden text-[0.7rem] uppercase tracking-[0.22em] text-[var(--fastbite-muted)] sm:block">Carrito</span>
                    <span class="block whitespace-nowrap text-xs font-semibold text-[var(--fastbite-ink)] sm:text-sm" data-cart-total>{{ number_format($cartTotal ?? $total ?? 0, 2) }} €</span>
                </span>
                <i class="fa-solid fa-cart-shopping shrink-0 text-[1rem] text-[var(--fastbite-ink)]" aria-hidden="true"></i>
            </a>
        </nav>
    </div>
</header>
