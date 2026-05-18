<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Extra;
use App\Models\Customer;
use App\Models\InventoryMovement;
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
                'ingredients' => collect(),
                'customers' => collect()
            ]);
        }

        $categories = Category::where('active', true)->orderBy('name')->get();
        $products = Product::with(['category', 'extras.ingredients', 'ingredients'])->where('active', true)->orderBy('name')->get();
        $ingredients = \App\Models\Ingredient::where('active', true)->get();
        
        // Cargamos todos los clientes VIP para el buscador instantáneo
        $customers = Customer::where('active', true)->latest()->get();

        return view('pos.index', compact('categories', 'products', 'ingredients', 'customers', 'activeRegister'));
    }

    // ALTA RÁPIDA DE CLIENTE VIP DESDE EL POS
    public function storeCustomer(Request $request)
    {
        if (Auth::user()->role === 'empleado') {
            return response()->json(['success' => false, 'message' => 'Acceso denegado. Solicita a un Gerente crear al cliente VIP.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone'
        ]);

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'points' => 0,
                'active' => true,
            ]);

            return response()->json(['success' => true, 'customer' => $customer]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array',
            'customer_id' => 'nullable|exists:customers,id'
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id() ?? 1;
            $total = floatval($request->total);
            $customerId = $request->customer_id;
            $paymentMethod = $request->payment_method;

            // LÓGICA DE PUNTOS VIP
            $customer = $customerId ? Customer::lockForUpdate()->find($customerId) : null;
            $pointsEarned = 0;

            if ($customer && !$customer->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este cliente VIP está dado de baja y no puede usarse en una venta.'
                ], 400);
            }

            if ($customer) {
                // Leer valores configurados por el admin (o usar defecto)
                $moneyForPoint = floatval(\App\Models\Setting::where('key', 'vip_money_for_point')->value('value') ?? 10);
                $pointValue = floatval(\App\Models\Setting::where('key', 'vip_point_value')->value('value') ?? 1);

                if ($paymentMethod === 'puntos') {
                    // Calculamos cuántos puntos equivale el total en dinero
                    $pointsNeeded = intval(ceil($total / $pointValue));
                    
                    if ($customer->points < $pointsNeeded) {
                        return response()->json(['success' => false, 'message' => 'Saldo de puntos insuficiente.'], 400);
                    }
                    $customer->decrement('points', $pointsNeeded);
                } elseif (in_array($paymentMethod, ['efectivo', 'tarjeta'])) {
                    // Dinero gastado / Dinero requerido para 1 punto
                    $pointsEarned = intval(floor($total / $moneyForPoint));
                    if ($pointsEarned > 0) {
                        $customer->increment('points', $pointsEarned);
                    }
                }
            }

            // 1. Crear el Ticket General
            $order = Order::create([
                'user_id' => $userId, 
                'customer_id' => $customerId,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'status' => 'completado',
            ]);

            // 2. Procesar cada producto del carrito
            foreach ($request->items as $item) {
                $product = Product::with('ingredients')->find($item['product_id']);
                $quantity = floatval($item['quantity']);

                $extrasTotal = collect($item['extras'] ?? [])->sum('price');
                $subtotal = ($product->price + $extrasTotal) * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'extras' => $item['extras'] ?? null,
                ]);

                // Descontar Inventario Base y registrar en Bitácora
                if ($product->use_dynamic_stock) {
                    foreach ($product->ingredients as $ingredient) {
                        $needed = floatval($ingredient->pivot->quantity ?? 0) * $quantity;
                        if ($needed > 0) {
                            $ingredient->decrement('current_quantity', $needed);

                            InventoryMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'user_id' => $userId,
                                'type' => 'venta',
                                'quantity' => $needed,
                                'reason' => "Venta POS | Ticket #{$order->id} ({$product->name})",
                            ]);
                        }
                    }
                } else {
                    $product->decrement('stock', $quantity);
                }

                // Descontar Inventario de Extras y registrar en Bitácora
                if (!empty($item['extras'])) {
                    foreach ($item['extras'] as $extraData) {
                        if (isset($extraData['id'])) {
                            $extraModel = Extra::with('ingredients')->find($extraData['id']);
                            if ($extraModel) {
                                foreach ($extraModel->ingredients as $extraIng) {
                                    $needed = floatval($extraIng->pivot->quantity ?? 0) * $quantity;
                                    if ($needed > 0) {
                                        $extraIng->decrement('current_quantity', $needed);

                                        InventoryMovement::create([
                                            'ingredient_id' => $extraIng->id,
                                            'user_id' => $userId,
                                            'type' => 'venta',
                                            'quantity' => $needed,
                                            'reason' => "Venta POS (Extra) | Ticket #{$order->id} ({$extraModel->name})",
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                if ($product->use_dynamic_stock) {
                    $product->refresh();
                    if ($product->calculated_stock !== null) {
                        $product->update(['stock' => $product->calculated_stock]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Venta procesada con éxito',
                'order_id' => $order->id,
                'points_earned' => $pointsEarned
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
        $order->load(['items.product', 'user', 'customer']);
        $received = (float) $request->query('received', $order->total);
        $change = (float) $request->query('change', 0);

        return view('pos.ticket', compact('order', 'received', 'change'));
    }
}