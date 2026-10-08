<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYRE SA - Iniciar Sesión</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1b2229] min-h-screen font-sans antialiased flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/2 p-8 md:p-12 lg:p-16 flex flex-col justify-center">
            <div class="mb-10 flex items-center gap-2 text-[#1b2229] font-extrabold text-3xl tracking-wider">
                <svg class="w-8 h-8 fill-current text-[#b3493b]" viewBox="0 0 24 24">
                    <polygon points="12 2 2 22 22 22" />
                </svg>
                <span>CYRE<span class="text-sm align-super ml-0.5 font-normal text-gray-400">SA</span></span>
            </div>

            <h1 class="text-2xl font-extrabold text-[#191f26] mb-2">Bienvenido de nuevo</h1>
            <p class="text-sm text-gray-500 mb-8 font-medium">Ingresa tus credenciales para acceder a la consola.</p>

            <form action="/login" method="POST" class="space-y-5">
                @csrf
                @if($errors->any())
                    <div class="bg-[#faebe8] text-[#b3493b] p-3 rounded-xl text-xs font-bold border border-[#f3d6d2]">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div>
                    <label for="email" class="block text-xs font-bold text-[#191f26] mb-1.5 uppercase tracking-wide">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" class="w-full pl-10 pr-4 py-3 bg-[#fbf8f2] border border-[#ede7df] rounded-xl text-sm focus:outline-none focus:border-[#b3493b] focus:ring-1 focus:ring-[#b3493b] transition" placeholder="tu@empresa.com" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-[#191f26] mb-1.5 uppercase tracking-wide">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="w-full pl-10 pr-4 py-3 bg-[#fbf8f2] border border-[#ede7df] rounded-xl text-sm focus:outline-none focus:border-[#b3493b] focus:ring-1 focus:ring-[#b3493b] transition" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#b3493b] hover:bg-[#9d3f32] text-white py-3 rounded-xl text-sm font-bold shadow-sm transition mt-4 flex justify-center items-center gap-2">
                    <span>Ingresar a la consola</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <div class="w-full md:w-1/2 bg-[#fbf8f2] p-8 md:p-12 lg:p-16 flex flex-col justify-center border-l border-[#ede7df]">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df] mb-8 relative">
                <div class="absolute -top-4 -left-4 bg-[#1b2229] text-white p-2 rounded-xl shadow-md">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191f26] mb-3 mt-2">Acceso Seguro</h3>
                <p class="text-sm text-[#585f69] leading-relaxed">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df] relative">
                <div class="absolute -top-4 -left-4 bg-[#b3493b] text-white p-2 rounded-xl shadow-md">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191f26] mb-3 mt-2">Gestión Ágil</h3>
                <p class="text-sm text-[#585f69] leading-relaxed">
                    Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </p>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
