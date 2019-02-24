<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Fornecedores;
use App\Models\Admin\Estado;
use App\Models\Admin\Paises;

/**
 * Description of FornecedoresController
 *
 * @author jucie
 */
class FornecedoresController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 2):
            header('Location: ' . BASE_URL . 'admin/forbidden');
        endif;
    }

    public function index() {
        $fornecedor = new Fornecedores;
        $limit = 10;
        $offset = 0;
        $this->viewData['fornecedores'] = $fornecedor->getAll($limit, $offset);
        $paginas = ceil(count($fornecedor::all()) / $limit);
        $this->viewData['fornecedores'] = $fornecedor->getAll($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->getView('admin/fornecedores/fornecedores', 'admin/' . TEMPLATE);
    }

    public function pagina($page = null) {
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? filter_var($page, FILTER_VALIDATE_INT) : 1;
        $fornecedor = new Fornecedores;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $paginas = ceil(count($fornecedor::all()) / $limit);
        $this->viewData['fornecedores'] = $fornecedor->getAll($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->getView('admin/fornecedores/fornecedores', 'admin/' . TEMPLATE);
    }

    public function cadastro() {
        $estados = new Estado;
        $paises = new Paises;
        $this->viewData['estados'] = $estados->all()->toArray();
        $this->viewData['paises'] = $paises->all()->toArray();
        $this->getView('admin/fornecedores/cadastrar', 'admin/' . TEMPLATE);
    }

    public function save() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $for = new Fornecedores;
        if ($for->salvar($dados)):
            echo $for->getResult()['id_fornecedor'];
        else:
            echo $for->getError();
        endif;
    }

    public function editar($id) {
        $for = new Fornecedores;
        $fornecedor = $for->getFornecedor($id);
        if ($fornecedor):
            $this->viewData = $fornecedor;
            $this->getView('admin/fornecedores/editar', 'admin/' . TEMPLATE);
        else:
            $this->getView('admin/404', 'admin/' . TEMPLATE);
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $fornecedor = new Fornecedores;
        $update = $fornecedor->utualizar($dados);
        if ($update):
            $retorno['fornecedor'] = $fornecedor->getResult();
            $retorno['response'] = 'success';
        else:
            $retorno['response'] = $fornecedor->getError();
        endif;
        echo json_encode($retorno);
    }

    public function delete($id) {
        $for = new Fornecedores;
        $fornecedor = $for->deleteFornecedor($id);
        if ($fornecedor):
            $retorno['response'] = $for->getResult();
        else:
            $retorno['response'] = $for->getError();
        endif;
        echo json_encode($retorno);
    }

}
