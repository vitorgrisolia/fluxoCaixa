<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\SaleApiController;
use App\Http\Controllers\Api\V1\StockAdjustmentApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:integration-auth')
        ->name('api.v1.auth.token.store');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', function (Request $request) {
            return response()->json([
                'id_user' => $request->user()->id_user,
                'nome' => $request->user()->nome,
                'email' => $request->user()->email,
                'tipo_usuario' => $request->user()->tipo_usuario,
            ]);
        })->name('api.v1.me');

        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])
            ->name('api.v1.auth.token.destroy');

        Route::get('/produtos', [ProductApiController::class, 'index'])
            ->middleware('permission:api.produtos.ler')
            ->name('api.v1.produtos.index');
        Route::get('/produtos/{id}', [ProductApiController::class, 'show'])
            ->middleware('permission:api.produtos.ler')
            ->name('api.v1.produtos.show');
        Route::post('/produtos', [ProductApiController::class, 'store'])
            ->middleware('permission:api.produtos.escrever')
            ->name('api.v1.produtos.store');
        Route::put('/produtos/{id}', [ProductApiController::class, 'update'])
            ->middleware('permission:api.produtos.escrever')
            ->name('api.v1.produtos.update');
        Route::patch('/produtos/{id}', [ProductApiController::class, 'update'])
            ->middleware('permission:api.produtos.escrever')
            ->name('api.v1.produtos.patch');
        Route::post('/produtos/{idProduto}/estoque', [StockAdjustmentApiController::class, 'store'])
            ->middleware('permission:api.estoque.ajustar')
            ->name('api.v1.produtos.estoque.store');

        Route::get('/vendas', [SaleApiController::class, 'index'])
            ->middleware('permission:api.vendas.ler')
            ->name('api.v1.vendas.index');
        Route::get('/vendas/resumo', [SaleApiController::class, 'summary'])
            ->middleware('permission:api.vendas.ler')
            ->name('api.v1.vendas.summary');
        Route::post('/vendas', [SaleApiController::class, 'store'])
            ->middleware('permission:api.vendas.escrever')
            ->name('api.v1.vendas.store');
    });
});
