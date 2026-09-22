@php
    use App\Enums\RestaurantOperationalStatus;

    $current  = $this->currentStatus();
    $statuses = RestaurantOperationalStatus::cases();

    $bgMap = [
        'open'   => 'bg-success-50 dark:bg-success-950 border-success-200 dark:border-success-800',
        'busy'   => 'bg-warning-50 dark:bg-warning-950 border-warning-200 dark:border-warning-800',
        'closed' => 'bg-danger-50 dark:bg-danger-950 border-danger-200 dark:border-danger-800',
    ];
    $textMap = [
        'open'   => 'text-success-800 dark:text-success-200',
        'busy'   => 'text-warning-800 dark:text-warning-200',
        'closed' => 'text-danger-800 dark:text-danger-200',
    ];
    $btnActive = [
        'open'   => 'bg-success-600 hover:bg-success-700 text-white shadow-sm',
        'busy'   => 'bg-warning-500 hover:bg-warning-600 text-white shadow-sm',
        'closed' => 'bg-danger-600 hover:bg-danger-700 text-white shadow-sm',
    ];
    $btnInactive = 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700';
@endphp

<x-filament-widgets::widget>
    <div class="rounded-xl border px-5 py-4 {{ $bgMap[$status] }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- Current status label --}}
            <div class="flex items-center gap-3">
                <span class="text-2xl leading-none">{{ $current->emoji() }}</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $textMap[$status] }} opacity-70">Estado del restaurante</p>
                    <p class="text-lg font-black {{ $textMap[$status] }}">{{ $current->label() }}</p>
                </div>
            </div>

            {{-- Quick-switch buttons --}}
            <div class="flex flex-wrap gap-2">
                @foreach($statuses as $s)
                    <button
                        wire:click="setStatus('{{ $s->value }}')"
                        wire:loading.attr="disabled"
                        wire:target="setStatus('{{ $s->value }}')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition-colors disabled:opacity-60
                            {{ $status === $s->value ? $btnActive[$s->value] : $btnInactive }}"
                    >
                        <span>{{ $s->emoji() }}</span>
                        <span>{{ $s->label() }}</span>
                    </button>
                @endforeach
            </div>

        </div>

        @if($current === \App\Enums\RestaurantOperationalStatus::Busy)
            <p class="mt-3 text-sm {{ $textMap[$status] }} opacity-80">
                Los clientes pueden seguir realizando pedidos. El tiempo estimado de espera podría ser mayor al habitual.
            </p>
        @elseif($current === \App\Enums\RestaurantOperationalStatus::Closed)
            <p class="mt-3 text-sm {{ $textMap[$status] }} opacity-80">
                Los clientes pueden ver la carta pero <strong>no pueden finalizar pedidos</strong> hasta que vuelvas a activar el restaurante.
            </p>
        @endif
    </div>
</x-filament-widgets::widget>
