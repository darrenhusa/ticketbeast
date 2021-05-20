<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

class ConcertsController extends Controller
{
    public function show($id) 
    {
    	// dd($concert);

        $concert = Concert::whereNotNull('published_at')->findOrFail($id)

    	return view('concerts.show', compact('concert'));
    }

     
}
