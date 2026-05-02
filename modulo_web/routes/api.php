<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CattleApiController;
use App\Http\Controllers\Api\VaccineApiController;
use App\Http\Controllers\Api\VaccineTypeApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('vaccine-types', [VaccineTypeApiController::class, 'index']);

    Route::get('cattle-with-vaccines', [CattleApiController::class, 'indexWithVaccines']);
    Route::get('cattle', [CattleApiController::class, 'index']);
    Route::post('cattle', [CattleApiController::class, 'store']);
    Route::get('cattle/{rfid_tag}', [CattleApiController::class, 'show']);
    Route::match(['put', 'patch'], 'cattle/{cattle}', [CattleApiController::class, 'update']);

    Route::get('vaccines', [VaccineApiController::class, 'index']);
    Route::post('vaccines', [VaccineApiController::class, 'store']);
});
