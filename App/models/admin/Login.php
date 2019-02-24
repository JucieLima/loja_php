<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Login extends Model {

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    const CREATED_AT = 'registro_usuario';
    const UPDATED_AT = 'ultimo_acesso_usuario';
    
    protected $guarded = ['visitas_usuario'];
    
    private $error;

    function getError() {
        return $this->error;
    }

    public function logar($dados) {
        if (in_array('', $dados)):
            $this->error = 'errempty';
            return false;
        else:
            $dados['senha_usuario'] = md5($dados['senha_usuario']);
            $user = self::whereRaw('senha_usuario = ?  AND email_usuario = ?', [$dados['senha_usuario'], $dados['email_usuario']])->get()->toArray();
            if ($user):
                $visitas['visitas_usuario'] = $user[0]['visitas_usuario'] + 1;
                self::find($user[0]['id_usuario'])->update($visitas);
                return $user[0];
            else:
                $this->error = 'error';
                return false;
            endif;
        endif;
    }

}
