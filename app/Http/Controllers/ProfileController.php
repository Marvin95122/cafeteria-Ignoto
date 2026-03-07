<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');  // Retorna la vista del perfil de edición
    }

    public function update(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        // Actualizar los datos del usuario autenticado
        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Redirigir de vuelta con un mensaje de éxito
        return redirect()->route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }
}
