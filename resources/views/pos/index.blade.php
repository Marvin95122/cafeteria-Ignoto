@extends('layouts.admin')

@section('content')

{{-- PANTALLA DE BLOQUEO: CAJA CERRADA --}}
@if(!isset($activeRegister) || !$activeRegister)
    <div class="fixed inset-0 bg-stone-900/90 backdrop-blur-md z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-10 max-w-lg w-full text-center shadow-2xl border-4 border-red-100 transform transition-all">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <span class="text-5xl">🔒</span>
            </div>
            <h2 class="text-3xl font-black text-red-600 mb-3 font-serif">Caja Cerrada</h2>
            <p class="text-stone-600 mb-8 text-lg">
                No se pueden realizar ventas en este momento. <br> 
                <strong>Pide al Gerente o Administrador que abra el turno del día</strong> en la sección de Corte de Caja.
            </p>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="bg-stone-200 hover:bg-stone-300 text-stone-800 font-bold py-3 px-8 rounded-xl transition">
                    Volver al Inicio
                </a>
                {{-- Si es gerente o admin, le damos un atajo directo para ir a abrir la caja --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                    <a href="{{ route('cash_registers.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-red-600/30 transition">
                        Abrir Caja Ahora
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif

<div id="pos-screen" class="pos-screen h-[calc(100vh-105px)] bg-stone-100 overflow-hidden">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="pos-layout" class="pos-layout grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] 2xl:grid-cols-[minmax(0,1fr)_355px] gap-3 h-full min-h-0 pt-2">
    
    {{-- SECCIÓN IZQUIERDA: CATÁLOGO DE PRODUCTOS --}}
    <div class="pos-catalog w-full min-w-0 flex flex-col h-full min-h-0">
        
        {{-- FILTROS COMPACTOS DEL POS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 px-3 py-2.5 mb-3 flex-none overflow-hidden">
            <div class="flex items-center gap-2 min-w-0">

                {{-- CATEGORÍAS --}}
                <div class="flex-1 min-w-0 overflow-x-auto scrollbar-hide" id="category-filters">
                    <div class="flex items-center gap-2 w-max pb-0.5">
                        <button onclick="filterByCategory('all', event)"
                                class="cat-btn active-cat px-4 py-2 bg-amber-800 text-white text-sm font-bold rounded-full shadow-sm whitespace-nowrap transition">
                            Todos
                        </button>

                        @foreach($categories as $category)
                            <button onclick="filterByCategory({{ $category->id }}, event)"
                                    class="cat-btn px-4 py-2 bg-white text-stone-600 hover:bg-amber-50 text-sm font-bold rounded-full border border-stone-200 shadow-sm whitespace-nowrap transition">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- BUSCADOR --}}
                <div class="relative w-[230px] 2xl:w-[280px] shrink-0">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                            </path>
                        </svg>
                    </span>

                    <input type="text"
                        id="posSearch"
                        placeholder="Buscar producto..."
                        onkeyup="filterProducts()"
                        class="w-full pl-10 pr-9 border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-200 py-2 bg-stone-50">

                    <button type="button"
                            onclick="clearProductSearch()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-red-500 transition"
                            title="Limpiar búsqueda">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto pr-1 custom-scrollbar">
            <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-2.5 sm:gap-3 pb-20 lg:pb-4" id="products-grid">
                @foreach($products as $product)
                    <button onclick="openProductModal({{ $product->id }})"
                            data-id="{{ $product->id }}"
                            data-category="{{ $product->category_id }}"
                            class="product-card bg-white p-2.5 sm:p-3 rounded-2xl shadow-sm border border-stone-100 hover:border-amber-300 hover:shadow-md transition text-left flex flex-col h-full relative group overflow-hidden">
                        
                        <div class="relative w-full h-24 sm:h-32 bg-stone-50 rounded-xl mb-2 sm:mb-3 overflow-hidden flex items-center justify-center border border-stone-100">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-contain object-center p-2 group-hover:scale-[1.03] transition duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-stone-300">
                                    <span class="text-2xl sm:text-4xl drop-shadow-sm">☕</span>
                                    <span class="text-[10px] font-bold mt-1">Sin imagen</span>
                                </div>
                            @endif

                            @if($product->extras->count() > 0)
                                <span class="absolute top-2 right-2 bg-white/95 text-amber-800 text-[10px] font-black px-2 py-1 rounded-full shadow-sm border border-amber-100">
                                    + Extras
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-between w-full">
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-stone-400 font-bold mb-1">
                                    {{ $product->category->name ?? 'Sin categoría' }}
                                </p>

                                <h3 class="product-name font-bold text-stone-800 text-xs sm:text-sm leading-tight mb-1.5 sm:mb-2 group-hover:text-amber-800 transition">
                                    {{ $product->name }}
                                </h3>
                            </div>

                            <div class="flex justify-between items-end gap-2 mt-2">
                                <span class="text-amber-700 font-black text-base sm:text-lg">
                                    ${{ number_format($product->price, 2) }}
                                </span>

                                <span class="stock-badge text-[10px] sm:text-xs font-bold {{ $product->calculated_stock > 0 ? 'text-stone-500 bg-stone-100' : 'text-red-500 bg-red-50' }} px-2 py-1 rounded-full whitespace-nowrap">
                                    {{ $product->calculated_stock }} disp.
                                </span>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SECCIÓN DERECHA: EL TICKET / CARRITO --}}
    <div id="pos-ticket-panel" class="pos-ticket-panel w-full h-full min-h-0 bg-white rounded-2xl shadow-lg border border-stone-200 flex flex-col overflow-hidden relative z-20">

        {{-- HEADER TICKET --}}
        <div class="flex-none bg-stone-50 px-3 py-2.5 border-b border-stone-200 rounded-t-2xl">
            <div class="flex justify-between items-center gap-2">
                <div class="min-w-0 flex-1">
                    <h2 class="font-bold text-stone-800 flex items-center gap-2 text-sm leading-tight">
                        🧾 Ticket de venta
                    </h2>

                    <p class="text-[11px] text-stone-400 mt-0.5">
                        <span id="cart-count">0</span> producto(s) en carrito
                    </p>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button"
                            onclick="closeMobileTicket()"
                            class="lg:hidden w-8 h-8 rounded-full bg-white text-stone-400 hover:text-amber-800 hover:bg-amber-50 border border-stone-200 flex items-center justify-center transition"
                            title="Cerrar ticket">
                        ↓
                    </button>
                    <button type="button"
                            id="vip-customer-btn"
                            onclick="openVipCustomerModal()"
                            class="h-9 min-w-9 max-w-[145px] rounded-full bg-white border border-amber-200 text-amber-700 hover:bg-amber-100 hover:text-amber-900 font-black flex items-center justify-center gap-1.5 px-2 transition shadow-sm overflow-hidden"
                            title="Cliente VIP">
                        <span class="shrink-0">👑</span>

                        <span id="vip-mini-info" class="hidden text-left leading-tight min-w-0">
                            <span id="vip-mini-name" class="block text-[10px] font-black truncate max-w-[92px]"></span>
                            <span id="vip-mini-points" class="block text-[9px] font-bold text-amber-700 truncate">0 puntos</span>
                        </span>
                    </button>

                    <button type="button"
                            id="vip-remove-btn"
                            onclick="removeVipCustomer()"
                            class="hidden w-8 h-8 rounded-full bg-white text-stone-400 hover:text-red-500 hover:bg-red-50 border border-stone-200 flex items-center justify-center transition"
                            title="Quitar cliente VIP">
                        ✕
                    </button>

                    <button onclick="clearCart()"
                            class="text-[11px] text-red-500 hover:text-red-700 font-bold transition flex items-center gap-1 bg-red-50 hover:bg-red-100 px-2.5 py-2 rounded-lg">
                        🗑 Vaciar
                    </button>
                </div>
            </div>
        </div>

        {{-- LISTA DEL CARRITO --}}
        <div class="flex-1 min-h-[190px] bg-stone-50/40 border-b border-stone-100 relative overflow-hidden">

            <div id="empty-cart-msg"
                class="absolute inset-0 flex flex-col items-center justify-center text-stone-400 text-xs italic text-center px-4">
                <span class="text-4xl mb-2">🛒</span>
                <span class="font-bold text-stone-500 not-italic">
                    El ticket está vacío
                </span>
                <span class="mt-1 text-xs">
                    Selecciona productos del catálogo para comenzar.
                </span>
            </div>

            <div id="cart-items-container"
                class="h-full overflow-y-auto p-2.5 custom-scrollbar space-y-2 relative z-10">
            </div>
        </div>

        {{-- ACCIONES DE COBRO --}}
        <div class="flex-none p-2 space-y-1.5">

            {{-- TOTAL --}}
            <div class="bg-stone-900 rounded-xl px-4 py-2 text-white shadow-md">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-amber-200 font-bold">
                            Total a pagar
                        </p>

                        <p class="text-[10px] text-stone-300 mt-0.5">
                            Importe final.
                        </p>
                    </div>

                    <span class="text-xl font-black text-amber-300" id="cart-total">
                        $0.00
                    </span>
                </div>
            </div>

            {{-- MÉTODOS DE PAGO --}}
            <div>
                <label class="text-[10px] font-black text-stone-500 mb-1.5 block uppercase tracking-wide">
                    Método de pago
                </label>

                <div class="grid grid-cols-3 gap-2">
                    <button onclick="setPaymentMethod('efectivo')"
                            id="btn-efectivo"
                            class="payment-btn bg-amber-100 border-amber-500 text-amber-800 border py-1 rounded-xl text-[10px] font-black transition flex flex-col items-center gap-0.5">
                        <span>💵</span>
                        <span>Efectivo</span>
                    </button>

                    <button onclick="setPaymentMethod('tarjeta')"
                            id="btn-tarjeta"
                            class="payment-btn bg-stone-50 border-stone-200 text-stone-500 border py-1 rounded-xl text-[10px] font-black transition flex flex-col items-center gap-0.5">
                        <span>💳</span>
                        <span>Tarjeta</span>
                    </button>

                    <button onclick="setPaymentMethod('puntos')"
                            id="btn-puntos"
                            class="payment-btn bg-stone-50 border-stone-200 text-stone-300 border py-1 rounded-xl text-[10px] font-black transition flex flex-col items-center gap-0.5 cursor-not-allowed"
                            disabled
                            title="Selecciona un cliente con saldo suficiente">
                        <span>🎁</span>
                        <span>Puntos</span>
                    </button>
                </div>
            </div>

            {{-- EFECTIVO Y CAMBIO --}}
            <div id="cash-payment-section" class="bg-stone-50 p-2 rounded-xl border border-stone-200">
                <div class="space-y-2">
                    <div>
                        <label class="text-[10px] font-black text-stone-600 uppercase tracking-wide">
                            Efectivo recibido
                        </label>

                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-500 font-black">
                                $
                            </span>

                            <input type="number"
                                id="cash-received"
                                oninput="calculateChange()"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full pl-8 border-stone-300 rounded-xl text-sm font-bold focus:border-amber-500 focus:ring-amber-200 py-1.5 bg-white">
                        </div>
                    </div>

                    <div class="flex justify-between items-center bg-white rounded-xl border border-stone-100 px-3 py-1.5">
                        <span class="text-xs font-bold text-stone-600">
                            Cambio
                        </span>

                        <span id="change-amount" class="text-base font-black text-stone-500">
                            $0.00
                        </span>
                    </div>
                </div>
            </div>

            {{-- NOTA COMPACTA --}}
            <div class="hidden xl:block bg-blue-50 border border-blue-100 rounded-xl px-3 py-1.5">
                <p class="text-[10px] text-blue-800 leading-snug">
                    <span class="font-black">Nota:</span>
                    en efectivo, el botón se activa al cubrir el total.
                </p>
            </div>
        </div>

        {{-- BOTÓN COBRAR FIJO ABAJO --}}
        <div class="flex-none bg-white border-t border-stone-200 p-2 rounded-b-2xl">
            <button id="checkout-btn"
                    onclick="processCheckout()"
                    disabled
                    class="w-full bg-amber-800 hover:bg-amber-900 text-white font-black text-sm py-2 rounded-xl shadow-md transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-amber-800">
                <span>🧾</span>
                <span>Cobrar ticket</span>
            </button>
        </div>
    </div>
