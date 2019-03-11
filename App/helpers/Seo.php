<?php

namespace App\Helpers;

use App\Models\Admin\Seo as ReadSeo;
use App\Models\Admin\Loja;
use App\Models\Admin\Produtos;
use App\Models\Admin\ProdutoImagens;

/**
 * Description of Seo
 *
 * @author jucie
 */
class Seo {

    private $controller;
    private $action;
    private $params;
    private $data;
    private $tags;
    private $store;

    /** dados povoados * */
    private $seoTags;

    function __construct($controller, $action, $params = null) {
        $this->controller = strip_tags(trim($controller));
        $this->action = strip_tags(trim($action));
        $this->params = $params ? filter_var_array($params, FILTER_DEFAULT) : null;
        $loja = new Loja;
        $this->store = $loja->find(1)->toArray();
        $this->setSeo();
    }

    public function getSeoTags() {
        return $this->seoTags;
    }

    /** PRIVATES * */
    private function setSeo() {
        $readSeo = new ReadSeo;
        $pages = $readSeo->all()->toArray();
        foreach ($pages as $page):
            switch ($this->controller):
                case 'index':
                    $this->data = [
                        'titulo' => $this->store['titulo'],
                        'description' => $this->store['descricao'],
                        'url' => $this->store['url_loja'],
                        'image' => BASE_URL . 'uploads/' . $this->store['imagem_padrao']
                    ];
                    break;
                case 'produtos':
                    $this->dataProducts();
                    break;
                case $this->controller == $page['controller'] && $this->action == $page['action']:
                    $this->data = $page;
                    break;
                case '404':
                default :
                    $this->data = [
                        'titulo' => 'Página não encontrada - ' . $this->store['titulo'],
                        'description' => 'Página não encontrada',
                        'url' => BASE_URL . '404',
                        'image' => BASE_URL . 'uploads/' . $this->store['imagem_padrao']
                    ];
            endswitch;
        endforeach;

        if ($this->data):
            $this->setTags();
        endif;
    }

    private function setTags() {
        $this->tags['title'] = $this->data['titulo'];
        $this->tags['description'] = Check::Words(html_entity_decode($this->data['description']), 25);
        $this->tags['url'] = $this->data['url'];
        $this->tags['image'] = $this->data['image'] ? $this->data['image'] : $this->store['imagem_padrao'];

        $this->tags = array_map('strip_tags', $this->tags);
        $this->tags = array_map('trim', $this->tags);

        $this->data = null;

        $this->setSeoTags();
    }

    private function setSeoTags() {

        $this->seoTags = '<title>' . $this->tags['title'] . '</title> ' . "\n";
        $this->seoTags .= '        <meta name="description" content="' . $this->tags['description'] . '"/>' . "\n";
        $this->seoTags .= '        <meta name="robots" content="index, follow" />' . "\n";
        $this->seoTags .= '        <link rel="canonical" href="' . $this->tags['url'] . '">' . "\n";
        $this->seoTags .= "\n";

        //FACEBOOK
        $this->seoTags .= '        <meta property="og:site_name" content="' . SITENAME . '" />' . "\n";
        $this->seoTags .= '        <meta property="og:locale" content="pt_BR" />' . "\n";
        $this->seoTags .= '        <meta property="og:title" content="' . $this->tags['title'] . '" />' . "\n";
        $this->seoTags .= '        <meta property="og:description" content="' . $this->tags['description'] . '" />' . "\n";
        $this->seoTags .= '        <meta property="og:image" content="' . $this->tags['image'] . '" />' . "\n";
        $this->seoTags .= '        <meta property="og:url" content="' . $this->tags['url'] . '" />' . "\n";
        $this->seoTags .= '        <meta property="og:type" content="article" />' . "\n";
        $this->seoTags .= "\n";

        //ITEM GROUP (TWITTER)
        $this->seoTags .= '        <meta itemprop="name" content="' . $this->tags['title'] . '">' . "\n";
        $this->seoTags .= '        <meta itemprop="description" content="' . $this->tags['description'] . '">' . "\n";
        $this->seoTags .= '        <meta itemprop="url" content="' . $this->tags['url'] . '">' . "\n";

        $this->tags = null;
    }

    private function dataProducts() {
        $product = new Produtos;
        $image = new ProdutoImagens;
        $readProduct = $product->where("path_produto", $this->params[0])->get()->toArray();
        if ($readProduct):
            $cover = $image->getCover($readProduct[0]['id_produto']);
            $this->data = [
                'titulo' => $readProduct[0]['titulo_produto'] . ' - ' . SITENAME,
                'description' => $readProduct[0]['descricao_produto'],
                'url' => BASE_URL . 'produto/' . $readProduct[0]['path_produto'],
                'image' => BASE_URL . 'uploads/' . $cover[0]['imagem_uri']
            ];
        else:
            header('Location: ' . BASE_URL . '404');
        endif;
    }

}
