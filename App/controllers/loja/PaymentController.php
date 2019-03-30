<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use PagSeguro\Services\Session;
use PagSeguro\Configuration\Configure;

/**
 * Description of PaymentController
 *
 * @author jucie
 */
class PaymentController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['client']) || !filter_input(INPUT_COOKIE, 'clientlogin')):
            header("Location: " . BASE_URL . "user/login");
        endif;
    }

    public function index() {
        try {
            $sessionCode = Session::create(Configure::getAccountCredentials());
            $this->viewData['sessionCode'] = $sessionCode;
        } catch (Exception $ex) {
            echo 'Erro: ' . $ex->getMessage();
        }
        $this->viewData['scripts'] = [
            BASE_URL . 'assets/js/checkout.js',
            'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
            BASE_URL . 'assets/js/pagseguroSession.js'
        ];
    }

}
