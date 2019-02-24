<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Upload;
use App\Models\Admin\ProdutoImagens;
use App\Helpers\Check;
use App\Models\Admin\VendaItens;

/**
 * Description of Produtos
 *
 * @author jucie
 */
class Produtos extends Model {

    protected $table = 'produtos';
    protected $primaryKey = 'id_produto';
    protected $fillable = [
        'categoria_produto',
        'fornecedor_produto',
        'marca_produto',
        'titulo_produto',
        'preco_custo_produto',
        'preco_venda_produto',
        'desconto_produto',
        'descricao_produto',
        'detalhes_produto',
        'quantidade_produto',
        'qtd_min_produto',
        'ativo_produto',
        'altura_produto',
        'largura_produto',
        'comprimento_produto',
        'peso_produto',
        'ultima_venda_produto',
        'qtd_vendidos_produto',
        'path_produto'
    ];
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

    public function getProdutos($limit, $offset, $orderBy = null, $ascending = null) {
        $order = $orderBy ? $orderBy : 'created_at';
        $asc = $ascending ? $ascending : 'ASC';
        return self::limit($limit)->offset($offset)->orderBy($order, $asc)->get()->toArray();
    }

    public function salvar(array $dados) {
        $this->data = $dados;
        if (empty($_FILES['imagem_produto']['name'][0])):
            $this->error = 'Você precisa enviar pelo menos uma imagem para cadastrar um produto.';
        elseif ($this->mainData() && $this->weightsAndMeasures()):
            $this->setUrl();
            if ($this->exeSalvar()):
                $this->sendImages();
                return true;
            endif;
        endif;
    }

    public function atualizar(array $dados) {
        $this->data = $dados;
        if ($this->mainData() && $this->weightsAndMeasures()):
            $this->setUrl();
            if ($this->exeUpdate()):
                $this->sendImages(0);
                return true;
            endif;
        endif;
    }

    public function excluir(int $id) {
        $this->data = $id;
        $find = self::find($this->data);
        if (!$find):
            $this->error = "Erro ao excluir, produto não encontrado.";
        elseif (!$this->checkSales()):
            $this->deleteImages();
            $this->deleteProduct();
            return true;
        endif;
    }

    public function searchProducts($value, $limit, $offset) {
        $codigo = (int) $value;
        $result['page'] = self::where('titulo_produto', 'LIKE', '%' . $value . '%')->orWhere('id_produto', '=', $codigo)->limit($limit)->offset($offset)->orderBy('titulo_produto', 'ASC')->get()->toArray();
        $result['total'] = self::where('titulo_produto', 'LIKE', '%' . $value . '%')->get()->toArray();
        return $result;
    }

    private function weightsAndMeasures() {
        $this->data['preco_custo_produto'] = floatval(str_replace(',', '.', str_replace('.', '', $this->data['preco_custo_produto'])));
        $this->data['preco_venda_produto'] = floatval(str_replace(',', '.', str_replace('.', '', $this->data['preco_venda_produto'])));
        $this->data['desconto_produto'] = floatval(str_replace(',', '.', str_replace('.', '', $this->data['desconto_produto'])));
        $this->data['qtd_min_produto'] = floatval(str_replace(',', '.', $this->data['qtd_min_produto']));
        $this->data['peso_produto'] = floatval(str_replace(',', '.', $this->data['peso_produto']));
        $this->data['quantidade_produto'] = (int) filter_var($this->data['quantidade_produto'], FILTER_SANITIZE_NUMBER_INT);
        $this->data['altura_produto'] = (int) filter_var($this->data['altura_produto'], FILTER_SANITIZE_NUMBER_INT);
        $this->data['largura_produto'] = (int) filter_var($this->data['largura_produto'], FILTER_SANITIZE_NUMBER_INT);
        $this->data['comprimento_produto'] = (int) filter_var($this->data['comprimento_produto'], FILTER_SANITIZE_NUMBER_INT);
        $diferenca = ($this->data['preco_venda_produto'] - $this->data['preco_custo_produto']);

        if ($this->data['preco_venda_produto'] <= $this->data['preco_custo_produto']):
            $this->error = 'Preço de venda inválido.';
        elseif ($this->data['desconto_produto'] > $diferenca):
            $this->error = 'O valor do <strong>desconto</strong> precisa ser menor do que a diferença entre o preço de venda e o preço de custo do produto.';
        else:
            return true;
        endif;
    }

