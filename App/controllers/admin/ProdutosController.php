<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Categorias;
use App\Models\Admin\Fornecedores;
use App\Models\Admin\Fabricantes;
use App\Models\Admin\Produtos;
use App\Models\Admin\ProdutoImagens;

/**
 * Description of ProdutosController
 *
 * @author jucie
 */
class ProdutosController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 2):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $produtos = new Produtos;
        $offset = 0;
        $limit = 10;
        $this->viewData['produtos'] = $produtos->getProdutos($limit, $offset);
        $paginas = ceil(count($produtos::all()) / $limit);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->viewData['sort'] = '';
        $this->viewData['ordem'] = 'padrão';
        $this->getView('admin/produtos/produtos', 'admin/' . TEMPLATE);
    }

    public function pagina($page, $sort = null, $ascending = null) {
        $orderBy = ($sort == 'price' ? 'preco_venda_produto' : ($sort == 'status' ? 'ativo_produto' : 'created_at'));
        $asc = ($ascending == 'asc' ? 'ASC' : ($ascending == 'desc' ? 'DESC' : null));
        $produtos = new Produtos;
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? $page : 1;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $paginas = ceil(count($produtos::all()) / $limit);
        $this->viewData['produtos'] = $produtos->getProdutos($limit, $offset, $orderBy, $asc);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->viewData['sort'] = $sort . '/' . $ascending == '/' ? '' : $sort . '/' . $ascending;
        $this->viewData['ordem'] = ($sort == 'default' ? 'data de criação' : ($sort == 'price' ? 'preço' : ($sort == 'status' ? 'status' : 'padrão')));
        $this->getView('admin/produtos/produtos', 'admin/' . TEMPLATE);
    }

    public function pesquisar($page, $npage, $pesquisa) {
        unset($page);
        $produtos = new Produtos;
        $p = $npage && filter_var($npage, FILTER_VALIDATE_INT) ? $npage : 1;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $search = $produtos->searchProducts($pesquisa, $limit, $offset);
        $this->viewData['produtos'] = $search['page'];
        $paginas = ceil(count($search['total']) / $limit);
        $this->viewData['total'] = count($search['total']);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->viewData['pesquisa'] = $pesquisa;
        $this->getView('admin/produtos/pesquisa', 'admin/' . TEMPLATE);
    }

    public function cadastro() {
        $categorias = new Categorias;
        $fabricantes = new Fabricantes;
        $fornecedores = new Fornecedores;
        $this->viewData['categorias'] = $categorias::all()->sortBy('id_categoria')->toArray();
        $this->viewData['fabricantes'] = $fabricantes::all()->sortBy('id_marca')->toArray();
        $this->viewData['fornecedores'] = $fornecedores::all()->sortBy('id_fornecedor')->toArray();
        $this->getView('admin/produtos/cadastrar', 'admin/' . TEMPLATE);
    }

    public function save() {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $produto = new Produtos;
        $salvar = $produto->salvar($dados);
        if ($salvar):
            $retorno['response'] = 'success';
            $retorno['produto'] = $produto->getResult();
        else:
            $retorno['response'] = $produto->getError();
        endif;
        echo json_encode($retorno);
    }

    public function editar($id) {
        $categorias = new Categorias;
        $fabricantes = new Fabricantes;
        $fornecedores = new Fornecedores;
        $produto = new Produtos;
        $produtoImagens = new ProdutoImagens;
        $this->viewData['produto'] = $produto->getProduct($id);
        if ($this->viewData['produto']):
            $this->viewData['categorias'] = $categorias::all()->sortBy('id_categoria')->toArray();
            $this->viewData['fabricantes'] = $fabricantes::all()->sortBy('id_marca')->toArray();
            $this->viewData['fornecedores'] = $fornecedores::all()->sortBy('id_fornecedor')->toArray();
            $this->viewData['imagens'] = $produtoImagens->getImages($id);
            $this->getView('admin/produtos/editar', 'admin/' . TEMPLATE);
        else:
            $this->getView('admin/404', 'admin/' . TEMPLATE);
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if ($dados):
            $produto = new Produtos;
            $atualizar = $produto->atualizar($dados);
            if ($atualizar):
                $retorno['response'] = 'success';
                $retorno['produto'] = $produto->getResult();
            else:
                $retorno['response'] = $produto->getError();
            endif;
        else:
            $retorno['response'] = 'Dados não processados, tente enviar uma quantidade menor de arquivos!';
        endif;

        echo json_encode($retorno);
    }

    public function delete() {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $produto = new Produtos;
        $excluir = $produto->excluir($id);
        if ($excluir):
            $retorno['response'] = 'success';
        else:
            $retorno['response'] = $produto->getError();
        endif;
        echo json_encode($retorno);
    }

    public function delete_image() {
        $imageid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $image = new ProdutoImagens;
        $excluir = $image->excluir($imageid);
        if ($excluir):
            $retorno['response'] = 'success';
            $retorno['result'] = $image->getResult();
        else:
            $retorno['response'] = $image->getError();
        endif;
        echo json_encode($retorno);
    }

    public function update_cover() {
        $imageid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $image = new ProdutoImagens;
        $excluir = $image->updateCover($imageid);
        if ($excluir):
            $retorno['response'] = 'success';
            $retorno['result'] = $image->getResult();
        else:
            $retorno['response'] = $image->getError();
        endif;
        echo json_encode($retorno);
    }

    public function read_images() {
        $id = filter_input(INPUT_POST, 'idproduto', FILTER_VALIDATE_INT);
        $produtoImagens = new ProdutoImagens;
        $this->viewData = $produtoImagens->getImages($id);
        $this->getView('admin/produtos/images');
    }

}
