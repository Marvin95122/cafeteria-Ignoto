<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = \App\Models\User::latest()->get();
        return view('employees.index', compact('employees'));
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
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'empleado',
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Empleado creado correctamente');
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
            return back()->with('error', '❌ ¡No puedes eliminar tu propia cuenta mientras estás conectado!');
        }

        if ($user->id == 1) {
            return back()->with('error', '🛡️ Este usuario es el Super Admin y está protegido.');
        }

        $user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}


/*namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class EmployeeController extends Controller
{
    public function index()
    {
        //$employees = User::where('role', 'empleado')->get();
        $employees = \App\Models\User::latest()->get();
        return view('employees.index', compact('employees'));
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
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'empleado',
    ]);

    return redirect()->route('employees.index')
        ->with('success', 'Empleado creado correctamente');
}


    public function edit(User $user)
    {
        return view('employees.edit', compact('user'));
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($user->id),
        ],
        'password' => 'nullable|min:6|confirmed',
        'role' => 'required|in:admin,empleado',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->role = $request->role;

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
    $authUser = auth()->user();

    if ($authUser && $authUser->id === $user->id) {
        return back()->with('error', 'No puedes eliminar tu propio usuario');
    }

    $user->delete();

    return redirect()->route('employees.index')
        ->with('success', 'Empleado eliminado correctamente');
}

}*/
