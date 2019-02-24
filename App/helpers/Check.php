<?php

namespace App\Helpers;

/**
 * Check.class [ HELPER ]
 * Classe responável por manipular e validade dados do sistema!
 * 
 * @copyright (c) 2014, Robson V. Leite UPINSIDE TECNOLOGIA
 */
class Check {

    private static $data;
    private static $format;

    /**
     * <b>Verifica E-mail:</b> Executa validação de formato de e-mail. Se for um email válido retorna true, ou retorna false.
     * @param STRING $email = Uma conta de e-mail
     * @return BOOL = True para um email válido, ou false
     */
    public static function email($email) {
        self::$data = (string) $email;
        self::$format = '/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/';

        if (preg_match(self::$format, self::$data)):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * <b>Tranforma URL:</b> Tranforma uma string no formato de URL amigável e retorna o a string convertida!
     * @param STRING $Name = Uma string qualquer
     * @return STRING = $data = Uma URL amigável válida
     */
    public static function uri($name) {
        self::$format = array();
        self::$format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        self::$format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        self::$data = strtr(utf8_decode($name), utf8_decode(self::$format['a']), self::$format['b']);
        self::$data = strip_tags(trim(self::$data));
        self::$data = str_replace(' ', '-', self::$data);
        self::$data = str_replace(array('-----', '----', '---', '--'), '-', self::$data);

        return strtolower(utf8_encode(self::$data));
    }

    /**
     * <b>Tranforma data:</b> Transforma uma data no formato DD/MM/YY em uma data no formato TIMESTAMP!
     * @param STRING $Name = data em (d/m/Y) ou (d/m/Y H:i:s)
     * @return STRING = $data = data no formato timestamp!
     */
    public static function data($data) {
        self::$format = explode(' ', $data);
        self::$data = explode('/', self::$format[0]);

        if (empty(self::$format[1])):
            self::$format[1] = date('H:i:s');
        endif;

        self::$data = self::$data[2] . '-' . self::$data[1] . '-' . self::$data[0] . ' ' . self::$format[1];
        return self::$data;
    }

    /**
     * <b>Limita os Palavras:</b> Limita a quantidade de palavras a serem exibidas em uma string!
     * @param STRING $string = Uma string qualquer
     * @return INT = $limite = String limitada pelo $limite
     */
    public static function Words($string, $limite, $pointer = null) {
        self::$data = strip_tags(trim($string));
        self::$format = (int) $limite;

        $ArrWords = explode(' ', self::$data);
        $NumWords = count($ArrWords);
        $NewWords = implode(' ', array_slice($ArrWords, 0, self::$format));

        $pointer = (empty($pointer) ? '...' : ' ' . $pointer );
        $Result = ( self::$format < $NumWords ? $NewWords . $pointer : self::$data );
        return $Result;
    }

    /**
     * <b>Obter categoria:</b> Informe o name (url) de uma categoria para obter o ID da mesma.
     * @param STRING $category_name = URL da categoria
     * @return INT $category_id = id da categoria informada
     */
    public static function CatByName($categoryName) {
        $read = new Read;
        $read->ExeRead('ws_categories', "WHERE category_name = :name", "name={$categoryName}");
        if ($read->getRowCount()):
            return $read->getResult()[0]['category_id'];
        else:
            echo "A categoria {$categoryName} não foi encontrada!";
            die;
        endif;
    }

    /**
     * <b>Usuários Online:</b> Ao executar este HELPER, ele automaticamente deleta os usuários expirados. Logo depois
     * executa um READ para obter quantos usuários estão realmente online no momento!
     * @return INT = Qtd de usuários online
     */
    public static function userOnline() {
        $now = date('Y-m-d H:i:s');
        $deleteUserOnline = new Delete;
        $deleteUserOnline->ExeDelete('ws_siteviews_online', "WHERE online_endview < :now", "now={$now}");

        $readUserOnline = new Read;
        $readUserOnline->ExeRead('ws_siteviews_online');
        return $readUserOnline->getRowCount();
    }

    /**
     * <b>imagem Upload:</b> Ao executar este HELPER, ele automaticamente verifica a existencia da imagem na pasta
     * uploads. Se existir retorna a imagem redimensionada!
     * @return HTML = imagem redimencionada!
     */
    public static function image($imageUrl, $imageDesc, $imageW = null, $imageH = null) {

        self::$data = $imageUrl;

        if (file_exists(self::$data) && !is_dir(self::$data)):
            $patch = HOME;
            $imagem = self::$data;
            return "<img src=\"{$patch}/tim.php?src={$patch}/{$imagem}&w={$imageW}&h={$imageH}\" alt=\"{$imageDesc}\" title=\"{$imageDesc}\"/>";
        else:
            return false;
        endif;
    }

}
