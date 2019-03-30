<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Clientes
 *
 * @author jucie
 */
class Clientes extends Model {

    protected $table = "clientes";
    protected $primaryKey = "id_cliente";
    protected $guarded = ['id_cliente'];

    const CREATED_AT = 'data_registro_cliente';
    const UPDATED_AT = 'ultima_visita_cliente';

    private $data;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function createCliente($dados) {
        $this->data = $dados;
        if ($this->checkCreate()):
            $_SESSION['client']['data'] = $this->data;
            $_SESSION['client']['create'] = true;
            $this->result = $this->data;
        endif;
    }

    public function loginClient($data) {
        $this->data = $data;
        if (empty($this->data['email_cliente'])):
            $this->error = 'O campo email deve ser preenchido!';
        elseif (empty($this->data['senha_cliente'])):
            $this->error = 'O campo senha deve ser preenchido!';
        elseif ($this->checkLogin()):
            $_SESSION['client']['data'] = $this->result;
            $_SESSION['client']['create'] = false;
            return true;
        endif;
    }

    public function getClienteView() {
        if (isset($_SESSION['client']) && $_SESSION['client']['create'] == true):
            $this->setClientCreate();
        else:
            $this->setClientRead();
        endif;
        return $this->data['data'];
    }

    public function addNewClient($dados) {
        $this->data = $dados;
        if ($this->checkNewClient()):
            $this->data['status_cliente'] = 1;
            $this->data['senha_cliente'] = md5($this->data['senha_cliente']);
            $this->addClient();
        endif;
    }
    
    public function logout() {
        unset($_SESSION['client']);
        setcookie("clientlogin", '', time() - 3600, '/');
        header("Location: " . BASE_URL . "user/login");
    }

    private function setClientRead() {
        if (isset($_SESSION['client'])):
            $this->data['data'] = $_SESSION['client']['data'];
        elseif (filter_input(INPUT_COOKIE, 'clientlogin', FILTER_DEFAULT)):
            $this->checkCookie();
        else:
            header("Location: " . BASE_URL . "user/login");
        endif;
    }

    private function checkCookie() {
        $cookie = filter_input(INPUT_COOKIE, 'clientlogin', FILTER_DEFAULT);
        $data = explode('@', $cookie);
        $email = base64_decode($data[0]);
        $senha = base64_decode($data[1]);
        $read = self::where('email_cliente', $email)
                        ->where('senha_cliente', $senha)
                        ->select('id_cliente', 'nome_cliente', 'sobrenome_cliente', 'email_cliente', 'logradouro_cliente',
                                'numero_cliente', 'complemento_cliente', 'bairro_cliente', 'cidade_cliente',
                                'estado_cliente', 'cep_cliente', 'telefone_cliente', 'sexo_cliente', 'cpf_cliente')
                        ->get()->toArray();
        if ($read):
            $this->data['data'] = $read[0];
            if (isset($this->data['keep_logged_in']) && $this->data['keep_logged_in'] == 'on'):
                $this->setCookie();
            endif;
        else:
            setcookie("clientlogin", '', time() - 3600, '/');
            header("Location: " . BASE_URL . "user/login");
        endif;
    }

    private function checkLogin() {
        $read = self::where("email_cliente", $this->data['email_cliente'])
                ->where("senha_cliente", md5($this->data['senha_cliente']))
                ->select('id_cliente', 'nome_cliente', 'sobrenome_cliente', 'email_cliente', 'logradouro_cliente',
                        'numero_cliente', 'complemento_cliente', 'bairro_cliente', 'cidade_cliente',
                        'estado_cliente', 'cep_cliente', 'telefone_cliente', 'sexo_cliente', 'cpf_cliente')
                ->get()
                ->toArray();
        if ($read):
            $this->result = $read[0];
            if (isset($this->data['keep_logged_in']) && $this->data['keep_logged_in'] == 'on'):
                $this->setCookie();
            endif;
            return true;
        else:
            $this->error = 'Erro ao logar, email e/ou senha não conferem!';
        endif;
    }

