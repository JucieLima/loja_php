<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Loja\Checkout;
use App\Models\Loja\Vendas;

/**
 * Description of CheckoutController
 *
 * @author jucie
 */
class CheckoutController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['client']) && !filter_input(INPUT_COOKIE, 'clientlogin')):
            header("Location: " . BASE_URL . "user/login");
        endif;
    }

    public function index() {
        if (!isset($_SESSION['cart']) || $_SESSION['cart']['total'] == 0):
            header("Location: " . BASE_URL);
        else:
            $checkout = new Checkout;
            $this->viewData = $checkout->getViewData();
            $this->viewData['scripts'] = [BASE_URL . 'assets/js/checkout.js'];
            $this->getView('loja/checkout', 'loja/' . TEMPLATE);
        endif;
    }

    public function payment_start() {
        $form = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $sale = new Vendas;
        if ($sale->startForSale($form)):
            $json['sale'] = $sale->getResult();
            $json['result'] = true;
        else:
            $json['error'] = $sale->getError();
            $json['result'] = false;
        endif;
        echo json_encode($json);
    }

}
