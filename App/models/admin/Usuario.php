<?php

namespace App\Models\Admin;

use App\Helpers\Upload;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of Usuario
 *
 * @author jucie
 */
class Usuario extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $guarded    = ['nome_suario', 'sobrenome_suario', 'email_suario', 'senha_suario',
        'permissao_suario', 'imagem_suario'];
    private $dados;
    private $error;
    private $result;

    const CREATED_AT = 'registro_usuario';
    const UPDATED_AT = 'ultimo_acesso_usuario';

    public function getError()
    {
        return $this->error;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getUsers($limit, $offset)
    {
        return self::limit($limit)->offset($offset)->orderBy('id_usuario', 'ASC')->get()->toArray();
    }

    public function getUser($id)
    {
        if (self::find($id)):
            return self::find($id)->toArray();
        endif;
    }

    public function salvar(array $dados)
    {
        $this->dados = $dados;
        if ($this->validarDados()):
            unset($this->dados['senhac']);
            $this->dados['senha_usuario']  = md5($this->dados['senha_usuario']);
            $upload                        = new Upload();
            $upload->image($_FILES['imagem_usuario'],
                $this->dados['nome_usuario'].' '.$this->dados['sobrenome_usuario']);
            $this->dados['imagem_usuario'] = $upload->getResult();
            if ($upload->getResult()):
                $this->exeSave();
                return true;
            else:
                $this->error = 'errupload';
                return false;
            endif;
        else:
            return false;
        endif;
    }

    public function updateUser($dados)
    {
        $this->dados = $dados;
        if ($this->validaUpdate()):
            $this->updateImage();
            $update = self::find($this->dados['id_usuario']);
            $update->update($this->dados);
            if ($update):
                $this->result = $update->toArray();
                return true;
            else:
                $this->error = 'error';
                return false;
            endif;
        endif;
    }

    public function excluir($id)
    {
        if ($this->checkAdmin($id)):
            $this->error = 'erradmin';
        elseif ($id == $_SESSION['userlogin']['id_usuario']):
            $this->error = 'errthis';
        else:
            if ($this->exeDelete($id)):
                $this->result = 'success';
                return true;
            else:
                $this->error = 'error';
            endif;
        endif;
    }

    public function excluirPerfil($id)
    {
        if (self::find($id)):
            $this->error = 'erruser';
        elseif ($this->checkAdmin($id)):
            $this->error = 'erradmin';
        else:
            if ($this->exeDelete($id)):
                $this->result = 'success';
                return true;
            else:
                $this->error = 'error';
            endif;
        endif;
    }

    private function validarDados()
    {
        $return = false;
        if (empty($_FILES['imagem_usuario']['name'])):
            $this->error = 'errimage';
        elseif (in_array('', $this->dados)):
            $this->error = 'errempty';
        elseif ($this->dados['senha_usuario'] !== $this->dados['senhac']):
            $this->error = 'errconfirm';
        elseif (!preg_match('/^((?=(?:.*?[a-zA-Z]){1,})(?=(?:.*?[0-9]){1,})).{6,16}$/',
                $this->dados['senha_usuario'])):
            $this->error = 'errpassword';
        elseif (!filter_var($this->dados['email_usuario'], FILTER_VALIDATE_EMAIL)):
            $this->error = 'errmail';
        elseif ($this->checkEmail()):
            $this->error = 'errmailread';
        else:
            $return = true;
        endif;
        return $return;
    }

    private function validaUpdate()
    {
        $retorno = false;
        if (empty($this->dados['senha_usuario'])):
            unset($this->dados['senha_usuario'], $this->dados['senhac']);
        endif;

        if (isset($this->dados['senhac']) && empty($this->dados['senhac'])):
            $this->error = 'errosenhac';
        elseif (isset($this->dados['senha_usuario']) && isset($this->dados['senhac'])
            && $this->dados['senha_usuario'] != $this->dados['senhac']):
            $this->error = 'errsenhaconfirm';
        elseif (in_array('', $this->dados)):
            $this->error = 'errempty';
        elseif (isset($this->dados['senha_usuario']) && !preg_match('/^((?=(?:.*?[a-z]){1,})(?=(?:.*?[0-9]){1,})).{6,16}$/',
                $this->dados['senha_usuario'])):
            $this->error = 'errsenha';
        elseif (!filter_var($this->dados['email_usuario'], FILTER_VALIDATE_EMAIL)):
            $this->error = 'errmail';
        elseif ($this->checkMailUpdate()):
            $this->error = 'errmailread';
        else:
            $retorno = true;
        endif;

        unset($this->dados['senhac']);

        if (isset($this->dados['senha_usuario'])):
            $this->dados['senha_usuario'] = md5($this->dados['senha_usuario']);
        else:
            unset($this->dados['senha_usuario']);
        endif;

        return $retorno;
    }

    private function exeSave()
    {
        $save = self::create($this->dados);
        if ($save):
            $this->result = $save->toArray();
            return true;
        else:
            $this->error = 'errcreate';
        endif;
    }

    private function checkEmail()
    {
        $email = ['email_usuario' => $this->dados['email_usuario']];
        $find  = self::find($email)->toArray();
        if ($find):
            return true;
        else:
            return false;
        endif;
    }

    private function checkMailUpdate()
    {
        $find = self::whereRaw("email_usuario = ? AND id_usuario != ?",
                array($this->dados['email_usuario'], $this->dados['id_usuario']))->get()->toArray();
        if ($find):
            return true;
        else:
            return false;
        endif;
    }

    private function updateImage()
    {
        if (!empty($_FILES['imagem_usuario'])):
            $upload = new Upload();
            $upload->image($_FILES['imagem_usuario'],
                $this->dados['nome_usuario'].' '.$this->dados['sobrenome_usuario']);
            if ($upload->getResult()):
                $this->dados['imagem_usuario'] = $upload->getResult();
            endif;
        endif;
    }

    private function checkAdmin($id)
    {
        $admin = self::whereRaw("id_usuario = ? AND permissao_usuario = ?",
                [$id, 1])->get()->toArray();
        if (!$admin):
            return false;
        else:
            $admin = self::whereRaw("permissao_usuario = 1")->get();
            if (count($admin) > 1):
                return false;
            endif;
            return true;
        endif;
    }

    private function exeDelete($id)
    {
        $delete = self::find($id);
        if ($delete):
            $delete->delete();
            return true;
        endif;
    }
}
