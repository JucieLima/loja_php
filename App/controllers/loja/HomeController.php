<?php

namespace App\Controllers\Loja;

use App\Core\Controller;

/**
 * Description of HomeController
 *
 * @author jucie
 */
class HomeController extends Controller {

    public function index() {
        $this->getView('loja/home', 'loja/' . TEMPLATE);
    }

}
