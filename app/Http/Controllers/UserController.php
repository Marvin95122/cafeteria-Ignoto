<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // MOSTRAR LISTA
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // FORM CREAR
    public function create()
    {
        return view('users.create');
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,empleado',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    // FORM EDITAR
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    //  UPDATE
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,empleado',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only('name', 'email', 'role');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    // DELETE
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }
}
