@extends('layouts.app')

@section('content')

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-[#191f26] tracking-tight">Tablero</h1>
            <p class="text-xs text-[#707680] mt-1 font-medium">Padrón sincronizado con la nómina hoy 06:00 · valores ilustrativos</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <select class="appearance-none bg-white border border-[#e4dfd7] rounded-xl px-4 py-2 pr-9 text-xs font-medium text-gray-700 shadow-sm hover:border-gray-400 focus:outline-none">
                    <option>Todas las provincias</option>
                </select>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 absolute right-3 top-3 text-gray-500 pointer-events-none"></i>
            </div>

            <div class="relative">
                <select class="appearance-none bg-white border border-[#e4dfd7] rounded-xl px-4 py-2 pr-9 text-xs font-medium text-gray-700 shadow-sm hover:border-gray-400 focus:outline-none">
                    <option>Septiembre 2026</option>
                </select>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 absolute right-3 top-3 text-gray-500 pointer-events-none"></i>
            </div>

            <button class="bg-[#b3493b] hover:bg-[#9d3f32] text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                <i data-lucide="arrow-up-to-line" class="w-4 h-4"></i>
                <span>Subir lote de recibos</span>
            </button>
        </div>
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-[#ede7df] flex flex-col justify-between">
            <span class="text-xs font-medium text-[#656c75]">Habilitados en el padrón</span>
            <div class="my-3">
                <span class="text-3xl font-extrabold text-[#191f26]">764</span>
            </div>
            <span class="text-[11px] text-[#787f89]">nómina activa + 12 autorizados</span>
        </div>


        <div class="bg-white p-5 rounded-2xl shadow-sm border border-[#ede7df] flex flex-col justify-between">
            <span class="text-xs font-medium text-[#656c75]">Cuentas activas</span>
            <div class="my-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-[#191f26]">512</span>
                <span class="text-xs font-semibold text-[#656c75]">· 67 %</span>
            </div>
            <div class="w-full bg-[#f0ebe3] h-1.5 rounded-full overflow-hidden">
                <div class="bg-[#191f26] h-1.5 rounded-full" style="width: 67%;"></div>
            </div>
        </div>


        <div class="bg-white p-5 rounded-2xl shadow-sm border border-[#ede7df] flex flex-col justify-between">
            <span class="text-xs font-medium text-[#656c75]">Recibos de agosto firmados</span>
            <div class="my-3">
                <span class="text-3xl font-extrabold text-[#191f26]">81 %</span>
            </div>
            <div class="w-full bg-[#f0ebe3] h-1.5 rounded-full overflow-hidden">
                <div class="bg-[#191f26] h-1.5 rounded-full" style="width: 81%;"></div>
            </div>
        </div>


        <div class="bg-[#191f26] text-white p-5 rounded-2xl shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-[#99a2ad]">Para decidir hoy</span>
            <div class="my-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">7</span>
                <span class="text-xs font-medium text-[#ccd2db]">en revisión</span>
            </div>
            <span class="text-[11px] text-[#e06655] font-medium">2 llevan más de 48 h</span>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">

        <div class="lg:col-span-5 bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df] flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-[#191f26] mb-5">Cuentas activas por provincia</h2>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#191f26] mb-1.5">
                            <span>Córdoba</span>
                            <span class="text-gray-500 font-normal">71 %</span>
                        </div>
                        <div class="w-full bg-[#f0ebe3] h-2.5 rounded-full overflow-hidden">
                            <div class="bg-[#b3493b] h-full rounded-full" style="width: 71%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#191f26] mb-1.5">
                            <span>Catamarca</span>
                            <span class="text-gray-500 font-normal">58 %</span>
                        </div>
                        <div class="w-full bg-[#f0ebe3] h-2.5 rounded-full overflow-hidden">
                            <div class="bg-[#b3493b] h-full rounded-full" style="width: 58%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#191f26] mb-1.5">
                            <span>Santiago del Estero</span>
                            <span class="text-gray-500 font-normal">49 %</span>
                        </div>
                        <div class="w-full bg-[#f0ebe3] h-2.5 rounded-full overflow-hidden">
                            <div class="bg-[#b3493b] h-full rounded-full" style="width: 49%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-[#f0ebe3] mt-6">
                <h3 class="text-xs font-bold text-[#191f26] mb-3">Sin enrolar: dónde están</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-[#59606a]">Invitación sin abrir</span>
                        <span class="font-bold text-[#191f26]">148</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#59606a]">Empezaron y no terminaron</span>
                        <span class="font-bold text-[#191f26]">71</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#59606a]">Derivados a enrolamiento asistido</span>
                        <span class="font-bold text-[#191f26]">33</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="lg:col-span-7 bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-base font-bold text-[#191f26]">Cola de revisión</h2>
                <a href="#" class="text-xs font-bold text-[#b3493b] hover:underline">Ver todo</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-[#7d848f] tracking-wider border-b border-[#f3eee7]">
                            <th class="pb-2.5">Persona</th>
                            <th class="pb-2.5">Motivo</th>
                            <th class="pb-2.5">Local</th>
                            <th class="pb-2.5">Desde</th>
                            <th class="pb-2.5 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7f3ec] text-[#2c333e]">
                        <tr>
                            <td class="py-3 font-bold">Gómez, Rocío</td>
                            <td class="py-3 text-[#585f69]">Cotejo facial dudoso</td>
                            <td class="py-3 text-[#585f69]">Catamarca Centro</td>
                            <td class="py-3 font-bold text-[#b3493b]">52 h</td>
                            <td class="py-3 text-right">
                                <button class="border border-[#c5c0b8] hover:bg-gray-50 px-3 py-1 rounded-full text-[11px] font-semibold text-[#191f26] transition">Revisar</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 font-bold">Ledesma, Hugo</td>
                            <td class="py-3 text-[#585f69]">Discrepancia con la nómina</td>
                            <td class="py-3 text-[#585f69]">Centro de distribución</td>
                            <td class="py-3 font-bold text-[#b3493b]">49 h</td>
                            <td class="py-3 text-right">
                                <button class="border border-[#c5c0b8] hover:bg-gray-50 px-3 py-1 rounded-full text-[11px] font-semibold text-[#191f26] transition">Revisar</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 font-bold">Juárez, Micaela</td>
                            <td class="py-3 text-[#585f69]">Domicilio sin verificar</td>
                            <td class="py-3 text-[#585f69]">Mercamax Santiago</td>
                            <td class="py-3 font-semibold text-gray-700">20 h</td>
                            <td class="py-3 text-right">
                                <button class="border border-[#c5c0b8] hover:bg-gray-50 px-3 py-1 rounded-full text-[11px] font-semibold text-[#191f26] transition">Revisar</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 font-bold">Sosa, Daniel</td>
                            <td class="py-3 text-[#585f69]">Blanqueo fallido (3 intentos)</td>
                            <td class="py-3 text-[#585f69]">Cordiez Cruz del Eje</td>
                            <td class="py-3 font-semibold text-gray-700">6 h</td>
                            <td class="py-3 text-right">
                                <button class="border border-[#c5c0b8] hover:bg-gray-50 px-3 py-1 rounded-full text-[11px] font-semibold text-[#191f26] transition">Revisar</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 font-bold">Paz, Lucía</td>
                            <td class="py-3 text-[#585f69]">DNI reemplazado</td>
                            <td class="py-3 text-[#585f69]">Cordiez Dean Funes</td>
                            <td class="py-3 font-semibold text-gray-700">2 h</td>
                            <td class="py-3 text-right">
                                <button class="border border-[#c5c0b8] hover:bg-gray-50 px-3 py-1 rounded-full text-[11px] font-semibold text-[#191f26] transition">Revisar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df]">
        <h2 class="text-base font-bold text-[#191f26] mb-4">Envíos recientes</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-[#7d848f] tracking-wider border-b border-[#f3eee7]">
                        <th class="pb-2.5">Envío</th>
                        <th class="pb-2.5">Tipo</th>
                        <th class="pb-2.5">Destinatarios</th>
                        <th class="pb-2.5">Firmados</th>
                        <th class="pb-2.5">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f7f3ec] text-[#2c333e]">
                    <tr>
                        <td class="py-3.5 font-bold">Recibos · Septiembre 2026</td>
                        <td class="py-3.5 text-gray-500 font-medium">DOC-01</td>
                        <td class="py-3.5 text-gray-600 font-medium">761 · <span class="text-gray-500">3 en cuarentena</span></td>
                        <td class="py-3.5 text-gray-400">—</td>
                        <td class="py-3.5">
                            <span class="bg-[#fef4e5] text-[#b47721] px-2.5 py-1 rounded-md text-[11px] font-bold">
                                Espera al segundo administrador
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3.5 font-bold">Recibos · Agosto 2026</td>
                        <td class="py-3.5 text-gray-500 font-medium">DOC-01</td>
                        <td class="py-3.5 text-gray-600 font-medium">758</td>
                        <td class="py-3.5 text-gray-600 font-medium">81 %</td>
                        <td class="py-3.5">
                            <span class="bg-[#faebe8] text-[#b3493b] px-2.5 py-1 rounded-md text-[11px] font-bold">
                                Publicado · vence 03/10
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3.5 font-bold">Vacaciones 2026/27 · Córdoba</td>
                        <td class="py-3.5 text-gray-500 font-medium">DOC-02</td>
                        <td class="py-3.5 text-gray-600 font-medium">212</td>
                        <td class="py-3.5 text-gray-600 font-medium">64 %</td>
                        <td class="py-3.5">
                            <span class="bg-[#faebe8] text-[#b3493b] px-2.5 py-1 rounded-md text-[11px] font-bold">
                                Publicado · recordatorio enviado
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection