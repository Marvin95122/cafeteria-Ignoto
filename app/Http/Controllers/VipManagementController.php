<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VipManagementController extends Controller
{
    // Vista principal del panel VIP
    public function index()
    {
        // Solo Admin y Gerente entran aquí
        if (!in_array(Auth::user()->role, ['admin', 'gerente'])) {
            abort(403, 'Acceso denegado. Solo administradores y gerentes.');
        }

        $customers = Customer::latest()->get();
        
        // Cargamos o creamos los valores por defecto (10% de recompensa, 1 Punto = 1 Peso)
        $moneyForOnePoint = Setting::firstOrCreate(['key' => 'vip_money_for_point'], ['value' => '10'])->value;
        $pointValue = Setting::firstOrCreate(['key' => 'vip_point_value'], ['value' => '1'])->value;

        return view('vip.index', compact('customers', 'moneyForOnePoint', 'pointValue'));
    }

    // Guardar ajustes (EXCLUSIVO DE ADMINISTRADOR)
    public function updateSettings(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('vip.index')->with('error', 'Solo el Administrador principal puede cambiar el valor de los puntos.');
        }

        $request->validate([
            'money_for_point' => 'required|numeric|min:1',
            'point_value' => 'required|numeric|min:0.01',
        ]);

        Setting::updateOrCreate(['key' => 'vip_money_for_point'], ['value' => $request->money_for_point]);
        Setting::updateOrCreate(['key' => 'vip_point_value'], ['value' => $request->point_value]);

        return redirect()->route('vip.index')->with('success', 'Configuración de Puntos actualizada correctamente.');
    }

    // Crear cliente desde el panel
    public function storeCustomer(Request $request)
    {
        // Ya protegido por el middleware de rutas para admin/gerente
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'points' => 'required|integer|min:0'
        ]);

        Customer::create($request->only('name', 'phone', 'points'));

        return redirect()->route('vip.index')->with('success', 'Cliente VIP registrado con éxito.');
    }

    // Actualizar puntos o datos manualmente
    public function updateCustomer(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'points' => 'required|integer|min:0'
        ]);

        $customer->update($request->only('name', 'phone', 'points'));

        return redirect()->route('vip.index')->with('success', 'Datos y puntos del cliente actualizados.');
    }

    // Eliminar cliente VIP
    public function destroyCustomer(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('vip.index')->with('success', 'Cliente VIP eliminado del sistema.');
    }
}