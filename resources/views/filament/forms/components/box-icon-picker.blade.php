<div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}', true) }">
    <div class="flex flex-wrap gap-3">
        
        <!-- Predefined Icon: Waypoints -->
        <button 
            type="button" 
            @click="state = 'waypoints'" 
            class="flex items-center justify-center w-14 h-14 rounded-xl transition cursor-pointer"
            :style="state === 'waypoints' 
                ? 'border: 2px solid var(--primary-500); background-color: color-mix(in srgb, var(--primary-500) 15%, transparent); color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent);' 
                : 'border: 1px solid color-mix(in srgb, var(--gray-400) 30%, transparent); background-color: transparent; color: var(--gray-400);'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4.5" r="2.5"/><path d="m10.2 6.3-3.9 3.9"/><circle cx="4.5" cy="12" r="2.5"/><path d="M7 12h10"/><circle cx="19.5" cy="12" r="2.5"/><path d="m13.8 17.7 3.9-3.9"/><circle cx="12" cy="19.5" r="2.5"/></svg>
        </button>

        <!-- Predefined Icon: Target -->
        <button 
            type="button" 
            @click="state = 'target'" 
            class="flex items-center justify-center w-14 h-14 rounded-xl transition cursor-pointer"
            :style="state === 'target' 
                ? 'border: 2px solid var(--primary-500); background-color: color-mix(in srgb, var(--primary-500) 15%, transparent); color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent);' 
                : 'border: 1px solid color-mix(in srgb, var(--gray-400) 30%, transparent); background-color: transparent; color: var(--gray-400);'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
        </button>

        <!-- Predefined Icon: Gem -->
        <button 
            type="button" 
            @click="state = 'gem'" 
            class="flex items-center justify-center w-14 h-14 rounded-xl transition cursor-pointer"
            :style="state === 'gem' 
                ? 'border: 2px solid var(--primary-500); background-color: color-mix(in srgb, var(--primary-500) 15%, transparent); color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent);' 
                : 'border: 1px solid color-mix(in srgb, var(--gray-400) 30%, transparent); background-color: transparent; color: var(--gray-400);'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
        </button>

        <!-- Predefined Icon: User Round -->
        <button 
            type="button" 
            @click="state = 'user-round'" 
            class="flex items-center justify-center w-14 h-14 rounded-xl transition cursor-pointer"
            :style="state === 'user-round' 
                ? 'border: 2px solid var(--primary-500); background-color: color-mix(in srgb, var(--primary-500) 15%, transparent); color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent);' 
                : 'border: 1px solid color-mix(in srgb, var(--gray-400) 30%, transparent); background-color: transparent; color: var(--gray-400);'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
        </button>

        <!-- Custom Upload option -->
        <button 
            type="button" 
            @click="state = 'custom'" 
            class="flex items-center justify-center w-14 h-14 rounded-xl transition cursor-pointer"
            title="Upload Custom Image"
            :style="state === 'custom' 
                ? 'border: 2px solid var(--primary-500); background-color: color-mix(in srgb, var(--primary-500) 15%, transparent); color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-500) 25%, transparent);' 
                : 'border: 1px solid color-mix(in srgb, var(--gray-400) 30%, transparent); background-color: transparent; color: var(--gray-400);'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        </button>

    </div>
</div>
