<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertsController;


Route::get('/concerts/{id}', [ConcertsController::class, 'show']);
