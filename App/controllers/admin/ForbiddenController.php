<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Description of ForbiddenController
 *
 * @author jucie
 */
class ForbiddenController extends Controller {
    public function index() {
        $this->getView('admin/forbidden', TEMPLATE);
    }
}
