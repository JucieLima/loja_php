<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of VendaItens
 *
 * @author jucie
 */
class VendaItens extends Model {

    protected $table = "venda_itens";
    protected $primaryKey = "id_vi";
    protected $fillable = ['qtd_itens', 'valor_unitario_vi', 'produto_vi', 'venda_vi'];
    public $timestamps = false;
    private $data;

    public function insertProducts($data) {
        $this->data = $data;
        $this->saveProduct();
    }

    public function getItens($idVenda) {
        return self::join('produtos', 'produto_vi', '=', 'id_produto')
                        ->where('venda_vi', $idVenda)
                        ->select('venda_itens.id_vi', 'produtos.titulo_produto', 'venda_itens.qtd_itens', 'venda_itens.valor_unitario_vi')
                        ->get()->toArray();
    }

    private function saveProduct() {
        self::create($this->data);
    }

}
