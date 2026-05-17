@extends('layouts.admin')

@section('content')
<div x-data="{ 
    showCreateModal: false, 
    showEditModal: false, 
    search: '',
    editForm: { id: '', name: '', phone: '', points: 0, active: true },
    openEdit(customer) {
        this.editForm.id = customer.id;
        this.editForm.name = customer.name;
        this.editForm.phone = customer.phone;
        this.editForm.points = customer.points;
        this.editForm.active = Boolean(customer.active);
        this.showEditModal = true;
    }
}">

    {{-- Encabezado principal --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold text-amber-900">Gestión de Clientes VIP</h1>
            <p class="text-stone-500 mt-1">Administra el programa de lealtad y el saldo de Puntos Ignoto.</p>
        </div>

        <button @click="showCreateModal = true"
           class="bg-amber-800 text-white px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Nuevo Cliente VIP
        </button>
    </div>

    {{-- Errores de validación de formularios --}}
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 text-red-800 rounded-r-xl text-sm">
            <ul class="list-disc pl-4 space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PANEL DE CONFIGURACIÓN GLOBAL DE PUNTOS --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-100 mb-8">
        <div class="flex items-center gap-3 mb-4 border-b border-stone-100 pb-3">
            <span class="text-2xl">⚙️</span>
            <div>
                <h3 class="font-bold text-lg text-stone-800">Reglas de Acumulación y Canje</h3>
                <p class="text-xs text-stone-400">Ajusta el comportamiento financiero del programa de lealtad.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('vip.settings.update') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1.5">Dinero requerido para ganar 1 Punto ($)</label>
                <input type="number" name="money_for_point" value="{{ $moneyForOnePoint }}" step="0.5" min="1" 
                       class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500"
                       {{ Auth::user()->role !== 'admin' ? 'readonly' : '' }}>
                <span class="text-[11px] text-stone-400 block mt-1">Ej: Por cada ${{ $moneyForOnePoint }} pesos gastados, se otorga 1 Punto.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1.5">Valor monetario de 1 Punto al pagar ($)</label>
                <input type="number" name="point_value" value="{{ $pointValue }}" step="0.5" min="0.5" 
                       class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500"
                       {{ Auth::user()->role !== 'admin' ? 'readonly' : '' }}>
                <span class="text-[11px] text-stone-400 block mt-1">Ej: 1 Punto equivale a ${{ $pointValue }} pesos de descuento.</span>
            </div>

            <div>
                @if(Auth::user()->role === 'admin')
                    <button type="submit" class="w-full bg-stone-800 hover:bg-stone-900 text-white font-bold py-2.5 px-4 rounded-lg text-sm transition shadow">
                        Guardar Ajustes
                    </button>
                @else
                    <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-[11px] text-amber-800 text-center font-medium">
                        🔒 Solo el Administrador puede cambiar la tasa de puntos.
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- SECCIÓN DE LISTADO Y BÚSQUEDA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-stone-100 flex flex-col lg:flex-row justify-between items-center gap-4">
            <h3 class="font-bold text-lg text-stone-800">Directorio de Clientes VIP</h3>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <form method="GET" action="{{ route('vip.index') }}" class="w-full sm:w-52">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full text-xs rounded-lg border-stone-300 px-3 py-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </form>

                <div class="relative w-full sm:w-72">
                    <input type="text" x-model="search" placeholder="Buscar por nombre o teléfono..." 
                        class="w-full text-xs rounded-lg border-stone-300 pl-8 pr-4 py-2 focus:ring-amber-500 focus:border-amber-500">
                    <span class="absolute left-2.5 top-2.5 text-stone-400 text-xs">🔍</span>
                </div>
            </div>
        </div>

        {{-- Tabla de clientes --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 text-xs uppercase border-b border-stone-100 font-bold">
                        <th class="py-3 px-6">Cliente</th>
                        <th class="py-3 px-6">Teléfono / Cuenta</th>
                        <th class="py-3 px-6 text-center">Saldo de Puntos</th>
                        <th class="py-3 px-6">Fecha de Registro</th>
                        <th class="py-3 px-6 text-center">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-amber-50/30 transition" 
                            x-show="search === '' || '{{ strtolower($customer->name) }}'.includes(search.toLowerCase()) || '{{ $customer->phone }}'.includes(search)">
                            
                            <td class="py-4 px-6 font-bold text-stone-900 flex items-center gap-2">
                                <span class="h-8 w-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                               </span>
                                {{ $customer->name }}
                            </td>
                            
                            <td class="py-4 px-6 font-mono text-stone-600">
                                📱 {{ $customer->phone }}
                            </td>
                            
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $customer->points > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-stone-100 text-stone-500' }}">
                                    🎁 {{ $customer->points }} Pts
                                </span>
                            </td>
                            
                            <td class="py-4 px-6 text-xs text-stone-400">
                                {{ $customer->created_at->format('d/m/Y') }}
                            </td>

                            <td class="py-4 px-6 text-center">
                                @if($customer->active)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-200 text-stone-600">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                                                        
                            <td class="py-4 px-6 text-center flex justify-center gap-2">
                                <button @click='openEdit(@json($customer))'
                                        class="px-2.5 py-1 text-xs bg-stone-100 hover:bg-amber-100 hover:text-amber-900 text-stone-600 rounded font-medium transition">
                                    Editar
                                </button>
                                
                                @if($customer->active)
                                    <form method="POST"
                                        action="{{ route('vip.customer.destroy', $customer) }}"
                                        onsubmit="return confirm('¿Dar de baja a este cliente VIP? Sus puntos e historial se conservarán.')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="px-2.5 py-1 text-xs bg-stone-100 hover:bg-orange-100 hover:text-orange-700 text-stone-600 rounded font-medium transition">
                                            Baja
                                        </button>
                                    </form>
                                @else
                                    <span class="px-2.5 py-1 text-xs bg-stone-100 text-stone-400 rounded font-medium">
                                        Dado de baja
                                    </span>
                                @endif

                                <form method="POST"
                                    action="{{ route('vip.customer.force-delete', $customer) }}"
                                    onsubmit="return confirm('¿Eliminar definitivamente este cliente? Solo se permitirá si no tiene ventas asociadas.')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="px-2.5 py-1 text-xs bg-stone-100 hover:bg-red-100 hover:text-red-700 text-stone-600 rounded font-medium transition">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-stone-400">
                                No hay clientes VIP registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: NUEVO CLIENTE --}}
    <div x-show="showCreateModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-cloak>
        <div @click.away="showCreateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-stone-100 transform transition-all">
            <div class="flex justify-between items-center mb-4 border-b border-stone-100 pb-3">
                <h3 class="font-bold text-lg text-amber-900 font-serif">✨ Registrar Nuevo VIP</h3>
                <button @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600 text-lg font-bold">×</button>
            </div>

            <form method="POST" action="{{ route('vip.customer.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Nombre Completo</label>
                    <input type="text" name="name" required placeholder="Ej: Alejandra Gómez" 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Teléfono Celular (10 dígitos)</label>
                    <input type="tel" name="phone" required placeholder="Ej: 9511234567" 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Saldo Inicial de Puntos</label>
                    <input type="number" name="points" value="0" min="0" required 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                    <span class="text-[10px] text-stone-400 mt-1 block">Por defecto inicia en 0. Puedes otorgar puntos de bienvenida.</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-stone-50">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-bold text-stone-500 hover:bg-stone-100 rounded-lg transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-amber-800 hover:bg-amber-900 text-white rounded-lg transition shadow">Registrar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDITAR CLIENTE --}}
    <div x-show="showEditModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-cloak>
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-stone-100 transform transition-all">
            <div class="flex justify-between items-center mb-4 border-b border-stone-100 pb-3">
                <h3 class="font-bold text-lg text-amber-900 font-serif">✏️ Modificar Cuenta VIP</h3>
                <button @click="showEditModal = false" class="text-stone-400 hover:text-stone-600 text-lg font-bold">×</button>
            </div>

            <form method="POST" :action="'{{ url('vip-management/customer') }}/' + editForm.id" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Nombre Completo</label>
                    <input type="text" name="name" x-model="editForm.name" required 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Teléfono Celular</label>
                    <input type="tel" name="phone" x-model="editForm.phone" required 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Ajustar Saldo de Puntos</label>
                    <input type="number" name="points" x-model="editForm.points" min="0" required 
                           class="w-full rounded-lg border-stone-300 text-sm focus:ring-amber-500 focus:border-amber-500 font-bold text-amber-800">
                    <span class="text-[10px] text-amber-700 mt-1 block">⚠️ Modificar el saldo directamente afectará el poder de compra del cliente.</span>
                </div>

                <div class="flex items-center gap-2 bg-stone-50 border border-stone-100 rounded-lg px-3 py-2">
                    <input type="checkbox"
                        name="active"
                        value="1"
                        x-model="editForm.active"
                        class="rounded border-stone-300 text-amber-700 focus:ring-amber-600">

                    <span class="text-xs font-bold text-stone-700">
                        Cliente VIP activo
                    </span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-stone-50">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-bold text-stone-500 hover:bg-stone-100 rounded-lg transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-stone-800 hover:bg-stone-900 text-white rounded-lg transition shadow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection