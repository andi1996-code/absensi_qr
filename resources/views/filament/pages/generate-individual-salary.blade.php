<div class="space-y-6">
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
                wire:loading.attr="disabled"
            >
                Simpan & Generate Gaji
            </x-filament::button>
        </div>
    </form>
</div>
