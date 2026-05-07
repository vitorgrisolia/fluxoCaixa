<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CaixaTurnoController;
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\CompraFuncionarioController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\ControleFinanceiroController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\FechamentoCaixaController;
use App\Http\Controllers\HistoricoCompraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\UsuarioController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->possuiPermissao('dashboard.visualizar')) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('leitor.produtos');
});

Route::prefix('dashboard')
    ->middleware(['auth', 'permission:dashboard.visualizar'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });

Route::prefix('home')
    ->middleware(['auth', 'permission:dashboard.visualizar'])
    ->controller(HomeController::class)
    ->group(function () {
        Route::get('/', 'index')->name('home.index');
        Route::get('/novo', 'create')->name('home.create');
    });

Route::prefix('usuario')
    ->middleware(['auth', 'permission:usuario.gerenciar'])
    ->controller(UsuarioController::class)
    ->group(function () {
        Route::get('/', 'index')->name('usuario.index');
        Route::get('/editar/{id}', 'edit')->name('usuario.edit');
        Route::post('/cadastrar', 'store')->name('usuario.store');
        Route::post('/atualizar/{id}', 'update')->name('usuario.update');
        Route::post('/deletar/{id}', 'destroy')->name('usuario.delete');
    });

Route::middleware(['auth', 'permission:pdv.acessar'])
    ->controller(ProdutoController::class)
    ->group(function () {
        Route::get('/leitor-produtos', 'leitor')->name('leitor.produtos');
        Route::post('/leitor-produtos/adicionar', 'adicionarAoLeitor')->name('leitor.produtos.adicionar');
        Route::post('/leitor-produtos/incrementar/{idProduto}', 'incrementarNoLeitor')->name('leitor.produtos.incrementar');
        Route::post('/leitor-produtos/decrementar/{idProduto}', 'decrementarNoLeitor')->name('leitor.produtos.decrementar');
        Route::post('/leitor-produtos/remover/{idProduto}', 'removerDoLeitor')->name('leitor.produtos.remover');
    });

Route::post('/leitor-produtos/zerar', [ProdutoController::class, 'zerarLeitor'])
    ->middleware(['auth', 'permission:pdv.zerar'])
    ->name('leitor.produtos.zerar');

Route::post('/leitor-produtos/cadastro-rapido', [ProdutoController::class, 'cadastroRapidoNoLeitor'])
    ->middleware(['auth', 'permission:pdv.cadastro_rapido'])
    ->name('leitor.produtos.cadastro-rapido');

Route::prefix('leitor-produtos')
    ->middleware(['auth', 'permission:pdv.vender'])
    ->controller(CompraFuncionarioController::class)
    ->group(function () {
        Route::get('/finalizar-compra', 'create')->name('leitor.finalizar');
        Route::post('/finalizar-compra', 'store')->name('leitor.finalizar.store');
    });

Route::prefix('caixa-turno')
    ->middleware(['auth', 'permission:caixa.turno.visualizar'])
    ->controller(CaixaTurnoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('caixa.turno.index');
    });

Route::post('/caixa-turno/abrir', [CaixaTurnoController::class, 'abrir'])
    ->middleware(['auth', 'permission:caixa.turno.abrir'])
    ->name('caixa.turno.abrir');

Route::post('/caixa-turno/movimentar', [CaixaTurnoController::class, 'movimentar'])
    ->middleware(['auth', 'permission:caixa.turno.movimentar'])
    ->name('caixa.turno.movimentar');

Route::prefix('leitor-produtos/historico')
    ->middleware(['auth', 'permission:pdv.historico.visualizar'])
    ->controller(HistoricoCompraController::class)
    ->group(function () {
        Route::get('/', 'index')->name('leitor.historico.index');
        Route::get('/novo', 'create')->name('leitor.historico.create');
        Route::get('/editar/{id}', 'edit')->name('leitor.historico.edit');
        Route::get('/mostrar/{id}', 'show')->name('leitor.historico.show');
        Route::post('/cadastrar', 'store')->name('leitor.historico.store');
        Route::post('/atualizar/{id}', 'update')->name('leitor.historico.update');
        Route::post('/deletar/{id}', 'destroy')->name('leitor.historico.destroy');
    });

Route::prefix('produto')
    ->middleware(['auth'])
    ->controller(ProdutoController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('permission:produto.visualizar')->name('produto.index');
        Route::get('/novo', 'create')->middleware('permission:produto.criar')->name('produto.create');
        Route::get('/editar/{id}', 'edit')->middleware('permission:produto.editar')->name('produto.edit');
        Route::post('/importar', 'importarLote')
            ->middleware(['permission:produto.importar', 'permission:produto.definir_preco'])
            ->name('produto.importar');
        Route::post('/cadastrar', 'store')
            ->middleware(['permission:produto.criar', 'permission:produto.definir_preco'])
            ->name('produto.store');
        Route::post('/atualizar/{id}', 'update')
            ->middleware(['permission:produto.editar', 'permission:produto.definir_preco'])
            ->name('produto.update');
        Route::post('/deletar/{id}', 'destroy')->middleware('permission:produto.excluir')->name('produto.delete');
    });

