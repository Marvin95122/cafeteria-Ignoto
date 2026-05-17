<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VipManagementController extends Controller
{
    // Vista principal del panel VIP
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'gerente'])) {
            abort(403, 'Acceso denegado. Solo administradores y gerentes.');
        }

        $query = Customer::latest();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        $customers = $query->get();

        $moneyForOnePoint = Setting::firstOrCreate(
            ['key' => 'vip_money_for_point'],
            ['value' => '10']
        )->value;

        $pointValue = Setting::firstOrCreate(
            ['key' => 'vip_point_value'],
            ['value' => '1']
        )->value;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'points' => 'required|integer|min:0'
        ]);

        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'points' => $request->points,
            'active' => true,
        ]);

        return redirect()
            ->route('vip.index')
            ->with('success', 'Cliente VIP registrado con éxito.');
    }

    // Actualizar puntos o datos manualmente
    public function updateCustomer(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'points' => 'required|integer|min:0'
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'points' => $request->points,
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('vip.index')
            ->with('success', 'Datos, puntos y estado del cliente actualizados.');
    }

    // Eliminar cliente VIP
    public function destroyCustomer(Customer $customer)
    {
        $customer->update([
            'active' => false,
        ]);

        return redirect()
            ->route('vip.index')
            ->with('success', 'Cliente VIP dado de baja correctamente. Su historial de compras se conservará.');
    }

    public function forceDeleteCustomer(Customer $customer)
    {
        if ($customer->orders()->exists()) {
            return redirect()
                ->route('vip.index')
                ->with('error', 'No puedes eliminar definitivamente este cliente VIP porque tiene ventas o tickets asociados. Puedes mantenerlo dado de baja.');
        }

        $customer->delete();

        return redirect()
            ->route('vip.index')
            ->with('success', 'Cliente VIP eliminado definitivamente porque no tenía ventas asociadas.');
    }
}