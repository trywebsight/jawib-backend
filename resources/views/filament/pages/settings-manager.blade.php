<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
        <x-filament::button style="margin-top: 1rem; display:block;" type="submit">
            {{__('save')}}
        </x-filament::button>
    </form>
</x-filament::page>
