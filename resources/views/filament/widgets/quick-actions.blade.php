<x-filament-widgets::widget>
    <x-filament::section heading="Acciones rápidas">
        <div class="grid grid-cols-2 gap-3">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    target="{{ $action['target'] }}"
                    class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        class="h-5 w-5 shrink-0 text-gray-400"
                    />
                    <span class="leading-tight">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
