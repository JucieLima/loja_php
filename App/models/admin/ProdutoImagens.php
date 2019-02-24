<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ProdutoImagens
 *
 * @author jucie
 */
class ProdutoImagens extends Model {

    protected $table = "produto_imagens";
    protected $primaryKey = 'imagem_id';
    protected $fillable = [
        'imagem_produto',
        'imagem_uri',
        'imagem_main'
    ];
    public $timestamps = false;
    private $image;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function sendImage($product_id, $image_folder, $cover) {
        $create = self::create(["imagem_produto" => $product_id, "imagem_uri" => $image_folder, 'imagem_main' => $cover]);
        if ($create):
            $this->result = $create->toArray();
            return true;
        else:
            $this->error = 'Erro ao enviar salvar imagem no banco de dados';
        endif;
    }

    public function getImages($id) {
        return self::whereRaw('imagem_produto = ?', $id)->orderByDesc("imagem_main")->get()->toArray();
    }
    
    public function getCover($product_id) {
        return self::where(["imagem_produto" => $product_id, "imagem_main" => 1])->get()->toArray();
    }

    public function excluir(int $id) {
        $this->image = $id;
        if ($this->checkImage()):
            return $this->exeDelete();
        endif;
    }

    public function updateCover(int $id) {
        $this->image = $id;
        return $this->setCover();
    }

    private function checkImage() {
        $image = self::find($this->image)->toArray();
        if ($image['imagem_main']):
            $others = self::where("imagem_produto", "=", $image['imagem_produto'])->where("imagem_main", "<>", 1)->get()->toArray();
            if ($others):
                $find = self::find($others[0]['imagem_id']);
                $find->imagem_main = 1;
                $find->save();
                return true;
            else:
                $this->error = "Você não pode excluir esta imagem.";
            endif;
        else:
            return true;
        endif;
    }

    private function exeDelete() {
        $delete = self::find($this->image);
        if ($delete):
            $this->result = $delete->imagem_produto;
            if (file_exists('uploads/' . $delete->imagem_uri)):
                unlink('uploads/' . $delete->imagem_uri);
            endif;
            $delete->delete();
            return true;
        endif;
    }

    private function setCover() {
        $find = self::find($this->image);
        if ($find->toArray()):
            $cover = self::where("imagem_produto", $find->imagem_produto)->where("imagem_main", 1)->get()->toArray();
            if ($cover):
                $findCover = self::find($cover[0]['imagem_id']);
                $findCover->imagem_main = 0;
                $findCover->save();
            endif;
            $this->result = $find->imagem_produto;
            $find->imagem_main = 1;
            $find->save();
            return true;
        else:
            $this->error = "Imagem não encontrada!";
        endif;
    }

}
