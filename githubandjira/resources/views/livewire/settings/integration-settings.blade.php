

<div class="space-y-10 max-w-3xl mx-auto bg-white border border-gray-200 rounded-xl p-8 shadow-sm">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">🔧 Persoonlijke Integratie Instellingen</h2>

    <div class="mb-4">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-4 py-2 bg-green-500 text-gray-700 text-sm font-semibold rounded hover:bg-green-700 transition">
            ← Terug naar dashboard
        </a>
    </div>


    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Repository Instellingen -->
        <div class="space-y-3">
            <h3 class="text-lg font-semibold">GitHub</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700">Organisatie naam</label>
                <input type="text" wire:model="github_username"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm" />
            </div>

            <div x-data="{ show: false }">
                <label class="block text-sm font-medium text-gray-700">Token</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        wire:model.lazy="github_token"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm pr-12"
                        :placeholder="show ? github_token : maskedGithubToken"
                        autocomplete="off"
                    />
                    <button type="button"
                            class="absolute top-1/2 right-2 -translate-y-1/2 text-xs text-blue-600"
                            @click="show = !show" tabindex="-1">
                        <span x-text="show ? 'Verberg' : 'Toon'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Jira Instellingen -->
        <div class="space-y-3 mt-6">
            <h3 class="text-lg font-semibold">Jira</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700">E-mailadres</label>
                <input type="text" wire:model="jira_email"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm" />
            </div>

            <div x-data="{ show: false }">
                <label class="block text-sm font-medium text-gray-700">API Token</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        wire:model.lazy="jira_token"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm pr-12"
                        :placeholder="show ? jira_token : maskedJiraToken"
                        autocomplete="off"
                    />
                    <button type="button"
                            class="absolute top-1/2 right-2 -translate-y-1/2 text-xs text-blue-600"
                            @click="show = !show" tabindex="-1">
                        <span x-text="show ? 'Verberg' : 'Toon'"></span>
                    </button>
                </div>
            </div>

            <div x-data="{ show: false }">
                <label class="block text-sm font-medium text-gray-700">Domein (zonder https://)</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        wire:model.lazy="jira_domain"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm pr-12"
                        :placeholder="show ? jira_domain : maskedJiraDomain"
                        autocomplete="off"
                    />
                    <button type="button"
                            class="absolute top-1/2 right-2 -translate-y-1/2 text-xs text-blue-600"
                            @click="show = !show" tabindex="-1">
                        <span x-text="show ? 'Verberg' : 'Toon'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Opslaan -->
        <div class="flex justify-center mt-8">
            <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                Opslaan
            </button>
        </div>

        <!-- Succesmelding -->
        @if (session()->has('message'))
            <div class="text-green-600 text-sm text-center">
                {{ session('message') }}
            </div>
        @endif
    </form>
</div>
