<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Loja\Shipping;
use App\Models\Loja\cart;
use App\Helpers\ResizeImage;
use App\Models\Loja\Coupons;

/**
 * Description of CartController
 *
 * @author jucie
 */
class CartController extends Controller {

    public function index() {
        $cart = new cart;
        $this->viewData['list'] = $cart->getList();
        $this->viewData['cropper'] = new ResizeImage;

        $this->viewData['display_c'] = 'style="display: none"';
        $this->viewData['display_s'] = 'style="display: none"';
        $this->viewData['sub_total'] = 0;
        $this->viewData['discount'] = 0;
        $this->viewData['coupon_discount'] = 0;
        $this->viewData['frete'] = 0;

        if (isset($_SESSION['coupon']) && $_SESSION['coupon']['error'] == false):            
            $this->viewData['coupon_discount'] = $_SESSION['coupon']['coupon_discount'];
            $this->viewData['display_c'] = 'style="display: block"';
        endif;

        if (isset($_SESSION['shipping']) && $_SESSION['shipping']['error'] == false):
            $this->viewData['frete'] = (float) str_replace('R$ ', '', str_replace(',', '.', $_SESSION['shipping']['valor']));
            $this->viewData['frete_value'] = str_replace('R$ ', '', $_SESSION['shipping']['valor']);
            $this->viewData['display_s'] = 'style="display: block"';
        endif;
        

        $this->getView('loja/cart', 'loja/' . TEMPLATE);
    }

    public function add() {
        $quantity = filter_input(INPUT_POST, 'quant', FILTER_VALIDATE_INT);
        $product = filter_input(INPUT_POST, 'product', FILTER_VALIDATE_INT);

        $cart = new cart;
        $cart->cartAdd($product, $quantity);
    }
    
    public function decrease() {
        $quantity = filter_input(INPUT_POST, 'quant', FILTER_VALIDATE_INT);
        $product = filter_input(INPUT_POST, 'product', FILTER_VALIDATE_INT);

        $cart = new cart;
        $cart->cartDecrease($product, $quantity);
    }
    
    public function remove() {
        $product = filter_input(INPUT_POST, 'product', FILTER_VALIDATE_INT);

        $cart = new cart;
        $cart->cartRemoveItem($product);
    }

    public function shipping() {
        $cep = str_replace('-', '', filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_NUMBER_INT));
        $shipping = new Shipping;
        echo json_encode($shipping->calc($cep));
    }

    public function add_coupon() {
        $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
        $coupon = new Coupons;
        $discount = $coupon->getDiscount($code);
        echo json_encode($discount);
    }

}
