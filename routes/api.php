<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DirectUpdateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Direct Update API (token-based, no GitHub required)
Route::prefix('direct-update')->group(function () {
    Route::post('/apply', [DirectUpdateController::class, 'apply']);
    Route::get('/status', [DirectUpdateController::class, 'status']);
    Route::post('/rollback', [DirectUpdateController::class, 'rollback']);
});

// Rota para ler estado da atualização em tempo real (sem bloqueio de sessão)
Route::get('/update-state', function () {
    $stateFile = storage_path('app/update_state.json');
    
    if (file_exists($stateFile)) {
        $content = file_get_contents($stateFile);
        return response()->json(json_decode($content, true));
    }
    
    return response()->json([
        'status' => 'Aguardando confirmação...',
        'progress' => 0,
        'step' => 'ready',
        'is_updating' => false,
        'logs' => []
    ]);
});
