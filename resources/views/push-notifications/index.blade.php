@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <!-- Header de sección sutil -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-[#191f26] tracking-tight">Actualización de Notificaciones</h1>
            <p class="text-xs text-[#707680] font-medium">Gestión y distribución de notificaciones push por cargo, sección y legajo.</p>
        </div>

        @if(session('success'))
            <div class="bg-[#eaf5ef] text-[#2c7a51] px-3.5 py-1.5 rounded-xl text-xs font-bold border border-[#c1ebd4] flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-[#faebe8] text-[#b3493b] px-3.5 py-1.5 rounded-xl text-xs font-bold border border-[#f3d6d2] flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Por favor completa los campos requeridos.</span>
            </div>
        @endif
    </div>

    <!-- Layout de 2 columnas con tarjetas delicadas y equilibradas (rounded-2xl) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- ========================================== -->
        <!-- PANEL IZQUIERDO: FORMULARIO Y FILTROS PARA -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#ede7df] shadow-sm p-4 sm:p-5 flex flex-col justify-between">
            <form id="notificationForm" action="{{ route('cola-revision.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf

                <!-- Fecha -->
                <div class="grid grid-cols-12 items-center gap-2.5">
                    <label class="col-span-3 text-xs font-bold text-[#585f69]">Fecha</label>
                    <div class="col-span-9">
                        <input type="date" name="notification_date" id="notification_date"
                            value="{{ date('Y-m-d') }}"
                            class="w-full sm:w-44 px-3 py-1.5 text-xs font-semibold bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                    </div>
                </div>

                <!-- Emisor -->
                <div class="grid grid-cols-12 items-start gap-2.5">
                    <label class="col-span-3 text-xs font-bold text-[#585f69] pt-1.5">Emisor</label>
                    <div class="col-span-9 space-y-1.5">
                        <div class="relative">
                            <select name="sender" id="senderSelect" required
                                class="w-full px-3 py-1.5 text-xs font-semibold bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b] appearance-none pr-8">
                                <option value="" disabled selected>Emisor de la Notificación</option>
                                @foreach($emisores as $emisor)
                                    <option value="{{ $emisor }}">{{ $emisor }}</option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 absolute right-2.5 top-2 pointer-events-none"></i>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="sender_show_section" value="1"
                                class="w-3.5 h-3.5 rounded border-[#ede7df] text-[#b3493b] focus:ring-0">
                            <span class="text-[11px] font-semibold text-[#585f69]">Muestra Sección como Emisor</span>
                        </label>
                    </div>
                </div>

                <!-- Motivo -->
                <div class="grid grid-cols-12 items-center gap-2.5">
                    <label class="col-span-3 text-xs font-bold text-[#585f69]">Motivo</label>
                    <div class="col-span-9 relative">
                        <input list="motivosList" name="reason" id="reasonInput" required placeholder="Seleccionar o escribir motivo..."
                            class="w-full px-3 py-1.5 text-xs font-semibold bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b] pr-8">
                        <datalist id="motivosList">
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo }}"></option>
                            @endforeach
                        </datalist>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 absolute right-2.5 top-2 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Prioridad -->
                <div class="grid grid-cols-12 items-center gap-2.5">
                    <label class="col-span-3 text-xs font-bold text-[#585f69]">Prioridad</label>
                    <div class="col-span-9 flex items-center gap-5">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="baja" class="text-[#b3493b] focus:ring-0">
                            <span>Baja</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="normal" checked class="text-[#b3493b] focus:ring-0">
                            <span>Normal</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="alta" class="text-[#b3493b] focus:ring-0">
                            <span class="text-[#b3493b] font-bold">Alta</span>
                        </label>
                    </div>
                </div>

                <!-- SECCIÓN "PARA" (FILTROS Y SUBLISTAS) -->
                <div class="pt-2.5 border-t border-[#ede7df] space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 bg-[#faebe8] text-[#b3493b] text-[11px] font-extrabold rounded-lg border border-[#f3d6d2]">Para</span>
                        <div class="flex items-center gap-1.5 text-gray-500">
                            <button type="button" id="btnFilterLink" title="Aplicar filtro actual" class="p-1 hover:text-[#b3493b] hover:bg-[#faf7f2] rounded-lg transition">
                                <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="button" id="btnClearSelection" title="Limpiar selección" class="p-1 hover:text-[#b3493b] hover:bg-[#faf7f2] rounded-lg transition">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="button" id="btnSelectAllVisible" title="Seleccionar todos los visibles" class="p-1 hover:text-[#b3493b] hover:bg-[#faf7f2] rounded-lg transition">
                                <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Radios de Criterio de Selección -->
                    <div class="p-2 bg-[#fbf8f2] rounded-xl border border-[#ede7df] flex items-center justify-around text-xs font-bold text-[#585f69]">
                        <label class="inline-flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="filterMode" value="cargo" checked class="text-[#b3493b] focus:ring-0">
                            <span>Cargo Sap</span>
                        </label>
                        <label class="inline-flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="filterMode" value="seccion" class="text-[#b3493b] focus:ring-0">
                            <span>Sección</span>
                        </label>
                        <label class="inline-flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="filterMode" value="legajo" class="text-[#b3493b] focus:ring-0">
                            <span>Legajo</span>
                        </label>
                    </div>

                    <!-- Sub Filtros Dinámicos -->
                    <div class="space-y-2 text-xs">
                        <!-- Cargo Sap Filter -->
                        <div id="filterBlockCargo" class="filter-block space-y-1">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" id="checkAllCargos" class="w-3.5 h-3.5 rounded border-[#ede7df] text-[#b3493b] focus:ring-0">
                                <span class="font-bold text-[#585f69] text-[11px]">Todos los Cargos Sap</span>
                            </label>
                            <div class="relative">
                                <select id="selectCargo" class="w-full px-3 py-1.5 bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b] appearance-none pr-8 text-xs">
                                    <option value="">-- Seleccionar Cargo específico --</option>
                                    @foreach($cargos as $cargo)
                                        <option value="{{ $cargo }}">{{ $cargo }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 absolute right-2.5 top-2 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Sección Filter -->
                        <div id="filterBlockSeccion" class="filter-block space-y-1">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" id="checkAllSecciones" class="w-3.5 h-3.5 rounded border-[#ede7df] text-[#b3493b] focus:ring-0">
                                <span class="font-bold text-[#585f69] text-[11px]">Todas las Secciones</span>
                            </label>
                            <div class="relative">
                                <select id="selectSeccion" class="w-full px-3 py-1.5 bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b] appearance-none pr-8 text-xs">
                                    <option value="">-- Seleccionar Sección específica --</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion }}">{{ $seccion }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 absolute right-2.5 top-2 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Legajo Filter -->
                        <div id="filterBlockLegajo" class="filter-block space-y-1">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" id="checkAllLegajos" class="w-3.5 h-3.5 rounded border-[#ede7df] text-[#b3493b] focus:ring-0">
                                <span class="font-bold text-[#585f69] text-[11px]">Todos los Legajos</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="inputSearchLegajo" placeholder="Buscar por legajo o apellido..."
                                    class="w-full px-3 py-1.5 bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b] text-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-2 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA INFERIOR DE DESTINATARIOS -->
                    <div class="border border-[#ede7df] rounded-xl overflow-hidden mt-2">
                        <div class="bg-[#191f26] text-white px-3 py-2 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <span>LEGAJO</span>
                            </div>
                            <div class="flex-1 px-3">APELLIDO Y NOMBRE</div>
                            <div class="flex items-center gap-2">
                                <span id="selectedCountBadge" class="bg-[#b3493b] text-white text-[9px] font-bold px-2 py-0.5 rounded-full">0 sel</span>
                                <i data-lucide="filter" class="w-3 h-3 opacity-80"></i>
                            </div>
                        </div>
                        <div class="max-h-44 overflow-y-auto divide-y divide-[#ede7df] bg-white text-xs" id="recipientsTableBody">
                            @foreach($employees as $employee)
                                <label class="flex items-center justify-between px-3 py-2 hover:bg-[#fbf8f2] cursor-pointer employee-row"
                                       data-id="{{ $employee->id }}"
                                       data-legajo="{{ $employee->legajo ?? 'S/L' }}"
                                       data-name="{{ $employee->name }}"
                                       data-cargo="{{ $employee->cargo_sap ?? '' }}"
                                       data-seccion="{{ $employee->seccion ?? '' }}">
                                    <div class="flex items-center gap-2 w-20">
                                        <input type="checkbox" name="recipients[]" value="{{ $employee->id }}"
                                               class="recipient-checkbox w-3.5 h-3.5 text-[#b3493b] rounded border-gray-300 focus:ring-0">
                                        <span class="font-mono font-bold text-[#b3493b] text-[10px]">{{ $employee->legajo ?? '-' }}</span>
                                    </div>
                                    <div class="flex-1 px-2 truncate text-[#191f26] font-medium text-[11px]">
                                        {{ $employee->name }}
                                        <span class="block text-[9px] text-[#707680] font-normal truncate">
                                            {{ $employee->cargo_sap ?? 'Sin cargo' }} &bull; {{ $employee->seccion ?? 'General' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mensaje / Flyer / Envío -->
                    <div class="pt-2.5 border-t border-[#ede7df] space-y-2.5">
                        <div>
                            <label class="block text-xs font-bold text-[#585f69] mb-1">Mensaje de la Notificación</label>
                            <textarea name="body" id="bodyInput" required rows="2" placeholder="Escribe el mensaje o cuerpo de la notificación..."
                                class="w-full px-3 py-1.5 text-xs bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#585f69] mb-1">Imagen / Flyer para Detalle (Opcional)</label>
                            <input type="file" name="image" id="imageInput" accept="image/*"
                                class="w-full text-xs text-[#585f69] file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#faebe8] file:text-[#b3493b] hover:file:bg-[#f5dad4] cursor-pointer">
                        </div>

                        <button type="submit" id="btnSubmitNotification"
                            class="w-full bg-[#b3493b] hover:bg-[#9d3f32] text-white py-2.5 px-4 rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Enviar Notificación Push (<span id="submitCountLabel">0</span>)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <!-- ========================================== -->
        <!-- PANEL DERECHO: GRILLA DE NOTIFICACIONES Y DETALLE -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 flex flex-col gap-4">
            
            <!-- GRILLA SUPERIOR DE NOTIFICACIONES -->
            <div class="bg-white rounded-2xl border border-[#ede7df] shadow-sm overflow-hidden flex flex-col">
                <div class="p-3 bg-[#fbf8f2] border-b border-[#ede7df] flex flex-wrap items-center justify-between gap-2.5 text-xs">
                    <span class="font-extrabold text-[#191f26] uppercase tracking-wide flex items-center gap-1.5 text-[11px]">
                        <i data-lucide="list-ordered" class="w-3.5 h-3.5 text-[#b3493b]"></i>
                        Cola de Notificaciones
                    </span>
                    <form method="GET" action="{{ route('cola-revision.index') }}" class="flex items-center gap-2">
                        <input type="text" name="q_reason" value="{{ request('q_reason') }}" placeholder="Filtrar por motivo..."
                            class="px-2.5 py-1 text-xs bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                        <button type="submit" class="bg-[#191f26] hover:bg-[#28323c] text-white px-3 py-1 rounded-lg text-xs font-bold transition">
                            Buscar
                        </button>
                        @if(request()->hasAny(['q_reason', 'q_id', 'q_date']))
                            <a href="{{ route('cola-revision.index') }}" class="text-xs text-[#b3493b] font-bold hover:underline">Limpiar</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto max-h-[340px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="sticky top-0 bg-[#191f26] text-white text-[10px] font-bold uppercase tracking-wider select-none z-10">
                            <tr>
                                <th class="w-8 py-2.5 px-2.5 text-center"></th>
                                <th class="py-2.5 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span>ID</span>
                                        <i data-lucide="search" class="w-3 h-3 opacity-60"></i>
                                    </div>
                                </th>
                                <th class="py-2.5 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span>FECHA</span>
                                        <i data-lucide="search" class="w-3 h-3 opacity-60"></i>
                                    </div>
                                </th>
                                <th class="py-2.5 px-3">MOTIVO</th>
                                <th class="py-2.5 px-3 text-right">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ede7df]" id="notificationsTableBody">
                            @forelse($notifications as $idx => $n)
                                @php
                                    $isSelected = $idx === 0;
                                    $dateFormatted = $n->notification_date ? $n->notification_date->format('d/m/Y') : ($n->created_at ? $n->created_at->format('d/m/Y') : '-');
                                @endphp
                                <tr class="notification-row cursor-pointer transition select-none {{ $isSelected ? 'bg-[#faebe8] font-bold text-[#b3493b]' : 'hover:bg-[#fbf8f2] text-[#2c333e]' }}"
                                    data-id="{{ $n->id }}"
                                    data-date="{{ $dateFormatted }}"
                                    data-reason="{{ $n->reason ?? $n->title }}"
                                    data-body="{{ $n->body }}"
                                    data-sender="{{ $n->sender ?? 'RRHH Gandia' }}"
                                    data-priority="{{ ucfirst($n->priority ?? 'normal') }}"
                                    data-status="{{ $n->status }}"
                                    data-image="{{ $n->image_path ? asset('storage/' . $n->image_path) : '' }}"
                                    data-recipients-count="{{ is_array($n->recipients) ? count($n->recipients) : 0 }}">
                                    <td class="py-2.5 px-2.5 text-center text-xs indicator-cell font-bold text-[#b3493b]">
                                        {{ $isSelected ? '»' : '>' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold">{{ $n->id }}</td>
                                    <td class="py-2.5 px-3">{{ $dateFormatted }}</td>
                                    <td class="py-2.5 px-3 font-bold uppercase truncate max-w-md">{{ $n->reason ?? $n->title }}</td>
                                    <td class="py-2.5 px-3 text-right">
                                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $n->status === 'Enviado correctamente' ? 'bg-[#eaf5ef] text-[#2c7a51] border border-[#c1ebd4]' : 'bg-[#fff8e6] text-[#b47a09] border border-[#fcefc2]' }}">
                                            {{ $n->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 font-semibold">
                                        No hay notificaciones registradas en la cola de revisión.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- PANEL INFERIOR: DETALLE Y VISOR DE FLYER / IMAGEN -->
            <div class="bg-white rounded-2xl border border-[#ede7df] shadow-sm p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-[#ede7df]">
                    <h3 class="text-xs font-extrabold text-[#585f69] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-[#b3493b]"></i>
                        Detalle
                    </h3>
                    <div id="detailMetaBadge" class="text-[10px] font-bold text-[#b3493b] bg-[#faebe8] px-2.5 py-0.5 rounded-full border border-[#f3d6d2]">
                        Cargando detalle...
                    </div>
                </div>

                <div id="detailContainer" class="flex-1 flex flex-col md:flex-row gap-4 items-center justify-center p-1 min-h-[190px]">
                    <!-- Flyer / Imagen -->
                    <div id="detailImageWrapper" class="w-full md:w-1/2 flex items-center justify-center bg-[#191f26] rounded-xl overflow-hidden p-2 shadow-sm min-h-[170px]">
                        <img id="detailImage" src="" alt="Flyer de la notificación" class="max-h-[190px] w-auto object-contain rounded-lg hidden">
                        <div id="detailImagePlaceholder" class="flex flex-col items-center justify-center text-gray-400 p-4 text-center">
                            <i data-lucide="image" class="w-8 h-8 mb-1.5 opacity-40"></i>
                            <span class="text-[11px] font-semibold text-gray-400">Sin imagen adjunta</span>
                        </div>
                    </div>

                    <!-- Información de la notificación -->
                    <div id="detailInfoWrapper" class="w-full md:w-1/2 flex flex-col justify-between h-full space-y-2.5 text-xs">
                        <div>
                            <span id="detailReason" class="text-xs font-extrabold text-[#191f26] block mb-1"></span>
                            <p id="detailBody" class="text-[#585f69] font-medium leading-relaxed bg-[#fbf8f2] p-2.5 rounded-xl border border-[#ede7df] max-h-24 overflow-y-auto text-[11px]"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[10px] pt-2 border-t border-[#ede7df]">
                            <div>
                                <span class="text-[#707680] block font-semibold">Emisor:</span>
                                <span id="detailSender" class="font-bold text-[#191f26]"></span>
                            </div>
                            <div>
                                <span class="text-[#707680] block font-semibold">Prioridad:</span>
                                <span id="detailPriority" class="font-bold text-[#b3493b]"></span>
                            </div>
                            <div>
                                <span class="text-[#707680] block font-semibold">Fecha:</span>
                                <span id="detailDate" class="font-bold text-[#191f26]"></span>
                            </div>
                            <div>
                                <span class="text-[#707680] block font-semibold">Destinatarios:</span>
                                <span id="detailRecipients" class="font-bold text-[#191f26]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Manejo de selección de filas en la grilla de notificaciones
    const rows = document.querySelectorAll('.notification-row');
    const detailMetaBadge = document.getElementById('detailMetaBadge');
    const detailImage = document.getElementById('detailImage');
    const detailImagePlaceholder = document.getElementById('detailImagePlaceholder');
    const detailReason = document.getElementById('detailReason');
    const detailBody = document.getElementById('detailBody');
    const detailSender = document.getElementById('detailSender');
    const detailPriority = document.getElementById('detailPriority');
    const detailDate = document.getElementById('detailDate');
    const detailRecipients = document.getElementById('detailRecipients');

    function selectNotificationRow(row) {
        rows.forEach(r => {
            r.classList.remove('bg-[#faebe8]', 'font-bold', 'text-[#b3493b]');
            r.classList.add('hover:bg-[#fbf8f2]', 'text-[#2c333e]');
            const ind = r.querySelector('.indicator-cell');
            if (ind) ind.textContent = '>';
        });

        row.classList.remove('hover:bg-[#fbf8f2]', 'text-[#2c333e]');
        row.classList.add('bg-[#faebe8]', 'font-bold', 'text-[#b3493b]');
        const currentInd = row.querySelector('.indicator-cell');
        if (currentInd) currentInd.textContent = '»';

        const id = row.dataset.id;
        const date = row.dataset.date;
        const reason = row.dataset.reason;
        const body = row.dataset.body;
        const sender = row.dataset.sender;
        const priority = row.dataset.priority;
        const status = row.dataset.status;
        const image = row.dataset.image;
        const recCount = row.dataset.recipientsCount;

        detailMetaBadge.textContent = `#${id} - ${status}`;
        detailReason.textContent = reason;
        detailBody.textContent = body || 'Sin contenido de texto.';
        detailSender.textContent = sender;
        detailPriority.textContent = priority;
        detailDate.textContent = date;
        detailRecipients.textContent = `${recCount} personas`;

        if (image) {
            detailImage.src = image;
            detailImage.classList.remove('hidden');
            detailImagePlaceholder.classList.add('hidden');
        } else {
            detailImage.src = '';
            detailImage.classList.add('hidden');
            detailImagePlaceholder.classList.remove('hidden');
        }
    }

    rows.forEach(row => {
        row.addEventListener('click', function() {
            selectNotificationRow(this);
        });
    });

    if (rows.length > 0) {
        selectNotificationRow(rows[0]);
    }

    // 2. Lógica interactiva de los Filtros de Destinatarios ("Para")
    const checkboxes = document.querySelectorAll('.recipient-checkbox');
    const employeeRows = document.querySelectorAll('.employee-row');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const submitCountLabel = document.getElementById('submitCountLabel');

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.recipient-checkbox:checked').length;
        selectedCountBadge.textContent = `${checkedCount} sel`;
        submitCountLabel.textContent = `${checkedCount}`;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Modos de filtro (Cargo Sap, Sección, Legajo)
    const filterRadios = document.querySelectorAll('input[name="filterMode"]');
    const blockCargo = document.getElementById('filterBlockCargo');
    const blockSeccion = document.getElementById('filterBlockSeccion');
    const blockLegajo = document.getElementById('filterBlockLegajo');

    function highlightActiveFilterMode() {
        const activeMode = document.querySelector('input[name="filterMode"]:checked').value;
        blockCargo.classList.toggle('opacity-40', activeMode !== 'cargo');
        blockSeccion.classList.toggle('opacity-40', activeMode !== 'seccion');
        blockLegajo.classList.toggle('opacity-40', activeMode !== 'legajo');
    }

    filterRadios.forEach(radio => {
        radio.addEventListener('change', highlightActiveFilterMode);
    });
    highlightActiveFilterMode();

    // Checkbox "Todos los Cargos Sap"
    const checkAllCargos = document.getElementById('checkAllCargos');
    const selectCargo = document.getElementById('selectCargo');
    checkAllCargos.addEventListener('change', function() {
        if (this.checked) {
            selectCargo.value = '';
            checkboxes.forEach(cb => cb.checked = true);
        } else {
            checkboxes.forEach(cb => cb.checked = false);
        }
        updateSelectedCount();
    });

    selectCargo.addEventListener('change', function() {
        const val = this.value;
        checkAllCargos.checked = false;
        if (!val) return;
        employeeRows.forEach(row => {
            const cb = row.querySelector('.recipient-checkbox');
            const cargo = row.dataset.cargo;
            if (cargo === val) {
                cb.checked = true;
                row.classList.remove('hidden');
            } else {
                cb.checked = false;
            }
        });
        updateSelectedCount();
    });

    // Checkbox "Todas las Secciones"
    const checkAllSecciones = document.getElementById('checkAllSecciones');
    const selectSeccion = document.getElementById('selectSeccion');
    checkAllSecciones.addEventListener('change', function() {
        if (this.checked) {
            selectSeccion.value = '';
            checkboxes.forEach(cb => cb.checked = true);
        } else {
            checkboxes.forEach(cb => cb.checked = false);
        }
        updateSelectedCount();
    });

    selectSeccion.addEventListener('change', function() {
        const val = this.value;
        checkAllSecciones.checked = false;
        if (!val) return;
        employeeRows.forEach(row => {
            const cb = row.querySelector('.recipient-checkbox');
            const seccion = row.dataset.seccion;
            if (seccion === val) {
                cb.checked = true;
                row.classList.remove('hidden');
            } else {
                cb.checked = false;
            }
        });
        updateSelectedCount();
    });

    // Checkbox "Todos los Legajos" y búsqueda rápida
    const checkAllLegajos = document.getElementById('checkAllLegajos');
    const inputSearchLegajo = document.getElementById('inputSearchLegajo');
    checkAllLegajos.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    inputSearchLegajo.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        employeeRows.forEach(row => {
            const legajo = (row.dataset.legajo || '').toLowerCase();
            const name = (row.dataset.name || '').toLowerCase();
            const cargo = (row.dataset.cargo || '').toLowerCase();
            const match = legajo.includes(q) || name.includes(q) || cargo.includes(q);
            row.style.display = match ? 'flex' : 'none';
        });
    });

    // Botones de acción rápida: Limpiar y Seleccionar todos visibles
    document.getElementById('btnClearSelection').addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        checkAllCargos.checked = false;
        checkAllSecciones.checked = false;
        checkAllLegajos.checked = false;
        updateSelectedCount();
    });

    document.getElementById('btnSelectAllVisible').addEventListener('click', function() {
        employeeRows.forEach(row => {
            if (row.style.display !== 'none') {
                const cb = row.querySelector('.recipient-checkbox');
                if (cb) cb.checked = true;
            }
        });
        updateSelectedCount();
    });

    // Validación antes del envío
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.recipient-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos un empleado destinatario antes de enviar la notificación.');
        }
    });

    updateSelectedCount();
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
@endsection