Route::prefix('estoque')
    ->middleware(['auth'])
    ->controller(EstoqueController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('permission:estoque.visualizar')->name('estoque.index');
        Route::post('/movimentar', 'store')->middleware('permission:estoque.movimentar')->name('estoque.store');
    });

Route::prefix('tipo')->middleware(['auth', 'admin'])->controller(TipoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('tipo.index');
        Route::get('/novo', 'create')->name('tipo.create');
        Route::get('/editar/{id}', 'edit')->name('tipo.edit');
        Route::get('/mostrar/{id}', 'show')->name('tipo.show');
        Route::post('/cadastrar', 'store')->name('tipo.store');
        Route::post('/atualizar/{id}', 'update')->name('tipo.update');
        Route::post('/deletar/{id}', 'destroy')->name('tipo.delete');
    });

Route::prefix('centro-de-custo')->middleware(['auth', 'admin'])->controller(CentroCustoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('centro.index');
        Route::get('/novo', 'create')->name('centro.create');
        Route::get('/editar/{id}', 'edit')->name('centro.edit');
        Route::get('/mostrar/{id}', 'show')->name('centro.show');
        Route::post('/cadastrar', 'store')->name('centro.store');
        Route::post('/atualizar/{id}', 'update')->name('centro.update');
        Route::get('/deletar/{id}', 'destroy')->name('centro.destroy');
    });

Route::prefix('lancamento')->middleware(['auth', 'admin'])->controller(LancamentoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('lancamento.index');
        Route::get('/novo', 'create')->name('lancamento.create');
        Route::get('/editar/{id}', 'edit')->name('lancamento.edit');
        Route::get('/mostrar/{id}', 'show')->name('lancamento.show');
        Route::post('/cadastrar', 'store')->name('lancamento.store');
        Route::post('/atualizar/{id}', 'update')->name('lancamento.update');
        Route::get('/deletar/{id}', 'destroy')->name('lancamento.destroy');
    });

Route::prefix('controle-financeiro')->middleware(['auth', 'admin'])->controller(ControleFinanceiroController::class)
    ->group(function () {
        Route::get('/', 'index')->name('controle-financeiro.index');
    });

Route::prefix('configuracoes')
    ->middleware(['auth', 'permission:configuracoes.editar'])
    ->controller(ConfiguracaoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('configuracoes.index');
        Route::post('/atualizar', 'update')->name('configuracoes.update');
    });

Route::prefix('auditoria')
    ->middleware(['auth', 'permission:auditoria.visualizar'])
    ->controller(AuditoriaController::class)
    ->group(function () {
        Route::get('/', 'index')->name('auditoria.index');
    });

Route::prefix('fechamento-caixa')
    ->middleware(['auth'])
    ->controller(FechamentoCaixaController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('permission:caixa.fechamento.visualizar')->name('fechamento-caixa.index');
        Route::get('/mostrar/{id}', 'show')->middleware('permission:caixa.fechamento.visualizar')->name('fechamento-caixa.show');
        Route::get('/novo', 'create')->middleware('permission:caixa.fechamento.gerenciar')->name('fechamento-caixa.create');
        Route::get('/editar/{id}', 'edit')->middleware('permission:caixa.fechamento.gerenciar')->name('fechamento-caixa.edit');
        Route::post('/cadastrar', 'store')->middleware('permission:caixa.fechamento.gerenciar')->name('fechamento-caixa.store');
        Route::post('/atualizar/{id}', 'update')->middleware('permission:caixa.fechamento.gerenciar')->name('fechamento-caixa.update');
        Route::post('/deletar/{id}', 'destroy')->middleware('permission:caixa.fechamento.gerenciar')->name('fechamento-caixa.destroy');
    });

Route::prefix('relatorios')
    ->middleware(['auth', 'permission:relatorios.visualizar'])
    ->controller(RelatorioController::class)
    ->group(function () {
        Route::get('/', 'index')->name('relatorios.index');
        Route::get('/exportar/csv', 'exportCsv')->name('relatorios.export.csv');
        Route::get('/exportar/pdf', 'exportPdf')->name('relatorios.export.pdf');
    });

Route::prefix('perfil')
    ->middleware(['auth'])
    ->controller(PerfilController::class)
    ->group(function () {
        Route::get('/', 'index')->name('perfil.index');
        Route::post('/atualizar', 'update')->middleware('permission:perfil.editar')->name('perfil.update');
        Route::post('/senha', 'updatePassword')->middleware('permission:perfil.editar')->name('perfil.password');
    });

require __DIR__.'/auth.php';
