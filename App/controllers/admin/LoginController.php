<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Login;

/**
 * Description of LoginController
 *
 * @author jucie
 */
class LoginController extends Controller {

    public function index() {
        if (isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL.'admin');
        else:
            $this->getView('admin/page-login');
        endif;
    }

    public function logar() {
        $dados['email_usuario'] = filter_input(INPUT_POST, 'email_usuario', FILTER_DEFAULT);
        $dados['senha_usuario'] = filter_input(INPUT_POST, 'senha_usuario', FILTER_DEFAULT);

        $login = new Login();
        $usuario = $login->logar($dados);
        if ($usuario):
            $_SESSION['userlogin'] = $usuario;
            echo 'success';
        else:
            echo $login->getError();
        endif;
    }

    public function logout() {
        if (isset($_SESSION['userlogin'])):
            unset($_SESSION['userlogin']);
        endif;
        header('Location:' . BASE_URL . 'admin/login');
    }

}
