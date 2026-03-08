@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6">

    {{-- SECCIÓN IZQUIERDA: FORMULARIO DE REGISTRO --}}
    <div class="w-full lg:w-1/3">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 sticky top-6">
            <h2 class="text-xl font-bold text-amber-900 mb-2">Registrar Movimiento</h2>
            <p class="text-sm text-stone-500 mb-6">Agrega stock por compras o reporta mermas (desperdicios o accidentes).</p>

            <form action="{{ route('inventory_movements.store') }}" method="POST">
                @csrf
                
                {{-- Selector de Ingrediente --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-stone-700 mb-1">Materia Prima</label>
                    <select name="ingredient_id" required class="w-full border-stone-300 rounded-lg focus:ring-amber-500">
                        <option value="" disabled selected>Selecciona un insumo...(Estan en ml,gr)</option>
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit_measure }}) - Quedan: {{ $ing->current_quantity }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Movimiento --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-stone-700 mb-2">Tipo de Movimiento</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer bg-stone-50 p-2 rounded-lg border border-stone-200 flex-1 hover:bg-green-50 transition">
                            <input type="radio" name="type" value="entrada" required class="text-green-600 focus:ring-green-500">
                            <span class="text-sm font-bold text-green-700">Entrada (Sumar)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-stone-50 p-2 rounded-lg border border-stone-200 flex-1 hover:bg-red-50 transition">
                            <input type="radio" name="type" value="merma" required class="text-red-600 focus:ring-red-500">
                            <span class="text-sm font-bold text-red-700">Merma (Restar)</span>
                        </label>
                    </div>
                </div>

                {{-- Cantidad --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-stone-700 mb-1">Cantidad (Gramos, ml, Pzas)</label>
                    <input type="number" name="quantity" step="0.01" min="0.01" required placeholder="Ej: 1000"
                           class="w-full border-stone-300 rounded-lg focus:ring-amber-500">
                </div>

                {{-- Razón / Motivo --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-stone-700 mb-1">Motivo o Justificación</label>
                    <textarea name="reason" rows="2" required placeholder="Ej: Compra en el súper / Se derramó por accidente"
                              class="w-full border-stone-300 rounded-lg focus:ring-amber-500 text-sm"></textarea>
                </div>

                <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 rounded-xl shadow-md transition">
                    Guardar Registro
                </button>
            </form>
        </div>
    </div>

    {{-- SECCIÓN DERECHA: HISTORIAL DE MOVIMIENTOS --}}
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-stone-50 px-6 py-4 border-b border-stone-200 flex justify-between items-center">
                <h3 class="font-bold text-stone-800">Bitácora de Movimientos</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-100 text-stone-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Fecha y Hora</th>
                            <th class="px-6 py-3">Insumo</th>
                            <th class="px-6 py-3">Movimiento</th>
                            <th class="px-6 py-3">Motivo / Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @forelse($movements as $mov)
                            <tr class="hover:bg-stone-50">
                                <td class="px-6 py-4">
                                    <span class="block font-bold text-stone-800">{{ $mov->created_at->format('d M, Y') }}</span>
                                    <span class="block text-xs text-stone-400">{{ $mov->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-stone-700">
                                    {{ $mov->ingredient->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($mov->type == 'entrada')
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1">
                                            <span>➕</span> +{{ floatval($mov->quantity) }} {{ $mov->ingredient->unit_measure }}
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1">
                                            <span>➖</span> -{{ floatval($mov->quantity) }} {{ $mov->ingredient->unit_measure }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-stone-800 font-medium">{{ $mov->reason }}</span>
                                    <span class="block text-xs text-stone-400">👤 {{ $mov->user->name ?? 'Admin' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-stone-400 italic">No hay registros de movimientos en la bitácora.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection