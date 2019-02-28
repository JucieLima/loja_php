<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Upload;

/**
 * Description of Silder
 *
 * @author jucie
 */
class Slider extends Model {

    protected $table = "slider";
    protected $primaryKey = 'slider_id';
    protected $guarded = ['slider_id'];
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

    public function updateSlider($dados) {
        $this->data = $dados;
        if ($this->checkData()):
            $this->updateImages();
            $this->updateData();
            return true;
        endif;
    }

    private function checkData() {
        if (in_array('', $this->data)):
            $this->error = "É obrigatório preencher todos os títulos e descrições.";
        else:
            return true;
        endif;
    }

    private function updateData() {
        for ($i = 1; $i < 5; $i++):
            $update = self::find($i);
            if ($update):
                $update->slider_title = $this->data['slider_title' . $i];
                $update->slider_description = $this->data['slider_description' . $i];
                $update->save();
            endif;
        endfor;
    }

    private function updateImages() {
        for ($i = 1; $i < 5; $i++):
            if (!empty($_FILES['slider_image' . $i]['name'])):
                $this->updateImageSlider($i);
            endif;

            if (!empty($_FILES['slider_sticker' . $i]['name'])):
                $this->updateStickerSlider($i);
            endif;
        endfor;
    }

    private function updateImageSlider(int $row) {
        $update = self::find($row);
        if ($update):
            if (file_exists('uploads/' . $update->slider_image && !is_dir('uploads/' . $update->slider_image))):
                unlink('uploads/' . $update->slider_image);
            endif;
            
            $upload = new Upload();
            $upload->image($_FILES['slider_image' . $row], 'slider-image-' . $row);
            if ($upload->getResult()):
                $update->slider_image = $upload->getResult();
                $update->save();
            endif;
        endif;
    }

    private function updateStickerSlider(int $row) {
        $update = self::find($row);
        if ($update):
            if (file_exists('uploads/' . $update->slider_sticker && !is_dir('uploads/' . $update->slider_sticker))):
                unlink('uploads/' . $update->slider_sticker);
            endif;
            
            $upload = new Upload();
            $upload->image($_FILES['slider_sticker' . $row], 'slider-sticker-' . $row);
            if ($upload->getResult()):
                $update->slider_sticker = $upload->getResult();
                $update->save();
            endif;
        endif;
    }

}