</div>

{{-- BARRA MÓVIL DEL TICKET --}}
<div id="mobile-ticket-bar"
     class="lg:hidden fixed left-3 right-3 bottom-3 z-40 bg-stone-900 text-white rounded-2xl shadow-2xl border border-stone-700 overflow-hidden">
    <button type="button"
            onclick="openMobileTicket()"
            class="w-full px-4 py-3 flex items-center justify-between gap-3">
        <div class="text-left">
            <p class="text-[10px] uppercase tracking-widest text-amber-200 font-black">
                Ticket
            </p>

            <p class="text-xs text-stone-300">
                <span id="mobile-cart-count">0</span> producto(s)
            </p>
        </div>

        <div class="text-right">
            <p class="text-[10px] text-stone-300">
                Total
            </p>

            <p id="mobile-cart-total" class="text-xl font-black text-amber-300">
                $0.00
            </p>
        </div>
    </button>
</div>

{{-- FONDO OSCURO DEL TICKET MÓVIL --}}
<div id="mobile-ticket-overlay"
     onclick="closeMobileTicket()"
     class="lg:hidden fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-40 hidden">
</div>

{{-- MODAL DE PRODUCTO --}}
<div id="product-modal"
     class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 opacity-0 transition-opacity duration-300">

    <div id="product-modal-content"
         class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col">

        {{-- ENCABEZADO --}}
        <div class="flex-none bg-amber-50 border-b border-amber-100 px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-amber-100 text-amber-800 text-[10px] font-black mb-2">
                    ☕ Producto seleccionado
                </div>

                <h3 class="font-serif font-black text-xl text-stone-900 leading-tight" id="modal-product-name">
                    Nombre del producto
                </h3>

                <p class="text-xs text-stone-500 mt-1">
                    Configura cantidad, extras y revisa el subtotal antes de agregar.
                </p>
            </div>

            <button onclick="closeProductModal()"
                    class="w-9 h-9 rounded-full bg-white text-stone-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition shrink-0"
                    title="Cerrar">
                ✕
            </button>
        </div>

        {{-- CONTENIDO --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-5">

            {{-- IMAGEN Y PRECIO --}}
            <div class="grid grid-cols-1 sm:grid-cols-[150px_1fr] gap-4 items-stretch">

                <div class="h-40 sm:h-full bg-stone-50 rounded-2xl overflow-hidden flex items-center justify-center border border-stone-100">
                    <img id="modal-product-image"
                        src=""
                        alt="Producto"
                        class="w-full h-full object-contain object-center p-3 hidden">

                    <div id="modal-product-placeholder"
                        class="flex flex-col items-center justify-center text-stone-300">
                        <span class="text-5xl">☕</span>
                        <span class="text-xs font-bold mt-2">Sin imagen</span>
                    </div>
                </div>

                <div class="bg-stone-50 border border-stone-100 rounded-2xl p-4 flex flex-col justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-black text-stone-400">
                            Precio base
                        </p>

                        <p class="text-3xl font-black text-amber-700 mt-1" id="modal-product-price">
                            $0.00
                        </p>

                        <p class="text-xs text-stone-500 mt-2" id="modal-unit-detail">
                            Sin extras seleccionados.
                        </p>
                    </div>

                    <div class="mt-4">
                        <label class="text-xs font-black text-stone-600 mb-2 block uppercase tracking-wide">
                            Cantidad
                        </label>

                        <div class="inline-flex items-center gap-3 bg-white border border-stone-200 rounded-2xl p-2">
                            <button onclick="changeModalQuantity(-1)"
                                    class="w-9 h-9 rounded-xl bg-stone-100 text-stone-700 font-black hover:bg-stone-200 transition">
                                −
                            </button>

                            <span id="modal-quantity"
                                  class="text-xl font-black text-stone-900 w-10 text-center">
                                1
                            </span>

                            <button onclick="changeModalQuantity(1)"
                                    class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 font-black hover:bg-amber-200 transition">
                                +
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXTRAS --}}
            <div id="modal-extras-container" class="hidden">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <label class="text-sm font-black text-stone-800 block">
                            Extras disponibles
                        </label>

                        <p class="text-xs text-stone-400">
                            Selecciona complementos para este producto.
                        </p>
                    </div>

                    <span id="modal-extras-count"
                          class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black">
                        0 extra(s)
                    </span>
                </div>

                <div id="modal-extras-list"
                     class="space-y-2 max-h-44 overflow-y-auto custom-scrollbar pr-1">
                </div>
            </div>

            {{-- SIN EXTRAS --}}
            <div id="modal-no-extras"
                 class="hidden bg-stone-50 border border-stone-100 rounded-2xl p-4 text-sm text-stone-500">
                <span class="font-bold text-stone-700">Sin extras disponibles.</span>
                Este producto se agregará únicamente con su precio base.
            </div>
        </div>

        {{-- RESUMEN Y BOTONES --}}
        <div class="flex-none border-t border-stone-100 bg-white p-4">
            <div class="bg-stone-900 text-white rounded-2xl px-4 py-3 mb-3 flex items-center justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-amber-200 font-black">
                        Subtotal
                    </p>

                    <p class="text-[10px] text-stone-300 mt-0.5">
                        Producto + extras × cantidad
                    </p>
                </div>

                <span id="modal-subtotal"
                      class="text-2xl font-black text-amber-300">
                    $0.00
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="closeProductModal()"
                        class="py-3 text-stone-600 font-black rounded-2xl hover:bg-stone-100 border border-stone-200 transition">
                    Cancelar
                </button>

                <button onclick="addToCartFromModal()"
                        id="btn-add-modal"
                        class="py-3 bg-amber-800 text-white font-black rounded-2xl hover:bg-amber-900 transition shadow-md flex items-center justify-center gap-2">
                    <span>🛒</span>
                    <span>Agregar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const productsDB = {!! json_encode($products->map(function($p) {
        $arr = $p->toArray(); $arr['calculated_stock'] = $p->calculated_stock; return $arr;
    })) !!};
    const ingredientsDB = {!! isset($ingredients) ? json_encode($ingredients) : '[]' !!};

    let customersDB = {!! isset($customers) ? json_encode($customers) : '[]' !!};
    const canCreateVipCustomer = @json(Auth::user()->role !== 'empleado');
    let currentSelectedCustomer = null;
    
    let cart = []; 
    let currentSelectedProduct = null; 
    let editingCartItemId = null; 
    let currentPaymentMethod = 'efectivo'; 
    let globalCartTotal = 0;
    let currentCategoryFilter = 'all';

    function openMobileTicket() {
        const panel = document.getElementById('pos-ticket-panel');
        const overlay = document.getElementById('mobile-ticket-overlay');

        if (!panel || !overlay) {
            return;
        }

        panel.classList.add('mobile-ticket-open');
        overlay.classList.remove('hidden');
    }

    function closeMobileTicket() {
        const panel = document.getElementById('pos-ticket-panel');
        const overlay = document.getElementById('mobile-ticket-overlay');

        if (!panel || !overlay) {
            return;
        }

        panel.classList.remove('mobile-ticket-open');
        overlay.classList.add('hidden');
    }

    // --- Calculadora de Stock Compartido ---
    function getAvailableStock(product, ignoreCartItemId = null) {
        if (!product.use_dynamic_stock) {
            let inCart = cart.filter(item => item.product.id === product.id && item.cartItemId !== ignoreCartItemId)
                             .reduce((sum, item) => sum + item.quantity, 0);
            return Math.max(0, product.stock - inCart);
        }

        let usedIngredients = {};
        cart.forEach(item => {
            if (ignoreCartItemId && item.cartItemId === ignoreCartItemId) return;
            if (item.product.use_dynamic_stock && item.product.ingredients) {
                item.product.ingredients.forEach(ing => {
                    usedIngredients[ing.id] = (usedIngredients[ing.id] || 0) + (parseFloat(ing.pivot.quantity) * item.quantity);
                });
            }
            if (item.extras) {
                item.extras.forEach(cartExtra => {
                    let fullExtra = item.product.extras.find(e => e.id == cartExtra.id);
                    if (fullExtra && fullExtra.ingredients) {
                        fullExtra.ingredients.forEach(ing => {
                            usedIngredients[ing.id] = (usedIngredients[ing.id] || 0) + (parseFloat(ing.pivot.quantity) * item.quantity);
                        });
                    }
                });
            }
        });

        let maxPossible = Infinity;
        if (product.ingredients && product.ingredients.length > 0) {
            product.ingredients.forEach(ing => {
                let dbIng = ingredientsDB.find(i => i.id === ing.id);
                if (dbIng) {
                    let available = parseFloat(dbIng.current_quantity) - (usedIngredients[ing.id] || 0);
                    let needed = parseFloat(ing.pivot.quantity);
                    let possible = Math.floor(available / needed);
                    if (possible < maxPossible) maxPossible = possible;
                }
            });
        } else { maxPossible = 0; }
        return maxPossible === Infinity ? 0 : Math.max(0, maxPossible);
    }

    function updateGridStock() {
        document.querySelectorAll('.product-card').forEach(card => {
            let productId = parseInt(card.dataset.id);
            let product = productsDB.find(p => p.id === productId);
            if (product) {
                let available = getAvailableStock(product);
                let badge = card.querySelector('.stock-badge');
                badge.innerText = `${available} disp.`;
                if (available > 0) {
                    badge.className = 'stock-badge text-xs font-bold text-stone-500 bg-stone-100 px-2 py-1 rounded-full whitespace-nowrap';
                    card.classList.remove('opacity-50', 'cursor-not-allowed');
                    card.disabled = false;
                } else {
                    badge.className = 'stock-badge text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded-full whitespace-nowrap';
                    card.classList.add('opacity-50', 'cursor-not-allowed');
                    card.disabled = true;
                }
            }
        });
    }

    // --- LÓGICA DEL CARRITO Y MODALES ---
    function applyProductFilters() {
        const search = document.getElementById('posSearch').value.toLowerCase().trim();

        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.querySelector('.product-name').innerText.toLowerCase();
            const matchesSearch = name.includes(search);
            const matchesCategory = currentCategoryFilter === 'all' || card.dataset.category == currentCategoryFilter;

            card.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
        });
    }

    function filterProducts() {
        applyProductFilters();
    }

    function filterByCategory(categoryId, event) {
        currentCategoryFilter = categoryId;

        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.classList.remove('bg-amber-800', 'text-white', 'active-cat');
            btn.classList.add('bg-white', 'text-stone-600');
        });

        event.currentTarget.classList.remove('bg-white', 'text-stone-600');
        event.currentTarget.classList.add('bg-amber-800', 'text-white', 'active-cat');

        applyProductFilters();
    }

    function clearProductSearch() {
        document.getElementById('posSearch').value = '';
        applyProductFilters();
    }

    function openProductModal(productId, editItemId = null) {
        currentSelectedProduct = productsDB.find(p => p.id === productId);
        editingCartItemId = editItemId;

        let available = getAvailableStock(currentSelectedProduct, editingCartItemId);

        if (available <= 0 && !editItemId) {
            Swal.fire('Agotado', 'No hay suficientes ingredientes para preparar esto.', 'error');
            return;
        }

        document.getElementById('modal-product-name').innerText =
            currentSelectedProduct.name + (editItemId ? ' (Editando)' : '');

        document.getElementById('modal-product-price').innerText =
            `$${parseFloat(currentSelectedProduct.price).toFixed(2)}`;

        document.getElementById('modal-quantity').innerText = '1';

        document.getElementById('btn-add-modal').innerHTML = editItemId
            ? '<span>💾</span><span>Guardar</span>'
            : '<span>🛒</span><span>Agregar</span>';

        // Imagen del producto
        const modalImage = document.getElementById('modal-product-image');
        const modalPlaceholder = document.getElementById('modal-product-placeholder');

        if (currentSelectedProduct.image) {
            modalImage.src = `{{ asset('storage') }}/${currentSelectedProduct.image}`;
            modalImage.classList.remove('hidden');
            modalPlaceholder.classList.add('hidden');
        } else {
            modalImage.src = '';
            modalImage.classList.add('hidden');
            modalPlaceholder.classList.remove('hidden');
        }

        const extrasContainer = document.getElementById('modal-extras-container');
        const noExtrasBox = document.getElementById('modal-no-extras');
        const extrasList = document.getElementById('modal-extras-list');

        extrasList.innerHTML = '';

        const activeExtras = currentSelectedProduct.extras
            ? currentSelectedProduct.extras.filter(e => e.pivot.active == 1)
            : [];

        if (activeExtras.length > 0) {
            extrasContainer.classList.remove('hidden');
            noExtrasBox.classList.add('hidden');

            activeExtras.forEach(extra => {
                const price = parseFloat(extra.pivot.price);

                const html = `
                    <label class="flex items-center justify-between gap-3 p-3 border border-stone-200 rounded-2xl cursor-pointer hover:bg-amber-50 hover:border-amber-200 transition extra-option">
                        <div class="flex items-center gap-3 min-w-0">
                            <input type="checkbox"
                                class="extra-checkbox w-5 h-5 text-amber-700 rounded border-stone-300 focus:ring-amber-500 shrink-0"
                                value="${extra.id}"
                                data-name="${extra.name}"
                                data-price="${price}"
                                onchange="updateModalSubtotal()">

                            <div class="min-w-0">
                                <span class="font-bold text-stone-800 text-sm block truncate">
                                    ${extra.name}
                                </span>
                                <span class="text-xs text-stone-400">
                                    Extra opcional
                                </span>
                            </div>
                        </div>

                        <span class="font-black text-amber-700 text-sm whitespace-nowrap">
                            +$${price.toFixed(2)}
                        </span>
                    </label>
                `;

                extrasList.insertAdjacentHTML('beforeend', html);
            });
        } else {
            extrasContainer.classList.add('hidden');
            noExtrasBox.classList.remove('hidden');
        }

        if (editItemId) {
            let itemToEdit = cart.find(i => i.cartItemId === editItemId);

            if (itemToEdit) {
                document.getElementById('modal-quantity').innerText = itemToEdit.quantity;

                itemToEdit.extras.forEach(ext => {
                    let cb = document.querySelector(`.extra-checkbox[value="${ext.id}"]`);
                    if (cb) cb.checked = true;
                });
            }
        }

        updateModalSubtotal();

        const modal = document.getElementById('product-modal');
        const content = document.getElementById('product-modal-content');

        modal.classList.remove('hidden');

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    }

    function updateModalSubtotal() {
        if (!currentSelectedProduct) return;

        const quantity = parseInt(document.getElementById('modal-quantity').innerText) || 1;
        const basePrice = parseFloat(currentSelectedProduct.price) || 0;

        const selectedExtras = Array.from(document.querySelectorAll('.extra-checkbox:checked'));
        const extrasTotal = selectedExtras.reduce((sum, cb) => {
            return sum + (parseFloat(cb.dataset.price) || 0);
        }, 0);

        const subtotal = (basePrice + extrasTotal) * quantity;

        document.getElementById('modal-subtotal').innerText = `$${subtotal.toFixed(2)}`;

        const extrasCount = selectedExtras.length;
        const extrasCountLabel = document.getElementById('modal-extras-count');

        if (extrasCountLabel) {
            extrasCountLabel.innerText = `${extrasCount} extra(s)`;
        }

        const unitDetail = document.getElementById('modal-unit-detail');

        if (unitDetail) {
            if (extrasCount > 0) {
                unitDetail.innerText = `Precio base + $${extrasTotal.toFixed(2)} en extras.`;
            } else {
                unitDetail.innerText = 'Sin extras seleccionados.';
            }
        }
    }


    function closeProductModal() {
        const modal = document.getElementById('product-modal');
        const content = document.getElementById('product-modal-content');
        modal.classList.add('opacity-0'); content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); currentSelectedProduct = null; editingCartItemId = null; }, 300);
    }

    function changeModalQuantity(amount) {
        let span = document.getElementById('modal-quantity');
        let current = parseInt(span.innerText);
        let available = getAvailableStock(currentSelectedProduct, editingCartItemId);

        if (amount > 0 && current >= available) {
            Swal.fire('Stock limitado', `Solo puedes preparar ${available} unidad(es) con tu inventario actual.`, 'warning');
            return;
        }

        if (current + amount > 0) {
            span.innerText = current + amount;
            updateModalSubtotal();
        }
    }

    function addToCartFromModal() {
        const quantity = parseInt(document.getElementById('modal-quantity').innerText);
        const checkboxes = document.querySelectorAll('.extra-checkbox:checked');
        const selectedExtras = Array.from(checkboxes).map(cb => ({ id: cb.value, name: cb.dataset.name, price: parseFloat(cb.dataset.price) }));

        const basePrice = parseFloat(currentSelectedProduct.price);
        const extrasTotal = selectedExtras.reduce((sum, extra) => sum + extra.price, 0);
        const subtotal = (basePrice + extrasTotal) * quantity;

        const newItem = {
            cartItemId: editingCartItemId ? editingCartItemId : Date.now().toString(),
            product: currentSelectedProduct,
            quantity: quantity,
            extras: selectedExtras,
            unitPrice: basePrice,
            subtotal: subtotal
        };

        if (editingCartItemId) {
            let index = cart.findIndex(i => i.cartItemId === editingCartItemId);
            if(index > -1) cart[index] = newItem;
        } else {
            cart.push(newItem);
        }

        closeProductModal();
        renderCart();

        if (window.innerWidth < 1024) {
            setTimeout(() => {
                openMobileTicket();
            }, 250);
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('empty-cart-msg');
        globalCartTotal = 0; 
        container.innerHTML = '';

        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.innerText = totalItems;
        }

        const mobileCartCount = document.getElementById('mobile-cart-count');
        if (mobileCartCount) {
            mobileCartCount.innerText = totalItems;
        }

        if (cart.length === 0) {
            emptyMsg.classList.remove('hidden');
            emptyMsg.style.display = 'flex';
            document.getElementById('cart-total').innerText = '$0.00';
            const mobileTotalEmpty = document.getElementById('mobile-cart-total');
            if (mobileTotalEmpty) {
                mobileTotalEmpty.innerText = '$0.00';
            }
        } else {
            emptyMsg.classList.add('hidden');
            emptyMsg.style.display = 'none';
            cart.forEach(item => {
                globalCartTotal += item.subtotal;
                let extrasHtml = item.extras.length > 0 ? `<p class="text-xs text-stone-400 mt-0.5">+ ${item.extras.map(e => e.name).join(', ')}</p>` : '';
                const html = `
                    <div class="bg-white p-2.5 rounded-xl shadow-sm border border-stone-100 flex gap-2 animate-fade-in-down z-10">
                        <div class="flex-1">
                            <h4 class="font-bold text-xs text-stone-800 leading-tight">${item.product.name}</h4>
                            ${extrasHtml}
                            <div class="font-black text-amber-700 text-xs mt-1">$${item.subtotal.toFixed(2)}</div>
                        </div>
                        <div class="flex flex-col items-end justify-between">
                            <div class="flex gap-2">
                                <button onclick="openProductModal(${item.product.id}, '${item.cartItemId}')" class="text-amber-500 hover:text-amber-700" title="Editar Extras"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                <button onclick="removeFromCart('${item.cartItemId}')" class="text-stone-300 hover:text-red-500" title="Eliminar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </div>
                            <div class="bg-stone-100 rounded-lg px-2 py-1 flex items-center gap-2 mt-2"><span class="text-xs font-bold text-stone-600">x${item.quantity}</span></div>
                        </div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', html);
            });
            document.getElementById('cart-total').innerText = `$${globalCartTotal.toFixed(2)}`;
            const mobileTotal = document.getElementById('mobile-cart-total');
            if (mobileTotal) {
                mobileTotal.innerText = `$${globalCartTotal.toFixed(2)}`;
            }
        }
        calculateChange();
    evaluatePointsPayment();
    updateGridStock(); 
    }

    function removeFromCart(cartItemId) { cart = cart.filter(item => item.cartItemId !== cartItemId); renderCart(); }
    
    function clearCart() { 
        if(cart.length > 0) { 
            Swal.fire({title: '¿Vaciar ticket?', showCancelButton: true, confirmButtonText: 'Sí, vaciar', confirmButtonColor: '#dc2626'})
            .then((r) => { 
                if(r.isConfirmed) { cart = []; renderCart(); document.getElementById('cash-received').value = ''; }
            }); 
        } 
    }

    function setPaymentMethod(method) {
        currentPaymentMethod = method;
        document.querySelectorAll('.payment-btn').forEach(btn => { btn.classList.remove('bg-amber-100', 'border-amber-500', 'text-amber-800'); btn.classList.add('bg-stone-50', 'border-stone-200', 'text-stone-500'); });
        const activeBtn = document.getElementById(`btn-${method}`);
        activeBtn.classList.remove('bg-stone-50', 'border-stone-200', 'text-stone-500'); activeBtn.classList.add('bg-amber-100', 'border-amber-500', 'text-amber-800');
        const cashSection = document.getElementById('cash-payment-section');
        if (method === 'efectivo') { cashSection.style.display = 'block'; } else { cashSection.style.display = 'none'; document.getElementById('cash-received').value = ''; }
        calculateChange();
    }

    function calculateChange() {
        const checkoutBtn = document.getElementById('checkout-btn');
        const changeLabel = document.getElementById('change-amount');
        const cashInput = document.getElementById('cash-received');

        if (cart.length === 0) {checkoutBtn.disabled = true;changeLabel.innerText = '$0.00';changeLabel.className = 'text-base font-black text-stone-500';return;}
        if (currentPaymentMethod === 'efectivo') {
            const received = parseFloat(cashInput.value) || 0;
            const change = received - globalCartTotal;
            if (change >= 0 && received > 0) {
                changeLabel.innerText = `$${change.toFixed(2)}`;
                changeLabel.className = 'text-base font-black text-green-600';
                checkoutBtn.disabled = false;
            } else {
                changeLabel.innerText = 'Falta dinero';
                changeLabel.className = 'text-xs font-bold text-red-500';
                checkoutBtn.disabled = true;
            }
        } else { checkoutBtn.disabled = false; }
    }

    async function processCheckout() {
        if (cart.length === 0) return;
        
        const btn = document.getElementById('checkout-btn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Cobrando...</span>';

        let exactReceived = globalCartTotal, exactChange = 0;
        if (currentPaymentMethod === 'efectivo') {
            const cashInput = parseFloat(document.getElementById('cash-received').value);
            if (!isNaN(cashInput) && cashInput >= globalCartTotal) { exactReceived = cashInput; exactChange = cashInput - globalCartTotal; }
        }

        const payload = {
            payment_method: currentPaymentMethod,
            total: globalCartTotal,
            customer_id: currentSelectedCustomer ? currentSelectedCustomer.id : null,
            cash_received: currentPaymentMethod === 'efectivo' ? exactReceived : null,
            cash_change: currentPaymentMethod === 'efectivo' ? exactChange : 0,
            items: cart.map(item => ({
                product_id: item.product.id,
                quantity: item.quantity,
                unit_price: item.unitPrice,
                extras: item.extras
            }))
        };

        try {
            const response = await fetch('{{ route("pos.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                window.open(
                    `/pos/ticket/${data.order_id}`,
                    'Ticket',
                    'width=420,height=650'
                );
                setTimeout(() => { cart = []; document.getElementById('cash-received').value = ''; window.location.reload(); }, 1000);
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.innerHTML = '<span>🧾</span><span>Cobrar ticket</span>';
                btn.disabled = false;
            }
        } catch (error) {
            Swal.fire('Error', 'Ocurrió un error en el cobro.', 'error');
            btn.innerHTML = '<span>🧾</span><span>Cobrar ticket</span>';
            btn.disabled = false;
        }
    }

    // --- LÓGICA DE CLIENTES VIP ---
    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function openVipCustomerModal() {
        Swal.fire({
            title: '👑 Cliente VIP',
            html: `
                <div class="text-left space-y-3">
                    <p class="text-sm text-stone-500">
                        Busca un cliente registrado para acumular o pagar con puntos.
                    </p>

                    <div class="flex gap-2">
                        <input id="swal-vip-search"
                            class="swal2-input !mx-0 !my-0 !w-full"
                            placeholder="Buscar por nombre o teléfono..."
                            autocomplete="off">

                        ${canCreateVipCustomer ? `
                            <button type="button"
                                    id="swal-new-vip-btn"
                                    class="px-3 rounded-lg bg-amber-800 text-white text-xs font-black hover:bg-amber-900 transition whitespace-nowrap">
                                + Nuevo
                            </button>
                        ` : ''}
                    </div>

                    <div id="swal-vip-results"
                        class="border border-stone-200 rounded-xl max-h-64 overflow-y-auto divide-y divide-stone-100">
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 560,
            didOpen: () => {
                const input = document.getElementById('swal-vip-search');
                const results = document.getElementById('swal-vip-results');
                const newButton = document.getElementById('swal-new-vip-btn');

                const renderResults = () => {
                    const query = input.value.toLowerCase().trim();

                    const filtered = customersDB
                        .filter(customer => {
                            const name = (customer.name || '').toLowerCase();
                            const phone = (customer.phone || '').toString();

                            if (query.length === 0) {
                                return true;
                            }

                            return name.includes(query) || phone.includes(query);
                        })
                        .slice(0, 8);

                    if (filtered.length === 0) {
                        results.innerHTML = `
                            <div class="p-4 text-center text-stone-400 text-sm">
                                No se encontraron clientes.
                            </div>
                        `;
                        return;
                    }

                    results.innerHTML = filtered.map(customer => `
                        <button type="button"
                                class="vip-result-btn w-full text-left p-3 hover:bg-amber-50 transition flex items-center justify-between gap-3"
                                data-customer-id="${customer.id}">
                            <div class="min-w-0">
                                <p class="font-black text-stone-800 text-sm truncate">
                                    ${escapeHtml(customer.name)}
                                </p>

                                <p class="text-xs text-stone-400">
                                    ${escapeHtml(customer.phone || 'Sin teléfono')}
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-xs font-black text-amber-700">
                                    ${Number(customer.points || 0).toLocaleString('es-MX')} puntos
                                </p>

                                <p class="text-[10px] text-stone-400">
                                    Seleccionar
                                </p>
                            </div>
                        </button>
                    `).join('');

                    document.querySelectorAll('.vip-result-btn').forEach(button => {
                        button.addEventListener('click', () => {
                            const customerId = parseInt(button.dataset.customerId);
                            const customer = customersDB.find(item => item.id === customerId);

                            if (customer) {
                                selectVipCustomer(customer);
                                Swal.close();
                            }
                        });
                    });
                };

                input.addEventListener('input', renderResults);

                if (newButton) {
                    newButton.addEventListener('click', () => {
                        Swal.close();

                        setTimeout(() => {
                            createNewCustomer();
                        }, 150);
                    });
                }

                renderResults();

                setTimeout(() => {
                    input.focus();
                }, 100);
            }
        });
    }

    function selectVipCustomer(customer) {
        currentSelectedCustomer = customer;

        document.getElementById('vip-mini-name').innerText = customer.name;
        document.getElementById('vip-mini-points').innerText = Number(customer.points || 0).toLocaleString('es-MX') + ' puntos';

        document.getElementById('vip-mini-info').classList.remove('hidden');
        document.getElementById('vip-remove-btn').classList.remove('hidden');

        const vipButton = document.getElementById('vip-customer-btn');

        vipButton.classList.remove('bg-white', 'text-amber-700');
        vipButton.classList.add('bg-amber-100', 'text-amber-900', 'border-amber-400');

        evaluatePointsPayment();
    }

    function removeVipCustomer() {
        currentSelectedCustomer = null;

        document.getElementById('vip-mini-name').innerText = '';
        document.getElementById('vip-mini-points').innerText = '0 puntos';

        document.getElementById('vip-mini-info').classList.add('hidden');
        document.getElementById('vip-remove-btn').classList.add('hidden');

        const vipButton = document.getElementById('vip-customer-btn');

        vipButton.classList.add('bg-white', 'text-amber-700');
        vipButton.classList.remove('bg-amber-100', 'text-amber-900', 'border-amber-400');

        if (currentPaymentMethod === 'puntos') {
            setPaymentMethod('efectivo');
        }

        evaluatePointsPayment();
    }

    function evaluatePointsPayment() {
        const btnPuntos = document.getElementById('btn-puntos');

        if (currentSelectedCustomer && parseFloat(currentSelectedCustomer.points) >= globalCartTotal && globalCartTotal > 0) {
            btnPuntos.disabled = false;
            btnPuntos.classList.remove('text-stone-300', 'cursor-not-allowed');
            btnPuntos.classList.add('text-amber-700', 'hover:border-amber-400');
            btnPuntos.title = "Pagar orden completa con Puntos Ignoto";
        } else {
            btnPuntos.disabled = true;
            btnPuntos.classList.add('text-stone-300', 'cursor-not-allowed');
            btnPuntos.classList.remove('text-amber-700', 'hover:border-amber-400');
            btnPuntos.title = "Saldo insuficiente para cubrir el total";

            if (currentPaymentMethod === 'puntos') {
                setPaymentMethod('efectivo');
            }
        }
    }

    function createNewCustomer() {
        Swal.fire({
            title: '✨ Nuevo Cliente VIP',
            html: `
                <input id="swal-cust-name" class="w-full border-stone-300 rounded-lg mb-3 px-3 py-2 text-sm focus:border-amber-500" placeholder="Nombre completo..." required>
                <input id="swal-cust-phone" type="tel" class="w-full border-stone-300 rounded-lg px-3 py-2 text-sm focus:border-amber-500" placeholder="Teléfono (10 dígitos)..." required>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Registrar VIP',
            confirmButtonColor: '#9a3412',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const name = document.getElementById('swal-cust-name').value.trim();
                const phone = document.getElementById('swal-cust-phone').value.trim();
                if (!name || !phone) {
                    Swal.showValidationMessage('Por favor completa ambos campos');
                }
                return { name, phone };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch('{{ route("pos.customer.store") }}', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json' 
                        },
                        body: JSON.stringify(result.value)
                    });
                    const data = await res.json();
                    if (data.success) {
                        customersDB.push(data.customer);
                        selectVipCustomer(data.customer);
                        Swal.fire({ icon: 'success', title: '¡Registrado!', text: 'Cliente vinculado a la orden actual', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', data.message || 'El teléfono ya está registrado', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                }
            }
        });
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-down { animation: fadeInDown 0.2s ease-out; }
    @media (max-width: 1023px) {
        .pos-screen {
            height: auto !important;
            min-height: calc(100vh - 90px);
            overflow: visible !important;
            padding-bottom: 5.5rem;
        }

        .pos-layout {
            display: block !important;
            height: auto !important;
            min-height: 0 !important;
        }

        .pos-catalog {
            height: auto !important;
            min-height: 0 !important;
        }

        .pos-catalog > .flex-1 {
            overflow: visible !important;
            min-height: 0 !important;
        }

        .pos-ticket-panel {
            position: fixed !important;
            left: 0.75rem;
            right: 0.75rem;
            bottom: 0.75rem;
            width: auto !important;
            height: min(82vh, 690px) !important;
            max-height: 82vh !important;
            transform: translateY(calc(100% + 1rem));
            transition: transform 0.25s ease;
            z-index: 50 !important;
            border-radius: 1.25rem !important;
        }

        .pos-ticket-panel.mobile-ticket-open {
            transform: translateY(0);
        }

        #cart-items-container {
            padding-bottom: 0.75rem;
        }
    }

    @media (max-width: 420px) {
        #products-grid {
            gap: 0.55rem;
        }

        .product-card {
            border-radius: 1rem;
        }

        .pos-ticket-panel {
            left: 0.5rem;
            right: 0.5rem;
            bottom: 0.5rem;
            height: 84vh !important;
            max-height: 84vh !important;
        }
    }
</style>
</div>
@endsection