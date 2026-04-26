<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CattleApiController;
use App\Http\Controllers\Api\VaccineApiController;
use App\Http\Controllers\Api\VaccineTypeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('vaccine-types', [VaccineTypeApiController::class, 'index']);

    Route::get('cattle-with-vaccines', [CattleApiController::class, 'indexWithVaccines']);
    Route::apiResource('cattle', CattleApiController::class)->only(['index', 'store', 'update']);
    Route::get('cattle/{rfid_tag}', [CattleApiController::class, 'show']);
    Route::apiResource('vaccines', VaccineApiController::class)->only(['index', 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
