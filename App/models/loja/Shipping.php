<?php

namespace App\Models\Loja;

use App\Models\Loja\Produtos;
use App\Models\Admin\Loja;

class Shipping {

    private $altura = 0;
    private $peso = 0;
    private $comprimento = 16;
    private $largura = 11;
    private $split = 1;

    public function __construct() {
        if (isset($_SESSION['cart'])):
            $this->setPackage();
        endif;
    }

    public function calc($cepDestino) {

        $loja = new Loja;
        $dataStore = $loja->find(1);
        $altura = $this->altura / $this->split;

        $url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx";
        $data = [
            'nCdEmpresa' => '',
            'sDsSenha' => '',
            'nCdServico' => '41106',
            'sCepOrigem' => $dataStore->cep,
            'sCepDestino' => $cepDestino,
            'nVlPeso' => $this->peso,
            'nCdFormato' => '1',
            'nVlComprimento' => $this->comprimento,
            'nVlAltura' => $altura,
            'nVlLargura' => $this->largura,
            'nVlDiametro' => '0',
            'sCdMaoPropria' => 'N',
            'nVlValorDeclarado' => '0',
            'sCdAvisoRecebimento' => 'N',
            'StrRetorno' => 'xml'
        ];

        $query = http_build_query($data);
        $ch = curl_init($url . '?' . $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        $xml = simplexml_load_string($r);
        if (isset($xml->cServico) && $xml->cServico->Erro == 0):
            $s = $xml->cServico->PrazoEntrega == 1 ? '' : 's';
            $valor = floatval(str_replace(',', '.', str_replace('.', '', $xml->cServico->Valor)));
            $_SESSION['shipping']['valor'] = 'R$ ' . number_format($valor * $this->split, 2, ',', '.');
            $_SESSION['shipping']['cost'] = $valor * $this->split;
            $_SESSION['shipping']['prazo'] = ((string) $xml->cServico->PrazoEntrega ) . ' dia' . $s;
            $_SESSION['shipping']['cep'] = $cepDestino;
            $_SESSION['shipping']['error'] = false;
        else:
            $_SESSION['shipping']['valor'] = 'R$ ' . number_format(0 * $this->split, 2, ',', '.');
            $_SESSION['shipping']['cost'] = 0 * $this->split;
            $_SESSION['shipping']['prazo'] = '0 dias';
            $_SESSION['shipping']['cep'] = '';
            if (isset($xml->cServico)):
                $this->getError($xml->cServico->Erro);
            else:
                $this->error = 'Erro ao contatar serviço dos correios, favor tente mais tarde!';
            endif;
        endif;
        return $_SESSION['shipping'];
    }

    private function setPackage() {
        $produts = new Produtos;
        foreach ($_SESSION['cart'] as $key => $value):
            if ($key != 'total'):
                $product = $produts->find($key);
                $this->altura += $product->altura_produto * $value;
                $this->peso += $product->peso_produto * $value;
                $this->largura = $product->largura_produto > $this->largura ? $product->largura_produto : $this->largura;
                $this->altura = $product->altura_produto > $this->altura ? $product->altura_produto : $this->altura;
                $this->comprimento = $product->comprimento_produto > $this->comprimento ? $product->comprimento_produto : $this->comprimento;
            endif;
        endforeach;
        $this->altura = $this->altura < 2 ? 2 : $this->altura;
        $this->comprimento = $this->comprimento > 105 ? 105 : $this->comprimento;
        $this->largura = $this->largura > 105 ? 105 : $this->largura;

        if ($this->altura > 105 || $this->altura + $this->comprimento + $this->largura > 200):
            $this->sliptPackage();
        endif;
    }

    private function sliptPackage() {
        while ($this->altura / $this->split > 105 || ($this->altura / $this->split) + $this->largura + $this->comprimento > 200):
            $this->split++;
        endwhile;
    }

    private function getError($error) {
        switch ($error):
            case -3 :
                $_SESSION['shipping']['error'] = 'Você informou um CEP de destino inválido';
                break;
            case -6 :
                $_SESSION['shipping']['error'] = 'Serviço indisponível para o trecho informado';
                break;
            case -10 :
                $_SESSION['shipping']['error'] = 'Precificação indisponível para o trecho informado';
                break;
            case -33 :
                $_SESSION['shipping']['error'] = 'Sistema temporariamente fora do ar. Favor tentar mais tarde';
                break;
            case -888 :
                $_SESSION['shipping']['error'] = 'Erro ao calcular a tarifa';
                break;
            case 7 :
                $_SESSION['shipping']['error'] = 'Serviço indisponível, tente mais tarde';
                break;
            default :
                $_SESSION['shipping']['error'] = 'Erro ao calcular o frete!';
        endswitch;
    }

}
