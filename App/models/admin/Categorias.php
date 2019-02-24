<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helpers\Check;

/**
 * Description of Categorias
 *
 * @author jucie
 */
class Categorias extends Model {

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;
    protected $fillable = [
        'titulo_categoria',
        'url_categoria',
        'mae_categoria',
        'ativo_categoria'
    ];
    private $dados;
    private $result;
    private $error;

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    public function getCategories($limit, $offset) {
        return self::limit($limit)->offset($offset)->orderBy('id_categoria', 'ASC')->get()->toArray();
    }

    public function getAll() {
        $array = [];
        $cats = self::all()->sortByDesc('mae_categoria')->toArray();
        if ($cats):
            foreach ($cats as $item):
                $item['filhas'] = [];
                $array[$item['id_categoria']] = $item;
            endforeach;

            while ($this->stillNeed($array)):
                $this->organize($array);
            endwhile;
        endif;

        return $array;
    }

    public function getCat($id) {
        if (self::find($id)):
            return self::find($id)->toArray();
        endif;
    }

    public function salvar($dados) {
        $this->dados = $dados;
        if ($this->validarDados()):
            $this->setData();
            $save = self::create($this->dados);
            if ($save):
                $this->result = $save->toArray();
                return true;
            else:
                $this->error = 'errcreate';
            endif;
        endif;
    }

    public function atualizar(array $dados) {
        $this->dados = $dados;
        if ($this->validarDados()):
            $this->setData();
            $update = self::find($this->dados['id_categoria']);
            $update->update($this->dados);
            if (!isset($this->dados['mae_categoria'])):
                $update->mae_categoria = null;
                $update->save();
            endif;
            if ($update):
                $this->result = $update->toArray();
                return true;
            else:
                $this->error = 'error';
            endif;
        endif;
    }

    public function excluir(int $id) {
        $this->dados = $id;
        if ($this->checkDaughters()):
            $this->error = 'errdaughters';
        elseif ($this->checkProducts()):
            $this->error = 'errproducts';
        else:
            $this->exeDelete();
            return true;
        endif;
        return false;
    }

    private function validarDados() {
        $retorno = false;

        if (!filter_var(BASE_URL . $this->dados['url_categoria'], FILTER_VALIDATE_URL) || empty($this->dados['url_categoria'])):
            unset($this->dados['url_categoria']);
        endif;

        $this->dados['mae_categoria'] = filter_var($this->dados['mae_categoria'], FILTER_SANITIZE_NUMBER_INT);
        $this->dados['ativo_categoria'] = filter_var($this->dados['ativo_categoria'], FILTER_SANITIZE_NUMBER_INT);

        if (empty($this->dados['titulo_categoria'])):
            $this->error = 'errtititle';
        else:
            $retorno = true;
        endif;

        return $retorno;
    }

    private function setData() {
        if (!isset($this->dados['url_categoria'])):
            $this->dados['url_categoria'] = Check::uri($this->dados['titulo_categoria']);
        endif;

        if (empty($this->dados['mae_categoria'])):
            unset($this->dados['mae_categoria']);
        elseif (empty($this->dados['ativo_categoria'])):
            $this->dados['ativo_categoria'] = 0;
        endif;
    }

    private function stillNeed($array) {
        foreach ($array as $item):
            if (!empty($item['mae_categoria'])):
                return true;
            endif;
        endforeach;
        return false;
    }

    private function organize(&$array) {
        foreach ($array as $id => $item):
            if (isset($array[$item['mae_categoria']])):
                $array[$item['mae_categoria']]['filhas'][$item['id_categoria']] = $item;
                unset($array[$id]);

                break;
            endif;
        endforeach;
    }

    private function checkDaughters() {
        $daughters = self::where("mae_categoria", $this->dados)->get()->toArray();
        if ($daughters):
            return true;
        else:
            return false;
        endif;
    }

    private function checkProducts() {
        $products = DB::table("produtos")->where("categoria_produto", $this->dados)->get()->toArray();
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
