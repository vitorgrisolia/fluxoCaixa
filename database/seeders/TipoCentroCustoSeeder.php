<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCentroCustoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipos')->updateOrInsert(
            ['id_tipo' => 1],
            ['tipo' => 'Saida', 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('tipos')->updateOrInsert(
            ['id_tipo' => 2],
            ['tipo' => 'Entrada', 'updated_at' => now(), 'created_at' => now()]
        );

        $saidas = [
            'Aluguel',
            'Energia eletrica',
            'Agua',
            'Internet',
            'Folha de pagamento',
            'Impostos',
            'Manutencao',
            'Compras de insumos',
            'Marketing',
            'Transporte',
            'Taxas bancarias',
            'Outros',
        ];

        $entradas = [
            'Vendas',
            'Servicos',
            'Recebimentos',
            'Reembolsos',
            'Investimentos',
        ];

        foreach ($saidas as $centro) {
            DB::table('centro_custos')->updateOrInsert(
                ['id_tipo' => 1, 'centro_custo' => $centro],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        foreach ($entradas as $centro) {
            DB::table('centro_custos')->updateOrInsert(
                ['id_tipo' => 2, 'centro_custo' => $centro],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
