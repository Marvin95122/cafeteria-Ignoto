@extends('layouts.admin')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="h-[calc(100vh-8rem)] flex flex-col md:flex-row gap-6 -mt-4 relative">
    
    {{-- SECCIÓN IZQUIERDA: CATÁLOGO DE PRODUCTOS --}}
    <div class="w-full md:w-2/3 flex flex-col h-full">
        
        <div class="flex items-center justify-between mb-4 bg-white p-4 rounded-xl shadow-sm border border-stone-100">
            <div>
                <h1 class="font-serif text-2xl font-bold text-amber-900">Caja Registradora</h1>
                <p class="text-xs text-stone-500">Selecciona los productos para cobrar.</p>
            </div>
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" id="posSearch" placeholder="Buscar producto..." onkeyup="filterProducts()"
                       class="w-full pl-10 border-stone-200 rounded-lg text-sm focus:border-amber-500 focus:ring-amber-200 py-2">
            </div>
        </div>

        <div class="flex gap-2 mb-4 overflow-x-auto pb-2 scrollbar-hide" id="category-filters">
            <button onclick="filterByCategory('all')" class="cat-btn active-cat px-4 py-2 bg-amber-800 text-white text-sm font-bold rounded-full shadow-sm whitespace-nowrap transition">
                Todos
            </button>
            @foreach($categories as $category)
                <button onclick="filterByCategory({{ $category->id }})" class="cat-btn px-4 py-2 bg-white text-stone-600 hover:bg-amber-50 text-sm font-medium rounded-full border border-stone-200 shadow-sm whitespace-nowrap transition">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="products-grid">
                @foreach($products as $product)
                    <button onclick="openProductModal({{ $product->id }})" 
                            data-category="{{ $product->category_id }}"
                            class="product-card bg-white p-3 rounded-xl shadow-sm border border-stone-100 hover:border-amber-300 hover:shadow-md transition text-left flex flex-col h-full relative group">
                        
                        {{-- IMAGEN O EMOJI (Corregido) --}}
                        <div class="w-full h-24 bg-stone-100 rounded-lg mb-3 overflow-hidden flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-5xl drop-shadow-sm">☕</span>
                            @endif
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-between">
                            <h3 class="product-name font-bold text-stone-800 text-sm leading-tight mb-1">{{ $product->name }}</h3>
                            <div class="flex justify-between items-end mt-2">
                                <span class="text-amber-700 font-bold">${{ number_format($product->price, 2) }}</span>
                                <span class="text-xs font-bold {{ $product->calculated_stock > 0 ? 'text-stone-500 bg-stone-100' : 'text-red-500 bg-red-50' }} px-1.5 py-0.5 rounded">
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
    <div class="w-full md:w-1/3 bg-white rounded-2xl shadow-lg border border-stone-200 flex flex-col h-full overflow-hidden">
        
        <div class="bg-stone-50 p-4 border-b border-stone-200 flex justify-between items-center">
            <h2 class="font-bold text-stone-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Ticket de Venta
            </h2>
            <button onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-bold transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Vaciar
            </button>
        </div>

        <div class="flex-1 relative bg-stone-50/30 overflow-hidden flex flex-col">
            <div id="empty-cart-msg" class="absolute inset-0 flex flex-col items-center justify-center text-stone-400 text-sm italic">
                El carrito está vacío.<br>Selecciona productos a la izquierda.
            </div>
            <div id="cart-items-container" class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-3 relative z-10"></div>
        </div>

        {{-- Resumen y Cobro --}}
        <div class="p-4 bg-white border-t border-stone-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative z-20">
            
            <div class="flex justify-between font-bold text-2xl text-stone-800 mb-4 pb-4 border-b border-stone-100 border-dashed">
                <span>TOTAL A PAGAR</span>
                <span class="text-amber-700" id="cart-total">$0.00</span>
            </div>

            <div class="mb-4">
                <label class="text-xs font-bold text-stone-500 mb-2 block">Método de Pago</label>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="setPaymentMethod('efectivo')" id="btn-efectivo" class="payment-btn bg-amber-100 border-amber-500 text-amber-800 border py-2 rounded-lg text-sm font-bold transition">💵 Efectivo</button>
                    <button onclick="setPaymentMethod('tarjeta')" id="btn-tarjeta" class="payment-btn bg-stone-50 border-stone-200 text-stone-500 border py-2 rounded-lg text-sm font-bold transition">💳 Tarjeta</button>
                </div>
            </div>

            {{-- Dinámica de Efectivo y Cambio --}}
            <div id="cash-payment-section" class="bg-stone-50 p-3 rounded-lg border border-stone-200 mb-4">
                <div class="flex gap-3 items-center mb-2">
                    <label class="text-xs font-bold text-stone-600 w-1/2">Efectivo Recibido:</label>
                    <div class="relative w-1/2">
                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-stone-500 font-bold">$</span>
                        <input type="number" id="cash-received" oninput="calculateChange()" step="0.01" min="0" placeholder="0.00" 
                               class="w-full pl-6 border-stone-300 rounded text-base font-bold focus:border-amber-500 focus:ring-amber-200">
                    </div>
                </div>
                <div class="flex justify-between items-center text-lg font-bold">
                    <span class="text-stone-600">Cambio:</span>
                    <span id="change-amount" class="text-stone-400">$0.00</span>
                </div>
            </div>
            
            <button id="checkout-btn" onclick="processCheckout()" disabled
                    class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold text-lg py-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                COBRAR TICKET
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
            <button onclick="addToCartFromModal()" class="flex-1 py-3 bg-amber-800 text-white font-bold rounded-xl hover:bg-amber-900 transition shadow-md">
                Agregar al Ticket
            </button>
        </div>
    </div>
