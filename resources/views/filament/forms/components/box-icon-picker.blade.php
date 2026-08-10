<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <style>
            .icon-picker-grid-btn {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 3rem;
                height: 3rem;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s;
                box-sizing: border-box;
                border: 2px solid color-mix(in srgb, var(--gray-400) 20%, transparent);
                background-color: transparent;
            }
            .icon-picker-grid-btn.is-selected {
                border: 2px solid var(--primary-500) !important;
                background-color: color-mix(in srgb, var(--primary-500) 15%, transparent) !important;
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent) !important;
            }
            .icon-picker-grid-btn svg {
                width: 28px !important;
                height: 28px !important;
                display: block;
                margin: auto !important;
            }
            .icon-picker-grid-btn .icon-container {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                filter: grayscale(1);
                transition: all 0.2s;
            }
            .icon-picker-grid-btn.is-selected .icon-container {
                opacity: 1 !important;
                filter: none !important;
            }
        </style>

        <div class="overflow-y-auto max-h-64" style="display: flex; flex-wrap: wrap; gap: 10px; padding: 4px 0;">
            @foreach($icons as $iconFilename)
                <button
                    type="button"
                    @click="state = '{{ $iconFilename }}'"
                    title="{{ str_replace('.svg', '', $iconFilename) }}"
                    class="icon-picker-grid-btn"
                    :class="{ 'is-selected': state === '{{ $iconFilename }}' }"
                >
                    <div class="icon-container">
                        {!! file_get_contents(public_path('icons/' . $iconFilename)) !!}
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
