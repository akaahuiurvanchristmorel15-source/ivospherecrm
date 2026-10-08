<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display the clean application menu grid.
     */
    public function index(Request $request): View
    {
        return view('menu.index');
    }
}
