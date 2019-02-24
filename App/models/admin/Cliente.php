<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Clientes
 *
 * @author jucie
 */
class Cliente extends Model {

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    protected $fillable = [
        'nome_cliente',
        'sobrenome_cliente',
        'email_cliente',
        'senha_cliente',
        'data_nasc_cliente',
        'logradouro_cliente',
        'numero_cliente',
        'complemento_cliente',
        'bairro_cliente',
        'cidade_cliente',
        'estado_cliente',
        'pais_cliente',
        'cep_cliente',
        'sexo_cliente',
        'rg_cliente',
        'cpf_cliente',
        'pais_cliente',
        'status_cliente',
        'telefone_cliente'
    ];

    const CREATED_AT = 'data_registro_cliente';
    const UPDATED_AT = 'ultima_visita_cliente';

    private $dados;
    private $result;
    private $error;

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    public function getClientes($limit, $offset) {
        return self::limit($limit)->offset($offset)->orderBy('id_cliente', 'ASC')->get()->toArray();
    }

    public function getCliente($id) {
        if(self::find($id)):
            return self::find($id)->toArray();
        endif;        
    }

    public function updateCliente(array $dados) {
        $this->dados = $dados;
        $complemento_cliente = $this->dados['complemento_cliente'];
        unset($this->dados['complemento_cliente']);
        if ($this->validarDadosUpdate()):
            $this->dados['complemento_cliente'] = $complemento_cliente;
            $this->dados['telefone_cliente'] = $this->dados['telefone_cliente_prefixo'].$this->dados['telefone_cliente'];
            $this->dados = filter_var_array($this->dados, FILTER_SANITIZE_STRING);
            $this->dados = filter_var_array($this->dados, FILTER_SANITIZE_SPECIAL_CHARS);
            $this->exeUpdate();
        endif;
    }

    private function validarDadosUpdate() {
        if (empty($this->dados['senha_cliente'])):
            unset($this->dados['senha_cliente'], $this->dados['senhac']);
        endif;  

        if (isset($this->dados['senhac']) && empty($this->dados['senhac'])):
            $this->error = 'errsenhac';
        elseif (isset($this->dados['senhac']) && $this->dados['senhac'] != $this->dados['senha_cliente']):
            $this->error = 'errsconfirm';
        elseif (in_array('', $this->dados)):
            $this->error = 'errempty';
        elseif (isset($this->dados['senha_cliente']) && !preg_match('/^((?=(?:.*?[a-z]){1,})(?=(?:.*?[0-9]){1,})).{6,16}$/', $this->dados['senha_cliente'])):
            $this->error = 'errsenha';
        elseif (!filter_var($this->dados['email_cliente'], FILTER_VALIDATE_EMAIL)):
            $this->error = 'errmail';
        elseif ($this->checkMailUpdate()):
            $this->error = 'errmailread';
        else:
            return true;
        endif;
        return false;
    }

    private function checkMailUpdate() {
        $find = self::whereRaw("email_cliente = ? AND id_cliente <> ?", array($this->dados['email_cliente'], $this->dados['id_cliente']))->get()->toArray();
        if ($find):
            return true;
        endif;
        return false;
    }

    private function exeUpdate() {
        $update = self::find($this->dados['id_cliente']);
        $update->update($this->dados);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'errupdate';
        endif;
    }

}
