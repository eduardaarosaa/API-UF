<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\MunicipioController;
use Illuminate\Auth\Events\Verified;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


 Route::get('/municipios/{uf}', [MunicipioController::class, 'index'] );