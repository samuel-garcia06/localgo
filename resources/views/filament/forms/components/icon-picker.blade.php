@php
    $statePath = $getStatePath();
    $icons = \App\Filament\Forms\Components\IconPicker::icons();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            selected: $wire.$entangle('{{ $statePath }}'),
            icons: {{ \Illuminate\Support\Js::from($icons) }}
        }"
        class="grid grid-cols-4 gap-2 sm:grid-cols-6 xl:grid-cols-7"
    >
        <template x-for="icon in icons" :key="icon.value">
            <button
                type="button"
                @click="selected = icon.value"
                :class="selected === icon.value
                    ? 'ring-2 ring-primary-500 bg-primary-50 border-primary-300 dark:bg-primary-950 dark:ring-primary-400 dark:border-primary-700'
                    : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5'"
                class="flex flex-col items-center justify-center gap-1.5 rounded-xl border p-3 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                <span class="text-2xl leading-none" x-text="icon.emoji"></span>
                <span
                    class="text-[10px] font-medium leading-tight"
                    :class="selected === icon.value ? 'text-primary-700 dark:text-primary-300' : 'text-gray-500 dark:text-gray-400'"
                    x-text="icon.label"
                ></span>
            </button>
        </template>

        <button
            type="button"
            @click="selected = null"
            :class="selected === null
                ? 'ring-2 ring-gray-400 bg-gray-100 border-gray-300 dark:bg-gray-800 dark:ring-gray-500'
                : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5'"
            class="flex flex-col items-center justify-center gap-1.5 rounded-xl border p-3 transition-all duration-150 focus:outline-none"
        >
            <span class="text-2xl leading-none">✕</span>
            <span class="text-[10px] font-medium leading-tight text-gray-400">Sin icono</span>
        </button>
    </div>
</x-dynamic-component>
