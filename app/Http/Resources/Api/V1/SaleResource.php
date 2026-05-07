<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id_compra' => (int) $this->id_compra,
            'id_turno' => $this->id_turno ? (int) $this->id_turno : null,
            'id_user' => (int) $this->id_user,
            'data_compra' => $this->data_compra ? $this->data_compra->toIso8601String() : null,
            'valor_total' => (float) $this->valor_total,
            'forma_pagamento' => $this->forma_pagamento,
            'dividir_valor' => $this->dividir_valor,
            'parcelas' => $this->parcelas ? (int) $this->parcelas : null,
            'itens' => $this->whenLoaded('itens', function () {
                return $this->itens->map(function ($item) {
                    return [
                        'id_venda_item' => (int) $item->id_venda_item,
                        'id_produto' => (int) $item->id_produto,
                        'nome_produto' => $item->nome_produto,
                        'lote' => $item->lote,
                        'codigo_barras' => $item->codigo_barras,
                        'quantidade' => (int) $item->quantidade,
                        'valor_unitario_venda' => (float) $item->valor_unitario_venda,
                        'valor_unitario_custo' => (float) $item->valor_unitario_custo,
                        'subtotal' => (float) $item->subtotal,
                        'subtotal_custo' => (float) $item->subtotal_custo,
                    ];
                })->values();
            }, []),
        ];
    }
}
