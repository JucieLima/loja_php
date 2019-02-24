<?php

namespace App\Helpers;

use App\Helpers\Check;

class Upload {

    private $file;
    private $name;
    private $send;

    /** IMAGE UPLOAD */
    private $width;
    private $image;

    /** RESULTSET */
    private $result;
    private $error;

    /** DIRETÓTIOS */
    private $folder;
    private static $baseDir;

    /**
     * Verifica e cria o diretório padrão de uploads no sistema!<br>
     * <b>../uploads/</b>
     */
    function __construct($baseDir = null) {
        self::$baseDir = ( (string) $baseDir ? $baseDir : 'uploads/');
        if (!file_exists(self::$baseDir) && !is_dir(self::$baseDir)):
            mkdir(self::$baseDir, 0777);
        endif;
    }

    /**
     * <b>Enviar imagem:</b> Basta envelopar um $_FILES de uma imagem e caso queira um nome e uma largura personalizada.
     * Caso não informe a largura será 1024!
     * @param FILES $image = Enviar envelope de $_FILES (JPG ou PNG)
     * @param STRING $name = Nome da imagems ( ou do artigo )
     * @param INT $width = Largura da imagem ( 1024 padrão )
     * @param STRING $folder = Pasta personalizada
     */
    public function image(array $image, $name = null, $width = null, $folder = null) {
        $this->file = $image;
        $this->name = ( (string) $name ? $name : substr($image['name'], 0, strrpos($image['name'], '.')) );
        $this->width = ( (int) $width ? $width : 1024 );
        $this->folder = ( (string) $folder ? $folder : 'images' );

        $this->checkFolder($this->folder);
        $this->setFileName();
        $this->uploadImage();
    }

    /**
     * <b>Enviar Arquivo:</b> Basta envelopar um $_FILES de um arquivo e caso queira um nome e um tamanho personalizado.
     * Caso não informe o tamanho será 2mb!
     * @param FILES $file = Enviar envelope de $_FILES (PDF ou DOCX)
     * @param STRING $name = Nome do arquivo ( ou do artigo )
     * @param STRING $folder = Pasta personalizada
     * @param STRING $maxFileSize = Tamanho máximo do arquivo (2mb)
     */
    public function file(array $file, $name = null, $folder = null, $maxFileSize = null) {
        $this->file = $file;
        $this->name = ( (string) $name ? $name : substr($file['name'], 0, strrpos($file['name'], '.')) );
        $this->folder = ( (string) $folder ? $folder : 'files' );
        $maxFileSize = ( (int) $maxFileSize ? $maxFileSize : 2 );

        $fileAccept = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf'
        ];