</div>

<script>
    // --- MAGIA: Inyectamos el stock calculado al objeto JSON para que JavaScript no se confunda ---
    const productsDB = {!! json_encode($products->map(function($p) {
        $arr = $p->toArray();
        $arr['calculated_stock'] = $p->calculated_stock; // Aseguramos que el stock pase a JS
        return $arr;
    })) !!};

    let cart = []; 
    let currentSelectedProduct = null; 
    let currentPaymentMethod = 'efectivo'; 
    let globalCartTotal = 0;

    function filterProducts() {
        const search = document.getElementById('posSearch').value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.querySelector('.product-name').innerText.toLowerCase();
            card.style.display = name.includes(search) ? 'flex' : 'none';
        });
    }

    function filterByCategory(categoryId) {
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.classList.remove('bg-amber-800', 'text-white', 'active-cat');
            btn.classList.add('bg-white', 'text-stone-600');
        });
        event.currentTarget.classList.remove('bg-white', 'text-stone-600');
        event.currentTarget.classList.add('bg-amber-800', 'text-white', 'active-cat');

        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (categoryId === 'all' || card.dataset.category == categoryId) ? 'flex' : 'none';
        });
    }

    function setPaymentMethod(method) {
        currentPaymentMethod = method;
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.classList.remove('bg-amber-100', 'border-amber-500', 'text-amber-800');
            btn.classList.add('bg-stone-50', 'border-stone-200', 'text-stone-500');
        });
        
        const activeBtn = document.getElementById(`btn-${method}`);
        activeBtn.classList.remove('bg-stone-50', 'border-stone-200', 'text-stone-500');
        activeBtn.classList.add('bg-amber-100', 'border-amber-500', 'text-amber-800');

        const cashSection = document.getElementById('cash-payment-section');
        if (method === 'efectivo') {
            cashSection.style.display = 'block';
        } else {
            cashSection.style.display = 'none';
            document.getElementById('cash-received').value = '';
        }
        calculateChange(); // Revalidar el botón de cobrar
    }

    // --- CÁLCULO DE CAMBIO Y VALIDACIÓN DE BOTÓN ---
    function calculateChange() {
        const checkoutBtn = document.getElementById('checkout-btn');
        const changeLabel = document.getElementById('change-amount');
        const cashInput = document.getElementById('cash-received');

        // Si el carrito está vacío, siempre bloqueado
        if (cart.length === 0) {
            checkoutBtn.disabled = true;
            changeLabel.innerText = '$0.00';
            changeLabel.className = 'text-stone-400';
            return;
        }

        if (currentPaymentMethod === 'efectivo') {
            const received = parseFloat(cashInput.value) || 0;
            const change = received - globalCartTotal;

            if (change >= 0 && received > 0) {
                changeLabel.innerText = `$${change.toFixed(2)}`;
                changeLabel.className = 'text-green-600 font-black text-xl'; // Cambio a favor, en verde
                checkoutBtn.disabled = false;
            } else {
                changeLabel.innerText = 'Falta dinero';
                changeLabel.className = 'text-red-500 text-sm'; // Falta dinero, rojo
                checkoutBtn.disabled = true;
            }
        } else {
            // Si es tarjeta, solo importa que haya algo en el carrito
            checkoutBtn.disabled = cart.length === 0;
        }
    }

    function openProductModal(productId) {
        currentSelectedProduct = productsDB.find(p => p.id === productId);
        
        let inCart = cart.filter(item => item.product.id === currentSelectedProduct.id)
                         .reduce((sum, item) => sum + item.quantity, 0);
                         
        let available = parseInt(currentSelectedProduct.calculated_stock) - inCart;

        if (available <= 0) {
            alert('Stock agotado. No puedes agregar más de este producto al ticket.');
            return; 
        }

        document.getElementById('modal-product-name').innerText = currentSelectedProduct.name;
        document.getElementById('modal-product-price').innerText = `$${parseFloat(currentSelectedProduct.price).toFixed(2)}`;
        document.getElementById('modal-quantity').innerText = '1';

        const extrasContainer = document.getElementById('modal-extras-container');
        const extrasList = document.getElementById('modal-extras-list');
        extrasList.innerHTML = '';
        const activeExtras = currentSelectedProduct.extras ? currentSelectedProduct.extras.filter(e => e.pivot.active == 1) : [];

        if (activeExtras.length > 0) {
            extrasContainer.classList.remove('hidden');
            activeExtras.forEach(extra => {
                const price = parseFloat(extra.pivot.price);
                const html = `
                    <label class="flex items-center justify-between p-3 border border-stone-200 rounded-lg cursor-pointer hover:bg-amber-50 transition">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="extra-checkbox w-5 h-5 text-amber-600 rounded border-stone-300 focus:ring-amber-500" 
                                   value="${extra.id}" data-name="${extra.name}" data-price="${price}">
                            <span class="font-medium text-stone-700">${extra.name}</span>
                        </div>
                        <span class="font-bold text-stone-500">+$${price.toFixed(2)}</span>
                    </label>
                `;
                extrasList.insertAdjacentHTML('beforeend', html);
            });
        } else {
            extrasContainer.classList.add('hidden');
        }

        const modal = document.getElementById('product-modal');
        const content = document.getElementById('product-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
    }

    function closeProductModal() {
        const modal = document.getElementById('product-modal');
        const content = document.getElementById('product-modal-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); currentSelectedProduct = null; }, 300);
    }

    function changeModalQuantity(amount) {
        const span = document.getElementById('modal-quantity');
        let current = parseInt(span.innerText);
        
        let inCart = cart.filter(item => item.product.id === currentSelectedProduct.id)
                         .reduce((sum, item) => sum + item.quantity, 0);
        let available = parseInt(currentSelectedProduct.calculated_stock) - inCart;

        if (amount > 0 && current >= available) {
            alert(`Solo puedes agregar un máximo de ${available} unidad(es) más.`);
            return;
        }
        if (current + amount > 0) { span.innerText = current + amount; }
    }

    function addToCartFromModal() {
        const quantity = parseInt(document.getElementById('modal-quantity').innerText);
        const checkboxes = document.querySelectorAll('.extra-checkbox:checked');
        const selectedExtras = Array.from(checkboxes).map(cb => ({
            id: cb.value, name: cb.dataset.name, price: parseFloat(cb.dataset.price)
        }));

        const basePrice = parseFloat(currentSelectedProduct.price);
        const extrasTotal = selectedExtras.reduce((sum, extra) => sum + extra.price, 0);
        const unitPriceWithExtras = basePrice + extrasTotal;
        const subtotal = unitPriceWithExtras * quantity;

        cart.push({
            cartItemId: Date.now().toString(),
            product: currentSelectedProduct,
            quantity: quantity,
            extras: selectedExtras,
            unitPrice: basePrice,
            subtotal: subtotal
        });

        closeProductModal();
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('empty-cart-msg');
        globalCartTotal = 0;
        container.innerHTML = '';

        if (cart.length === 0) {
            emptyMsg.style.display = 'flex';
            document.getElementById('cart-total').innerText = '$0.00';
            calculateChange();
            return;
        }

        emptyMsg.style.display = 'none';

        cart.forEach(item => {
            globalCartTotal += item.subtotal;
            let extrasHtml = item.extras.length > 0 ? `<p class="text-xs text-stone-400 mt-0.5">+ ${item.extras.map(e => e.name).join(', ')}</p>` : '';
            
            const html = `
                <div class="bg-white p-3 rounded-xl shadow-sm border border-stone-100 flex gap-3 relative animate-fade-in-down z-10">
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-stone-800">${item.product.name}</h4>
                        ${extrasHtml}
                        <div class="font-bold text-amber-700 text-sm mt-1">$${item.subtotal.toFixed(2)}</div>
                    </div>
                    <div class="flex flex-col items-end justify-between">
                        <button onclick="removeFromCart('${item.cartItemId}')" class="text-stone-300 hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <div class="bg-stone-100 rounded-lg px-2 py-1 flex items-center gap-2 mt-2">
                            <span class="text-xs font-bold text-stone-600">x${item.quantity}</span>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('cart-total').innerText = `$${globalCartTotal.toFixed(2)}`;
        calculateChange(); // Recalcula el cambio cada que agregas o quitas algo
    }

    function removeFromCart(cartItemId) { cart = cart.filter(item => item.cartItemId !== cartItemId); renderCart(); }
    function clearCart() { if(cart.length > 0 && confirm('¿Vaciar el ticket?')) { cart = []; renderCart(); document.getElementById('cash-received').value = '';} }

    async function processCheckout() {
        if (cart.length === 0) return;

        const btn = document.getElementById('checkout-btn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cobrando...';

        // 1. CAPTURAR EL EFECTIVO EXACTO ESTRICTAMENTE
        let exactReceived = globalCartTotal;
        let exactChange = 0;

        if (currentPaymentMethod === 'efectivo') {
            const cashInput = parseFloat(document.getElementById('cash-received').value);
            if (!isNaN(cashInput) && cashInput >= globalCartTotal) {
                exactReceived = cashInput;
                exactChange = cashInput - globalCartTotal;
            }
        }

        const payload = {
            payment_method: currentPaymentMethod,
            total: globalCartTotal,
            items: cart.map(item => ({
                product_id: item.product.id,
                quantity: item.quantity,
                unit_price: item.unitPrice,
                extras: item.extras 
            }))
        };

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch('{{ route("pos.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                // 2. ENVIAR LAS VARIABLES EXACTAS A LA URL DEL TICKET
                const ticketUrl = `/pos/ticket/${data.order_id}?received=${exactReceived}&change=${exactChange}`;
                
                window.open(ticketUrl, 'Ticket', 'width=400,height=600');
                
                setTimeout(() => {
                    cart = [];
                    document.getElementById('cash-received').value = '';
                    window.location.reload(); 
                }, 1000);

            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = 'COBRAR TICKET';
                btn.disabled = false;
            }

        } catch (error) {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar el pago.');
            btn.innerHTML = 'COBRAR TICKET';
            btn.disabled = false;
        }
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
@endsection