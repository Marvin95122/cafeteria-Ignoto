<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $perPage = $request->integer('per_page', 12);

        if (! in_array($perPage, [6, 12, 24, 48])) {
            $perPage = 12;
        }

        $employees = $query->paginate($perPage)->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('active', true)->count();
        $inactiveUsers = User::where('active', false)->count();
        $adminUsers = User::where('role', 'admin')->count();
        $managerUsers = User::where('role', 'gerente')->count();
        $employeeUsers = User::where('role', 'empleado')->count();

        return view('employees.index', compact(
            'employees',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'adminUsers',
            'managerUsers',
            'employeeUsers'
        ));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,gerente,empleado',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('employees.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id == 1 && auth()->id() != 1) {
            if ($request->filled('password')) {
                return back()->with('error', '⛔ SEGURIDAD: No tienes permiso para cambiar la contraseña del Super Administrador.');
            }

            if ($request->email !== $user->email) {
                return back()->with('error', '⛔ SEGURIDAD: No puedes modificar el correo del Super Administrador.');
            }

            if ($request->role != 'admin') {
                return back()->with('error', '⛔ SEGURIDAD: No puedes degradar al Super Administrador.');
            }
        }

        if (auth()->id() == $user->id && ! $request->has('active')) {
            return back()->with('error', '❌ No puedes desactivar tu propia cuenta mientras estás conectado.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:admin,empleado,gerente',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($user->id != 1) {
            $user->role = $request->role;
            $user->active = $request->has('active');
        } else {
            $user->role = 'admin';
            $user->active = true;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', '❌ No puedes dar de baja tu propia cuenta mientras estás conectado.');
        }

        if ($user->id == 1) {
            return back()->with('error', '🛡️ Este usuario es el Super Admin y está protegido.');
        }

        $user->update([
            'active' => false,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Usuario dado de baja correctamente. Su historial se conservará.');
    }

    public function forceDelete(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', '❌ No puedes eliminar definitivamente tu propia cuenta.');
        }

        if ($user->id == 1) {
            return back()->with('error', '🛡️ El Super Admin no puede eliminarse definitivamente.');
        }

        if ($user->orders()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque tiene ventas registradas.');
        }

        if ($user->cashRegisters()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque tiene aperturas o cierres de caja.');
        }

        if ($user->expenses()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque tiene gastos registrados.');
        }

        if ($user->inventoryMovements()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque tiene movimientos de inventario.');
        }

        if ($user->cancelledOrders()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque aparece como responsable de cancelaciones de ventas.');
        }

        if ($user->cancelledExpenses()->exists()) {
            return back()->with('error', 'No puedes eliminar definitivamente este usuario porque aparece como responsable de cancelaciones de gastos.');
        }

        $user->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Usuario eliminado definitivamente porque no tenía historial asociado.');
    }
}
