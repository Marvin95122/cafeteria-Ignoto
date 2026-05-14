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
        $activeRegister = \App\Models\CashRegister::where('status', 'abierta')->exists();

        if (!$activeRegister) {
            return view('pos.index', [
                'activeRegister' => false, 
                'products' => collect(), 
                'categories' => collect(), 
                'ingredients' => collect()
            ]);
        }

        $categories = Category::where('active', true)->get();
        $products = Product::with(['extras.ingredients', 'ingredients'])->where('active', true)->get();
        $ingredients = \App\Models\Ingredient::where('active', true)->get();

        return view('pos.index', compact('categories', 'products', 'ingredients', 'activeRegister'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => Auth::id() ?? 1, 
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'status' => 'completado',
            ]);

            foreach ($request->items as $item) {
                $product = Product::with('ingredients')->find($item['product_id']);
                $quantity = $item['quantity'];

                $extrasTotal = collect($item['extras'])->sum('price');
                $subtotal = ($product->price + $extrasTotal) * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'extras' => $item['extras'],
                ]);

                // --- INSUMOS PRINCIPALES ---
                if ($product->use_dynamic_stock) {
                    foreach ($product->ingredients as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $quantity;
                        $ingredient->decrement('current_quantity', $needed);

                        // Registro exitoso en bitácora
                        \App\Models\InventoryMovement::create([
                            'ingredient_id' => $ingredient->id,
                            'user_id' => Auth::id() ?? 1,
                            'type' => 'venta',
                            'quantity' => $needed,
                            'reason' => "Venta POS | Ticket #{$order->id} ({$product->name})",
                        ]);
                    }
                } else {
                    $product->decrement('stock', $quantity);
                }

                // --- INSUMOS DE LOS EXTRAS ---
                if (!empty($item['extras'])) {
                    foreach ($item['extras'] as $extraData) {
                        $extraModel = Extra::with('ingredients')->find($extraData['id']);
                        if ($extraModel) {
                            foreach ($extraModel->ingredients as $extraIng) {
                                $needed = $extraIng->pivot->quantity * $quantity;
                                $extraIng->decrement('current_quantity', $needed);

                                // Registro exitoso en bitácora para el extra
                                \App\Models\InventoryMovement::create([
                                    'ingredient_id' => $extraIng->id,
                                    'user_id' => Auth::id() ?? 1,
                                    'type' => 'venta',
                                    'quantity' => $needed,
                                    'reason' => "Venta POS (Extra) | Ticket #{$order->id} ({$extraModel->name})",
                                ]);
                            }
                        }
                    }
                }

                if ($product->use_dynamic_stock) {
                    $product->refresh();
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Venta procesada con éxito',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ticket(Request $request, Order $order)
    {
        $order->load(['items.product', 'user']);
        $received = (float) $request->query('received', $order->total);
        $change = (float) $request->query('change', 0);

        return view('pos.ticket', compact('order', 'received', 'change'));
    }
}