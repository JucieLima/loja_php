<?php

namespace App\Controllers\Loja;

use App\Core\Controller;

/**
 * Description of NotFoundController
 *
 * @author jucie
 */
class NotFoundController extends Controller {

    public function index() {
        $this->getView('loja/404', 'loja/' . TEMPLATE);
    }

}
