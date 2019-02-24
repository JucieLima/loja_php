<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Usuario;

/**
 * Description of Usuarios
 *
 * @author jucie
 */
class UsuariosController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 1):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $usuarios = new Usuario();        
        $offset = 0;
        $limit = 10;
        $paginas = ceil(count($usuarios::all())/ $limit);        
        $this->viewData['users'] = $usuarios->getUsers($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->getView('admin/usuarios/usuarios', 'admin/' . TEMPLATE);
    }

    public function cadastro() {
        $this->getView('admin/usuarios/cadastrar', 'admin/' . TEMPLATE);
    }

    public function editar($id) {
        $iduser = filter_var($id, FILTER_VALIDATE_INT);
        $usuario = new Usuario;
        $this->viewData = $usuario->getUser($iduser);
        $this->getView('admin/usuarios/editar', 'admin/' . TEMPLATE);
    }

    public function save() {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $user = new Usuario;

        $save = $user->salvar($dados);
        if ($save):
            echo $user->getResult()['id_usuario'];
        else:
            echo $user->getError();
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $user = new Usuario;

        $update = $user->updateUser($dados);
        if ($update):
            echo $dados['id_usuario'];
        else:
            echo $user->getError();
        endif;
    }

    public function delete() {
        $userid = filter_input(INPUT_POST, 'user', FILTER_VALIDATE_INT);
        $user = new Usuario();
        $delete = $user->excluir($userid);
        if ($delete):
            echo $user->getResult();
        else:
            echo $user->getError();
        endif;
    }

    public function pagina($page = null) {
        $usuarios = new Usuario();
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? $page : 1;       
        $limit = 10;
        $offset = ($p-1)*$limit;
        $paginas = ceil(count($usuarios::all())/ $limit);
        $this->viewData['users'] = $usuarios->getUsers($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->getView('admin/usuarios/usuarios', 'admin/' . TEMPLATE);
    }

}
