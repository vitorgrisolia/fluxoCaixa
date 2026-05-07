<?php

namespace App\Providers;

use App\Models\CaixaMovimentacao;
use App\Models\CaixaTurno;
use App\Models\CentroCusto;
use App\Models\Compra;
use App\Models\ConfiguracaoSistema;
use App\Models\FechamentoCaixa;
use App\Models\Lancamento;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use App\Models\Tipo;
use App\Models\User;
use App\Models\VendaItem;
use App\Observers\ModelAuditObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $observer = ModelAuditObserver::class;
        User::observe($observer);
        Produto::observe($observer);
        Compra::observe($observer);
        VendaItem::observe($observer);
        MovimentacaoProduto::observe($observer);
        CaixaTurno::observe($observer);
        CaixaMovimentacao::observe($observer);
        FechamentoCaixa::observe($observer);
        Lancamento::observe($observer);
        CentroCusto::observe($observer);
        Tipo::observe($observer);
        ConfiguracaoSistema::observe($observer);

        if (Schema::hasTable('configuracoes')) {
            $configuracaoSistema = ConfiguracaoSistema::first();
            View::share('configuracaoSistema', $configuracaoSistema);
        }
    }
}
