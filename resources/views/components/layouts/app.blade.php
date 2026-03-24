@props(['header' => null])

<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Quran Center') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex flex-col">
        <x-app.navbar :header="$header" />

        <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>

</body>
</html>
