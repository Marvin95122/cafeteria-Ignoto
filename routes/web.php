<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\PosController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard (usuarios autenticados)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('categories', CategoryController::class)
        ->except(['show']);

    Route::resource('products', ProductController::class)
        ->except(['show']);

    Route::resource('ingredients', IngredientController::class);
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/ticket/{order}', [PosController::class, 'ticket'])->name('pos.ticket');
});



/*
|--------------------------------------------------------------------------
| Gestión de empleados (SOLO ADMIN)
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

/*
|--------------------------------------------------------------------------
| Auth (Laravel Breeze / Jetstream)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';