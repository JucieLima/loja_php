<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Loja\Produtos;
use CoffeeCode\Cropper\Cropper;
use App\Models\Admin\Display;
use App\Models\Admin\ProdutoImagens;

/**
 * Description of ProdutoController
 *
 * @author jucie
 */
class ProdutosController extends Controller {

    public function item($url) {
        $produtos = new Produtos;
        $this->viewData['produto'] = $produtos->getProductByUri($url);
        if ($this->viewData['produto']):
            $display = new Display;
            $images = new ProdutoImagens;
            $this->viewData['images'] = $images->getImages($this->viewData['produto'][0]['id_produto']);
            $this->viewData['produto_gallery'] = $produtos->getProductByUri($url);
            $this->viewData['display'] = $display->find(1)->toArray();
            $this->viewData['marcas'] = $produtos->getListOfBrands();
            $this->viewData['range'] = $produtos->getPriceRange();
            $this->viewData['recommended'] = $produtos->getRecommended();
            $this->viewData['cropper'] = new Cropper('files/images');
            $this->getView('loja/produto', 'loja/' . TEMPLATE);
        else:
            header('Location: ' . BASE_URL . '/404');
        endif;
    }

}
