<?php

namespace App\Models\Loja;

use PagSeguro\Domains\Requests\DirectPayment\CreditCard;
use App\Models\Loja\Vendas;
use App\Models\Loja\VendaItens;
use PagSeguro\Configuration\Configure;

class PagSeguro
{
    private $data;
    private $result;
    private $error;

    public function getResult()
    {
        return $this->result;
    }

    public function getError()
    {
        return $this->error;
    }

    public function paymentCard($data)
    {
        $this->data = $data;
        $creditCard = new CreditCard;
        $venda      = new Vendas;
        $this->data = array_merge($this->data, $venda->getSale($this->data['id_venda'])[0]);

        /**
         * Modo do pagamento
         *
         * Presença: Obrigatória.
         * Tipo: Texto.
         * Formato: aceita a opção default.
         *
         * @var string $paymentMode
         * @options=['default']
         */
        $creditCard->setMode('default');

        /**
         * Especifica o e-mail que vai receber o pagamento
         *
         * Presença:Obrigatória.
         * Tipo:Texto.
         * Presença:Obrigatória.
         * Formato:Um e-mail válido, com limite de 60 caracteres. O e-mail informado deve estar vinculado à conta PagSeguro que
         * está realizando a chamada.
         *
         * @var string $receiverEmail
         */
        $creditCard->setReceiverEmail(EMAIL);

        /**
         * Código de referência da transação. Informa o código que foi usado para fazer referência ao pagamento. Este código
         * foi fornecido no momento do pagamento e é útil para vincular as transações do PagSeguro às vendas registradas no seu
         * sistema.
         *
         * Presença: Opcional.
         * Formato: Livre, com o limite de 200 caracteres.
         * Tipo: Texto.
         *
         * @var string $reference
         */
        $creditCard->setReference($this->data['id_venda']);

        /**
         * Moeda utilizada. Indica a moeda na qual o pagamento será feito. No momento, a única opção disponível é BRL (Real).
         *
         * Presença: Obrigatória.
         * Tipo: Texto.
         * Formato: Somente o valor BRL é aceito.
         *
         * @var string $currency
         * @options=['BRL']
         */
        $creditCard->setCurrency('BRL');

        $vendaItens = new VendaItens;
        $items      = $vendaItens->getItens($this->data['id_venda']);

        foreach ($items as $item) {
            $totalItem = $item['valor_unitario_vi'] - $item['valor_unitario_vi']
                * $this->data['desconto_venda'];
            $creditCard->addItems()->withParameters(
                $item['id_vi'], $item['titulo_produto'], $item['qtd_itens'],
                $totalItem
            );
        }
        /**
         * Nome completo do comprador. Informa o nome completo do comprador que realizou o pagamento.
         *
         * Presença: Opcional.
         * Tipo: Texto.
         * Formato:* No mínimo duas sequências de caracteres, com o limite total de 50 caracteres.
         *
         * @var string $senderName
         */
        $creditCard->setSender()->setName($this->data['nome_cliente'].' '.$this->data['sobrenome_cliente']);

        /**
         * E-mail do comprador. Informa o e-mail do comprador que realizou a transação.
         *
         * Presença: Obrigatória.
         * Tipo: Texto.
         * Formato: um e-mail válido (p.e., usuario\@site.com.br), com no máximo 60 caracteres.
         *
         * @var string $senderEmail
         */
        $creditCard->setSender()->setEmail($this->data['email_cliente']);

        $this->setPhone();

        /** @var \PagSeguro\Domains\Phone $phone */
        $creditCard->setSender()->setPhone()->withParameters($this->data['telefone_contato'][0],
            $this->data['telefone_contato'][1]);

        /** @var \PagSeguro\Domains\Document $document */
        $creditCard->setSender()->setDocument()->withParameters('CPF',
            $this->data['cpf_cliente']);

        /**
         * Identificador do vendedor (fingerprint) gerado pelo JavaScript do PagSeguro.
         *
         * Presença: Obrigatória.
         * Tipo: Texto.
         * Formato: Obtido a partir de uma chamada javascript PagseguroDirectPayment.getSenderHash().
         *
         * @var string $senderHash
         *
         * @see https://devpagseguro.readme.io/docs/checkout-web-usando-a-sua-tela#obter-indicacao-do-comprador
         */
        $creditCard->setSender()->setHash($this->data['idpagseguro']);

        $creditCard->setShipping()->setCost()->withParameters($this->data['frete_venda']);


//        'desconto_venda' => float 62.538

        /** @var \PagSeguro\Domains\Address $address */
        $creditCard->setShipping()->setAddress()->withParameters(
            $this->data['idpagseguro'], $this->data['numero_venda'],
            $this->data['bairro_venda'], $this->data['cep_venda'],
            $this->data['cidade_venda'], $this->data['estado_venda'], 'BRA',
            $this->data['complemento_venda']
        );

        /** @var \PagSeguro\Domains\Address $address */
        $creditCard->setBilling()->setAddress()->withParameters(
            $this->data['idpagseguro'], $this->data['numero_venda'],
            $this->data['bairro_venda'], $this->data['cep_venda'],
            $this->data['cidade_venda'], $this->data['estado_venda'], 'BRA',
            $this->data['complemento_venda']
        );

        /**
         * Token do Cartão de Crédito. Token retornado no serviço de obtenção de token do cartão de crédito.
         *
         * Presença: Obrigatório para Cartão de Crédito.
         * Tipo: Texto.
         * Formato: Não tem limite de caracteres.
         *
         * @var string $token
         */
        $creditCard->setToken($this->data['cardToken']);

        $installments    = explode(';', $this->data['installments']);
        $valueInstall    = (float) $installments[1];
        $installments[2] = $installments[2] == 'true' ? true : false;
        /**
         * Quantidade de parcelas. Quantidade de parcelas escolhidas pelo cliente.
         *
         * Presença: Obrigatório para Cartão de Crédito.
         * Tipo: Inteiro.
         * Valores aceitos: [1, 18].
         *
         * @var integer $quantity
         */
        /** @var float $value */
        $creditCard->setInstallment()->withParameters((int) $installments[0],
            $valueInstall, $installments[2]);

        // Set the credit card holder information
        $creditCard->setHolder()->setName($this->data['cardholder_name']); // Equals in Credit Card

        /** @var \PagSeguro\Domains\Phone $phone */
        $creditCard->setHolder()->setPhone()->withParameters($this->data['telefone_contato'][0],
            $this->data['telefone_contato'][1]);

        /** @var \PagSeguro\Domains\Document $document */
        $creditCard->setHolder()->setDocument()->withParameters('CPF',
            $this->data['cpf_number']);
        
        $creditCard->setNotificationUrl(BASE_URL.'notifications/pagseguro');

        try {
            $this->result = $creditCard->register(
                /** @var \PagSeguro\Domains\AccountCredentials | \PagSeguro\Domains\ApplicationCredentials $credential */
                Configure::getAccountCredentials()
            );
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            exit;
        }
    }

    private function setPhone()
    {
        $phone                          = explode(' ',
            $this->data['telefone_contato']);
        $phone[0]                       = str_replace('(', '', $phone[0]);
        $phone[0]                       = str_replace(')', '', $phone[0]);
        $phone[1]                       = str_replace('-', '', $phone[1]);
        $this->data['telefone_contato'] = $phone;
    }
}