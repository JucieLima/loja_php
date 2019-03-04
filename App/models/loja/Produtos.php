<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Produtos
 *
 * @author jucie
 */
class Produtos extends Model {

    protected $table = 'produtos';
    protected $primaryKey = 'id_produto';
    protected $guarded = ['id_produto', 'created_at', 'updated_at'];
    private $data;
    private $files;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getProduct(int $id) {
        if (self::find($id)):
            return self::find($id)->toArray();
        endif;
    }

    public function getProdutos($limit, $offset) {
        return self::join('categorias', 'id_categoria', '=', 'categoria_produto')
                        ->join('marcas', 'id_marca', '=', 'marca_produto')
                        ->join('produto_imagens', 'id_produto', '=', 'imagem_produto')
                        ->where("imagem_main", 1)
                        ->where("ativo_produto", 1)
                        ->select('produtos.*', 'categorias.titulo_categoria', 'marcas.titulo_marca', 'produto_imagens.imagem_uri')
                        ->limit($limit)
                        ->offset($offset)
                        ->inRandomOrder()
                        ->get()
                        ->toArray();
    }

    public function getProductByUri($url) {
        $this->data = filter_var($url, FILTER_SANITIZE_STRING);
        $prod = self::where('path_produto', $this->data)->where("ativo_produto",1)->get()->toArray();
        if ($prod):
            $this->result = $prod;
            return $prod;
        else:
            $this->error = "Produto não encontrado!";
            return false;
        endif;
    }

    public function searchProducts($value, $limit, $offset): array {
        $codigo = (int) $value;
        $result['page'] = self::
                join('categorias', 'id_categoria', '=', 'categoria_produto')
                ->join('marcas', 'id_marca', '=', 'marca_produto')
                ->join('produto_imagens', 'id_produto', '=', 'imagem_produto')
                ->select('produtos.*', 'categorias.titulo_categoria', 'marcas.titulo_marca', 'produto_imagens.imagem_uri')
                ->where("imagem_main", 1)
                ->where("ativo_produto", 1)
                ->where('titulo_produto', 'LIKE', '%' . $value . '%')
                ->limit($limit)
                ->offset($offset)
                ->orderBy('titulo_produto', 'ASC')
                ->get()
                ->toArray();
        $result['total'] = self::
                where('titulo_produto', 'LIKE', '%' . $value . '%')
                ->orWhere('id_produto', '=', $codigo)
                ->get()
                ->toArray();
        return $result;
    }

    public function getRecommended(): array {
        $recommended = self::join('produto_imagens', 'id_produto', '=', 'imagem_produto')
                ->select('id_produto', 'titulo_produto', 'preco_venda_produto', 'desconto_produto', 'ativo_produto', 'path_produto', 'sale_produto', 'imagem_uri')
                ->where('sale_produto', 1)
                ->where('ativo_produto', 1)
                ->where("imagem_main", 1)
                ->inRandomOrder()
                ->limit(12)
                ->get()
                ->toArray();
        return $recommended;
    }

    public function getListOfBrands(): array {
        $list = self::join('marcas', 'id_marca', '=', 'marca_produto')
                ->select(DB::raw('count(marca_produto) as total, id_marca,titulo_marca'))
                ->groupBy("marca_produto")
                ->orderBy('titulo_marca', 'ASC')
                ->where('ativo_produto', 1)
                ->get()
                ->toArray();
        return $list;
    }

    public function getPriceRange() {
        $this->result = [];
        $this->getMinRange();
        $this->getMaxRange();
        return $this->result;
    }

    private function getMinRange() {
        $this->result['min'] = self::select(DB::raw('MIN(preco_venda_produto - desconto_produto) as value'))->where("ativo_produto", 1)->get()->toArray()[0];
    }

    private function getMaxRange() {
        $this->result['max'] = self::select(DB::raw('MAX(preco_venda_produto - desconto_produto) as value'))->where("ativo_produto", 1)->get()->toArray()[0];
    }

}
