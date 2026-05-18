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

<div class="flex flex-col lg:flex-row gap-5 lg:h-[calc(100vh-140px)] pt-4 min-h-0">
    
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
    <div class="w-full lg:w-[360px] xl:w-[390px] 2xl:w-[420px] lg:shrink-0 bg-white rounded-2xl shadow-lg border border-stone-200 flex flex-col overflow-hidden lg:sticky lg:top-4 lg:h-[calc(100vh-150px)]">

        {{-- HEADER TICKET --}}
        <div class="flex-none bg-stone-50 px-4 py-3 border-b border-stone-200 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-stone-800 flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    Ticket de venta
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

        {{-- LISTA DEL CARRITO CON SCROLL PROPIO --}}
        <div class="flex-none relative bg-stone-50/30 border-b border-stone-100">
            <div id="empty-cart-msg"
                class="h-[135px] flex flex-col items-center justify-center text-stone-400 text-sm italic text-center px-6">
                <span class="text-4xl mb-2">🛒</span>
                <span class="font-bold text-stone-500 not-italic">
                    El ticket está vacío
                </span>
                <span class="mt-1 text-xs">
                    Selecciona productos del catálogo para comenzar.
                </span>
            </div>

            <div id="cart-items-container"
                class="max-h-[220px] overflow-y-auto p-3 custom-scrollbar space-y-2 relative z-10">
            </div>
        </div>

        {{-- RESUMEN, VIP Y COBRO --}}
        <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar p-3 bg-white space-y-3">

            {{-- CLIENTE VIP --}}
            <div class="bg-amber-50/70 border border-amber-100 rounded-2xl p-3">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <div>
                        <label class="text-[11px] font-black text-amber-900 flex items-center gap-1 uppercase tracking-wide">
                            <span>👑</span> Cliente VIP / Lealtad
                        </label>

                        <p class="text-[10px] text-amber-700 mt-0.5 leading-tight">
                            Vincula un cliente para acumular o pagar con puntos.
                        </p>
                    </div>

                    @if(Auth::user()->role !== 'empleado')
                        <button onclick="createNewCustomer()"
                                class="text-[10px] text-amber-800 hover:text-amber-900 font-black bg-white border border-amber-100 px-2.5 py-1.5 rounded-lg transition leading-tight">
                            + Nuevo VIP
                        </button>
                    @endif
                </div>

                <div id="vip-search-box" class="relative">
                    <input type="text"
                        id="vipSearchInput"
                        oninput="searchVipCustomer()"
                        placeholder="Buscar por teléfono o nombre..."
                        autocomplete="off"
                        class="w-full border-amber-200 rounded-xl text-xs pl-3 pr-9 py-2.5 focus:border-amber-500 focus:ring-amber-200 bg-white">

                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 pointer-events-none">
                        🔍
                    </span>

                    <div id="vip-results"
                        class="absolute left-0 right-0 mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-40 overflow-y-auto hidden z-50 divide-y divide-stone-100 text-xs">
                    </div>
                </div>

                <div id="selected-vip-card"
                    class="hidden bg-white border border-amber-200 rounded-xl p-3 flex justify-between items-center mt-3 shadow-sm">
                    <div>
                        <div class="font-black text-stone-800 text-sm flex items-center gap-1">
                            <span>👤</span>
                            <span id="vip-display-name"></span>
                        </div>

                        <div class="text-xs text-amber-800 mt-1">
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
            <div class="bg-stone-900 rounded-2xl px-4 py-3 text-white shadow-md">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-widest text-amber-200 font-bold">
                            Total a pagar
                        </p>

                        <p class="text-[10px] text-stone-300 mt-0.5">
                            Importe final del ticket.
                        </p>
                    </div>

                    <span class="text-2xl font-black text-amber-300" id="cart-total">
                        $0.00
                    </span>
                </div>
            </div>

            {{-- MÉTODOS DE PAGO --}}
            <div>
                <label class="text-[11px] font-black text-stone-500 mb-2 block uppercase tracking-wide">
                    Método de pago
                </label>

                <div class="grid grid-cols-3 gap-2">
                    <button onclick="setPaymentMethod('efectivo')"
                            id="btn-efectivo"
                            class="payment-btn bg-amber-100 border-amber-500 text-amber-800 border py-2 rounded-xl text-[11px] font-black transition flex flex-col items-center gap-1">
                        <span>💵</span>
                        <span>Efectivo</span>
                    </button>

                    <button onclick="setPaymentMethod('tarjeta')"
                            id="btn-tarjeta"
                            class="payment-btn bg-stone-50 border-stone-200 text-stone-500 border py-2 rounded-xl text-[11px] font-black transition flex flex-col items-center gap-1">
                        <span>💳</span>
                        <span>Tarjeta</span>
                    </button>

                    <button onclick="setPaymentMethod('puntos')"
                            id="btn-puntos"
                            class="payment-btn bg-stone-50 border-stone-200 text-stone-300 border py-2 rounded-xl text-[11px] font-black transition flex flex-col items-center gap-1 cursor-not-allowed"
                            disabled
                            title="Selecciona un cliente con saldo suficiente">
                        <span>🎁</span>
                        <span>Puntos</span>
                    </button>
                </div>
            </div>

            {{-- EFECTIVO Y CAMBIO --}}
            <div id="cash-payment-section" class="bg-stone-50 p-3 rounded-2xl border border-stone-200">
                <div class="space-y-3">
                    <div>
                        <label class="text-[11px] font-black text-stone-600 uppercase tracking-wide">
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
                                class="w-full pl-8 border-stone-300 rounded-xl text-base font-bold focus:border-amber-500 focus:ring-amber-200 py-2.5 bg-white">
                        </div>
                    </div>

                    <div class="flex justify-between items-center bg-white rounded-xl border border-stone-100 px-4 py-2.5">
                        <span class="text-sm font-bold text-stone-600">
                            Cambio
                        </span>

                        <span id="change-amount" class="text-lg font-black text-stone-500">
                            $0.00
                        </span>
                    </div>
                </div>
            </div>

            {{-- AYUDA DE COBRO --}}
            <div class="bg-blue-50 border border-blue-100 rounded-2xl px-3 py-2">
                <p class="text-[10px] text-blue-800 leading-snug">
                    <span class="font-black">Nota:</span>
                    en efectivo, el botón se activa cuando el monto recibido cubre el total.
                </p>
            </div>
        </div>

        {{-- BOTÓN COBRAR FIJO ABAJO --}}
        <div class="flex-none bg-white border-t border-stone-200 p-3 shadow-[0_-4px_10px_rgba(0,0,0,0.04)]">
            <button id="checkout-btn"
                    onclick="processCheckout()"
                    disabled
                    class="w-full bg-amber-800 hover:bg-amber-900 text-white font-black text-base py-3 rounded-2xl shadow-md transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-amber-800">
                <span>🧾</span>
                <span>Cobrar ticket</span>
            </button>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="product-modal" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300" id="product-modal-content">
        <div class="p-5 border-b border-stone-100 flex justify-between items-center bg-amber-50/50">
            <h3 class="font-bold text-xl text-stone-800" id="modal-product-name">Nombre del Producto</h3>
            <button onclick="closeProductModal()" class="text-stone-400 hover:text-red-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-5">
            <p class="text-2xl font-bold text-amber-700 mb-6" id="modal-product-price">$0.00</p>
            <div class="mb-6">
                <label class="text-sm font-bold text-stone-600 mb-2 block">Cantidad</label>
                <div class="flex items-center gap-3">
                    <button onclick="changeModalQuantity(-1)" class="w-10 h-10 rounded-full bg-stone-100 text-stone-600 font-bold hover:bg-stone-200 transition">-</button>
                    <span id="modal-quantity" class="text-xl font-bold text-stone-800 w-8 text-center">1</span>
                    <button onclick="changeModalQuantity(1)" class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold hover:bg-amber-200 transition">+</button>
                </div>
            </div>
            <div id="modal-extras-container" class="mb-2 hidden">
                <label class="text-sm font-bold text-stone-600 mb-2 block">Agregar Extras (Opcional)</label>
                <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar pr-2" id="modal-extras-list"></div>
            </div>
        </div>
        <div class="p-5 border-t border-stone-100 bg-stone-50 flex gap-3">
            <button onclick="closeProductModal()" class="flex-1 py-3 text-stone-600 font-bold rounded-xl hover:bg-stone-200 transition">Cancelar</button>
            <button onclick="addToCartFromModal()" id="btn-add-modal" class="flex-1 py-3 bg-amber-800 text-white font-bold rounded-xl hover:bg-amber-900 transition shadow-md">
                Agregar al Ticket
            </button>
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

        document.getElementById('modal-product-name').innerText = currentSelectedProduct.name + (editItemId ? ' (Editando)' : '');
        document.getElementById('modal-product-price').innerText = `$${parseFloat(currentSelectedProduct.price).toFixed(2)}`;
        document.getElementById('modal-quantity').innerText = '1';
        document.getElementById('btn-add-modal').innerText = editItemId ? 'Guardar Cambios' : 'Agregar al Ticket';

        const extrasContainer = document.getElementById('modal-extras-container');
        const extrasList = document.getElementById('modal-extras-list');
        extrasList.innerHTML = '';
        const activeExtras = currentSelectedProduct.extras ? currentSelectedProduct.extras.filter(e => e.pivot.active == 1) : [];

        if (activeExtras.length > 0) {
            extrasContainer.classList.remove('hidden');
            activeExtras.forEach(extra => {
                const price = parseFloat(extra.pivot.price);
                const html = `
                    <label class="flex items-center justify-between p-3 border border-stone-200 rounded-lg cursor-pointer hover:bg-amber-50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="extra-checkbox w-5 h-5 text-amber-600 rounded border-stone-300" value="${extra.id}" data-name="${extra.name}" data-price="${price}">
                            <span class="font-medium text-stone-700">${extra.name}</span>
                        </div>
                        <span class="font-bold text-stone-500">+$${price.toFixed(2)}</span>
                    </label>`;
                extrasList.insertAdjacentHTML('beforeend', html);
            });
        } else { extrasContainer.classList.add('hidden'); }

        if (editItemId) {
            let itemToEdit = cart.find(i => i.cartItemId === editItemId);
            if(itemToEdit) {
                document.getElementById('modal-quantity').innerText = itemToEdit.quantity;
                itemToEdit.extras.forEach(ext => {
                    let cb = document.querySelector(`.extra-checkbox[value="${ext.id}"]`);
                    if(cb) cb.checked = true;
                });
            }
        }

        const modal = document.getElementById('product-modal');
        const content = document.getElementById('product-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
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
            Swal.fire('Stock Limitado', `Solo puedes preparar ${available} unidad(es) con tu inventario actual.`, 'warning');
            return;
        }
        if (current + amount > 0) { span.innerText = current + amount; }
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

        if (cart.length === 0) {checkoutBtn.disabled = true;changeLabel.innerText = '$0.00';changeLabel.className = 'text-lg font-black text-stone-500';return;}
        if (currentPaymentMethod === 'efectivo') {
            const received = parseFloat(cashInput.value) || 0;
            const change = received - globalCartTotal;
            if (change >= 0 && received > 0) {
                changeLabel.innerText = `$${change.toFixed(2)}`;
                changeLabel.className = 'text-lg font-black text-green-600';
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
            items: cart.map(item => ({ product_id: item.product.id, quantity: item.quantity, unit_price: item.unitPrice, extras: item.extras }))
        };

        try {
            const response = await fetch('{{ route("pos.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                window.open(`/pos/ticket/${data.order_id}?received=${exactReceived}&change=${exactChange}`, 'Ticket', 'width=400,height=600');
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