<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Models\Admin\Usuario;

/**
 * Description of ProfileController
 *
 * @author jucie
 */
class ProfileController extends Controller{
    public function index() {
        $this->viewData = $_SESSION['userlogin'];
        $this->getView("admin/usuarios/perfil", 'admin/'.TEMPLATE);
    }
    
    public function delete() {
        $userid = filter_input(INPUT_POST, 'user', FILTER_VALIDATE_INT);
        $user = new Usuario();
        $delete = $user->excluirPerfil($userid);
        if ($delete):
            echo $user->getResult();
        else:
            echo $user->getError();
        endif;
    }
}
