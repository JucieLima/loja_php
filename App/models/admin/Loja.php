<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Upload;

/**
 * Description of Loja
 *
 * @author jucie
 */
class Loja extends Model {

    protected $table = "loja_config";
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;
    private $data;
    private $result;
    private $error;

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    public function atualizar(array $data) {
        $this->data = $data;
        if ($this->validateData()):
            $this->sendImages();
            return $this->updateData();
        endif;
    }

    private function validateData() {
        if (in_array('', $this->data)):
            $this->error = "Todos os campos são obrigatórios!";
        elseif (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)):
            $this->error = "Informe um email válido!";
        else:
            return true;
        endif;
    }

    private function sendImages() {
        $delete = self::find(1);
        if (!empty($_FILES['logo'])):
            $upload = new Upload();
            $upload->image($_FILES['logo'], 'logo-image');
            if ($upload->getResult()):
                $this->data['logo'] = $upload->getResult();
                if (file_exists('uploads/' . $delete->logo)):
                    unlink('uploads/' . $delete->logo);
                endif;
            endif;
        endif;

        if (!empty($_FILES['imagem_padrao'])):
            $upload = new Upload();
            $upload->image($_FILES['imagem_padrao'], 'social-image');
            if ($upload->getResult()):
                $this->data['imagem_padrao'] = $upload->getResult();
                if (file_exists('uploads/' . $delete->imagem_padrao)):
                    unlink('uploads/' . $delete->imagem_padrao);
                endif;
            endif;
        endif;
    }

    private function updateData() {
        $update = self::find(1);
        $update->update($this->data);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'Erro ao atualizar dados no banco!';
            return false;
        endif;
    }

}
