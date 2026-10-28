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
    
    <!-- Barra Superior Delicada (Estilo Layout Referencia con Paleta Terracota Institucional) -->
    <header class="max-w-[1700px] mx-auto px-4 sm:px-6 pt-4">
        <div class="bg-gradient-to-r from-[#b3493b] via-[#a33f32] to-[#802a20] rounded-2xl shadow-md shadow-[#b3493b]/15 px-5 py-3 flex items-center justify-between text-white">
            <!-- Marca / Título -->
            <div class="flex items-center gap-3">
                <span class="text-base sm:text-lg font-black tracking-tight">Cyre S.A.</span>
                <span class="hidden sm:inline-block text-[11px] font-semibold text-white/70 uppercase tracking-wider pl-3 border-l border-white/20">
                    RRHH &bull; Gandia
                </span>
            </div>

            <!-- Acciones a la derecha -->
            <div class="flex items-center gap-2">
                <!-- Botón de recarga -->
                <button type="button" onclick="window.location.reload()" title="Actualizar datos"
                    class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition flex items-center justify-center">
                    <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                </button>

                <!-- Identificación del usuario -->
                @auth
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 text-xs font-semibold text-white/90">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-white/70"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </div>
                @endauth

                <!-- Botón Cerrar Sesión -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Cerrar sesión"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/25 text-white transition flex items-center gap-1.5 text-xs font-bold">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Contenido Principal sin sidebar a ancho completo -->
    <main class="w-full max-w-[1700px] mx-auto px-4 sm:px-6 py-5">
        @yield('content')
    </main>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
