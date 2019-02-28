<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Upload;

/**
 * Description of Display
 *
 * @author jucie
 */
class Display extends Model{

    protected $table = "displays";
    protected $primaryKey = "display_id";
    protected $guarded = ["display_id"];
    public $timestamps = false;
    private $data;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function updateHome(array $dados) {
        $this->data = $dados;
        if ($this->checkData()):
            $this->sendBanner();
            $this->exeUpdate();
            return true;
        endif;
    }

    private function checkData() {
        if (in_array('', $this->data)):
            $this->error = "É obrigatório marcar todas as opções";
        else:
            return true;
        endif;
    }

    private function sendBanner() {
        if (!empty($_FILES['banner_path']['name'])):
            $find = self::find(1);
            if (file_exists('uploads/' . $find->banner_path && !is_dir('uploads/' . $find->banner_path))):
                unlink('uploads/' . $find->banner_path);
            endif;

            $upload = new Upload();
            $upload->image($_FILES['banner_path'], 'banner');
            if ($upload->getResult()):
                $this->data['banner_path'] = $upload->getResult();
            endif;
        endif;
    }
    
    private function exeUpdate() {
        $update = self::find(1);
        $update->update($this->data);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'Erro ao atualizar tabela no banco de dados.';
        endif;
    }

}
