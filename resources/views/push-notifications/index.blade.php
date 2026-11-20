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
        @if(session('error'))
            <div class="bg-[#faebe8] text-[#b3493b] px-3.5 py-1.5 rounded-xl text-xs font-bold border border-[#f3d6d2] flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-[#faebe8] text-[#b3493b] px-3.5 py-1.5 rounded-xl text-xs font-bold border border-[#f3d6d2] flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>{{ $errors->first() }}</span>
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
                            value="{{ old('notification_date', date('Y-m-d')) }}"
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
                                <option value="" disabled {{ old('sender') ? '' : 'selected' }}>Emisor de la Notificación</option>
                                @foreach($emisores as $emisor)
                                    <option value="{{ $emisor }}" {{ old('sender') === $emisor ? 'selected' : '' }}>{{ $emisor }}</option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 absolute right-2.5 top-2 pointer-events-none"></i>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="sender_show_section" value="1" {{ old('sender_show_section') ? 'checked' : '' }}
                                class="w-3.5 h-3.5 rounded border-[#ede7df] text-[#b3493b] focus:ring-0">
                            <span class="text-[11px] font-semibold text-[#585f69]">Muestra Sección como Emisor</span>
                        </label>
                    </div>
                </div>

                <!-- Motivo -->
                <div class="grid grid-cols-12 items-center gap-2.5">
                    <label class="col-span-3 text-xs font-bold text-[#585f69]">Motivo</label>
                    <div class="col-span-9 relative">
                        <input list="motivosList" name="reason" id="reasonInput" required value="{{ old('reason') }}" placeholder="Seleccionar o escribir motivo..."
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
                        @php
                            $oldPriority = old('priority', 'normal');
                        @endphp
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="baja" {{ $oldPriority === 'baja' ? 'checked' : '' }} class="text-[#b3493b] focus:ring-0">
                            <span>Baja</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="normal" {{ $oldPriority === 'normal' ? 'checked' : '' }} class="text-[#b3493b] focus:ring-0">
                            <span>Normal</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#585f69]">
                            <input type="radio" name="priority" value="alta" {{ $oldPriority === 'alta' ? 'checked' : '' }} class="text-[#b3493b] focus:ring-0">
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
                            @php
                                $oldRecipients = array_map('intval', old('recipients', []));
                            @endphp
                            @foreach($employees as $employee)
                                <label class="flex items-center justify-between px-3 py-2 hover:bg-[#fbf8f2] cursor-pointer employee-row"
                                       data-id="{{ $employee->id }}"
                                       data-legajo="{{ $employee->legajo ?? 'S/L' }}"
                                       data-name="{{ $employee->name }}"
                                       data-cargo="{{ $employee->cargo_sap ?? '' }}"
                                       data-seccion="{{ $employee->seccion ?? '' }}">
                                    <div class="flex items-center gap-2 w-20">
                                        <input type="checkbox" name="recipients[]" value="{{ $employee->id }}"
                                               {{ in_array((int) $employee->id, $oldRecipients, true) ? 'checked' : '' }}
                                               class="recipient-checkbox w-3.5 h-3.5 text-[#b3493b] rounded border-gray-300 focus:ring-0">
                                        <span class="font-mono font-bold text-[#b3493b] text-[10px]">{{ $employee->legajo ?? '-' }}</span>
                                    </div>
                                    <div class="flex-1 px-2 truncate text-[#191f26] font-medium text-[11px]">
                                        {{ $employee->name }}
                                        @if(empty($employee->fcm_token))
                                            <span class="ml-1 inline-block bg-[#fff8e6] text-[#b47a09] border border-[#fcefc2] text-[9px] font-bold px-1.5 rounded-full align-middle">Sin app</span>
                                        @endif
                                        <span class="block text-[9px] text-[#707680] font-normal truncate">
                                            {{ $employee->cargo_sap ?? 'Sin cargo' }} &bull; {{ $employee->seccion ?? 'General' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mensaje / Enlace / Flyer / Programación / Envío -->
                    <div class="pt-2.5 border-t border-[#ede7df] space-y-2.5">
                        <div>
                            <label class="block text-xs font-bold text-[#585f69] mb-1">Mensaje de la Notificación</label>
                            <textarea name="body" id="bodyInput" required rows="2" placeholder="Escribe el mensaje o cuerpo de la notificación..."
                                class="w-full px-3 py-1.5 text-xs bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">{{ old('body') }}</textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#585f69]">Enlace opcional (URL)</label>
                            <input type="url" name="link_url" id="linkUrlInput" value="{{ old('link_url') }}" placeholder="https://ejemplo.com/recurso"
                                class="w-full px-3 py-1.5 text-xs bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                            <div id="actionTextWrapper" class="{{ old('link_url') ? '' : 'hidden' }}">
                                <input type="text" name="action_text" id="actionTextInput" maxlength="60" value="{{ old('action_text') }}"
                                    placeholder="Texto del botón (opcional, ej. Abrir enlace)"
                                    class="w-full px-3 py-1.5 text-xs bg-[#faf7f2] border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#585f69] mb-1">Imagen / Flyer para Detalle (Opcional)</label>
                            <input type="file" name="image" id="imageInput" accept="image/*"
                                class="w-full text-xs text-[#585f69] file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#faebe8] file:text-[#b3493b] hover:file:bg-[#f5dad4] cursor-pointer">
                        </div>

                        @php
                            $oldSendMode = old('send_mode', 'now');
                        @endphp
                        <div class="p-2.5 bg-[#fbf8f2] rounded-xl border border-[#ede7df] space-y-2">
                            <span class="block text-xs font-bold text-[#585f69]">Momento de envío</span>
                            <div class="flex items-center gap-5 text-xs font-semibold text-[#585f69]">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="send_mode" value="now" {{ $oldSendMode === 'now' ? 'checked' : '' }} class="text-[#b3493b] focus:ring-0">
                                    <span>Enviar ahora</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="send_mode" value="schedule" {{ $oldSendMode === 'schedule' ? 'checked' : '' }} class="text-[#b3493b] focus:ring-0">
                                    <span class="text-[#3730a3] font-bold">Programar para más tarde</span>
                                </label>
                            </div>
                            <div id="scheduleFieldsWrapper" class="{{ $oldSendMode === 'schedule' ? '' : 'hidden' }} space-y-1.5">
                                <input type="hidden" name="scheduled_at" id="scheduledAtInput" value="{{ old('scheduled_at') }}">
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-7">
                                        <label for="scheduledDateInput" class="block text-[10px] font-bold text-[#585f69] mb-0.5">Fecha</label>
                                        <input type="date" id="scheduledDateInput" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-bwignore="true"
                                            class="w-full px-2.5 py-1.5 text-xs font-semibold bg-white border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                                    </div>
                                    <div class="col-span-5">
                                        <label for="scheduledHourInput" class="block text-[10px] font-bold text-[#585f69] mb-0.5">Hora</label>
                                        <div class="flex items-center gap-1">
                                            <select id="scheduledHourInput" aria-label="Hora de envío" autocomplete="off" data-lpignore="true"
                                                class="w-full px-2 py-1.5 text-xs font-semibold bg-white border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                                                @for($h = 0; $h < 24; $h++)
                                                    <option value="{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}</option>
                                                @endfor
                                            </select>
                                            <span class="text-xs font-bold text-[#585f69]">:</span>
                                            <select id="scheduledMinuteInput" aria-label="Minutos de envío" autocomplete="off" data-lpignore="true"
                                                class="w-full px-2 py-1.5 text-xs font-semibold bg-white border border-[#ede7df] rounded-xl text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                                                @for($m = 0; $m < 60; $m++)
                                                    <option value="{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <span class="block text-[10px] text-[#707680]">Hora Argentina (ART · UTC-3). Mínimo 1 minuto posterior a la hora actual.</span>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmitNotification"
                            class="w-full bg-[#b3493b] hover:bg-[#9d3f32] disabled:opacity-70 disabled:cursor-not-allowed text-white py-2.5 px-4 rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2">
                            <span id="submitIconIdle" class="inline-flex items-center">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            </span>
                            <svg id="submitSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span id="submitBtnText"><span id="submitActionLabel">{{ $oldSendMode === 'schedule' ? 'Programar' : 'Enviar' }}</span> Notificación Push (<span id="submitCountLabel">0</span>)</span>
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
                    <form method="GET" action="{{ route('cola-revision.index') }}" class="flex flex-wrap items-center gap-2">
                        <select name="q_status" class="px-2.5 py-1 text-xs bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                            <option value="" {{ request('q_status') ? '' : 'selected' }}>Todos los estados</option>
                            <option value="programadas" {{ request('q_status') === 'programadas' ? 'selected' : '' }}>Programadas</option>
                            <option value="enviadas" {{ request('q_status') === 'enviadas' ? 'selected' : '' }}>Enviadas</option>
                            <option value="canceladas" {{ request('q_status') === 'canceladas' ? 'selected' : '' }}>Canceladas</option>
                        </select>
                        <input type="text" name="q_reason" value="{{ request('q_reason') }}" placeholder="Filtrar por motivo..."
                            class="px-2.5 py-1 text-xs bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                        <button type="submit" class="bg-[#191f26] hover:bg-[#28323c] text-white px-3 py-1 rounded-lg text-xs font-bold transition">
                            Buscar
                        </button>
                        @if(request()->hasAny(['q_reason', 'q_id', 'q_date', 'q_status']))
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
                                    $scheduledFormatted = $n->scheduled_at ? $n->scheduled_at->format('d/m/Y H:i') . ' hs' : '';
                                    $scheduledInputVal = $n->scheduled_at ? $n->scheduled_at->format('Y-m-d\TH:i') : '';
                                    $sentFormatted = $n->sent_at ? $n->sent_at->format('d/m/Y H:i') . ' hs' : '';
                                    $isScheduled = $n->estaProgramada();
                                    $badgeClasses = match ($n->status) {
                                        \App\Models\PushNotification::STATUS_ENVIADO => 'bg-[#eaf5ef] text-[#2c7a51] border border-[#c1ebd4]',
                                        \App\Models\PushNotification::STATUS_PROGRAMADA => 'bg-[#eef2ff] text-[#3730a3] border border-[#c7d2fe]',
                                        \App\Models\PushNotification::STATUS_CANCELADA => 'bg-[#f3f4f6] text-[#6b7280] border border-[#e5e7eb]',
                                        default => 'bg-[#fff8e6] text-[#b47a09] border border-[#fcefc2]',
                                    };
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
                                    data-link-url="{{ $n->link_url ?? '' }}"
                                    data-action-text="{{ $n->action_text ?? '' }}"
                                    data-scheduled-at="{{ $scheduledFormatted }}"
                                    data-scheduled-at-input="{{ $scheduledInputVal }}"
                                    data-sent-at="{{ $sentFormatted }}"
                                    data-is-scheduled="{{ $isScheduled ? '1' : '0' }}"
                                    data-cancel-url="{{ route('cola-revision.cancel', $n) }}"
                                    data-send-now-url="{{ route('cola-revision.send-now', $n) }}"
                                    data-reschedule-url="{{ route('cola-revision.reschedule', $n) }}"
                                    data-recipients-count="{{ is_array($n->recipients) ? count($n->recipients) : 0 }}">
                                    <td class="py-2.5 px-2.5 text-center text-xs indicator-cell font-bold text-[#b3493b]">
                                        {{ $isSelected ? '»' : '>' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold">{{ $n->id }}</td>
                                    <td class="py-2.5 px-3">{{ $dateFormatted }}</td>
                                    <td class="py-2.5 px-3 font-bold uppercase truncate max-w-md">{{ $n->reason ?? $n->title }}</td>
                                    <td class="py-2.5 px-3 text-right">
                                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badgeClasses }}">
                                            {{ $n->status }}
                                        </span>
                                        @if($isScheduled && $scheduledFormatted !== '')
                                            <span class="block text-[9px] text-[#3730a3] font-semibold mt-0.5">{{ $scheduledFormatted }}</span>
                                        @endif
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
                        <div class="space-y-2">
                            <span id="detailReason" class="text-xs font-extrabold text-[#191f26] block"></span>
                            <p id="detailBody" class="text-[#585f69] font-medium leading-relaxed bg-[#fbf8f2] p-2.5 rounded-xl border border-[#ede7df] max-h-24 overflow-y-auto text-[11px]"></p>
                            <div id="detailLinkWrapper" class="hidden bg-[#fbf8f2] px-2.5 py-1.5 rounded-xl border border-[#ede7df] flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <i data-lucide="link-2" class="w-3.5 h-3.5 text-[#b3493b] shrink-0"></i>
                                    <a id="detailLinkAnchor" href="#" target="_blank" rel="noopener noreferrer"
                                        class="text-[11px] font-semibold text-[#b3493b] underline truncate"></a>
                                </div>
                                <span id="detailLinkActionText" class="hidden shrink-0 text-[10px] font-bold bg-[#faebe8] text-[#b3493b] px-2 py-0.5 rounded-md"></span>
                            </div>
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
                            <div id="detailScheduleWrapper" class="col-span-2 hidden">
                                <span id="detailScheduleLabel" class="text-[#707680] block font-semibold"></span>
                                <span id="detailScheduleValue" class="font-bold text-[#3730a3]"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones para notificaciones programadas -->
                <div id="detailScheduledActions" class="hidden mt-3 pt-3 border-t border-[#ede7df] space-y-2">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <form id="formSendNow" method="POST" action="" onsubmit="return confirm('¿Enviar esta notificación inmediatamente a todos sus destinatarios?');">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#2c7a51] hover:bg-[#236341] text-white text-[11px] font-bold transition inline-flex items-center gap-1.5">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                <span>Enviar ahora</span>
                            </button>
                        </form>
                        <button type="button" id="btnToggleReschedule"
                            class="px-3 py-1.5 rounded-xl bg-[#eef2ff] hover:bg-[#e0e7ff] text-[#3730a3] border border-[#c7d2fe] text-[11px] font-bold transition inline-flex items-center gap-1.5">
                            <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i>
                            <span>Reprogramar</span>
                        </button>
                        <form id="formCancelScheduled" method="POST" action="" onsubmit="return confirm('¿Cancelar el envío programado de esta notificación?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#faebe8] hover:bg-[#f5dad4] text-[#b3493b] border border-[#f3d6d2] text-[11px] font-bold transition inline-flex items-center gap-1.5">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                <span>Cancelar envío</span>
                            </button>
                        </form>
                    </div>
                    <div id="rescheduleFormWrapper" class="hidden p-2.5 bg-[#fbf8f2] rounded-xl border border-[#ede7df]">
                        <form id="formReschedule" method="POST" action="" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="scheduled_at" id="rescheduleInput">
                            <label for="rescheduleDateInput" class="text-[11px] font-bold text-[#585f69]">Nueva fecha y hora:</label>
                            <input type="date" id="rescheduleDateInput" required autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-bwignore="true"
                                class="px-2.5 py-1 text-xs font-semibold bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                            <div class="flex items-center gap-1">
                                <select id="rescheduleHourInput" aria-label="Nueva hora" autocomplete="off" data-lpignore="true"
                                    class="px-2 py-1 text-xs font-semibold bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                                    @for($h = 0; $h < 24; $h++)
                                        <option value="{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}">{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                                <span class="text-xs font-bold text-[#585f69]">:</span>
                                <select id="rescheduleMinuteInput" aria-label="Nuevos minutos" autocomplete="off" data-lpignore="true"
                                    class="px-2 py-1 text-xs font-semibold bg-white border border-[#ede7df] rounded-lg text-[#191f26] focus:outline-none focus:border-[#b3493b]">
                                    @for($m = 0; $m < 60; $m++)
                                        <option value="{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#3730a3] hover:bg-[#2c2585] text-white text-[11px] font-bold transition">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pad = n => String(n).padStart(2, '0');
    const ARG_TIMEZONE = 'America/Argentina/Buenos_Aires';
    const argPartsFormatter = new Intl.DateTimeFormat('en-CA', {
        timeZone: ARG_TIMEZONE,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    });

    function getMinScheduleParts() {
        const nextMinuteInstant = new Date(Math.floor(Date.now() / 60000) * 60000 + 60000);
        const parts = {};
        argPartsFormatter.formatToParts(nextMinuteInstant).forEach(p => {
            if (p.type !== 'literal') parts[p.type] = p.value;
        });
        const dateStr = `${parts.year}-${parts.month}-${parts.day}`;
        const hour = Number(parts.hour);
        const minute = Number(parts.minute);
        return {
            dateStr,
            hour,
            minute,
            isoStr: `${dateStr}T${pad(hour)}:${pad(minute)}`,
        };
    }

    function parseDateTimeParts(value) {
        const match = /^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})$/.exec((value || '').trim());
        if (!match) return null;
        return {
            dateStr: match[1],
            hour: match[2],
            minute: match[3],
        };
    }

    function createDateTimePicker({ hiddenInput, dateInput, hourSelect, minuteSelect }) {
        function sync(preferredValue = null) {
            const min = getMinScheduleParts();

            dateInput.min = min.dateStr;

            const parsed = preferredValue ? parseDateTimeParts(preferredValue) : null;
            if (parsed) {
                dateInput.value = parsed.dateStr;
                hourSelect.value = parsed.hour;
                minuteSelect.value = parsed.minute;
            } else if (!dateInput.value) {
                dateInput.value = min.dateStr;
                hourSelect.value = pad(min.hour);
                minuteSelect.value = pad(min.minute);
            }

            if (dateInput.value < min.dateStr) {
                dateInput.value = min.dateStr;
            }

            const isMinDay = dateInput.value === min.dateStr;
            Array.from(hourSelect.options).forEach(opt => {
                opt.disabled = isMinDay && Number(opt.value) < min.hour;
            });
            if (isMinDay && Number(hourSelect.value) < min.hour) {
                hourSelect.value = pad(min.hour);
            }

            const isMinHour = isMinDay && Number(hourSelect.value) === min.hour;
            Array.from(minuteSelect.options).forEach(opt => {
                opt.disabled = isMinHour && Number(opt.value) < min.minute;
            });
            if (isMinHour && Number(minuteSelect.value) < min.minute) {
                minuteSelect.value = pad(min.minute);
            }

            hiddenInput.value = `${dateInput.value}T${hourSelect.value}:${minuteSelect.value}`;
        }

        function isValid() {
            const val = (hiddenInput.value || '').trim();
            return parseDateTimeParts(val) !== null && val >= getMinScheduleParts().isoStr;
        }

        dateInput.addEventListener('change', () => sync());
        hourSelect.addEventListener('change', () => sync());
        minuteSelect.addEventListener('change', () => sync());

        return { sync, isValid };
    }

    // 1. Manejo de selección de filas en la grilla de notificaciones
    const rows = document.querySelectorAll('.notification-row');
    const detailMetaBadge = document.getElementById('detailMetaBadge');
    const detailImage = document.getElementById('detailImage');
    const detailImagePlaceholder = document.getElementById('detailImagePlaceholder');
    const detailReason = document.getElementById('detailReason');
    const detailBody = document.getElementById('detailBody');
    const detailLinkWrapper = document.getElementById('detailLinkWrapper');
    const detailLinkAnchor = document.getElementById('detailLinkAnchor');
    const detailLinkActionText = document.getElementById('detailLinkActionText');
    const detailSender = document.getElementById('detailSender');
    const detailPriority = document.getElementById('detailPriority');
    const detailDate = document.getElementById('detailDate');
    const detailRecipients = document.getElementById('detailRecipients');
    const detailScheduleWrapper = document.getElementById('detailScheduleWrapper');
    const detailScheduleLabel = document.getElementById('detailScheduleLabel');
    const detailScheduleValue = document.getElementById('detailScheduleValue');
    const detailScheduledActions = document.getElementById('detailScheduledActions');
    const formSendNow = document.getElementById('formSendNow');
    const formCancelScheduled = document.getElementById('formCancelScheduled');
    const formReschedule = document.getElementById('formReschedule');
    const rescheduleInput = document.getElementById('rescheduleInput');
    const rescheduleFormWrapper = document.getElementById('rescheduleFormWrapper');
    const btnToggleReschedule = document.getElementById('btnToggleReschedule');

    const reschedulePicker = createDateTimePicker({
        hiddenInput: rescheduleInput,
        dateInput: document.getElementById('rescheduleDateInput'),
        hourSelect: document.getElementById('rescheduleHourInput'),
        minuteSelect: document.getElementById('rescheduleMinuteInput'),
    });

    if (btnToggleReschedule) {
        btnToggleReschedule.addEventListener('click', function() {
            reschedulePicker.sync(rescheduleInput.value || null);
            rescheduleFormWrapper.classList.toggle('hidden');
        });
    }

    if (formReschedule) {
        formReschedule.addEventListener('submit', function(e) {
            if (!reschedulePicker.isValid()) {
                e.preventDefault();
                reschedulePicker.sync();
                alert('La fecha y hora debe ser al menos 1 minuto posterior a la hora actual.');
            }
        });
    }

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
        const linkUrl = row.dataset.linkUrl || '';
        const actionText = row.dataset.actionText || '';
        const scheduledAt = row.dataset.scheduledAt || '';
        const scheduledAtInput = row.dataset.scheduledAtInput || '';
        const sentAt = row.dataset.sentAt || '';
        const isScheduled = row.dataset.isScheduled === '1';

        detailMetaBadge.textContent = `#${id} - ${status}`;
        detailReason.textContent = reason;
        detailBody.textContent = body || 'Sin contenido de texto.';
        detailSender.textContent = sender;
        detailPriority.textContent = priority;
        detailDate.textContent = date;
        detailRecipients.textContent = `${recCount} personas`;

        if (linkUrl) {
            detailLinkAnchor.href = linkUrl;
            detailLinkAnchor.textContent = linkUrl;
            detailLinkWrapper.classList.remove('hidden');
            if (actionText) {
                detailLinkActionText.textContent = actionText;
                detailLinkActionText.classList.remove('hidden');
            } else {
                detailLinkActionText.classList.add('hidden');
            }
        } else {
            detailLinkAnchor.href = '#';
            detailLinkAnchor.textContent = '';
            detailLinkWrapper.classList.add('hidden');
            detailLinkActionText.classList.add('hidden');
        }

        if (isScheduled && scheduledAt) {
            detailScheduleLabel.textContent = 'Envío programado para:';
            detailScheduleValue.textContent = scheduledAt;
            detailScheduleWrapper.classList.remove('hidden');
        } else if (sentAt) {
            detailScheduleLabel.textContent = 'Enviada el:';
            detailScheduleValue.textContent = sentAt;
            detailScheduleWrapper.classList.remove('hidden');
        } else {
            detailScheduleWrapper.classList.add('hidden');
        }

        rescheduleFormWrapper.classList.add('hidden');
        if (isScheduled) {
            formSendNow.action = row.dataset.sendNowUrl || '';
            formCancelScheduled.action = row.dataset.cancelUrl || '';
            formReschedule.action = row.dataset.rescheduleUrl || '';
            reschedulePicker.sync(scheduledAtInput || null);
            detailScheduledActions.classList.remove('hidden');
        } else {
            detailScheduledActions.classList.add('hidden');
        }

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

    // 2. Lógica interactiva de enlace opcional y programación de envío
    const linkUrlInput = document.getElementById('linkUrlInput');
    const actionTextWrapper = document.getElementById('actionTextWrapper');
    const sendModeRadios = document.querySelectorAll('input[name="send_mode"]');
    const scheduleFieldsWrapper = document.getElementById('scheduleFieldsWrapper');
    const scheduledAtInput = document.getElementById('scheduledAtInput');
    const submitActionLabel = document.getElementById('submitActionLabel');

    const scheduledDateInput = document.getElementById('scheduledDateInput');
    const schedulePicker = createDateTimePicker({
        hiddenInput: scheduledAtInput,
        dateInput: scheduledDateInput,
        hourSelect: document.getElementById('scheduledHourInput'),
        minuteSelect: document.getElementById('scheduledMinuteInput'),
    });

    function syncLinkFields() {
        const hasLink = (linkUrlInput.value || '').trim().length > 0;
        actionTextWrapper.classList.toggle('hidden', !hasLink);
    }

    function syncSendMode() {
        const checkedRadio = document.querySelector('input[name="send_mode"]:checked');
        const isSchedule = checkedRadio && checkedRadio.value === 'schedule';
        scheduleFieldsWrapper.classList.toggle('hidden', !isSchedule);
        scheduledDateInput.required = isSchedule;
        if (isSchedule) {
            schedulePicker.sync(scheduledAtInput.value || null);
        }
        submitActionLabel.textContent = isSchedule ? 'Programar' : 'Enviar';
    }

    linkUrlInput.addEventListener('input', syncLinkFields);
    sendModeRadios.forEach(r => r.addEventListener('change', syncSendMode));
    syncLinkFields();
    syncSendMode();

    // 3. Lógica interactiva de los Filtros de Destinatarios ("Para")
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

    function isVisible(row) {
        return row.style.display !== 'none';
    }

    function checkRows(predicate, checked = true) {
        employeeRows.forEach(row => {
            if (predicate(row)) {
                const cb = row.querySelector('.recipient-checkbox');
                if (cb) cb.checked = checked;
            }
        });
        updateSelectedCount();
    }

    // Checkbox "Todos los Cargos Sap"
    const checkAllCargos = document.getElementById('checkAllCargos');
    const selectCargo = document.getElementById('selectCargo');
    checkAllCargos.addEventListener('change', function() {
        selectCargo.value = '';
        checkRows(row => (row.dataset.cargo || '') !== '', this.checked);
    });

    selectCargo.addEventListener('change', function() {
        checkAllCargos.checked = false;
        const val = this.value;
        if (val) checkRows(row => row.dataset.cargo === val);
    });

    // Checkbox "Todas las Secciones"
    const checkAllSecciones = document.getElementById('checkAllSecciones');
    const selectSeccion = document.getElementById('selectSeccion');
    checkAllSecciones.addEventListener('change', function() {
        selectSeccion.value = '';
        checkRows(row => (row.dataset.seccion || '') !== '', this.checked);
    });

    selectSeccion.addEventListener('change', function() {
        checkAllSecciones.checked = false;
        const val = this.value;
        if (val) checkRows(row => row.dataset.seccion === val);
    });

    // Checkbox "Todos los Legajos" y búsqueda rápida
    const checkAllLegajos = document.getElementById('checkAllLegajos');
    const inputSearchLegajo = document.getElementById('inputSearchLegajo');
    checkAllLegajos.addEventListener('change', function() {
        checkRows(isVisible, this.checked);
    });

    inputSearchLegajo.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        checkAllLegajos.checked = false;
        employeeRows.forEach(row => {
            const legajo = (row.dataset.legajo || '').toLowerCase();
            const name = (row.dataset.name || '').toLowerCase();
            const cargo = (row.dataset.cargo || '').toLowerCase();
            const match = legajo.includes(q) || name.includes(q) || cargo.includes(q);
            row.style.display = match ? 'flex' : 'none';
        });
    });

    document.getElementById('btnFilterLink').addEventListener('click', function() {
        const activeMode = document.querySelector('input[name="filterMode"]:checked').value;
        if (activeMode === 'cargo' && selectCargo.value) {
            checkRows(row => row.dataset.cargo === selectCargo.value);
        } else if (activeMode === 'seccion' && selectSeccion.value) {
            checkRows(row => row.dataset.seccion === selectSeccion.value);
        } else if (activeMode === 'legajo') {
            checkRows(isVisible);
        }
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
        checkRows(isVisible);
    });

    // Validación antes del envío y estado de carga
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.recipient-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos un empleado destinatario antes de enviar la notificación.');
            return;
        }

        const checkedMode = document.querySelector('input[name="send_mode"]:checked');
        const isSchedule = checkedMode && checkedMode.value === 'schedule';

        if (isSchedule) {
            if (!schedulePicker.isValid()) {
                e.preventDefault();
                schedulePicker.sync();
                alert('Seleccioná una fecha y hora al menos 1 minuto posterior a la hora actual.');
                return;
            }
        }

        const btnSubmit = document.getElementById('btnSubmitNotification');
        const iconIdle = document.getElementById('submitIconIdle');
        const spinner = document.getElementById('submitSpinner');
        const btnText = document.getElementById('submitBtnText');

        btnSubmit.disabled = true;
        iconIdle.classList.add('hidden');
        spinner.classList.remove('hidden');
        btnText.textContent = isSchedule ? 'Programando notificación...' : 'Enviando notificación...';
    });

    updateSelectedCount();
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
@endsection
