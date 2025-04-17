<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Integraties</title>
    @vite('resources/css/app.css') {{-- Tailwind laden --}}
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-3xl mx-auto py-10 px-4">
    @livewire('settings.user-integration-settings')
</div>

@livewireScripts
</body>
</html>
