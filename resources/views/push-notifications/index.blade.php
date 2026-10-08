@extends('layouts.app')

@section('content')
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-[#191f26] tracking-tight">Cola de Revisión (Push)</h1>
            <p class="text-xs text-[#707680] mt-1 font-medium">Gestiona y envía notificaciones push a los empleados.</p>
        </div>

        <button onclick="document.getElementById('pushModal').classList.remove('hidden')" class="bg-[#b3493b] hover:bg-[#9d3f32] text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span>Enviar Push</span>
        </button>
    </div>

    @if(session('success'))
        <div class="bg-[#eaf5ef] text-[#2c7a51] p-4 rounded-xl mb-6 text-sm font-bold border border-[#c1ebd4]">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-[#faebe8] text-[#b3493b] p-4 rounded-xl mb-6 text-sm font-bold border border-[#f3d6d2]">
            Hubo un error al enviar la notificación. Revisa los datos.
        </div>
    @endif

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#ede7df]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-[#7d848f] tracking-wider border-b border-[#f3eee7]">
                        <th class="pb-2.5">Título</th>
                        <th class="pb-2.5">Cuerpo</th>
                        <th class="pb-2.5">Destinatarios</th>
                        <th class="pb-2.5">Fecha</th>
                        <th class="pb-2.5">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f7f3ec] text-[#2c333e]">
                    @forelse($notifications as $notification)
                        <tr>
                            <td class="py-3.5 font-bold">{{ $notification->title }}</td>
                            <td class="py-3.5 text-[#585f69] font-medium truncate max-w-xs">{{ \Illuminate\Support\Str::limit($notification->body, 50) }}</td>
                            <td class="py-3.5 text-gray-600 font-medium">{{ is_array($notification->recipients) ? count($notification->recipients) : 0 }} empleados</td>
                            <td class="py-3.5 text-gray-500">{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5">
                                @php
                                    $statusColor = match($notification->status) {
                                        'Enviado correctamente' => 'bg-[#eaf5ef] text-[#2c7a51] border-[#c1ebd4]',
                                        'Procesando' => 'bg-[#fff8e6] text-[#b47a09] border-[#fcefc2]',
                                        'Enviado con errores', 'Sin destinatarios válidos' => 'bg-[#faebe8] text-[#b3493b] border-[#f3d6d2]',
                                        default => 'bg-[#eaf5ef] text-[#2c7a51] border-[#c1ebd4]'
                                    };
                                @endphp
                                <span class="{{ $statusColor }} px-2.5 py-1 rounded-md text-[11px] font-bold border">
                                    {{ $notification->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400 font-medium">No hay notificaciones enviadas aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    <div id="pushModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="p-6 border-b border-[#ede7df] flex justify-between items-center bg-[#fbf8f2]">
                <h3 class="text-lg font-bold text-[#191f26]">Nueva Notificación Push</h3>
                <button onclick="document.getElementById('pushModal').classList.add('hidden')" class="text-gray-400 hover:text-[#b3493b] transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('cola-revision.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#191f26] mb-1.5 uppercase tracking-wide">Título</label>
                    <input type="text" name="title" required class="w-full px-4 py-3 bg-[#fbf8f2] border border-[#ede7df] rounded-xl text-sm focus:outline-none focus:border-[#b3493b] focus:ring-1 focus:ring-[#b3493b]" placeholder="Ej: Nuevo recibo disponible">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#191f26] mb-1.5 uppercase tracking-wide">Mensaje / Cuerpo</label>
                    <textarea name="body" required rows="3" class="w-full px-4 py-3 bg-[#fbf8f2] border border-[#ede7df] rounded-xl text-sm focus:outline-none focus:border-[#b3493b] focus:ring-1 focus:ring-[#b3493b]" placeholder="Escribe el mensaje de la notificación..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#191f26] mb-1.5 uppercase tracking-wide">Destinatarios (Empleados)</label>
                    <select name="recipients[]" multiple required class="w-full px-4 py-3 bg-[#fbf8f2] border border-[#ede7df] rounded-xl text-sm focus:outline-none focus:border-[#b3493b] h-32">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-[#717a86] mt-2 font-semibold">Mantén presionado Ctrl (o Cmd) para seleccionar varios.</p>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-[#ede7df] mt-6">
                    <button type="button" onclick="document.getElementById('pushModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-[#585f69] hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-[#b3493b] hover:bg-[#9d3f32] text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm transition flex items-center gap-2">
                        <span>Enviar Notificación</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
