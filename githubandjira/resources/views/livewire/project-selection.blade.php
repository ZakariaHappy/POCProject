



<div class="flex flex-col space-y-6">

    @if (!$this->githubService->isConfigured() || !$this->jiraService->isConfigured())
        <div class="p-4 mb-6 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded">
            ⚠️ Je integratiegegevens zijn nog niet (volledig) ingevuld. Ga naar <strong>Integratie Instellingen</strong> in de sidebar om dit te doen.
        </div>
    @endif
    <!-- Top section: Jira & GitHub side by side -->
    <div class="grid grid-cols-1 md:grid-cols-2">

        <!-- Jira Box -->
        <div class="bg-white border border-black rounded-xl p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-black mb-4">Zoek in Jira Projecten</h2>

            <form wire:submit.prevent="fetchJiraProject" class="space-y-3">
                <div>
                    <label for="projectKey" class="block text-sm font-medium text-gray-700">Project Key</label>
                    <input
                        type="text"
                        id="projectKey"
                        wire:model="projectKey"
                        placeholder="Bijv. ZSTAGE"
                        required
                        class="mt-1 w-full rounded-md border border-blue-300 bg-blue-50 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                    >
                </div>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-black transition">
                    Zoek Project
                </button>
            </form>

            @if ($issues)
                <h3 class="mt-6 font-semibold text-black">Jira Issues:</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach ($issues as $issue)
                        <li>
                            <strong>{{ $issue['key'] }}</strong> - {{ $issue['fields']['summary'] }}

                            @if (!empty($issue['fields']['issuelinks']))
                                <ul class="text-sm text-gray-600 mt-1 pl-4">
                                    @foreach ($issue['fields']['issuelinks'] as $link)
                                        @if (isset($link['inwardIssue']))
                                            <li>↳ Linked: {{ $link['inwardIssue']['key'] }}</li>
                                        @elseif (isset($link['outwardIssue']))
                                            <li>↳ Linked: {{ $link['outwardIssue']['key'] }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                            @if (isset($issue['fields']['parent']))
                                <ul class="text-sm text-gray-600 mt-1 pl-4">
                                    <li>↳ SubStory van: {{ $issue['fields']['parent']['key'] }}</li>
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @elseif ($jiraError)
                <p class="text-red-500 mt-4">{{ $jiraError }}</p>
            @else
                <p class="text-black mt-4">...</p>
            @endif


        @if ($project)
                <form wire:submit.prevent="fetchJiraReleaseIssues" class="mt-6 space-y-3">
                    <div>
                        <label for="releaseName" class="block text-sm font-medium">Release naam:</label>
                        <input type="text" id="releaseName" wire:model="releaseName" class="mt-1 w-full rounded border-gray-300" placeholder="Bijv. Release Testen" required>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-black">Toon tickets voor deze release</button>
                </form>

                <!-- Nieuwe knop om de release branch aan te maken via Livewire -->
                @if ($releaseName)
                    <div class="mt-4">
                        <button wire:click="createReleaseBranch" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-800">
                            Maak Release Branch
                        </button>

                        <!-- Feedback na branch-aanmaak -->
                        @if (session()->has('message'))
                            <div class="mt-4 text-green-600">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="mt-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session()->has('error_details'))
                            <div class="mt-4 p-4 bg-red-100 border border-red-300 rounded text-sm text-red-800">
                                <strong>Details van foutmelding:</strong><br>
                                <pre class="whitespace-pre-wrap">{{ session('error_details') }}</pre>
                            </div>
                        @endif
                    </div>
                @endif

            @endif

            @if (session()->has('jira_message'))
                <div class="mt-4 text-green-600">
                    {{ session('jira_message') }}
                </div>
            @endif

        </div>

        <!-- GitHub Box -->
        <div class="bg-white border border-black rounded-xl p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-black mb-4">Zoek in GitHub Repositories</h2>
            <form wire:submit.prevent="fetchGithubRepositories" class="space-y-3">
                <div>
                    <label for="githubRepoName" class="block text-sm font-medium">Repository Naam</label>
                    <input type="text" id="githubRepoName" wire:model="githubRepoName" class="mt-1 w-full rounded-md border border-blue-300 bg-blue-50 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                           required>
                </div>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-black">Zoek Repositories</button>
            </form>
            @if ($githubError)
                <p class="text-red-500 mt-4">{{ $githubError }}</p>
            @endif


        @if ($githubRepositories)
                <h3 class="mt-6 font-semibold text-green-700">GitHub Repositories</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach ($githubRepositories as $repo)
                        <li class="border-b pb-2 flex justify-between items-center">
                            <span><strong>{{ $repo['name'] }}</strong></span>
                            <button wire:click="selectRepoAndFetchBranches('{{ $repo['full_name'] }}')" class="text-sm bg-gray-800 text-white px-2 py-1 rounded hover:bg-black">
                                Bekijk Branches
                            </button>
                        </li>
                    @endforeach
                </ul>

            @else
                <p class="text-black mt-4">...</p>
            @endif

            @if ($branches)
                <h3 class="mt-6 font-semibold text-green-700">Branches:</h3>
                <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                    @foreach ($branches as $branch)
                        <li>{{ $branch['name'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Matches box -->


    <div class="flex flex-col space-y-6">
        <!-- Matches box -->
        <div class="bg-white border border-black rounded-xl p-6 shadow-sm">
            <h3 class="text-xl font-semibold text-black mb-4">Matches tussen Jira issues en GitHub branches:</h3>

            @if ($matchedBranches && count($matchedBranches) > 0)
                <ul class="space-y-3 text-sm">
                    @foreach ($matchedBranches as $match)
                        <li class="border-b pb-2">
                            <strong>{{ $match['issue']['key'] }} - {{ $match['issue']['fields']['summary'] }}</strong><br>
                            ↳ Branch: <code class="bg-gray-100 px-1 rounded text-gray-700">{{ $match['branch'] }}</code>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-black">...</p>
            @endif

            <!-- Knop voor het maken van de mergevoorstellen -->
            @if ($matchedBranches && count($matchedBranches) > 0)
                <button wire:click="createMergeProposals" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900">
                    Maak Mergevoorstellen
                </button>
            @endif

            <!-- Resultaten van aangemaakte pull requests met mergeconflict-info -->
            @if (!empty($pullRequestResults))
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Resultaten van Mergevoorstellen:</h3>
                    <ul class="space-y-4 text-sm">
                        @foreach ($pullRequestResults as $pr)
                            <li class="border-b pb-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <a href="{{ $pr['url'] }}" class="text-blue-600 font-medium" target="_blank">
                                            {{ $pr['branch'] }}
                                        </a>
                                        <span class="ml-2 text-gray-500">voor issue <strong>{{ $pr['issue'] }}</strong></span>
                                    </div>
                                    <div class="mt-2 sm:mt-0">
                                        @if ($pr['mergeable'] === true)
                                            <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">✅ Geen conflict</span>
                                        @elseif ($pr['mergeable'] === false)
                                            <span class="inline-block bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full">⚠️ Mergeconflict</span>
                                        @else
                                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">⏳ Status onbekend</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Feedback berichten -->
            @if (session()->has('match_message'))
                <div class="mt-4 text-green-600">
                    {{ session('match_message') }}
                </div>
            @endif
        @if (session()->has('error_mergen'))
                <div class="mt-4 p-4 bg-red-100 border border-red-300 rounded text-sm text-red-800">
                    <strong>Details van foutmelding:</strong><br>
                    <pre class="whitespace-pre-wrap">{{ session('error_mergen') }}</pre>
                </div>
            @endif

        </div>
    </div>




</div>
