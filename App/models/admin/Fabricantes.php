<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Fabricantes
 *
 * @author jucie
 */
class Fabricantes extends Model {

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

    public function salvar(array $dados) {
        $this->dados = $dados;
        if (empty($this->dados['titulo_marca'])):
            $this->error = 'errempty';
        elseif ($this->checkTitle()):
            $this->error = 'errisset';
        else:
            $brand = self::create($this->dados);
            if ($brand):
                $this->result = $brand->toArray();
                return true;
            else:
                $this->error = 'error';
                return false;
            endif;
        endif;
    }

    public function atualizar(array $dados) {
        $this->dados = $dados;
        if (empty($this->dados['titulo_marca'])):
            $this->error = 'errempty';
        elseif ($this->checkTitleUpdate()):
            $this->error = 'errisset';
        else:
            $update = self::find($this->dados['id_marca']);
            $update->update($this->dados);
            if ($update):
                $this->result = $update->toArray();
                return true;
            else:
                $this->error = 'error';
                return false;
            endif;
        endif;
    }

    public function getBrand($id) {
        $this->dados = filter_var($id, FILTER_VALIDATE_INT);
        $find = self::find($id)->toArray();
        if ($find):
            return $find;
        endif;
    }
    
    public function getBrands($limit, $offset) {
        return self::limit($limit)->offset($offset)->orderBy('id_marca', 'ASC')->get()->toArray();
    }
    
    public function excluir(int $id) {
        $this->dados = $id;
        if ($this->checkProducts()):
            $this->error = 'errproducts';
        else:
            $this->exeDelete();
            return true;
        endif;
        return false;
    }

    private function checkTitle() {
        $find = self::where("titulo_marca", $this->dados['titulo_marca'])->get()->toArray();
        if ($find):
            return true;
        else:
            return false;
        endif;
    }
    
    private function checkTitleUpdate() {
        $find = self::where("titulo_marca",'=', $this->dados['titulo_marca'])->where("id_marca",'<>', $this->dados['id_marca'])->get()->toArray();
        if ($find):
            return true;
        else:
            return false;
        endif;
    }
    
    private function checkProducts() {
        $products = DB::table("produtos")->where("marca_produto", $this->dados)->get()->toArray();
        if ($products):
            return true;
        else:
            return false;
        endif;
    }
    
    private function exeDelete() {
        $delete = self::find($this->dados);
        if ($delete->delete()):
            $this->result = 'success';
        else:
            $this->error = 'error';
        endif;
    }

}
