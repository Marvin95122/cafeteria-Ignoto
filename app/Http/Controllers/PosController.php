<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index(Request $request)
    {
        // Se revisa que si hay una caja abierta
        $activeRegister = \App\Models\CashRegister::where('status', 'abierta')->exists();

        // Si la caja esta cerrada, podemos saltarnos la busqueda de productos para ahorrar memoria
        if (!$activeRegister) {
            return view('pos.index', ['activeRegister' => false, 'products' => collect(), 'categories' => collect()]);
        }

        $query = Product::with(['category', 'extras.ingredients', 'ingredients'])->where('active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->get();
        $categories = Category::where('active', true)->get();

        return view('pos.index', compact('products', 'categories', 'activeRegister'));
    }

    public function store(Request $request)
    {
        // Validamos que nos envíen la información correcta
        $request->validate([
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array',
        ]);

        try {
            DB::beginTransaction(); // Iniciamos una transacción segura

            // 1. Crear el Ticket General (Order)
            $order = Order::create([
                'user_id' => Auth::id() ?? 1, 
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'status' => 'completado',
            ]);

            // 2. Procesar cada producto del carrito
            foreach ($request->items as $item) {
                $product = Product::with('ingredients')->find($item['product_id']);
                $quantity = $item['quantity'];

                // Calcular subtotal por seguridad desde el backend (Precio base + suma de extras) * cantidad
                $extrasTotal = collect($item['extras'])->sum('price');
                $subtotal = ($product->price + $extrasTotal) * $quantity;

                // Guardar el detalle de la venta
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'extras' => $item['extras'],
                ]);

                // Descontar Inventario del Producto Principal
                if ($product->use_dynamic_stock) {
                    foreach ($product->ingredients as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $quantity;
                        $ingredient->decrement('current_quantity', $needed);
                    }
                } else {
                    $product->decrement('stock', $quantity);
                }

                // Descontar Inventario de los EXTRAS
                if (!empty($item['extras'])) {
                    foreach ($item['extras'] as $extraData) {
                        $extraModel = Extra::with('ingredients')->find($extraData['id']);
                        if ($extraModel) {
                            foreach ($extraModel->ingredients as $extraIng) {
                                $needed = $extraIng->pivot->quantity * $quantity;
                                $extraIng->decrement('current_quantity', $needed);
                            }
                        }
                    }
                }

                // Recalcular el stock del producto si era dinámico (para actualizar la tabla products)
                if ($product->use_dynamic_stock) {
                    $product->refresh();
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }

            DB::commit(); // Todo salió bien, guardamos definitivamente en la BD.

            return response()->json([
                'success' => true, 
                'message' => 'Venta procesada con éxito',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Hubo un error, deshacemos todo para que no descuadre nada.
            return response()->json([
                'success' => false, 
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ticket(Request $request, Order $order)
    {
        // Cargamos la orden con sus detalles y el usuario
        $order->load(['items.product', 'user']);

        $received = (float) $request->query('received', $order->total);
        $change = (float) $request->query('change', 0);

        return view('pos.ticket', compact('order', 'received', 'change'));
    }
}