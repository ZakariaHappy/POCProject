<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Instellingen' }}</title>
    @vite('resources/css/app.css') <!-- Tailwind CSS -->
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 p-6">
<div class="max-w-4xl mx-auto">
    {{ $slot }}
</div>
@livewireScripts
</body>
</html>
