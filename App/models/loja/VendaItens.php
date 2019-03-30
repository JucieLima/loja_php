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

    private function saveProduct() {
        self::create($this->data);
    }

}
