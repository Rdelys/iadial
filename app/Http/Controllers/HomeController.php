<?php

namespace App\Http\Controllers;

use App\Models\IarecepVisit;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        IarecepVisit::create([
            'path' => request()->path(),
            'ip'   => request()->ip(),
        ]);

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