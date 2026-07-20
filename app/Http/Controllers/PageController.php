<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function faq()
    {
        return view('faq');
    }

    public function mentionsLegales()
    {
        return view('mentions-legales');
    }

    public function cgu()
    {
        return view('cgu');
    }
}