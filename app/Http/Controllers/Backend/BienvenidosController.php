<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BienvenidosController extends Controller
{
    public function principal()
    {
        return view('auth.login');
    }
}
