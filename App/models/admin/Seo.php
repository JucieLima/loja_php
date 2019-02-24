<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Upload;

/**
 * Description of Seo
 *
 * @author jucie
 */
class Seo extends Model {

    protected $table = "seo";
    protected $primaryKey = "id";
    protected $guarded = ['id', 'url', 'controller', 'action'];
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

    public function updatePage(array $data) {
        $this->data = $data;
        if ($this->checkData()):
            $this->sendImage();
            return $this->exeUpdate();
        endif;
    }

    public function deleteImagePage(int $id) {
        $this->data = $id;
        $this->exeDeleteImage();
    }

    private function checkData() {
        if (in_array('', $this->data)):
            $this->error = "Exceto a imagem, todos os campos são obrigatórios!";
        else:
            return true;
        endif;
    }

    private function sendImage() {
        $delete = self::find($this->data['id']);
        if (!empty($_FILES['image'])):
            $upload = new Upload();
            $upload->image($_FILES['image'], $this->data['nome']);
            if ($upload->getResult()):
                $this->data['image'] = $upload->getResult();
                if (file_exists('uploads/' . $delete->image && !is_dir('uploads/' . $delete->image))):
                    unlink('uploads/' . $delete->image);
                endif;
            endif;
        endif;
    }

    private function exeUpdate() {
        $update = self::find($this->data['id']);
        $update->update($this->data);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'Erro ao atualizar dados no banco!';
        endif;
    }

    private function exeDeleteImage() {
        $delete = self::find($this->data);
        if (file_exists('uploads/' . $delete->image && !is_dir('uploads/' . $delete->image))):
            unlink('uploads/' . $delete->image);
        endif;
        $delete->image = null;
        $delete->save();
    }

}
