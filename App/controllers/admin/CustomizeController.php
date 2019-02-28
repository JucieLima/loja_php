<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Slider;
use App\Models\Admin\Display;

/**
 * Description of CustomizeController
 *
 * @author jucie
 */
class CustomizeController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 1):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function home() {
        $this->viewData['silder'] = new Slider;
        $this->viewData['display'] = new Display;
        $this->getView('admin/customize/home', 'admin/' . TEMPLATE);
    }

    public function update_slider() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $slider = new Slider;
        $update = $slider->updateSlider($dados);
        if ($update):
            $response['response'] = "success";
            $response['result'] = $slider->getResult();
        else:
            $response['response'] = $slider->getError();
        endif;
        echo json_encode($response);
    }

    public function update_display() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_NUMBER_INT);
        $display = new Display;
        $update = $display->updateHome($dados);
        if ($update):
            $response['response'] = "success";
            $response['result'] = $display->getResult();
        else:
            $response['response'] = $display->getError();
        endif;
        echo json_encode($response);
    }

}
