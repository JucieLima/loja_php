<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Fabricantes;

/**
 * Description of CategoriasController
 *
 * @author jucie
 */
class FabricantesController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 2):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $fab = new Fabricantes;
        $offset = 0;
        $limit = 10;
        $paginas = ceil(count($fab::all()) / $limit);
        $this->viewData['brands'] = $fab->getBrands($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->getView('admin/fabricantes/fabricantes', 'admin/' . TEMPLATE);
    }

    public function cadastro() {
        $this->getView('admin/fabricantes/cadastrar', 'admin/' . TEMPLATE);
    }

    public function save() {
        $titulo = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $fab = new Fabricantes;
        if ($fab->salvar($titulo)):
            echo $fab->getResult()['id_marca'];
        else:
            echo $fab->getError();
        endif;
    }

    public function editar($id) {
        $fab = new Fabricantes;
        $this->viewData = $fab->getBrand($id);
        if ($this->viewData):
            $this->getView('admin/fabricantes/editar', 'admin/' . TEMPLATE);
        else:
            $this->getView('admin/404', 'admin/' . TEMPLATE);
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $fab = new Fabricantes;
        $update = $fab->atualizar($dados);
        if ($update):
            echo $fab->getResult()['id_marca'];
        else:
            echo $fab->getError();
        endif;
    }

    public function pagina($page = null) {
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? filter_var($page, FILTER_VALIDATE_INT) : 1;
        $fab = new Fabricantes;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $paginas = ceil(count($fab::all()) / $limit);
        $this->viewData['brands'] = $fab->getBrands($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->getView('admin/fabricantes/fabricantes', 'admin/' . TEMPLATE);
    }

    public function delete($id) {
        $fab = new Fabricantes;
        $delete = $fab->excluir($id);
        if ($delete):
            echo $fab->getResult();
        else:
            echo $fab->getError();
        endif;
    }

}
