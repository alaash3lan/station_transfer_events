<?php

use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/transfers', [TransferController::class, 'ingest']);
Route::get('/stations/{stationId}/summary', [TransferController::class, 'summary']);
