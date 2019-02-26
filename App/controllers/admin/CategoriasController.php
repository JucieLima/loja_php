<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Categorias;

/**
 * Description of CategoriasController
 *
 * @author jucie
 */
class CategoriasController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 2):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $cat = new Categorias;               
        $offset = 0;
        $limit = 10;
        $paginas = ceil(count($cat::all())/ $limit);        
        $this->viewData['categorias'] = $cat->getCategories($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->getView('admin/categorias/categorias', 'admin/' . TEMPLATE);
    }

    public function cadastro() { 
        $this->viewData = new Categorias;
        $this->getView('admin/categorias/cadastrar', 'admin/' . TEMPLATE);
    }

    public function save() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $cat = new Categorias;
        $save = $cat->salvar($dados);
        if ($save):
            echo $cat->getResult()['id_categoria'];
        else:
            echo $cat->getError();
        endif;
    }

    public function editar($id) {
        $idcat = filter_var($id, FILTER_VALIDATE_INT);
        $cat = new Categorias;
        $this->viewData['categoria'] = $cat->getCat($idcat);
        if ($this->viewData['categoria']):
            $this->viewData['categorias'] = new Categorias;
            $this->getView('admin/categorias/editar', 'admin/' . TEMPLATE);
        else:
            $this->getView('admin/404', 'admin/' . TEMPLATE);
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $cat = new Categorias;
        $update = $cat->atualizar($dados);
        if ($update):
            echo $cat->getResult()['id_categoria'];
        else:
            echo $cat->getError();
        endif;
    }
    
    public function pagina($page = null) {
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? filter_var($page, FILTER_VALIDATE_INT) : 1;
        $cat = new Categorias;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $paginas = ceil(count($cat::all()) / $limit);
        $this->viewData['categorias'] = $cat->getCategories($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->getView('admin/categorias/categorias', 'admin/' . TEMPLATE);
    }
    
    public function delete() {
        $catid = filter_input(INPUT_POST, 'catid', FILTER_VALIDATE_INT);
        $cat = new Categorias;
        $delete = $cat->excluir($catid);
        if ($delete):
            echo $cat->getResult();
        else:
            echo $cat->getError();
        endif;
    }

}
