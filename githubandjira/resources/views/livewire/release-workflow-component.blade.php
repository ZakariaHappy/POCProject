<div>
    @php
        $steps = [
            1 => 'Jira Selectie',
            2 => 'Branches ophalen',
            3 => 'Controle taken',
            4 => 'Matches bekijken',
            5 => 'Release branch',
            6 => 'Mergevoorstellen',
            7 => 'Release uitvoeren',
        ];
    @endphp

    <div class="mb-4">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded hover:bg-green-700 transition">
            ← Terug naar dashboard
        </a>
    </div>


    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex justify-between items-center text-xs sm:text-sm font-medium text-gray-600">
            @foreach ($steps as $step => $label)
                <div class="flex items-center space-x-2">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full border-2
                    {{ $currentStep > $step ? 'bg-green-600 text-white border-green-600' :
                       ($currentStep === $step ? 'bg-gray-800 text-white border-gray-800' : 'bg-white border-gray-300') }}">
                        {{ $step }}
                    </div>
                    <span class="hidden sm:inline-block {{ $currentStep > $step ? 'text-green-700' : 'text-gray-700' }}">{{ $label }}</span>
                </div>
                @if (!$loop->last)
                    <div class="flex-auto border-t-2 mx-2 {{ $currentStep > $step ? 'border-green-600' : 'border-gray-300' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="space-y-6 w-full">
        {{-- Stap 1 --}}
        @if($currentStep === 1)
            <div class="p-4 rounded-lg border bg-white">
                <h2 class="font-semibold text-gray-900 text-lg mb-1">Stap 1: Kies Jira-project & release</h2>
                <livewire:jira-section-component/>
                <div class="flex justify-end mt-4">
                    @if(!empty($issues))
                        <button wire:click="goToStep(2)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700 focus:outline-none">Volgende: GitHub Branches ophalen</button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Stap 2 --}}
        @if($currentStep === 2)
            <div class="p-4 rounded-lg border bg-white mb-2">
                <h2 class="font-semibold text-gray-900 text-lg mb-1">Stap 2: Haal GitHub branches op</h2>
                <livewire:github-section-component :issues="$issues"/>
                <div class="flex justify-between mt-4">
                    <button wire:click="goToStep(1)" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400">Vorige stap</button>
                    @if(!empty($branches))
                        <button wire:click="goToStep(3)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700">Volgende: Openstaande taken controleren</button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Stap 3 --}}
        @if($currentStep === 3)
            <div class="p-6 rounded-lg border bg-white mb-2">
                <h2 class="font-semibold text-gray-900 text-lg mb-4">Stap 3: Openstaande subtasks/linked tasks</h2>
                @if(!empty($releaseStatus['notAccepted']))
                    <p class="font-bold text-red-500 mb-3"><span class="text-lg">⚠️</span> Let op: Er zijn nog openstaande taken/subtasks</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($releaseStatus['notAccepted'] as $issue)
                            <div class="bg-white border rounded-lg p-4 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <a href="{{ $jiraBaseUrl }}/browse/{{ $issue['key'] }}" class="font-bold text-blue-600 underline" target="_blank" rel="noopener">{{ $issue['key'] }}</a>
                                    <span class="text-gray-500">–</span>
                                    <span class="font-medium text-gray-800">{{ $issue['summary'] }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm mb-2">
                                    <span class="bg-yellow-300 text-gray-700 px-2 py-0.5 rounded-full">{{ $issue['status'] }}</span>
                                    @if (!empty($issue['parentKey']))
                                        <span class="text-xs text-gray-600">↳ Subtask van: <a href="{{ $jiraBaseUrl }}/browse/{{ $issue['parentKey'] }}" class="underline text-blue-600" target="_blank" rel="noopener">{{ $issue['parentKey'] }}</a></span>
                                    @endif
                                    <span class="text-xs text-green-800">
                                        @if(!empty($issue['assignee'])) Assigned to: {{ $issue['assignee'] }} @else <span class="text-gray-400">No assignee</span> @endif
                                    </span>
                                </div>
                                {{-- Linked Issues --}}
                                @if (!empty($issue['linkedIssues']))
                                    <div class="border-l-4 border-gray-200 pl-3 mt-2">
                                        <div class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">🔗 Linked Issues:</div>
                                        <ul class="space-y-1 text-xs">
                                            @foreach($issue['linkedIssues'] as $linked)
                                                <li>
                                                    <a href="{{ $jiraBaseUrl }}/browse/{{ $linked['key'] }}" class="font-bold text-gray-800 underline" target="_blank" rel="noopener">{{ $linked['key'] }}</a>
                                                    <span class="text-gray-700">– {{ $linked['summary'] }}</span>
                                                    <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded-full">{{ $linked['status'] }}</span>
                                                    @if (!empty($linked['parentKey']))
                                                        <span class="text-xs text-gray-600">↳ Linked aan taak: <a href="{{ $jiraBaseUrl }}/browse/{{ $linked['parentKey'] }}" class="underline text-gray-800" target="_blank" rel="noopener">{{ $linked['parentKey'] }}</a></span>
                                                    @endif
                                                    <span class="text-xs text-gray-700 ml-2">
                                                        @if(!empty($linked['assignee'])) Assigned to: {{ $linked['assignee'] }} @else <span class="text-gray-400">No assignee</span> @endif
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                {{-- Subtasks --}}
                                @if (!empty($issue['subtasks']))
                                    <div class="border-l-4 border-gray-200 pl-3 mt-2">
                                        <div class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">📝 Subtasks:</div>
                                        <ul class="space-y-1 text-xs">
                                            @foreach($issue['subtasks'] as $subtask)
                                                <li>
                                                    <a href="{{ $jiraBaseUrl }}/browse/{{ $subtask['key'] }}" class="font-bold text-gray-800 underline" target="_blank" rel="noopener">{{ $subtask['key'] }}</a>
                                                    <span class="text-gray-700">– {{ $subtask['summary'] }}</span>
                                                    <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded-full">{{ $subtask['status'] }}</span>
                                                    @if (!empty($subtask['parentKey']))
                                                        <span class="text-xs text-gray-600">↳ Subtask van: <a href="{{ $jiraBaseUrl }}/browse/{{ $subtask['parentKey'] }}" class="underline text-gray-800" target="_blank" rel="noopener">{{ $subtask['parentKey'] }}</a></span>
                                                    @endif
                                                    <span class="text-xs text-gray-700 ml-2">
                                                        @if(!empty($subtask['assignee'])) Assigned to: {{ $subtask['assignee'] }} @else <span class="text-gray-400">No assignee</span> @endif
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-700">Geen openstaande taken!</p>
                @endif
                <div class="flex justify-between mt-6">
                    <button wire:click="goToStep(2)" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400">Vorige stap</button>
                    <button wire:click="goToStep(4)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700">Ga door met release</button>
                </div>
            </div>
        @endif

        {{-- Stap 4 --}}
        @if($currentStep === 4)
            <div class="p-6 rounded-lg border bg-white mb-2">
                <h2 class="font-semibold text-gray-900 text-lg mb-4">Stap 4: Bekijk matches tussen Jira-issues en GitHub-branches</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    @if (!empty($unmatchedIssues))
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-900 p-4 rounded mb-6">
                            <p class="font-bold mb-2">⚠️ Niet alle Jira-issues zijn gekoppeld aan een branch:</p>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                @foreach ($unmatchedIssues as $issue)
                                    <li>
                                        <a href="{{ $jiraBaseUrl }}/browse/{{ $issue['key'] }}"
                                           class="underline text-blue-600"
                                           target="_blank" rel="noopener">
                                            {{ $issue['key'] }}
                                        </a> – {{ $issue['summary'] ?? '' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                @foreach ($matchedBranches as $match)
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <a href="{{ $jiraBaseUrl }}/browse/{{ $match['issue']['key'] }}" class="text-lg font-bold text-blue-600 underline" target="_blank" rel="noopener">{{ $match['issue']['key'] }}</a>
                                <span class="text-gray-500">–</span>
                                <span class="font-medium text-gray-800">{{ $match['issue']['summary'] }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-sm mb-2">
                                <span class="bg-yellow-300 text-gray-700 px-2 py-0.5 rounded-full">{{ $match['issue']['status'] }}</span>
                                <span class="text-gray-500">👤 {{ $match['issue']['assignee'] ?? 'n/a' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm mb-2">
                                <span class="text-gray-700 font-semibold">🌿 Branch:</span>
                                <a href="{{ rtrim($GitHubBaseUrl, '/') }}/{{ $selectedRepo }}/tree/{{ $match['branch']['name'] }}" class="bg-gray-50 px-2 py-1 rounded text-blue-600 font-mono underline" target="_blank" rel="noopener">{{ $match['branch']['name'] }}</a>
                            </div>
                            @if(!empty($match['issue']['subtasks']))
                                <div class="border-l-4 border-gray-200 pl-3 mt-3 mb-2">
                                    <div class="text-xs font-semibold text-gray-800 mb-1 flex items-center gap-1">📝 Subtasks:</div>
                                    <ul class="space-y-1 text-xs">
                                        @foreach($match['issue']['subtasks'] as $subtask)
                                            <li>
                                                <a href="{{ $jiraBaseUrl }}/browse/{{ $subtask['key'] }}" class="font-bold text-blue-600 underline" target="_blank" rel="noopener">{{ $subtask['key'] }}</a>
                                                <span class="text-gray-700">– {{ $subtask['summary'] }}</span>
                                                <span class="bg-yellow-300 text-gray-700 px-1.5 py-0.5 rounded-full">{{ $subtask['status'] }}</span>
                                                <span class="text-gray-500">👤 {{ $subtask['assignee'] ?? 'n/a' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if (!empty($match['issue']['linkedIssues']))
                                <div class="border-l-4 border-gray-200 pl-3 mt-2">
                                    <div class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">🔗 Linked Issues:</div>
                                    <ul class="space-y-1 text-xs">
                                        @foreach ($match['issue']['linkedIssues'] as $linked)
                                            <li>
                                                <a href="{{ $jiraBaseUrl }}/browse/{{ $linked['key'] }}" class="font-bold text-blue-600 underline" target="_blank" rel="noopener">{{ $linked['key'] }}</a>
                                                <span class="text-gray-700">– {{ $linked['summary'] }}</span>
                                                <span class="bg-yellow-300 text-gray-700 px-1.5 py-0.5 rounded-full">{{ $linked['status'] }}</span>
                                                <span class="text-gray-500">👤 {{ $linked['assignee'] ?? 'n/a' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-6">
                    <button wire:click="goToStep(3)" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400">Vorige stap</button>
                    <button wire:click="goToStep(5)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700">Volgende: Release Branch aanmaken</button>
                </div>
            </div>
        @endif

        {{-- Stap 5 --}}
        @if($currentStep === 5)
            <div class="mt-4">
                <div class="flex justify-between mb-4">
                    <button wire:click="goToStep(4)" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400">Vorige stap</button>
                    <button wire:click="goToStep(6)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700">Volgende: Mergevoorstellen maken</button>
                </div>
                @if($releaseBranchExists)
                    <div class="mb-4 p-2 rounded bg-gray-50 border border-gray-200 text-gray-800">
                        De release branch <strong class="text-green-600">{{ $releaseBranchFormatted }}</strong> bestaat al.
                    </div>
                @else
                    <div class="mb-4 p-2 rounded bg-gray-50 border border-gray-200 text-gray-800">
                        De release branch <strong>{{ $releaseBranchFormatted }}</strong> bestaat nog niet.
                    </div>
                @endif
                <div class="mb-4 p-4 bg-white rounded shadow text-gray-800">
                    <div class="font-semibold mb-2">Release branch aanmaken</div>
                    <div>
                        Je staat op het punt om een release branch aan te maken in
                        <span class="font-mono bg-gray-100 rounded px-2 py-1"><strong class="text-green-600">{{ $selectedRepo }} </strong></span>.
                    </div>
                    <div class="mt-1">
                        De branch krijgt de naam:
                        @if($releaseBranchFormatted)
                            <span class="font-mono bg-gray-100 rounded px-2 py-1"><strong class="text-green-600">{{ $releaseBranchFormatted }}</strong></span>
                        @else
                            <span class="text-red-700 font-semibold">Er is geen release-datum gevonden voor deze Release!</span>
                        @endif
                    </div>
                    <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                        <li>Deze branch wordt aangemaakt vanaf <span class="font-mono">main</span>.</li>
                        <li>Dit is de basis voor alle mergevoorstellen in deze release.</li>
                        <li>Controleer de naam goed; na aanmaken kun je deze niet meer wijzigen.</li>
                    </ul>
                </div>
                <button wire:click="createReleaseBranch" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700" @if($releaseBranchExists) disabled @endif>Maak Release Branch</button>
                {{-- Loading indicator voor createReleaseBranch --}}
                <div wire:loading wire:target="createReleaseBranch" class="flex items-center gap-2 text-gray-700 text-sm mt-4">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Release branch wordt aangemaakt...
                </div>



            @if (session()->has('jira_message'))
                    <div class="mt-4 text-green-700">{{ session('jira_message') }}</div>
                @endif
                @if (session()->has('error'))
                    <div class="mt-4 text-red-700">{{ session('error') }}
                        @if(session('error_details'))
                            <br><span class="text-xs">{{ session('error_details') }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif


        {{-- Stap 6 --}}
        @if($currentStep === 6)
            <div class="mt-4">
                <div class="flex justify-between mb-4">
                    <button wire:click="goToStep(5)" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400">Vorige stap</button>
                    <button wire:click="goToStep(7)" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700">Volgende: Release uitvoeren</button>
                </div>
                <div class="mb-6 p-6 bg-white rounded-lg border border-gray-200 shadow text-gray-800">
                    <div class="font-semibold text-lg mb-2">Mergevoorstellen aanmaken</div>
                    <p>
                        Je staat op het punt om mergevoorstellen (pull requests) aan te maken voor alle relevante feature branches in
                        <span class="font-mono bg-gray-100 rounded px-2 py-1 text-gray-900"><strong class="text-green-600">{{ $selectedRepo }}</strong></span>
                        richting
                        <span class="font-mono bg-gray-100 rounded px-2 py-1 text-gray-900"><strong class="text-green-600">{{ $releaseBranchFormatted }}</strong></span>.
                    </p>
                    @if(!empty($matchedBranches))
                        <div class="mt-4">
                            <span class="font-semibold">Deze branches worden meegenomen:</span>
                            <ul class="divide-y divide-gray-100 mt-2">
                                @foreach($matchedBranches as $branch)
                                    <li class="py-2 flex flex-wrap items-baseline gap-x-2 text-base leading-6">
                                        <a href="{{ rtrim($GitHubBaseUrl, '/') }}/{{ $selectedRepo }}/tree/{{ $branch['branch']['name'] }}"
                                           target="_blank" rel="noopener"
                                           class="font-mono bg-gray-100 rounded px-2 py-1 text-sm text-blue-600 underline">
                                            {{ $branch['branch']['name'] }}
                                        </a>
                                        <span class="ml-1 text-xs text-gray-500 font-semibold uppercase tracking-wide">voor</span>
                                        <a href="{{ $jiraBaseUrl }}/browse/{{ $branch['issue']['key'] }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-block bg-gray-100 text-blue-600 rounded px-2 py-0.5 text-xs font-bold underline">
                                            {{ $branch['issue']['key'] }}
                                        </a>
                                        @if(!empty($branch['issue']['summary']))
                                            <span class="text-gray-600 text-xs italic ml-2">– {{ $branch['issue']['summary'] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="mt-2 text-red-700">
                            Er zijn geen branches gevonden die aan een issue gekoppeld zijn.
                        </div>
                    @endif
                    <ul class="mt-4 text-sm text-gray-600 list-disc list-inside space-y-1">
                        <li>Voor elke branch wordt een pull request aangemaakt naar de release branch.</li>
                        <li>Zorg dat feature branches up-to-date zijn met <span class="font-mono">main</span> om mergeconflicten te voorkomen.</li>
                        <li>Na het aanmaken kun je de status van elke pull request hieronder zien.</li>
                    </ul>
                </div>
                <button wire:click="createMergeProposals" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">Maak Mergevoorstellen</button>
                {{-- Loading indicator voor createMergeProposals --}}
                <div wire:loading wire:target="createMergeProposals" class="flex items-center gap-2 text-gray-700 text-sm mt-4">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Mergevoorstellen worden aangemaakt...
                </div>
            @if (!empty($pullRequestResults))
                    <div id="merge-results" class="bg-white border border-gray-200 rounded-lg p-6 shadow mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resultaten van Mergevoorstellen:</h3>
                        <ul class="space-y-4 text-sm">
                            @foreach ($pullRequestResults as $pr)
                                <li class="border-b pb-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            @if ($pr['url'])
                                                <a href="{{ $pr['url'] }}" class="text-gray-800 font-mono font-medium underline" target="_blank">
                                                    {{ $pr['branch'] }}
                                                </a>
                                            @else
                                                <a href="{{ rtrim($GitHubBaseUrl, '/') }}/{{ $selectedRepo }}/tree/{{ $pr['branch'] }}"
                                                   target="_blank" rel="noopener"
                                                   class="text-gray-700 font-mono font-medium underline">
                                                    {{ $pr['branch'] }}
                                                </a>
                                            @endif
                                            <span class="ml-2 text-xs text-gray-500">
                                        voor
                                        @if(is_array($pr['issue']))
                                                    @foreach($pr['issue'] as $issue)
                                                        <a href="{{ $jiraBaseUrl }}/browse/{{ $issue['key'] }}"
                                                           target="_blank"
                                                           rel="noopener"
                                                           class="inline-block bg-gray-100 text-gray-900 rounded px-2 py-0.5 text-xs font-bold ml-1 underline">
                                                    {{ $issue['key'] }}
                                                </a>
                                                        @if(!empty($issue['summary']))
                                                            <span class="italic text-gray-600 ml-1">– {{ $issue['summary'] }}</span>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <a href="{{ $jiraBaseUrl }}/browse/{{ $pr['issue'] }}"
                                                       target="_blank"
                                                       rel="noopener"
                                                       class="inline-block bg-gray-100 text-gray-900 rounded px-2 py-0.5 text-xs font-bold ml-1 underline">
                                                {{ $pr['issue'] }}
                                            </a>
                                                @endif
                                    </span>
                                        </div>
                                        <div class="mt-2 sm:mt-0">
                                            @if ($pr['mergeable'] === true)
                                                <span class="inline-block bg-gray-100 text-green-700 text-xs px-2 py-1 rounded-full">✅ Geen conflict</span>
                                            @elseif ($pr['mergeable'] === false)
                                                <span class="inline-block bg-gray-100 text-red-700 text-xs px-2 py-1 rounded-full">⚠️ Mergeconflict</span>
                                            @else
                                                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">⏳ Status onbekend</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($pr['error'])
                                        <div class="mt-2 text-sm text-red-700">Fout: {{ $pr['error'] }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Stap 7 --}}
        @if($currentStep === 7)
            <div class="p-4 bg-gray-100 rounded-lg border border-gray-300 mt-4">
                <button wire:click="goToStep(6)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded mb-4">Vorige stap</button>
                <h2 class="font-semibold text-gray-900 text-lg mb-1">Laatste stap: Release uitvoeren</h2>
                <button wire:click="release" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Release uitvoeren
                </button>
            </div>
        @endif

        <div class="mt-8 text-center">
            <button
                wire:click="resetWorkflow"
                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md shadow hover:bg-red-800 transition"
            >
                🔄 Workflow resetten
            </button>
        </div>

        @push('scripts')
            <script>
                window.addEventListener('scroll-to-merge-results', () => {
                    const el = document.getElementById('merge-results');
                    if (el) {
                        el.scrollIntoView({behavior: 'smooth'});
                    }
                });
            </script>
@endpush
    </div>




