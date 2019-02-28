<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use App\Models\Admin\Loja;
use App\Models\Admin\Categorias;
use App\Models\Admin\Slider;

/**
 * Description of HomeController
 *
 * @author jucie
 */
class HomeController extends Controller {

    public function index() {
        $loja = new Loja;
        $slider = new Slider;
        $this->viewData['loja'] = $loja->find(1)->toArray();
        $this->viewData['categorias'] = new Categorias;
        $this->viewData['slider'] = $slider->all()->toArray();
        $this->getView('loja/home', 'loja/' . TEMPLATE);
    }

}
