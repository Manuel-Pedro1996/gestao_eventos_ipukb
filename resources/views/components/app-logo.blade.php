@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Gestão de Eventos" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-15 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-15 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Gestão de Eventos" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-30 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-30 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