    private function mainData() {
        if ($this->data['categoria_produto'] == ''):
            $this->error = 'Selecione uma categoria para este produto.';
        elseif ($this->data['fornecedor_produto'] == ''):
            $this->error = 'Selecione um fornecedor para este produto.';
        elseif ($this->data['marca_produto'] == ''):
            $this->error = 'Selecione uma marca para este produto.';
        elseif ($this->data['titulo_produto'] == ''):
            $this->error = 'Digite um nome para este produto.';
        elseif ($this->data['preco_custo_produto'] == ''):
            $this->error = 'Informe o preço de custo do produto.';
        elseif ($this->data['preco_venda_produto'] == ''):
            $this->error = 'Informe o preço de venda para este produto.';
        elseif ($this->data['descricao_produto'] == ''):
            $this->error = 'Faça uma breve descrição para este produto.';
        elseif ($this->data['detalhes_produto'] == ''):
            $this->error = 'Descreva os detalhes do produto.';
        else:
            return true;
        endif;
    }

    private function exeSalvar() {
        $save = self::create($this->data);
        if ($save):
            $this->result = $save->toArray();
            return true;
        else:
            $this->error = 'Erro ao salvar produto no banco de dados.';
        endif;
    }

    public function exeUpdate() {
        $update = self::find($this->data['id_produto']);
        $update->update($this->data);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'Erro ao atualizar produto no banco de dados.';
        endif;
    }

    private function setUrl() {
        $this->data['path_produto'] = Check::uri($this->data['titulo_produto']);
        if (isset($this->data['id_produto'])):
            $read = self::where('id_produto', '<>', $this->data['id_produto'])->where('titulo_produto', $this->data['titulo_produto'])->get()->toArray();
            if ($read):
                $this->data['path_produto'] = $this->data['path_produto'] . '-' . substr(md5(time()), 0, 5);
            endif;
        else:
            $read = self::where('titulo_produto', $this->data['titulo_produto'])->get()->toArray();
            if ($read):
                $this->data['path_produto'] = $this->data['path_produto'] . '-' . substr(md5(time()), 0, 5);
            endif;
        endif;
    }

    private function sendImages($update = null) {
        if (!empty($_FILES['imagem_produto']['name'][0])):
            $this->setArrayImages();
            $upload = new Upload;
            $imagesProduct = new ProdutoImagens;
            $c = 1;
            $cover = $update ?? 1;
            foreach ($this->files as $imageSend):
                $imageName = $this->result['id_produto'] . $c . substr(md5(time()), 0, 5);
                $c++;
                $upload->image($imageSend, $imageName);
                if ($upload->getResult()):
                    $imagesProduct->sendImage($this->result['id_produto'], $upload->getResult(), $cover);
                    $cover = 0;
                else:
                    $this->error = $upload->getError();
                endif;
            endforeach;
        endif;
    }

    private function setArrayImages() {
        $arrayKeys = array_keys($_FILES['imagem_produto']);
        $files = $_FILES['imagem_produto'];
        for ($i = 0; $i < count($files['name']); $i++):
            foreach ($arrayKeys as $key):
                $this->files[$i][$key] = $files[$key][$i];
            endforeach;
        endfor;
    }

    private function checkSales() {
        $venda = new VendaItens;
        $check = $venda::where("produto_vi", $this->data)->get()->toArray();
        if ($check):
            $this->error = "Este produto já foi vendido, portanto não poderá ser excluído. Você ainda poderá desativa-lo para que não fique dispónivel na loja.";
            return true;
        endif;
    }

    private function deleteImages() {
        $imagesProduct = new ProdutoImagens;
        $images = $imagesProduct::where("imagem_produto", $this->data)->get()->toArray();
        foreach ($images as $images):
            if (file_exists('uploads/' . $images['imagem_uri'])):
                unlink('uploads/' . $images['imagem_uri']);
            endif;
            $imagesProduct::find($images['imagem_id'])->delete();
        endforeach;
    }

    private function deleteProduct() {
        $delete = self::find($this->data);
        if ($delete):
            $this->result = $delete->toArray();
            $delete->delete();
        endif;
    }

}
