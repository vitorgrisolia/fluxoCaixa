<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'id_produto' => (int) $this->id_produto,
            'nome' => $this->nome,
            'lote' => $this->lote,
            'codigo_barras' => $this->codigo_barras,
            'quantidade' => (int) $this->quantidade,
            'estoque_minimo' => (int) $this->estoque_minimo,
            'tipo_quantidade' => $this->tipo_quantidade,
            'validade' => optional($this->validade)->format('Y-m-d'),
            'preco_compra' => (float) $this->preco_compra,
            'preco_venda' => (float) $this->preco_venda,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
