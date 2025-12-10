<?php

class App
{
    private $controller = 'HomeController';
    private $method     = 'index';
    private $params     = array();

    public function __construct()
    {
        $url = $this->parseUrl();

        if (isset($url[0]) && $url[0] !== '') {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = APP_ROOT . '/app/Controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        require_once APP_ROOT . '/app/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1]) && $url[1] !== '' && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : array();

        call_user_func_array(array($this->controller, $this->method), $this->params);
    }

    private function parseUrl()
    {
        if (!isset($_GET['url'])) {
            return array();
        }

        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return explode('/', $url);
    }
}
