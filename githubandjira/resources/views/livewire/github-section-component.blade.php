<div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-md space-y-6">
    <h2 class="text-2xl font-semibold text-gray-800">🔍 Zoek in GitHub Repositories</h2>

    <form wire:submit.prevent="showRepository" class="space-y-5">
        <div>
            <label for="githubRepoName" class="block text-sm font-medium text-gray-700 mb-1">Repository Naam</label>
            <input type="text" id="githubRepoName" wire:model="githubRepoName" placeholder="Bijv. ZakariaHappy/TestingPoC" required class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <button type="submit" class="bg-gray-800 text-white px-5 py-2.5 rounded-md hover:bg-gray-900 transition font-medium">Zoek Repository</button>
        </div>
    </form>

    <!-- Laadindicator voor repository ophalen -->
    <div wire:loading wire:target="showRepository" class="flex items-center gap-2 text-gray-700 text-sm mt-4">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
        Repository wordt opgehaald...
    </div>

    @if ($error)
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">{{ $error }}</div>
    @endif

    @if ($repository)
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-green-700 mb-3">✅ Gevonden GitHub Repository</h3>
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-md p-3">
                <span class="font-medium text-gray-800">{{ $repository->getName() }}</span>
                <button wire:click="getBranches('{{ addslashes($repository->getFullName()) }}')" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-blue-700 transition">Bekijk Branches</button>
            </div>
        </div>
    @elseif (!$repository && !$error)
        <p class="text-gray-600 mt-6 italic" wire:loading.remove wire:target="showRepository">Geen repositories gevonden.</p>
    @endif

    <!-- Laadindicator voor branches ophalen -->
    <div wire:loading wire:target="getBranches" class="flex items-center gap-2 text-gray-700 text-sm mt-4">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
        Branches worden opgehaald...
    </div>

    @if ($branches)
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-green-700 mb-3">🌿 Branches van {{ $selectedRepo }}</h3>
            <ul class="list-disc pl-5 text-sm text-gray-800 space-y-1">
                @foreach ($branches as $branch)
                    <li>{{ $branch['name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
