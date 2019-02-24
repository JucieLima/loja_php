<?php

namespace App\Core;

/**
 * Description of Core
 *
 * @author jucie
 */
class Run {

    private $zones;
    private $zone;
    private $url;
    private $params;
    private $controller;
    private $action;

    public function __construct(array $zones = null) {
        $this->zones = $zones ? $zones : array();
        $this->params = array();
        $url = strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)));
        $this->url = explode('/', $url);
        $this->walkZones();
        $this->setController();
        $this->callControllers();
    }
    
    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }
    
    public function getZone() {
        return $this->zone;
    }

    private function walkZones() {
        foreach ($this->zones as $zone):
            if (key($zone) == $this->url[0]):
                $this->zone = $zone[key($zone)] . '\\';
                array_shift($this->url);
                $this->url[0] = isset($this->url[0]) ? $this->url[0] : 'Home';
                break;
            else:
                $this->zone = 'loja\\';
            endif;
        endforeach;
    }

    private function setController() {
        if (isset($this->url[0]) && $this->url[0] != ''):
            $this->controller = '\\App\\Controllers\\' . $this->zone . ucfirst($this->url[0]) . 'Controller';
            array_shift($this->url);
            $this->action = isset($this->url[0]) && $this->url[0] != '' ? $this->url[0] : 'index';
            array_shift($this->url);
            $this->params = count($this->url) > 0 ? $this->url : $this->params;
        else:
            $this->controller = '\App\Controllers\\' . $this->zone . 'HomeController';
            $this->action = 'index';
        endif;
    }

    private function callControllers() {
        if (file_exists(__dir__ . "/../controllers/" . $this->zone . $this->controller . ".php") || !method_exists($this->controller, $this->action)) :
            $this->controller = '\App\Controllers\\' . $this->zone . 'NotFoundController';
            $this->action = 'index';
        endif;

        $c = new $this->controller;
        call_user_func_array(array($c, $this->action), $this->params);
    }

}
