<div class="space-y-10">
    <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-sm max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">🔧 Persoonlijke Integratie Instellingen</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- GitHub sectie -->
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-black">GitHub</h3>

                <!-- GitHub gebruikersnaam -->
                <div>
                    <label class="block text-sm text-gray-600 mb-1">GitHub gebruikersnaam</label>
                    <input wire:model="github_username" placeholder="Bijv. mijn-gebruikersnaam"
                           class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm">
                    @error('github_username') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- GitHub token (verborgen) -->
                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm text-gray-600 mb-1">GitHub token</label>
                    <input :type="show ? 'text' : 'password'" wire:model="github_token"
                           placeholder="Bijv. ghp_..."
                           class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm pr-10">
                    <button type="button" @click="show = !show"
                            class="absolute top-9 right-3 text-gray-500 hover:text-gray-700">
                        <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.71-4.362m3.336-2.12A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.953 9.953 0 01-4.507 5.569M3 3l18 18"/>
                        </svg>
                    </button>
                    @error('github_token') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- GitHub repo -->
                <div x-data="{ show: false }">
                    <label class="block text-sm text-gray-600 mb-1">Standaard GitHub repository</label>
                    <div class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            wire:model="github_repo"
                            placeholder="Bijv. mijn-gebruiker/voorbeeld-repo"
                            class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm pr-10"
                        >
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-600">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.982 9.982 0 012.442-4.242M9.878 9.879a3 3 0 104.243 4.243M6.1 6.1l11.8 11.8"/>
                            </svg>
                        </button>
                    </div>
                    @error('github_repo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

            </div>

            <!-- Jira sectie -->
            <div class="space-y-3 mt-6">
                <h3 class="text-lg font-semibold text-black">Jira</h3>

                <!-- Jira email (verborgen) -->
                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm text-gray-600 mb-1">Jira e-mailadres</label>
                    <input :type="show ? 'text' : 'password'" wire:model="jira_email"
                           placeholder="Bijv. naam@bedrijf.nl"
                           class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm pr-10">
                    <button type="button" @click="show = !show"
                            class="absolute top-9 right-3 text-gray-500 hover:text-gray-700">
                        <!-- zelfde iconen als eerder -->
                        <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.71-4.362m3.336-2.12A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.953 9.953 0 01-4.507 5.569M3 3l18 18"/>
                        </svg>
                    </button>
                    @error('jira_email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Jira token (verborgen) -->
                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm text-gray-600 mb-1">Jira API token</label>
                    <input
                        :type="show ? 'text' : 'password'"
                        wire:model="jira_token"
                        placeholder="Bijv. abc123..."
                        class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm pr-10 overflow-hidden truncate"
                    >

                    <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-3 my-auto text-gray-500 hover:text-gray-700 bg-white px-1">
                    <!-- Oog gesloten -->
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <!-- Oog open -->
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.982 9.982 0 012.442-4.242M9.878 9.879a3 3 0 104.243 4.243M6.1 6.1l11.8 11.8"/>
                        </svg>
                    </button>

                    @error('jira_token') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>


                <!-- Jira domein -->
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jira domein (zonder https://)</label>
                    <input wire:model="jira_domain" placeholder="Bijv. mijn-bedrijf.atlassian.net"
                           class="w-full rounded-md border border-gray-300 focus:border-black focus:ring focus:ring-black/20 p-2 shadow-sm">
                    @error('jira_domain') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Submit knop -->
            <div class="flex justify-center mt-8">
                <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition">
                    Opslaan
                </button>
            </div>

            <!-- Succesmelding -->
            <div
                x-data="{ show: false }"
                x-init="
                    $wire.on('integration-saved', () => {
                        show = true;
                        setTimeout(() => show = false, 4000);
                    })
                "
                x-show="show"
                x-transition
                class="text-green-600 text-sm mt-2"
            >
                Integratie-instellingen succesvol opgeslagen!
            </div>
        </form>
    </div>
</div>
