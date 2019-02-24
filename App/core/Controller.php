<?php

namespace App\Core;

/**
 * Description of Controller
 *
 * @author jucie
 */
class Controller {

    protected $viewData = array();
    private $viewPath;
    private $templatePath;

    protected function getView($viewPath, $templatePath = null) {
        $this->viewPath = $viewPath;
        $this->templatePath = $templatePath;
        if ($this->templatePath):
            $this->template();
        else:
            $this->content();
        endif;        
    }

    private function content() {
        if (file_exists(__DIR__ . "/../views/{$this->viewPath}.phtml")):
            require_once __DIR__ . "/../views/{$this->viewPath}.phtml";
        else:
            echo 'Error: View path not found!<br/>';
            echo __DIR__ . "/../views/{$this->viewPath}.phtml";
        endif;
    }

    private function template() {
        if (file_exists(__DIR__ . "/../views/{$this->templatePath}.phtml")):
            require_once __DIR__ . "/../views/{$this->templatePath}.phtml";
        else:
            echo 'Error: template path not found!';
        endif;
    }

}
