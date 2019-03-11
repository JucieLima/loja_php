<?php

namespace App\Helpers;

/**
 * Description of Local
 *
 * @author jucie
 */
class Local {

    private $url;
    private $controller;
    private $action;
    private $params;

    function __construct() {
        $url = strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)));
        $this->url = explode('/', $url);
        if (isset($this->url[0]) && $this->url[0] != ''):
            $this->controller = $this->url[0];
            array_shift($this->url);
            $this->action = isset($this->url[0]) && $this->url[0] != '' ? $this->url[0] : 'index';
            array_shift($this->url);
            $this->params = isset($this->url[0]) && $this->url[0] != '' ? $this->url : null;
        else:
            $this->controller = 'index';
            $this->action = 'index';
        endif;
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }
    
    public function getParams() {
        return $this->params;
    }

}
