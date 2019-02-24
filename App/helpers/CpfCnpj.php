<?php

namespace App\Helpers;

/**
 * Description of CpfCnpj
 *
 * @author jucie
 */
class CpfCnpj {

    private $campo;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function validar(string $cpfoucnpj) {
        $this->campo = preg_replace('/[^0-9]/is', '', $cpfoucnpj);       
        if (strlen($this->campo) == 14) {
            $this->valida_cnpj();
        }else if (strlen($this->campo) == 11) {
            $this->valida_cpf();
        }else{
            $this->error = 'Formato inválido!';
        }
        
        if($this->result):
            return true;
        endif;
    }

    private function valida_cnpj() {

        // O valor original
        $cnpj_original = $this->campo;

        // Captura os primeiros 12 números do CNPJ
        $primeiros_numeros_cnpj = substr($this->campo, 0, 12);


        // Faz o primeiro cálculo
        $primeiro_calculo = $this->multiplica_cnpj($primeiros_numeros_cnpj);

        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 : 11 - ( $primeiro_calculo % 11 );

        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = $this->multiplica_cnpj($primeiros_numeros_cnpj, 6);
        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 : 11 - ( $segundo_calculo % 11 );

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            $this->result = 'CNPJ válido';
            return true;
        } else {
            $this->error = "CNPJ inválido";
        }
    }

    /**
     * Multiplicação do CNPJ
     *
     * @param string $primeiros_numeros Os digitos do CNPJ
     * @param int $posicao A posição que vai iniciar a regressão
     * @return int O
     *
     */
    private function multiplica_cnpj($primeiros_numeros, $posicao = 5) {
        // Variável para o cálculo
        $calculo = 0;

        // Laço para percorrer os item do cnpj
        for ($i = 0; $i < strlen($primeiros_numeros); $i++) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ( $primeiros_numeros[$i] * $posicao );

            // Decrementa a posição a cada volta do laço
            $posicao--;

            // Se a posição for menor que 2, ela se torna 9
            if ($posicao < 2) {
                $posicao = 9;
            }
        }
        // Retorna o cálculo
        return $calculo;
    }

    private function valida_cpf() {
        // Verifica se o CPF tem 11 caracteres
        // Ex.: 02546288423 = 11 números
        if (strlen($this->campo) != 11) {
            return false;
        }

        // Captura os 9 primeiros dígitos do CPF
        // Ex.: 02546288423 = 025462884
        $digitos = substr($this->campo, 0, 9);

        // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
        $cpf_provisorio = $this->calc_digitos_posicoes($digitos);

        // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
        $novo_cpf = $this->calc_digitos_posicoes($cpf_provisorio, 11);

        // Verifica se o novo CPF gerado é idêntico ao CPF enviado
        if ($novo_cpf === $this->campo) {
            // CPF válido
            $this->result = 'CPF válido';
            return true;
        } else {
            $this->error = 'CPF inválido';
            // CPF inválido
            return false;
        }
    }

    /**
     * Multiplica dígitos vezes posições 
     *
     * @param string $digitos Os digitos desejados
     * @param int $posicoes A posição que vai iniciar a regressão
     * @param int $soma_digitos A soma das multiplicações entre posições e dígitos
     * @return int Os dígitos enviados concatenados com o último dígito
     *
     */
    private function calc_digitos_posicoes($digitos, $posicoes = 10, $soma_digitos = 0) {
        // Faz a soma dos dígitos com a posição
        // Ex. para 10 posições: 
        //   0    2    5    4    6    2    8    8   4
        // x10   x9   x8   x7   x6   x5   x4   x3  x2
        //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
        for ($i = 0; $i < strlen($digitos); $i++) {
            $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
            $posicoes--;
        }

        // Captura o resto da divisão entre $soma_digitos dividido por 11
        // Ex.: 196 % 11 = 9
        $soma = $soma_digitos % 11;

        // Verifica se $soma_digitos é menor que 2
        if ($soma < 2) {
            // $soma_digitos agora será zero
            $soma = 0;
        } else {
            // Se for maior que 2, o resultado é 11 menos $soma_digitos
            // Ex.: 11 - 9 = 2
            // Nosso dígito procurado é 2
            $soma = 11 - $soma;
        }

        // Concatena mais um dígito aos primeiro nove dígitos
        // Ex.: 025462884 + 2 = 0254628842
        $cpf = $digitos . $soma;

        // Retorna
        return $cpf;
    }

}
