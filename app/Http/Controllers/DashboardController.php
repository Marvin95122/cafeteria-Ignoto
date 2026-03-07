<?php

namespace App\Http\Controllers;

use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'totalUsuarios' => User::count(),
            'totalEmpleados' => User::where('role', 'empleado')->count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
        ]);
    }
}
