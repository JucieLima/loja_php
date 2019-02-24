<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Description of NotFoundController
 *
 * @author jucie
 */
class NotFoundController extends Controller{

    public function index() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        else:
            $this->getView('admin/404', 'admin/'.TEMPLATE);
        endif;
    }

}
