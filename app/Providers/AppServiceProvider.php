<?php

namespace App\Providers;

use App\Models\ConfiguracaoSistema;
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

        if (Schema::hasTable('configuracoes')) {
            $configuracaoSistema = ConfiguracaoSistema::first();
            View::share('configuracaoSistema', $configuracaoSistema);
        }
    }
}
