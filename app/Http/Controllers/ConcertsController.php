<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

class ConcertsController extends Controller
{
    public function show(Concert $concert) 
    {
    	// dd($concert);

    	return view('concerts.show', compact('concert'));
    }

     
}
