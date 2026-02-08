<?php

use App\Http\Controllers\EntregaController;
use App\Http\Controllers\OcorrenciaController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/recebimento-confirmado/{token}', [EntregaController::class, 'confirmarRecebimento']);

Route::get('/recebimento-negado/{token}', [EntregaController::class, 'negarRecebimento']);
