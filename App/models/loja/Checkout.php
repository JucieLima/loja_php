<?php

namespace App\Models\Loja;

use App\Models\Loja\Clientes;
use App\Models\Loja\cart;
use App\Helpers\ResizeImage;
use App\Models\Admin\Estado;

/**
 * Description of Checkout
 *
 * @author jucie
 */
class Checkout {
    
    private $viewData;

    public function getViewData() {
        $cart = new cart;
        $estados = new Estado;
        $clientes = new Clientes;
        
        $this->viewData['list'] = $cart->getList();
        $this->viewData['estados'] = $estados->all()->toArray();
        $this->viewData['client'] = $clientes->getClienteView();        
        $this->viewData['cropper'] = new ResizeImage;

        $this->viewData['sub_total'] = 0;
        $this->viewData['discount'] = 0;
        $this->viewData['frete'] = 0;

        if (isset($_SESSION['coupon']) && $_SESSION['coupon']['error'] == false):
            $this->viewData['discount_coupon'] = $_SESSION['coupon']['coupon_discount'];
            $this->viewData['display_c'] = 'style="display: block"';
        endif;

        if (isset($_SESSION['shipping']) && $_SESSION['shipping']['error'] == false):
            $this->viewData['frete'] = (float) str_replace('R$ ', '', str_replace(',', '.', $_SESSION['shipping']['valor']));
            $this->viewData['frete_value'] = str_replace('R$ ', '', $_SESSION['shipping']['valor']);
            $this->viewData['display_s'] = 'style="display: block"';
        endif;  
        
        return $this->viewData;
    }

}
