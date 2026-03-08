<?php

use Illuminate\Support\Facades\Route;
#Controllers
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->tipo_usuario === 'admin') {
        return redirect()->route('dashboard');
    }

    return redirect()->route('leitor.produtos');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
| Thomas Melo - 19-09-2022
*/
Route::prefix('dashboard')
    ->middleware(['auth', 'admin'])
    ->group( function(){
        Route::get('/', function () { 
            return view('dashboard');
        })->name('dashboard');

});

/*
Kaue Castelani -HOME 29/11/2022
*/
Route::prefix('home')->middleware(['auth', 'admin'])->controller(HomeController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('home.index');
    Route::get('/novo', 'create')->           name('home.create');
});

/*
|--------------------------------------------------------------------------
| USUARIOS (ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('usuario')->middleware(['auth', 'admin'])->controller(UsuarioController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('usuario.index');
    Route::get('/editar/{id}', 'edit')->      name('usuario.edit');
    Route::post('/cadastrar', 'store')->      name('usuario.store');
    Route::post('/atualizar/{id}', 'update')->name('usuario.update');
    Route::post('/deletar/{id}', 'destroy')-> name('usuario.delete');
});

/*
|--------------------------------------------------------------------------
| LEITOR DE PRODUTOS (FUNCIONARIO)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'funcionario'])->controller(ProdutoController::class)
->group(function ()
{
    Route::get('/leitor-produtos', 'leitor')->name('leitor.produtos');
});

/*
|--------------------------------------------------------------------------
| PRODUTOS (ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('produto')->middleware(['auth', 'admin'])->controller(ProdutoController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('produto.index');
    Route::get('/novo', 'create')->           name('produto.create');
    Route::get('/editar/{id}', 'edit')->      name('produto.edit');
    Route::post('/cadastrar', 'store')->      name('produto.store');
    Route::post('/atualizar/{id}', 'update')->name('produto.update');
    Route::post('/deletar/{id}', 'destroy')-> name('produto.delete');
});


/*
|--------------------------------------------------------------------------
| TIPOS
|--------------------------------------------------------------------------
| Thomas Melo - 19-09-2022
*/
//Metodo Prefixo Facilitar Rotas, Middleware(Definir Tipos de Acesso), Quando uso o prefixo, utilizar GROUP de rotas
Route::prefix('tipo')->middleware(['auth', 'admin'])->controller(TipoController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('tipo.index');
    Route::get('/novo', 'create')->           name('tipo.create');
    Route::get('/editar/{id}', 'edit')->      name('tipo.edit');
    Route::get('/mostrar/{id}', 'show')->     name('tipo.show');
    Route::post('/cadastrar', 'store')->      name ('tipo.store');
    Route::post('/atualizar/{id}', 'update')->name ('tipo.update');
    Route::post('/deletar/{id}', 'destroy')-> name ('tipo.delete');
});
/*
|--------------------------------------------------------------------------
| CENTRO DE CUSTO
|--------------------------------------------------------------------------
| Thomas Melo - 19-09-2022
*/
Route::prefix('centro-de-custo')->middleware(['auth', 'admin'])->controller(CentroCustoController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('centro.index');
    Route::get('/novo', 'create')->           name('centro.create');
    Route::get('/editar/{id}', 'edit')->      name('centro.edit');
    Route::get('/mostrar/{id}', 'show')->     name('centro.show');
    Route::post('/cadastrar', 'store')->      name ('centro.store');
    Route::post('/atualizar/{id}', 'update')->name ('centro.update');
    Route::get('/deletar/{id}', 'destroy')-> name ('centro.destroy');
});
/*
|--------------------------------------------------------------------------
| LANÇAMENTOS
|--------------------------------------------------------------------------
| Thomas Melo - 19-09-2022
*/
Route::prefix('lancamento')->middleware(['auth', 'admin'])->controller(LancamentoController::class)
->group(function ()
{
    Route::get('/', 'index')->                name('lancamento.index');
    Route::get('/novo', 'create')->           name('lancamento.create');
    Route::get('/editar/{id}', 'edit')->      name('lancamento.edit');
    Route::get('/mostrar/{id}', 'show')->     name('lancamento.show');
    Route::post('/cadastrar', 'store')->      name ('lancamento.store');
    Route::post('/atualizar/{id}', 'update')->name ('lancamento.update');
    Route::get('/deletar/{id}', 'destroy')-> name ('lancamento.destroy');
});
/*
|--------------------------------------------------------------------------
| RELATORIOS
|--------------------------------------------------------------------------
| Thomas Melo - 19-09-2022
*/



require __DIR__.'/auth.php';
