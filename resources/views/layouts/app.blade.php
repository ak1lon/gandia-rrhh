<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYRE SA - Tablero de Control</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fbf8f2] min-h-screen text-[#1a1f26] font-sans antialiased">
    <div class="flex flex-col lg:flex-row w-full min-h-screen bg-[#fbf8f2]">
        <x-sidebar />
        <main class="flex-1 p-6 lg:p-10 overflow-y-auto h-screen">
            @yield('content')
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
