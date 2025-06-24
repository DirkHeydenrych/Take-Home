<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderViewController extends Controller
{
    /**
     * Display the order viewer page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('orders.index');
    }
}
