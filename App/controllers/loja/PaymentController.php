<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use PagSeguro\Services\Session;
use PagSeguro\Configuration\Configure;
use App\Models\Loja\Vendas;
use App\Models\Loja\VendaItens;
use App\Models\Loja\PagSeguro;

/**
 * Description of PaymentController
 *
 * @author jucie
 */
class PaymentController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['client']) && !filter_input(INPUT_COOKIE, 'clientlogin')):
            header("Location: " . BASE_URL . "user/login");
        endif;
    }

    public function order($saleId) {
        try {
            $sessionCode = Session::create(Configure::getAccountCredentials());
            $this->viewData['sessionCode'] = $sessionCode;
        } catch (Exception $ex) {
            echo 'Erro: ' . $ex->getMessage();
        }
        $venda = new Vendas;
        $itens = new VendaItens;
        $this->viewData['sale'] = $venda->getSale($saleId)[0];
        $this->viewData['itens'] = $itens->getItens($saleId);
        $this->viewData['scripts'] = [
            BASE_URL . 'assets/js/checkout.js',
            'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
            BASE_URL . 'assets/js/payment.js'
        ];
        $this->getView('loja/payment', 'loja/' . TEMPLATE);
    }

    public function send() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $pagseguro = new PagSeguro;
        if ($pagseguro->paymentCard($dados)):
            $json['error'] = false;
            $json['result'] = $pagseguro->getResult();
        else:
            $json['error'] = true;
            $json['msg'] = $pagseguro->getError();
        endif;
        echo json_encode($json);
    }
    
    public function obrigado() {
        $this->getView('loja/obrigado', 'loja/' . TEMPLATE);
    }

}
