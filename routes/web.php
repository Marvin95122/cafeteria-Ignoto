<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| ACCESO PARA TODOS (Admin, Gerente y Empleado)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Semáforo Inteligente: Si es empleado se va a ventas, si no, al dashboard real
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'empleado') {
            return redirect()->route('pos.index');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Módulo de Ventas (Punto de Venta)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/ticket/{order}', [PosController::class, 'ticket'])->name('pos.ticket');
    Route::post('/pos/customer', [PosController::class, 'storeCustomer'])->name('pos.customer.store');
});

/*
|--------------------------------------------------------------------------
| ACCESO MEDIO (Solo Admin y Gerente)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,gerente'])->group(function () {
    Route::delete('/categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::delete('/products/{product}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::delete('/ingredients/{ingredient}/force-delete', [IngredientController::class, 'forceDelete'])->name('ingredients.force-delete');
    Route::resource('ingredients', IngredientController::class);
    
    // RUTAS DEL CORTE DE CAJA
    Route::get('/caja', [App\Http\Controllers\CashRegisterController::class, 'index'])->name('cash_registers.index');
    Route::post('/caja/abrir', [App\Http\Controllers\CashRegisterController::class, 'open'])->name('cash_registers.open');
    Route::post('/caja/cerrar', [App\Http\Controllers\CashRegisterController::class, 'close'])->name('cash_registers.close');
    Route::post('/caja/gasto', [App\Http\Controllers\CashRegisterController::class, 'storeExpense'])->name('cash_registers.expense');
    Route::post('/caja/venta/{order}/cancelar', [App\Http\Controllers\CashRegisterController::class, 'cancelOrder'])->name('cash_registers.cancel_order');
    Route::delete('/caja/gasto/{expense}', [App\Http\Controllers\CashRegisterController::class, 'destroyExpense'])->name('cash_registers.expense.destroy');

    // RUTAS DE INVENTARIO
    Route::get('/inventario/movimientos/carga-masiva', [App\Http\Controllers\InventoryMovementController::class, 'createBulk'])->name('inventory_movements.create_bulk');
    Route::post('/inventario/movimientos/carga-masiva', [App\Http\Controllers\InventoryMovementController::class, 'storeBulk'])->name('inventory_movements.store_bulk');
    Route::get('/inventario/movimientos', [App\Http\Controllers\InventoryMovementController::class, 'index'])->name('inventory_movements.index');
    Route::post('/inventario/movimientos', [App\Http\Controllers\InventoryMovementController::class, 'store'])->name('inventory_movements.store');

    // RUTAS DEL PANEL VIP
    Route::get('/vip-management', [App\Http\Controllers\VipManagementController::class, 'index'])->name('vip.index');
    Route::post('/vip-management/settings', [App\Http\Controllers\VipManagementController::class, 'updateSettings'])->name('vip.settings.update');
    Route::post('/vip-management/customer', [App\Http\Controllers\VipManagementController::class, 'storeCustomer'])->name('vip.customer.store');
    Route::put('/vip-management/customer/{customer}', [App\Http\Controllers\VipManagementController::class, 'updateCustomer'])->name('vip.customer.update');
    Route::delete('/vip-management/customer/{customer}/force-delete', [App\Http\Controllers\VipManagementController::class, 'forceDeleteCustomer'])->name('vip.customer.force-delete');
    Route::delete('/vip-management/customer/{customer}', [App\Http\Controllers\VipManagementController::class, 'destroyCustomer'])->name('vip.customer.destroy');
});

/*
|--------------------------------------------------------------------------
| ACCESO TOTAL (Solo Administrador)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/empleados', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/empleados/crear', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/empleados', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/empleados/{user}/editar', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/empleados/{user}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/empleados/{user}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

require __DIR__.'/auth.php';