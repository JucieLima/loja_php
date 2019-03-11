<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Admin\Slider;
use App\Models\Admin\Display;
use App\Models\Loja\Produtos;
use App\Helpers\ResizeImage;

/**
 * Description of HomeController
 *
 * @author jucie
 */
class HomeController extends Controller {

    public function index() {
        $slider = new Slider;
        $display = new Display;
        $produtos = new Produtos;        
        $this->viewData['slider'] = $slider->all()->toArray();
        $this->viewData['display'] = $display->find(1)->toArray();
        $this->viewData['featured_products'] = $produtos->getProdutos($this->viewData['display'] ['featured_products'], 0);
        $this->viewData['recommended'] = $produtos->getRecommended();
        $this->viewData['marcas'] = $produtos->getListOfBrands();
        $this->viewData['range'] = $produtos->getPriceRange();
        $this->viewData['cropper'] = new ResizeImage;
        $this->getView('loja/home', 'loja/' . TEMPLATE);        
    }

}