    private function setCookie() {
        $dataCookie = base64_encode($this->data['email_cliente']) . '@' . base64_encode(md5($this->data['senha_cliente']));
        setcookie("clientlogin", $dataCookie, time() + (60 * 60 * 24 * 7), '/');
    }

    private function checkCreate() {
        $read = self::where("email_cliente", $this->data['email_cliente'])
                ->get()
                ->toArray();

        if (empty($this->data['nome_cliente'])):
            $this->error = 'O campo nome é obrigatório!';
        elseif (empty($this->data['email_cliente'])):
            $this->error = 'O campo email é obrigatório!';
        elseif (empty($this->data['senha_cliente'])):
            $this->error = 'O campo senha é obrigatório!';
        elseif ($read):
            $this->error = "O email que você está tentando usar já está foi cadastrado para outro usuário.";
        elseif (!preg_match('/^((?=(?:.*?[a-zA-Z]){1,})(?=(?:.*?[0-9]){1,})).{6,16}$/',
                        $this->data['senha_cliente'])):
            $this->error = 'A senha deve possuir pelo menos um algarismo e uma letra e deve conter pelo menos 6 caracteres.';
        elseif (!filter_var($this->data['email_cliente'], FILTER_VALIDATE_EMAIL)):
            $this->error = 'Parece que o email que você esta tentando cadastrar não possui um formato válido!';
        else:
            return true;
        endif;
    }

    private function setClientCreate() {
        $this->data = $_SESSION['client'];
        $this->data['data']['sobrenome_cliente'] = '';
        $this->data['data']['cpf_cliente'] = '';
        $this->data['data']['telefone_cliente'] = '';
        $this->data['data']['telefone_cliente'] = '';
        $this->data['data']['cep_cliente'] = isset($_SESSION['shipping']['cep']) ? $_SESSION['shipping']['cep'] : '';
        $this->data['data']['logradouro_cliente'] = '';
        $this->data['data']['numero_cliente'] = '';
        $this->data['data']['bairro_cliente'] = '';
        $this->data['data']['cidade_cliente'] = '';
        $this->data['data']['estado_cliente'] = '';
        $this->data['data']['sexo_cliente'] = '0';
        $this->data['data']['complemento_cliente'] = '';
        $this->setAdressByCep();
    }

    private function setAdressByCep() {
        if (isset($_SESSION['shipping']['cep']) && $_SESSION['shipping']['error'] == false):
            if (!isset($_SESSION['adress'])):
                $json_file = file_get_contents("https://viacep.com.br/ws/" . $_SESSION['shipping']['cep'] . "/json/");
                $json_str = json_decode($json_file, true);
                $_SESSION['adress'] = json_decode($json_file, true);
            else:
                $json_str = $_SESSION['adress'];
            endif;
            $this->data['data']['logradouro_cliente'] = $json_str['logradouro'];
            $this->data['data']['bairro_cliente'] = $json_str['bairro'];
            $this->data['data']['cidade_cliente'] = $json_str['localidade'];
            $this->data['data']['estado_cliente'] = $json_str['uf'];
            $this->data['data']['complemento_cliente'] = $json_str['complemento'];
        else:
            $this->data['data']['cep_cliente'] = '';
        endif;
    }

    private function checkNewClient() {
        $read = self::where("email_cliente", $this->data['email_cliente'])
                ->get()
                ->toArray();

        if ($read):
            $this->error = "Já existe um cliente cadastrado com este email.";
        elseif (!preg_match('/^((?=(?:.*?[a-zA-Z]){1,})(?=(?:.*?[0-9]){1,})).{6,16}$/',
                        $this->data['senha_cliente'])):
            $this->error = 'A senha deve possuir pelo menos um algarismo e uma letra e deve conter pelo menos 6 caracteres.';
        elseif (!filter_var($this->data['email_cliente'], FILTER_VALIDATE_EMAIL)):
            $this->error = 'Parece que o email que você esta tentando cadastrar não possui um formato válido!';
        else:
            return true;
        endif;
    }

    private function addClient() {
        $save = self::insertGetId($this->data);
        if ($save):
            $this->result = $save;
            return true;
        else:
            $this->error = 'Erro ao tentar inserir dados no banco!';
        endif;
    }

}
