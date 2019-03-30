<?php

namespace App\Models\Loja;

use App\Models\Loja\Produtos;

/**
 * Description of cart
 *
 * @author jucie
 */
class cart {

    private $cart;
    private $list = [];

    public function getList() {
        if (isset($_SESSION['cart'])):
            $this->cart = $_SESSION['cart'];
            $this->setList();
            return $this->list;
        else:
            return [];
        endif;
    }

    public function getTotalCart() {
        $total = 0;
        $products = new Produtos;
        if (isset($_SESSION['cart'])):
            foreach ($_SESSION['cart'] as $item => $quantity):
                if ($item !== 'total'):
                    $product = $this->getProduct($item, $products);
                    $total += ($product[0]['preco_venda_produto'] - $product[0]['desconto_produto']) * $quantity;
                endif;
            endforeach;
        endif;
        return $total;
    }

    private function setList() {
        $products = new Produtos;
        foreach ($this->cart as $item => $quantity):
            if ($item !== 'total'):
                $product = $this->getProduct($item, $products);
                $this->list[] = [
                    'id' => $item,
                    'quantity' => $quantity,
                    'price' => ($product[0]['preco_venda_produto'] - $product[0]['desconto_produto']),
                    'total_roduct' => ($product[0]['preco_venda_produto'] - $product[0]['desconto_produto']) * $quantity,
                    'titulo' => $product[0]['titulo_produto'],
                    'image' => $product[0]['imagem_uri'],
                    'path' => $product[0]['path_produto'],
                    'quantity' => $quantity
                ];
            endif;
        endforeach;
    }

    private function getProduct($id_proudct, $products) {
        $product = $products->getProduct($id_proudct);
        return $product;
    }

    public function cartAdd($product, $quantity) {
        if (!isset($_SESSION['cart'])):
            $_SESSION['cart']['total'] = $quantity;
        else:
            $_SESSION['cart']['total'] += $quantity;
        endif;

        if (!isset($_SESSION['cart'][$product])):
            $_SESSION['cart'][$product] = $quantity;
        else:
            $_SESSION['cart'][$product] += $quantity;
        endif;
    }

    public function cartDecrease($product, $quantity) {
        if (!isset($_SESSION['cart'])):
            $_SESSION['cart']['total'] = $quantity;
        else:
            $_SESSION['cart']['total'] -= $quantity;
        endif;

        if (isset($_SESSION['cart'][$product])):
            $_SESSION['cart'][$product] -= $quantity;
        endif;
    }

    public function cartRemoveItem($product) {
        if (isset($_SESSION['cart'][$product])):
            $_SESSION['cart']['total'] = $_SESSION['cart']['total'] - $_SESSION['cart'][$product];
            unset($_SESSION['cart'][$product]);
        endif;
    }

}
