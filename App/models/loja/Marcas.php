<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;
use App\Models\Loja\Produtos;

/**
 * Description of Fabricantes
 *
 * @author jucie
 */
class Marcas extends Model {

    protected $table = "marcas";
    protected $primaryKey = "id_marca";
    protected $fillable = ['titulo_marca'];
    public $timestamps = false;
    private $dados;
    private $result;
    private $error;

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    public function getBrand($id) {
        $this->dados = filter_var($id, FILTER_VALIDATE_INT);
        $find = self::find($id)->toArray();
        if ($find):
            return $find;
        endif;
    }

}
