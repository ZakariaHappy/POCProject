<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | GitHub & Jira Tool</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r p-6 space-y-6 shadow">
        <h2 class="text-2xl font-bold text-blue-600">Happy Releasing</h2>
        <nav class="flex flex-col space-y-4">
            <a href="{{ route('release') }}" class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-600 transition" dusk="sidebar-release">Release Workflow</a>
            <a href="{{ route('integration') }}" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600 transition">Integratie Instellingen</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-left w-full">Log uit</button>
            </form>
        </nav>

    </aside>



    <!-- Main content -->
{{--    <main class="flex-1 p-10">--}}
{{--        <div id="welcome-section">--}}
{{--            <h1 class="text-3xl font-bold mb-6">Welkom bij de GitHub & Jira Tool</h1>--}}
{{--            <p class="text-gray-700 text-lg">Gebruik de navigatie aan de linkerkant om te starten met een project of om je integratiegegevens te beheren.</p>--}}
{{--        </div>--}}

{{--        <div id="project-section" class="hidden">--}}
{{--            @livewire('release-workflow-component')--}}
{{--        </div>--}}

{{--        <div id="integrations-section" class="hidden">--}}
{{--            @livewire('settings.integration-settings')--}}
{{--        </div>--}}



{{--    </main>--}}



</div>

<script>
    function showSection(section) {
        document.getElementById('welcome-section').classList.add('hidden');
        document.getElementById('project-section').classList.add('hidden');
        document.getElementById('integrations-section').classList.add('hidden');

        if (section === 'project') {
            document.getElementById('project-section').classList.remove('hidden');
        } else if (section === 'integrations') {
            document.getElementById('integrations-section').classList.remove('hidden');
        }
    }
</script>

@livewireScripts
</body>
</html>
