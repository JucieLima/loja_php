<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
/**
 * Description of HomeController
 *
 * @author jucie
 */
class HomeController extends Controller{
    public function index() {
        if(!isset($_SESSION['userlogin'])):
            header('Location: '.BASE_URL.'admin/login');
        else:
            $this->getView('admin/home', 'admin/'.TEMPLATE);
        endif;
    }
}
