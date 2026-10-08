<aside class="w-full lg:w-72 bg-[#1b2229] text-[#9ca3af] flex flex-col justify-between p-6 shrink-0 border-r border-[#262f38] h-auto lg:h-screen overflow-y-auto">
    <div>

        <div class="mb-8 pl-2">
            <div class="flex items-center gap-2 text-white font-extrabold text-2xl tracking-wider">
                <svg class="w-6 h-6 fill-current text-white" viewBox="0 0 24 24">
                    <polygon points="12 2 2 22 22 22" />
                </svg>
                <span>CYRE<span class="text-xs align-super ml-0.5 font-normal text-gray-400">SA</span></span>
            </div>
            <p class="text-[10px] tracking-widest text-[#717a86] font-semibold mt-1 uppercase">Consola · App de Acceso</p>
        </div>


        <nav class="space-y-1 text-sm font-medium">

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl bg-[#28323c] text-white font-semibold transition">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>Tablero</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="users-2" class="w-4 h-4"></i>
                <span>Padrón</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Personas</span>
            </a>

            <a href="{{ route('cola-revision.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl {{ request()->routeIs('cola-revision.index') ? 'bg-[#28323c] text-white font-semibold' : 'hover:text-white hover:bg-[#222b34]' }} transition">
                <div class="flex items-center gap-3.5">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Cola de revisión</span>
                </div>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Envíos</span>
            </a>

            <a href="#" class="flex items-center justify-between px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <div class="flex items-center gap-3.5">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Casos</span>
                </div>
                <span class="bg-[#2a343e] text-gray-400 text-xs px-2 py-0.5 rounded-full">3</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                <span>Reportes</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Configuración</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#222b34] transition">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Auditoría</span>
            </a>
        </nav>
    </div>


    <div class="pt-6 border-t border-[#262f38] mt-6 flex flex-col gap-4">
        <div class="pl-2">
            <p class="font-semibold text-white leading-snug text-xs">{{ auth()->user()->name ?? 'Administrador' }}</p>
            <p class="text-[#717a86] mt-0.5 text-[11px]">Ámbito: las 3 provincias</p>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-xl hover:text-white hover:bg-[#b3493b] transition text-sm font-medium">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
