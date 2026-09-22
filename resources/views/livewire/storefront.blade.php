<div class="fastbite-app min-h-screen" wire:poll.30000ms="$refresh">
    @include('partials.store-header')

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

    <main id="top" class="mx-auto w-full max-w-[1520px] px-3 pb-24 {{ $restaurantSettings->isOpen() ? 'pt-20 sm:pt-24' : 'pt-32 sm:pt-36' }} sm:px-6 lg:px-8">
        @if (session('order-success'))
            <div class="notice-success mb-4 rounded-[24px] px-5 py-4 text-sm font-medium text-emerald-950">
                {{ session('order-success') }}
            </div>
        @endif
        <section class="grid gap-4 lg:gap-5">
            <section class="hero-card overflow-hidden rounded-[20px] sm:rounded-[24px] lg:rounded-[32px]">
                <div class="grid min-w-0 gap-0 lg:grid-cols-1">
                    <div class="min-w-0 px-4 py-4 sm:px-5 lg:px-8 lg:py-6">
                        <p class="break-words text-[0.62rem] font-semibold uppercase leading-4 tracking-[0.14em] text-emerald-100 sm:text-[0.68rem] sm:tracking-[0.24em]">{{ $restaurant['hero_note'] }}</p>
                        <div class="mt-2 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div class="min-w-0">
                                <h1 class="break-words text-3xl font-black leading-none tracking-tight text-white sm:text-4xl lg:text-[3.75rem]">{{ $restaurant['name'] }}</h1>
                                <p class="mt-2 max-w-3xl text-sm leading-5 text-white/82 lg:text-base lg:leading-7">{{ $restaurant['tagline'] }}</p>
                            </div>

                            <div class="flex min-w-0 flex-wrap gap-2">
                                <div class="hero-stat max-w-full rounded-2xl px-2.5 py-2 sm:px-3 lg:px-4 lg:py-2.5">
                                    <p class="whitespace-normal text-[0.55rem] font-semibold uppercase tracking-[0.12em] text-white/70 sm:text-[0.58rem] sm:tracking-[0.16em]">Valoración</p>
                                    <p class="mt-0.5 text-base font-bold text-white lg:text-lg">{{ number_format($restaurant['rating'], 1) }} <span class="text-amber-300">★</span></p>
                                </div>
                                <div class="hero-stat max-w-full rounded-2xl px-2.5 py-2 sm:px-3 lg:px-4 lg:py-2.5">
                                    <p class="whitespace-normal text-[0.55rem] font-semibold uppercase tracking-[0.12em] text-white/70 sm:text-[0.58rem] sm:tracking-[0.16em]">Entrega</p>
                                    <p class="mt-0.5 text-base font-bold text-white lg:text-lg">{{ $restaurant['eta'] }}</p>
                                </div>
                                <div class="hero-stat max-w-full rounded-2xl px-2.5 py-2 sm:px-3 lg:px-4 lg:py-2.5">
                                    <p class="whitespace-normal text-[0.55rem] font-semibold uppercase tracking-[0.12em] text-white/70 sm:text-[0.58rem] sm:tracking-[0.16em]">Envío</p>
                                    <p class="mt-0.5 text-base font-bold text-white lg:text-lg">{{ $restaurant['delivery_fee_label'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex min-w-0 flex-wrap gap-2">
                            @foreach ($restaurant['service_modes'] as $mode)
                                <span class="hero-chip max-w-full rounded-full px-2.5 py-1.5 text-[0.82rem] font-medium leading-5 text-white/92 sm:px-3 sm:text-sm">{{ $mode }}</span>
                            @endforeach
                            <span class="hero-chip max-w-full whitespace-normal rounded-full px-2.5 py-1.5 text-[0.82rem] font-medium leading-5 text-white/92 sm:px-3 sm:text-sm">{{ $restaurant['minimum_order'] }}</span>
                            <span class="hero-chip hidden max-w-full whitespace-normal rounded-full px-2.5 py-1.5 text-[0.82rem] font-medium leading-5 text-white/92 sm:inline-flex sm:px-3 sm:text-sm">{{ $restaurant['address'] }}</span>
                        </div>
                    </div>

                    <div class="hero-visual relative aspect-[16/5] max-h-[110px] w-full overflow-hidden sm:max-h-[140px] lg:mx-8 lg:mb-6 lg:aspect-[21/7] lg:max-h-[240px] lg:rounded-[26px] 2xl:max-h-[280px]">
                        <img src="{{ $restaurant['hero_image'] }}" alt="{{ $restaurant['name'] }}" class="h-full w-full object-cover object-[center_58%] lg:object-[center_54%]" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        <div class="hidden fallback-flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,#4ade80_0%,#16a34a_46%,#052e16_100%)] p-6 text-center text-white">
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/10 bg-white/95 px-3 py-3 text-[var(--fastbite-ink)] sm:px-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[var(--fastbite-accent)]">Carta {{ $restaurant['name'] }}</p>
                            <p class="mt-0.5 text-sm text-[var(--fastbite-muted)]">{{ $productsCount }} productos visibles · {{ $selectedCategoryName }}</p>
                        </div>

                        <div class="grid w-full min-w-0 gap-2 sm:grid-cols-[minmax(0,1fr)_auto] lg:max-w-[680px]">
                            <label for="menu-search" class="search-shell flex min-h-[48px] items-center gap-3 rounded-full px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[var(--fastbite-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0a7 7 0 0 1 14 0Z" />
                                </svg>
                                <input id="menu-search" wire:model.live.debounce.250ms="search" type="search" placeholder="Buscar productos" class="w-full min-w-0 border-0 bg-transparent p-0 text-sm text-[var(--fastbite-ink)] placeholder:text-[var(--fastbite-muted)] focus:outline-none focus:ring-0">
                            </label>

                            <div class="delivery-toggle inline-flex w-full min-w-0 rounded-full p-1 sm:w-auto">
                                <button wire:click="setFulfillment('delivery')" class="delivery-pill {{ $fulfillment === 'delivery' ? 'delivery-pill-active' : '' }} min-h-[42px] min-w-0 flex-1 rounded-full px-3 py-2 text-sm font-semibold whitespace-normal sm:flex-none sm:px-4">
                                    Entrega
                                </button>
                                <button wire:click="setFulfillment('pickup')" class="delivery-pill {{ $fulfillment === 'pickup' ? 'delivery-pill-active' : '' }} min-h-[42px] min-w-0 flex-1 rounded-full px-3 py-2 text-sm font-semibold whitespace-normal sm:flex-none sm:px-4">
                                    Recogida
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="category-rail mt-3 -mx-1 overflow-x-auto px-1 pb-1">
                        <div class="flex w-max flex-nowrap gap-2">
                            <button wire:click="selectCategory" class="category-pill {{ $selectedCategory === null ? 'category-pill-active' : '' }} max-w-full shrink-0 rounded-full px-4 py-2.5 text-sm font-semibold whitespace-normal">
                                Todo
                            </button>
                            @foreach ($categories as $category)
                                <button wire:click="selectCategory('{{ $category['slug'] }}')" class="category-pill {{ $selectedCategory === $category['slug'] ? 'category-pill-active' : '' }} max-w-[85vw] shrink-0 rounded-full px-4 py-2.5 text-sm font-semibold whitespace-normal">
                                    <span class="mr-2">{{ $category['icon'] }}</span>{{ $category['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- ── Aviso del restaurante ──────────────────────────────────────── --}}
            @if(filled($restaurantSettings->customer_notice))
                <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-900">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                    </svg>
                    <p class="leading-relaxed">{{ $restaurantSettings->customer_notice }}</p>
                </div>
            @endif

            <div class="space-y-8">
                @forelse ($groupedProducts as $group)
                    <section id="{{ $group['category']['slug'] }}" class="space-y-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--fastbite-accent)]">{{ $group['category']['icon'] }} {{ $group['category']['name'] }}</p>
                            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-[var(--fastbite-ink)]">{{ $group['category']['count'] }} {{ $group['category']['count'] == 1 ? 'opción' : 'opciones' }}</h3>
                            <p class="mt-1 text-sm text-[var(--fastbite-muted)]">{{ $group['category']['description'] }}</p>
                        </div>

                        <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($group['products'] as $product)
                                <article class="product-card group overflow-hidden rounded-[28px]" data-product-card="{{ $product['id'] }}">
                                    <div class="relative aspect-[16/11] overflow-hidden bg-[var(--fastbite-surface-alt)]">
                                        <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Support\LocalgoStore::placeholderImageUrl() }}';">
                                        <span class="product-tag absolute left-4 top-4 rounded-full px-3 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-white">
                                            {{ $product['tag'] }}
                                        </span>
                                    </div>

                                    <div class="space-y-4 p-4 sm:p-5">
                                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h4 class="break-words text-lg font-bold tracking-tight text-[var(--fastbite-ink)]">{{ $product['name'] }}</h4>
                                                <p class="mt-2 text-sm leading-6 text-[var(--fastbite-muted)]">{{ $product['description'] }}</p>
                                            </div>
                                            <span class="price-pill w-fit shrink-0 rounded-full px-3 py-1.5 text-sm font-bold text-[var(--fastbite-ink)]">
                                                {{ number_format($product['price'], 2) }} €
                                            </span>
                                        </div>

                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <span class="min-w-0 break-words text-xs font-medium uppercase tracking-[0.14em] text-[var(--fastbite-muted)] sm:tracking-[0.18em]">{{ strtoupper($product['category_name']) }}</span>
                                            <button
                                                x-data="{ added: false, timer: null }"
                                                @click="clearTimeout(timer); added = true; timer = setTimeout(() => { added = false }, 1000)"
                                                :class="{ 'add-button-added': added }"
                                                wire:click="addToCart({{ $product['id'] }})"
                                                class="add-button inline-flex min-h-[46px] w-full items-center justify-center gap-2 rounded-full px-5 text-sm font-semibold text-white sm:w-auto"
                                                data-add-button="{{ $product['id'] }}"
                                                data-default-label="Añadir">
                                                <i class="add-button-check fa-solid fa-check" aria-hidden="true"></i>
                                                <span x-text="added ? 'Añadido' : 'Añadir'" data-add-button-label>Añadir</span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <section class="surface-panel rounded-[28px] px-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-2xl font-bold text-[var(--fastbite-ink)]">No hemos encontrado productos</h3>
                        <p class="mt-2 text-sm text-[var(--fastbite-muted)]">Prueba con otra categoría o cambia el texto del buscador.</p>
                        <button wire:click="selectCategory" class="add-button mt-5 inline-flex min-h-[46px] items-center justify-center rounded-full px-5 text-sm font-semibold text-white">
                            Ver toda la carta
                        </button>
                    </section>
                @endforelse
            </div>
        </section>
    </main>

</div>
