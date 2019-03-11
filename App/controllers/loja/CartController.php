<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Loja\Shipping;

/**
 * Description of CartController
 *
 * @author jucie
 */
class CartController extends Controller{
   
    public function add() {
        $quantity = filter_input(INPUT_POST, 'quant', FILTER_VALIDATE_INT);
        $product= filter_input(INPUT_POST, 'product', FILTER_VALIDATE_INT);
        
        if(!isset($_SESSION['cart'])):
            $_SESSION['cart']['total'] = $quantity;
        else:
            $_SESSION['cart']['total'] += $quantity;
        endif;
        
        if(!isset($_SESSION['cart'][$product])):
            $_SESSION['cart'][$product] = $quantity;
        else:
            $_SESSION['cart'][$product] += $quantity;
        endif;     
    }
    
    public function shipping() {
        $cep = str_replace('-', '', filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_NUMBER_INT));
        $shipping = new Shipping;
        echo json_encode($shipping->calc($cep));
    }
    
}
