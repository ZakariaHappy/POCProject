<div class="p-4 rounded-lg border bg-white">
    <h2 class="font-semibold text-gray-900 text-lg mb-1">Stap 1: Kies Jira-project & release</h2>
    {{-- Zet hier jouw bestaande Jira selectie form / component! --}}
    <livewire:jira-section-component wire:key="jira-section-step1" />

    <div class="flex justify-end mt-4">
        @if(!empty($issues))
            <button wire:click="goToNext"
                    class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700 focus:outline-none">
                Volgende: GitHub Branches ophalen
            </button>
        @endif
    </div>
</div>
