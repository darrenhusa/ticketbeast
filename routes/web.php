<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertsController;


Route::get('/concerts/{concert}', [ConcertsController::class, 'show']);
