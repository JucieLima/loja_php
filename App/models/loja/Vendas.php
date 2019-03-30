<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;
use App\Models\Loja\Shipping;
use App\helpers\CpfCnpj;
use App\Models\Loja\Clientes;
use App\Models\Loja\Produtos;
use App\Models\Loja\VendaItens;

/**
 * Description of Vendas
 *
 * @author jucie
 */
class Vendas extends Model {

    protected $table = "vendas";
    protected $primaryKey = "id_venda";
    protected $guarded = ["id_venda"];
    private $sale;
    private $data;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function startForSale($data) {
        $this->data = $data;
        if ($this->checkCep() && $this->checkForm()):
            return $this->makeSale();
        endif;
    }

    private function checkCep() {
        if (empty($this->data['cep_cliente'])):
            $this->error = 'Você precisa informar um CEP para realizar a entrega do produto!';
        elseif (!preg_match('/[0-9]{5}-[0-9]{3}/', $this->data['cep_cliente'])):
            $this->error = 'CEP inválido, por favor digite outro CEP!';
        else:
            $this->data['cep_cliente'] = str_replace('-', '', $this->data['cep_cliente']);
            $shipping = new Shipping;
            $calc = $shipping->calc($this->data['cep_cliente']);
            if (!$calc['error'] == false):
                $this->error = $calc['error'];
            else:
                return true;
            endif;
        endif;
    }

    private function checkForm() {
        $cpfCnpj = new CpfCnpj();
        $observacao_venda = $this->data['observacao_venda'];
        $this->data['pais_cliente'] = "BR";

        if (empty($this->data['complemento_cliente'])):
            unset($this->data['complemento_cliente']);
        endif;

        unset($this->data['observacao_venda']);

        if (in_array('', $this->data)):
            $this->error = "Todos os campos, exceto complemento e observações, são obrigatórios!";
        elseif (!$cpfCnpj->validar($this->data['cpf_cliente'])):
            $this->error = 'CPF inválido, por favor informe outro CPF!';
        elseif ($this->addNewClient()):
            $this->data['observacao_venda'] = $observacao_venda;
            return true;
        endif;
    }

    private function addNewClient() {
        if (isset($_SESSION['client']) && $_SESSION['client']['create'] == true):
            $client = new Clientes;
            $client->addNewClient($this->data);
            if (!$client->getResult()):
                $this->error = $client->getError();
            else:
                $this->result = $client->getResult();
                return true;
            endif;
        else:
            $this->result = $this->data['id_cliente'];
            return true;
        endif;
    }

    private function makeSale() {
        $cart = new Cart;
        $desconto = (float) $_SESSION['coupon']['coupon_discount'];
        $this->sale['cliente_venda'] = $this->result;
        $this->sale['qtd_itens_venda'] = $_SESSION['cart']['total'];
        $this->sale['valor_venda'] = ($cart->getTotalCart() + $_SESSION['shipping']['cost']) - ($cart->getTotalCart() * $desconto);
        $this->sale['frete_venda'] = $_SESSION['shipping']['cost'];
        $this->sale['desconto_venda'] = $cart->getTotalCart() * $desconto;
        $this->sale['status'] = 0;
        $this->sale['logradouro_venda'] = $this->data['logradouro_cliente'];
        $this->sale['numero_venda'] = $this->data['numero_cliente'];
        $this->sale['bairro_venda'] = $this->data['bairro_cliente'];
        $this->sale['cidade_venda'] = $this->data['cidade_cliente'];
        $this->sale['estado_venda'] = $this->data['estado_cliente'];
        $this->sale['pais_venda'] = $this->data['pais_cliente'];
        $this->sale['cep_venda'] = $this->data['cep_cliente'];
        $this->sale['cep_venda'] = $this->data['cep_cliente'];
        $this->sale['observacao_venda'] = $this->data['logradouro_cliente'];
        $this->sale['created_at'] = date('Y-m-d H:i:s');
        $this->sale['updated_at'] = date('Y-m-d H:i:s');
        $this->sale['telefone_contato'] = $this->data['telefone_cliente'];
        $this->sale['complemento_venda'] = isset($this->data['complemento_cliente']) ? $this->data['complemento_cliente'] : null;

        return $this->insertSale();
    }

    private function insertSale() {
        $save = self::insertGetId($this->sale);
        if ($save):
            $this->result = $save;
            $this->insertProducts();
            return true;
        else:
            $this->error = 'Erro ao tentar inserir venda no banco de dados!';
        endif;
    }

    private function insertProducts() {
        $products = new Produtos;
        $vendaItens = new VendaItens;
        $productSale = [];
        foreach ($_SESSION['cart'] as $item => $quantity):
            if ($item !== 'total'):
                $product = $this->getProduct($item, $products);
                $productSale['qtd_itens'] = $quantity;
                $productSale['valor_unitario_vi'] = $product[0]['preco_venda_produto'] - $product[0]['desconto_produto'];
                $productSale['produto_vi'] = $item;
                $productSale['venda_vi'] = $this->result;

                $vendaItens->insertProducts($productSale);
            endif;
        endforeach;
        unset($_SESSION['cart'], $_SESSION['shipping'], $_SESSION['coupon']);
    }

    private function getProduct($id_proudct, $products) {
        $product = $products->getProduct($id_proudct);
        return $product;
    }

}
