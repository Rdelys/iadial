<?php

namespace App\Http\Controllers;

use App\Models\IarecepVisit;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        IarecepVisit::create([
            'path' => request()->path(),
            'ip'   => request()->ip(),
        ]);

        if (Auth::check()) {
            return view('profile', ['user' => Auth::user()]);
        }

        return view('home');
    }

    public function tarifs()
    {
        IarecepVisit::create([
            'path' => request()->path(),
            'ip'   => request()->ip(),
        ]);

        return view('tarifs');
    }
}