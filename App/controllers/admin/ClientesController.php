<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Cliente;
use App\Models\Admin\Estado;
use App\Models\Admin\Paises;

/**
 * Description of Usuarios
 *
 * @author jucie
 */
class ClientesController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 2):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $cliente = new Cliente;
        $offset = 0;
        $limit = 10;
        $paginas = ceil(count($cliente::all()) / $limit);
        $this->viewData['clientes'] = $cliente->getClientes($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = 1;
        $this->getView('admin/clientes/clientes', 'admin/' . TEMPLATE);
    }

    public function editar($id) {
        $cliente = new Cliente;
        $this->viewData['cliente'] = $cliente->getCliente($id);
        if ($this->viewData['cliente']):
            $estados = new Estado;
            $this->viewData['estados'] = $estados->all()->toArray();
            $this->getView('admin/clientes/editar', 'admin/' . TEMPLATE);
        else:
            $this->getView('admin/404', 'admin/'.TEMPLATE);
        endif;
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $user = new Cliente;
        $user->updateCliente($dados);
        if ($user->getResult()):
            echo $user->getResult()['id_cliente'];
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
        $cliente = new Cliente;
        $p = $page && filter_var($page, FILTER_VALIDATE_INT) ? filter_var($page, FILTER_VALIDATE_INT) : 1;
        $limit = 10;
        $offset = ($p - 1) * $limit;
        $paginas = ceil(count($cliente::all()) / $limit);
        $this->viewData['clientes'] = $cliente->getClientes($limit, $offset);
        $this->viewData['offset'] = $offset;
        $this->viewData['limit'] = $limit;
        $this->viewData['paginas'] = $paginas;
        $this->viewData['pagina'] = $p;
        $this->getView('admin/clientes/clientes', 'admin/' . TEMPLATE);
    }

}
