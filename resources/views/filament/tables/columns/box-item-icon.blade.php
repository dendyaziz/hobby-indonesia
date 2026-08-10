@php
    $record = $getRecord();
    $iconName = $record->icon_name;
    $isCustom = $iconName === 'custom';
    $imageUrl = $isCustom ? $record->getFirstMediaUrl('box-item-custom-icon') : null;

    $svgContent = null;
    if (!$isCustom && $iconName) {
        $legacyMap = [
            'target' => 'GpsFix.svg',
            'gem' => 'Diamond.svg',
            'user-round' => 'User.svg',
            'waypoints' => 'Path.svg',
        ];

        $normalized = strtolower(trim($iconName));
        $filename = $legacyMap[$normalized] ?? (str_ends_with($iconName, '.svg') ? $iconName : $iconName . '.svg');

        $svgPath = public_path('icons/' . $filename);
        if (!file_exists($svgPath) && !str_ends_with($iconName, '.svg')) {
            $capitalizedFilename = ucfirst($iconName) . '.svg';
            $svgPathCapitalized = public_path('icons/' . $capitalizedFilename);
            if (file_exists($svgPathCapitalized)) {
                $svgPath = $svgPathCapitalized;
            }
        }

        if (file_exists($svgPath)) {
            $svgContent = file_get_contents($svgPath);
        }
    }
@endphp

<div class="flex items-center justify-start px-4 py-2">
    @if($isCustom && $imageUrl)
        <img src="{{ $imageUrl }}" class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700" style="width: 32px; height: 32px; border-radius: 6px;" />
    @elseif($svgContent)
        <div class="table-icon-wrapper w-8 h-8 flex items-center justify-center" style="color: #E38825; width: 32px; height: 32px;">
            <style>
                .table-icon-wrapper svg {
                    width: 32px !important;
                    height: 32px !important;
                    display: block;
                    margin: auto;
                }
            </style>
            {!! $svgContent !!}
        </div>
    @else
        <span class="text-xs text-gray-400 italic">{{ $iconName ?: 'None' }}</span>
    @endif
</div>
