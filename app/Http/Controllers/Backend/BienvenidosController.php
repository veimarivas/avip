<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BienvenidosController extends Controller
{
    public function principal()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return response()
            ->view('auth.login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
    }
}
