<?php
namespace App\Helpers;

class ResizeImage {

    private $file;
    private $filename;
    private $image;
    private $width;
    private $src_x;
    private $src_y;
    private $src_w;
    private $src_h;
    private $height;
    private $type;
    private $align;

    public function resize($image, $width = null, $height = null, $align = '') {
        $this->file = $image;
        $imageData = getimagesize($image);
        $this->width = $width ?? 0;
        $this->height = $height ?? 0;
        $this->align = $align;
        $name = explode('/', substr($this->file, 0, strrpos($this->file, '.')));
        $this->filename = md5(end($name) . $this->width . $this->height.$this->align);
        $dot = explode(".", $this->file);
        $ext = strtolower(end($dot));

        if (!file_exists('files/images/' . $this->filename . '.' . $ext)):
            $this->type = $imageData['mime'];

            $allowedImages = ['image/pjpeg', 'image/jpeg', 'image/jpg', 'image/x-png', 'image/png'];

            if ($this->width == 0 && $this->height == 0) {
                $this->width = 100;
                $this->height = 100;
            }

            if (in_array($this->type, $allowedImages)):
                return $this->manipulateImage($allowedImages);
            else:
                return 'Invalid mime type';
            endif;
        else:
            return 'files/images/' . $this->filename . '.' . $ext;
        endif;
    }

    private function manipulateImage() {
        $this->getimage();

        if (!empty($this->image)):
            list($width, $height) = getimagesize($this->file);

            if ($this->width && !$this->height) :
                $this->height = floor($height * ($this->width / $width));
            elseif ($this->height && !$this->width):
                $this->width = floor($width * ($this->height / $height));
            endif;

            // Retorna um identificador (recurso) que representa uma imagem 
            // preta do tamanho calculado anteriormente
            $image = imagecreatetruecolor($this->width, $this->height);

            // Cria um fundo transparente para a imagem
            $color = imagecolorallocatealpha($image, 0, 0, 0, 127);

            // Executa um preenchimento de recepiente a partir da coordenada 
            // especificada (o canto superior esquerdo é 0, 0) com dado $color no $image.   
            imagefill($image, 0, 0, $color);

            // Restaura a transparencia da imagem
            imagesavealpha($image, true);

            $this->src_x = $this->src_y = 0;
            $this->src_w = $width;
            $this->src_h = $height;

            $this->getMeasures();

            // Realiza o corte da imagem
            imagecopyresampled($image, $this->image, 0, 0, $this->src_x, $this->src_y, $this->width, $this->height, $this->src_w, $this->src_h);

            
            //Garante a existência da pasta
            if(!file_exists('files/images')):
                mkdir('files/images', 0775);
            endif;
            
            switch ($this->type):
                case 'image/jpg':
                case 'image/jpeg':
                case 'image/pjpeg':
                    imagejpeg($image, 'files/images/' . $this->filename . '.jpg');
                    $path = 'files/images/' . $this->filename . '.jpg';
                    break;
                case 'image/png':
                case 'image/x-png':
                    imagepng($image, 'files/images/' . $this->filename . '.png');
                    $path = 'files/images/' . $this->filename . '.png';
                    break;
            endswitch;
            imagedestroy($image);
            return $path;
        endif;
    }

    private function getimage() {
        switch ($this->type):
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $this->image = imagecreatefromjpeg($this->file);
                break;
            case 'image/png':
            case 'image/x-png':
                $this->image = imagecreatefrompng($this->file);
                break;
        endswitch;
    }

    //Obtem as medidas para o corte
    private function getMeasures() {
        list($width, $height) = getimagesize($this->file);
        // Compara no eixo X
        $cmp_x = $width / $this->width;
        // Compara no eixo Y
        $cmp_y = $height / $this->height;

        if ($cmp_x > $cmp_y):
            $this->src_w = round($width / $cmp_x * $cmp_y);
            $this->src_x = round(($width - ($width / $cmp_x * $cmp_y)) / 2);
        elseif ($cmp_y > $cmp_x):
            $this->src_h = round($height / $cmp_y * $cmp_x);
            $this->src_y = round(($height - ($height / $cmp_y * $cmp_x)) / 2);
        endif;

        $this->getPositioning($width, $height);
    }

    // Obtem o posicionamento do corte!
    private function getPositioning($width, $height) {
        switch ($this->align) {
            case 't':
            case 'tl':
            case 'lr':
            case 'tr':
            case 'rt':
                $this->src_y = 0;
                break;
            case 'b':
            case 'bl':
            case 'lb':
            case 'br':
            case 'rb':
                $this->src_y = $height - $this->src_h;
                break;
            case 'l':
            case 'tl':
            case 'lt':
            case 'bl':
            case 'lb':
                $this->src_x = 0;
                break;
            case 'r':
            case 'tr':
            case 'rt':
            case 'br':
            case 'rb':
                $this->src_x = $width - $this->width;
                $this->src_x = $width - $this->src_w;
                break;
            default:
                break;
        }
    }

}
