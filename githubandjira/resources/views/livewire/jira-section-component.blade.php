<div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-md space-y-6">
    <h2 class="text-2xl font-semibold text-gray-800">🔍 Zoek Jira-issues voor release</h2>

    <form wire:submit.prevent="fetchJiraReleaseIssues" class="space-y-5">
        <div>
            <label for="projectKey" class="block text-sm font-medium text-gray-700 mb-1">Project Key</label>
            <input type="text" id="projectKey" dusk="projectKey" wire:model="projectKey" placeholder="Bijv. ZSTAGE" required class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="releaseName" class="block text-sm font-medium text-gray-700 mb-1">Release naam</label>
            <input type="text" id="releaseName" dusk="releaseName" wire:model="releaseName" placeholder="Bijv. Release Testen" required class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="pt-2">
            <button type="submit" class="bg-gray-800 text-white px-5 py-2.5 rounded-md hover:bg-gray-900 transition font-medium">
                Haal issues op
            </button>
        </div>
    </form>

    {{-- Loading indicator --}}
    <div wire:loading wire:target="fetchJiraReleaseIssues" class="flex items-center gap-2 text-gray-700 text-sm mt-4">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
        Issues worden opgehaald...
    </div>

    {{-- Jira issues lijst --}}
    @if ($issues)
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">📋 Jira Issues:</h3>
            <ul class="space-y-4 text-sm text-gray-800">
                @foreach ($issues as $issue)
                    <li class="bg-gray-50 border border-gray-200 rounded-md p-4 shadow-sm">
                        <div class="font-semibold">{{ $issue['key'] }} – {{ $issue['summary'] }}</div>
                        {{-- Subtasks --}}
                        @if (!empty($issue['subtasks']))
                            <div class="mt-2">
                                <div class="text-xs font-semibold text-blue-600 mb-1">📝 Subtasks:</div>
                                <ul class="ml-4 list-disc list-inside text-xs text-blue-700">
                                    @foreach ($issue['subtasks'] as $subtask)
                                        <li>{{ $subtask['key'] }} – {{ $subtask['summary'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {{-- Linked Issues --}}
                        @if (!empty($issue['linkedIssues']))
                            <div class="mt-2">
                                <div class="text-xs font-semibold text-green-600 mb-1">🔗 Linked issues:</div>
                                <ul class="ml-4 list-disc list-inside text-xs text-green-700">
                                    @foreach ($issue['linkedIssues'] as $linked)
                                        <li>{{ $linked['key'] }} – {{ $linked['summary'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Error message --}}
    @if ($jiraError)
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
            {{ $jiraError }}
        </div>
    @endif
</div>