        if ($this->file['size'] > ($maxFileSize * (1024 * 1024))):
            $this->result = false;
            $this->error = "Arquivo muito grande, tamanho máximo permitido de {$maxFileSize}mb";
        elseif (!in_array($this->file['type'], $fileAccept)):
            $this->result = false;
            $this->error = 'Tipo de arquivo não suportado. Envie .PDF ou .DOCX!';
        else:
            $this->checkFolder($this->folder);
            $this->setFileName();
            $this->moveFile();
        endif;
    }

    /**
     * <b>Enviar Mífia:</b> Basta envelopar um $_FILES de uma mídia e caso queira um nome e um tamanho personalizado.
     * Caso não informe o tamanho será 40mb!
     * @param FILES $Media = Enviar envelope de $_FILES (MP3 ou MP4)
     * @param STRING $name = Nome do arquivo ( ou do artigo )
     * @param STRING $folder = Pasta personalizada
     * @param STRING $maxFileSize = Tamanho máximo do arquivo (40mb)
     */
    public function media(array $Media, $name = null, $folder = null, $maxFileSize = null) {
        $this->file = $Media;
        $this->name = ( (string) $name ? $name : substr($Media['name'], 0, strrpos($Media['name'], '.')) );
        $this->folder = ( (string) $folder ? $folder : 'medias' );
        $maxFileSize = ( (int) $maxFileSize ? $maxFileSize : 40 );

        $fileAccept = [
            'audio/mp3',
            'video/mp4'
        ];

        if ($this->file['size'] > ($maxFileSize * (1024 * 1024))):
            $this->result = false;
            $this->error = "Arquivo muito grande, tamanho máximo permitido de {$maxFileSize}mb";
        elseif (!in_array($this->file['type'], $fileAccept)):
            $this->result = false;
            $this->error = 'Tipo de arquivo não suportado. Envie audio MP3 ou vídeo MP4!';
        else:
            $this->checkFolder($this->folder);
            $this->setFileName();
            $this->moveFile();
        endif;
    }

    /**
     * <b>Verificar Upload:</b> Executando um getResult é possível verificar se o Upload foi executado ou não. Retorna
     * uma string com o caminho e nome do arquivo ou FALSE.
     * @return STRING  = Caminho e Nome do arquivo ou False
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * <b>Obter Erro:</b> Retorna um array associativo com um code, um title, um erro e um tipo.
     * @return ARRAY $Error = Array associatico com o erro
     */
    public function getError() {
        return $this->error;
    }

    /*
     * ***************************************
     * **********  PRIVATE METHODS  **********
     * ***************************************
     */

    //Verifica e cria os diretórios com base em tipo de arquivo, ano e mês!
    private function checkFolder($folder) {
        list($y, $m) = explode('/', date('Y/m'));
        $this->createFolder("{$folder}");
        $this->createFolder("{$folder}/{$y}");
        $this->createFolder("{$folder}/{$y}/{$m}/");
        $this->send = "{$folder}/{$y}/{$m}/";
    }

    //Verifica e cria o diretório base!
    private function createFolder($folder) {
        if (!file_exists(self::$baseDir . $folder) && !is_dir(self::$baseDir . $folder)):
            mkdir(self::$baseDir . $folder, 0777);
        endif;
    }

    //Verifica e monta o nome dos arquivos tratando a string!
    private function setFileName() {
        $fileName = Check::uri($this->name) . strrchr($this->file['name'], '.');
        if (file_exists(self::$baseDir . $this->send . $fileName)):
            $fileName = Check::uri($this->name) . '-' . time() . strrchr($this->file['name'], '.');
        endif;
        $this->name = $fileName;
    }

    //Realiza o upload de imagens redimensionando a mesma!
    private function uploadImage() {
        
        $maxFileSize = 5;

        switch ($this->file['type']):
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $this->image = imagecreatefromjpeg($this->file['tmp_name']);
                break;
            case 'image/png':
            case 'image/x-png':
                $this->image = imagecreatefrompng($this->file['tmp_name']);
                break;
        endswitch;

        if (!$this->image):
            $this->result = false;
            $this->error = 'Tipo de arquivo inválido, envie imagens JPG ou PNG!';
        elseif ($this->file['size'] > ($maxFileSize * (1024 * 1024))):
            $this->result = false;
            $this->error = "Arquivo muito grande, tamanho máximo permitido de {$maxFileSize}mb";
        else:
            $x = imagesx($this->image);
            $y = imagesy($this->image);
            $imageX = ( $this->width < $x ? $this->width : $x );
            $imageH = ($imageX * $y) / $x;

            $newImage = imagecreatetruecolor($imageX, $imageH);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $imageX, $imageH, $x, $y);

            switch ($this->file['type']):
                case 'image/jpg':
                case 'image/jpeg':
                case 'image/pjpeg':
                    imagejpeg($newImage, self::$baseDir . $this->send . $this->name);
                    break;
                case 'image/png':
                case 'image/x-png':
                    imagepng($newImage, self::$baseDir . $this->send . $this->name);
                    break;
            endswitch;

            if (!$newImage):
                $this->result = false;
                $this->error = 'Tipo de arquivo inválido, envie imagens JPG ou PNG!';
            else:
                $this->result = $this->send . $this->name;
                $this->error = null;
            endif;

            imagedestroy($this->image);
            imagedestroy($newImage);
        endif;
    }

    //Envia arquivos e mídias
    private function moveFile() {
        if (move_uploaded_file($this->file['tmp_name'], self::$baseDir . $this->send . $this->name)):
            $this->result = $this->send . $this->name;
            $this->error = null;
        else:
            $this->result = false;
            $this->error = 'Erro ao mover o arquivo. Favor tente mais tarde!';
        endif;
    }

}
