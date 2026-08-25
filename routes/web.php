<?php

use Illuminate\Support\Facades\Route;
#Controllers
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraFuncionarioController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\ControleFinanceiroController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoFiscalController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\EstornoCompraController;
use App\Http\Controllers\FechamentoCaixaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HistoricoCompraController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
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
| 
*/
Route::prefix('dashboard')
    ->middleware(['auth', 'admin'])
    ->group( function(){
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

});


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
    Route::post('/leitor-produtos/adicionar', 'adicionarAoLeitor')->name('leitor.produtos.adicionar');
    Route::post('/leitor-produtos/remover/{idProduto}', 'removerDoLeitor')->name('leitor.produtos.remover');
    Route::post('/leitor-produtos/zerar', 'zerarLeitor')->name('leitor.produtos.zerar');
});

Route::prefix('leitor-produtos')->middleware(['auth', 'funcionario'])->controller(CompraFuncionarioController::class)
->group(function ()
{
    Route::get('/finalizar-compra', 'create')->name('leitor.finalizar');
    Route::post('/finalizar-compra', 'store')->name('leitor.finalizar.store');
});

/*
|--------------------------------------------------------------------------
| HISTORICO DE COMPRAS (FUNCIONARIO)
|--------------------------------------------------------------------------
*/
Route::prefix('leitor-produtos/historico')->middleware(['auth'])->controller(HistoricoCompraController::class)
->group(function ()
{
    Route::get('/', 'index')->name('leitor.historico.index');
    Route::get('/mostrar/{id}', 'show')->name('leitor.historico.show');
});

Route::prefix('cliente')->middleware(['auth', 'admin'])->controller(ClienteController::class)
->group(function ()
{
    Route::get('/', 'index')->name('cliente.index');
    Route::get('/novo', 'create')->name('cliente.create');
    Route::get('/editar/{id}', 'edit')->name('cliente.edit');
    Route::post('/cadastrar', 'store')->name('cliente.store');
    Route::post('/atualizar/{id}', 'update')->name('cliente.update');
    Route::post('/desativar/{id}', 'destroy')->name('cliente.destroy');
});

Route::post('/compras/{id}/estornar', [EstornoCompraController::class, 'store'])
    ->middleware(['auth', 'admin'])
    ->name('compras.estornar');

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
| ESTOQUE / MOVIMENTACOES DE PRODUTO (ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('estoque')->middleware(['auth', 'admin'])->controller(EstoqueController::class)
->group(function ()
{
    Route::get('/', 'index')->               name('estoque.index');
    Route::post('/movimentar', 'store')->    name('estoque.store');
});



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
    Route::post('/deletar/{id}', 'destroy')-> name ('centro.destroy');
});
/*
|--------------------------------------------------------------------------
| LANÇAMENTOS
|--------------------------------------------------------------------------
|
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
    Route::post('/deletar/{id}', 'destroy')-> name ('lancamento.destroy');
});
/*
|--------------------------------------------------------------------------
| Controle Financeiro
|--------------------------------------------------------------------------
|
*/
Route::prefix('controle-financeiro')->middleware(['auth', 'admin'])->controller(ControleFinanceiroController::class)
->group(function ()
{
    Route::get('/', 'index')->name('controle-financeiro.index');

});

/*
|--------------------------------------------------------------------------
| CONFIGURACOES GERAIS
|--------------------------------------------------------------------------
*/
Route::prefix('configuracoes')->middleware(['auth', 'admin'])->controller(ConfiguracaoController::class)
->group(function ()
{
    Route::get('/', 'index')->name('configuracoes.index');
    Route::post('/atualizar', 'update')->name('configuracoes.update');
});

Route::prefix('documentos-fiscais')->middleware(['auth', 'admin'])->controller(DocumentoFiscalController::class)
->group(function ()
{
    Route::get('/', 'index')->name('documentos-fiscais.index');
    Route::get('/{id}', 'show')->name('documentos-fiscais.show');
    Route::post('/solicitar/{idCompra}', 'solicitar')->name('documentos-fiscais.solicitar');
});

/*
|--------------------------------------------------------------------------
| AUDITORIA / LOGS
|--------------------------------------------------------------------------
*/
Route::prefix('auditoria')->middleware(['auth', 'admin'])->controller(AuditoriaController::class)
->group(function ()
{
    Route::get('/', 'index')->name('auditoria.index');
});

/*
|--------------------------------------------------------------------------
| FECHAMENTO DE CAIXA
|--------------------------------------------------------------------------
|
*/
Route::prefix('fechamento-caixa')->middleware(['auth'])->controller(FechamentoCaixaController::class)
->group(function ()
{
    Route::get('/', 'index')->name('fechamento-caixa.index');
    Route::get('/novo', 'create')->name('fechamento-caixa.create');
    Route::get('/mostrar/{id}', 'show')->name('fechamento-caixa.show');
    Route::post('/cadastrar', 'store')->name('fechamento-caixa.store');
    Route::post('/reabrir/{id}', 'reabrir')->middleware('admin')->name('fechamento-caixa.reabrir');
});
/*
|--------------------------------------------------------------------------
| RELATORIOS
|--------------------------------------------------------------------------
| 
*/
Route::prefix('relatorios')->middleware(['auth', 'admin'])->controller(RelatorioController::class)
->group(function ()
{
    Route::get('/', 'index')->name('relatorios.index');
    Route::get('/exportar/csv', 'exportCsv')->name('relatorios.export.csv');
    Route::get('/exportar/pdf', 'exportPdf')->name('relatorios.export.pdf');
});

/*
|--------------------------------------------------------------------------
| PERFIL (ADMIN E FUNCIONARIO)
|--------------------------------------------------------------------------
*/
Route::prefix('perfil')->middleware(['auth'])->controller(PerfilController::class)
->group(function ()
{
    Route::get('/', 'index')->name('perfil.index');
    Route::post('/atualizar', 'update')->name('perfil.update');
    Route::post('/senha', 'updatePassword')->name('perfil.password');
});



require __DIR__.'/auth.php';
