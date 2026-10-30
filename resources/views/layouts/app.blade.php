<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyre S.A. - Notificaciones y RRHH</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fbf8f2] min-h-screen text-[#1a1f26] font-sans antialiased">
    
    <div class="flex flex-col lg:flex-row min-h-screen">
        <x-sidebar />

        <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6 lg:h-screen lg:overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
