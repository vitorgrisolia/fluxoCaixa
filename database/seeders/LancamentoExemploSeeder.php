<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LancamentoExemploSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usuario = DB::table('users')->where('email', 'admin@example.com')->first();
        if (!$usuario) {
            $usuario = DB::table('users')->orderBy('id_user')->first();
        }

        if (!$usuario) {
            return;
        }

        $centro = DB::table('centro_custos')->where('centro_custo', 'Vendas')->first();
        if (!$centro) {
            $centro = DB::table('centro_custos')->where('id_tipo', 2)->orderBy('id_centro_custo')->first();
        }

        $centroId = $centro?->id_centro_custo;

        if (!$centroId) {
            $centroId = DB::table('centro_custos')->insertGetId([
                'id_tipo' => 2,
                'centro_custo' => 'Vendas',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $dataFaturamento = Carbon::today()->toDateString();

        DB::table('lancamentos')->updateOrInsert(
            [
                'id_user' => $usuario->id_user,
                'id_centro_custo' => $centroId,
                'dt_faturamento' => $dataFaturamento,
                'descricao' => 'Venda de teste',
            ],
            [
                'observacoes' => 'Lancamento exemplo criado pelo seeder.',
                'valor' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
