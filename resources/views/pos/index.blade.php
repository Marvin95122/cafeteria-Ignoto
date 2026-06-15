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

<div class="flex flex-col min-h-[calc(100vh-theme(spacing.16))] bg-stone-100">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="flex flex-col lg:flex-row lg:items-start gap-5 lg:h-[calc(100vh-140px)] pt-4 min-h-0">
    
    {{-- SECCIÓN IZQUIERDA: CATÁLOGO DE PRODUCTOS --}}
    <div class="w-full lg:flex-1 flex flex-col lg:h-full min-h-0">
        
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-5 bg-white p-5 rounded-2xl shadow-sm border border-stone-100">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold mb-2">
                    🧾 Punto de venta activo
                </div>

                <h1 class="font-serif text-2xl md:text-3xl font-bold text-amber-900">
                    Selecciona productos para cobrar
                </h1>

                <p class="text-sm text-stone-500 mt-1">
                    Busca por nombre o filtra por categoría para armar el ticket de venta.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 xl:items-center">
                <div class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-sm text-stone-600">
                    <span class="font-bold text-amber-800">{{ $products->count() }}</span>
                    producto(s) disponible(s)
                </div>

                <div class="relative w-full sm:w-80">
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
                        class="w-full pl-10 pr-10 border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-200 py-3 bg-stone-50">

                    <button type="button"
                            onclick="clearProductSearch()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-red-500 transition"
                            title="Limpiar búsqueda">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-5 bg-white p-5 rounded-2xl shadow-sm border border-stone-100">
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide" id="category-filters">
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

        <div class="lg:flex-1 lg:overflow-y-auto pr-0 lg:pr-2 custom-scrollbar">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" id="products-grid">
                @foreach($products as $product)
                    <button onclick="openProductModal({{ $product->id }})"
                            data-id="{{ $product->id }}"
                            data-category="{{ $product->category_id }}"
                            class="product-card bg-white p-3 rounded-2xl shadow-sm border border-stone-100 hover:border-amber-300 hover:shadow-md transition text-left flex flex-col h-full relative group overflow-hidden">
                        
                        <div class="relative w-full h-28 bg-stone-100 rounded-xl mb-3 overflow-hidden flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <span class="text-5xl drop-shadow-sm">☕</span>
                            @endif

                            @if($product->extras->count() > 0)
                                <span class="absolute top-2 right-2 bg-white/90 text-amber-800 text-[10px] font-black px-2 py-1 rounded-full shadow-sm">
                                    + Extras
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-between w-full">
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-stone-400 font-bold mb-1">
                                    {{ $product->category->name ?? 'Sin categoría' }}
                                </p>

                                <h3 class="product-name font-bold text-stone-800 text-sm leading-tight mb-2 group-hover:text-amber-800 transition">
                                    {{ $product->name }}
                                </h3>
                            </div>

                            <div class="flex justify-between items-end gap-2 mt-2">
                                <span class="text-amber-700 font-black text-lg">
                                    ${{ number_format($product->price, 2) }}
                                </span>

                                <span class="stock-badge text-xs font-bold {{ $product->calculated_stock > 0 ? 'text-stone-500 bg-stone-100' : 'text-red-500 bg-red-50' }} px-2 py-1 rounded-full whitespace-nowrap">
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
    <div class="w-full lg:w-[340px] xl:w-[360px] 2xl:w-[380px] lg:shrink-0 lg:self-start bg-white rounded-2xl shadow-lg border border-stone-200 flex flex-col overflow-hidden lg:sticky lg:top-4 lg:h-[calc(100vh-145px)] lg:max-h-[720px] relative z-20">

        {{-- HEADER TICKET --}}
        <div class="flex-none bg-stone-50 px-4 py-3 border-b border-stone-200 flex justify-between items-center rounded-t-2xl">
            <div>
                <h2 class="font-bold text-stone-800 flex items-center gap-2 text-sm">
                    🧾 Ticket de venta
                </h2>

                <p class="text-[11px] text-stone-400 mt-1">
                    <span id="cart-count">0</span> producto(s) en carrito
                </p>
            </div>

            <button onclick="clearCart()"
                    class="text-[11px] text-red-500 hover:text-red-700 font-bold transition flex items-center gap-1 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg">
                🗑 Vaciar
            </button>
        </div>

        {{-- LISTA DEL CARRITO --}}
        <div class="h-[220px] lg:h-auto lg:flex-1 lg:min-h-0 bg-stone-50/40 border-b border-stone-100 relative overflow-hidden">

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

        {{-- CLIENTE VIP --}}
        <div class="flex-none p-2.5 space-y-2">

            <div class="bg-amber-50/70 border border-amber-100 rounded-xl p-2">
                <div class="flex justify-between items-start gap-2 mb-2">
                    <div>
                        <label class="text-[10px] font-black text-amber-900 flex items-center gap-1 uppercase tracking-wide">
                            👑 Cliente VIP / Lealtad
                        </label>

                        <p class="text-[10px] text-amber-700 mt-0.5 leading-tight hidden xl:block">
                            Acumula o paga con puntos.
                        </p>
                    </div>

                    @if(Auth::user()->role !== 'empleado')
                        <button onclick="createNewCustomer()"
                                class="text-[10px] text-amber-800 hover:text-amber-900 font-black bg-white border border-amber-100 px-2.5 py-1 rounded-lg transition">
                            + Nuevo VIP
                        </button>
                    @endif
                </div>

                <div id="vip-search-box" class="relative">
                    <input type="text"
                        id="vipSearchInput"
                        oninput="searchVipCustomer()"
                        placeholder="Buscar cliente..."
                        autocomplete="off"
                        class="w-full border-amber-200 rounded-xl text-xs pl-3 pr-9 py-2 focus:border-amber-500 focus:ring-amber-200 bg-white">

                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 pointer-events-none">
                        🔍
                    </span>

                    <div id="vip-results"
                        class="absolute left-0 right-0 mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-40 overflow-y-auto hidden z-50 divide-y divide-stone-100 text-xs">
                    </div>
                </div>

                <div id="selected-vip-card"
                    class="hidden bg-white border border-amber-200 rounded-xl p-2.5 flex justify-between items-center mt-2 shadow-sm">
                    <div>
                        <div class="font-black text-stone-800 text-xs flex items-center gap-1">
                            👤 <span id="vip-display-name"></span>
                        </div>

                        <div class="text-[11px] text-amber-800 mt-1">
                            Saldo:
                            <strong id="vip-display-points">0</strong>
                            puntos
                        </div>
                    </div>

                    <button onclick="removeVipCustomer()"
                            class="text-stone-400 hover:text-red-500 font-bold px-2 py-1 text-xs bg-stone-50 hover:bg-red-50 rounded-lg transition">
                        ✖
                    </button>
                </div>
            </div>

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
        <div class="flex-none bg-white border-t border-stone-200 p-2.5 rounded-b-2xl">
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

                <div class="h-36 sm:h-full bg-stone-100 rounded-2xl overflow-hidden flex items-center justify-center border border-stone-100">
                    <img id="modal-product-image"
                         src=""
                         alt="Producto"
                         class="w-full h-full object-cover hidden">

                    <div id="modal-product-placeholder"
                         class="text-5xl text-stone-300">
                        ☕
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
    let currentSelectedCustomer = null;
    
    let cart = []; 
    let currentSelectedProduct = null; 
    let editingCartItemId = null; 
    let currentPaymentMethod = 'efectivo'; 
    let globalCartTotal = 0;
    let currentCategoryFilter = 'all';

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
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('empty-cart-msg');
        globalCartTotal = 0; 
        container.innerHTML = '';

        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.innerText = cart.reduce((sum, item) => sum + item.quantity, 0);
        }

        if (cart.length === 0) {
            emptyMsg.classList.remove('hidden');
            emptyMsg.style.display = 'flex';
            document.getElementById('cart-total').innerText = '$0.00';
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
    function searchVipCustomer() {
        const query = document.getElementById('vipSearchInput').value.toLowerCase().trim();
        const resultsContainer = document.getElementById('vip-results');
        resultsContainer.innerHTML = '';

        if (query.length < 2) {
            resultsContainer.classList.add('hidden');
            return;
        }

        const filtered = customersDB.filter(c => c.phone.includes(query) || c.name.toLowerCase().includes(query)).slice(0, 5);

        if (filtered.length > 0) {
            resultsContainer.classList.remove('hidden');
            filtered.forEach(c => {
                const item = document.createElement('div');
                item.className = 'p-2.5 hover:bg-amber-50 cursor-pointer flex justify-between items-center';
                item.innerHTML = `
                    <span class="font-bold text-stone-800">${c.name}</span>
                    <span class="text-stone-500 font-mono">${c.phone}</span>
                `;
                item.onclick = () => selectVipCustomer(c);
                resultsContainer.appendChild(item);
            });
        } else {
            resultsContainer.classList.add('hidden');
        }
    }

    function selectVipCustomer(customer) {
        currentSelectedCustomer = customer;
        document.getElementById('vip-search-box').classList.add('hidden');
        document.getElementById('vip-results').classList.add('hidden');
        document.getElementById('vipSearchInput').value = '';

        document.getElementById('vip-display-name').innerText = customer.name;
        document.getElementById('vip-display-points').innerText = customer.points;
        document.getElementById('selected-vip-card').classList.remove('hidden');

        evaluatePointsPayment();
    }

    function removeVipCustomer() {
        currentSelectedCustomer = null;
        document.getElementById('selected-vip-card').classList.add('hidden');
        document.getElementById('vip-search-box').classList.remove('hidden');
        if (currentPaymentMethod === 'puntos') setPaymentMethod('efectivo');
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
            if (currentPaymentMethod === 'puntos') setPaymentMethod('efectivo');
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
</style>
</div>
@endsection