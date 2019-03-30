<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Loja\Clientes;

/**
 * Description of UserController
 *
 * @author jucie
 */
class UserController extends Controller {

    public function __construct() {
        if (isset($_SESSION['client']) || filter_input(INPUT_COOKIE,'clientlogin')):
            header("Location: " . BASE_URL . "checkout");
        endif;
    }

    public function login() {
        $this->viewData['scripts'] = [BASE_URL . 'assets/js/login-loja.js'];
        $this->getView('loja/login', 'loja/' . TEMPLATE);
    }
    
    public function logout() {
        $cliente = new Clientes;
        $cliente->logout();
    }

    public function login_form() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);        
        $cliente = new Clientes;
        $cliente->loginClient($dados);
        if ($cliente->getResult()):
            $retorno['success'] = true;
            $retorno['result'] = $cliente->getResult();
        else:
            $retorno['success'] = false;
            $retorno['error'] = $cliente->getError();
        endif;
        echo json_encode($retorno);
    }

    public function signup_form() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $cliente = new Clientes;
        $cliente->createCliente($dados);
        if ($cliente->getResult()):
            $retorno['success'] = true;
            $retorno['result'] = $cliente->getResult();
        else:
            $retorno['success'] = false;
            $retorno['error'] = $cliente->getError();
        endif;
        echo json_encode($retorno);
    }

}
