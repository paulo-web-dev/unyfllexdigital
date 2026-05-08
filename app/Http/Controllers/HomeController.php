<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes; 

class HomeController extends Controller
{
    public function index()
    {
        $classes = Classes::where('express', '1')->get();
        
        return view('pages.home',[
            'classes' => $classes,
            ]
        );
    }
}
