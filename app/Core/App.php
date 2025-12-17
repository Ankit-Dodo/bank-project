<?php
namespace App\Core;
class App
{
    private $controller = 'HomeController';
    private $method     = 'index';
    private $params     = array();

    public function __construct()
    {
        $url = $this->parseUrl();

        // decide controller name from URL (if present)
        if (isset($url[0]) && $url[0] !== '') {
            $candidate = ucfirst($url[0]) . 'Controller';
            $controllerFile = APP_ROOT . '/app/Controllers/' . $candidate . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $candidate;
                unset($url[0]);
            }
        }

        // require the controller file (if it exists)
        $controllerFilePath = APP_ROOT . '/app/Controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFilePath)) {
            // helpful error: file not found where router expects it
            throw new \Exception("Controller file not found: {$controllerFilePath}");
        }

        // capture currently declared classes so we can detect newly declared ones after require
        $declBefore = get_declared_classes();

        require_once $controllerFilePath;

        $declAfter = get_declared_classes();
        $newDecl   = array_diff($declAfter, $declBefore);

        // Try standard class name first (no namespace)
        $controllerClassName = $this->controller;

        if (class_exists($controllerClassName)) {
            // ok
        } else {
            // If class not found, attempt to detect namespace declared in the file
            $fileContents = file_get_contents($controllerFilePath);
            $ab = null;
            if (preg_match('/namespace\s+([^;]+);/i', $fileContents, $m)) {
                $ab = trim($m[1]);
                $fqcn = $ab . '\\' . $this->controller;
                if (class_exists($fqcn)) {
                    $controllerClassName = $fqcn;
                }
            }

            // If still not found, inspect newly-declared classes and pick one that ends with "Controller"
            if (!class_exists($controllerClassName)) {
                $found = null;
                foreach ($newDecl as $c) {
                    if (str_ends_with($c, 'Controller')) {
                        $found = $c;
                        break;
                    }
                }
                if ($found !== null) {
                    $controllerClassName = $found;
                }
            }

            // Final check
            if (!class_exists($controllerClassName)) {
                // Build helpful debug message
                $foundClasses = !empty($newDecl) ? implode(', ', $newDecl) : '(none)';
                throw new \Exception("Controller class '{$this->controller}' not found after requiring {$controllerFilePath}."
                    . " Newly-declared classes: {$foundClasses}. "
                    . "Check the class name and namespace inside the file.");
            }
        }

        // instantiate controller
        $this->controller = new $controllerClassName;

        // method selection from URL (if present and exists on controller)
        if (isset($url[1]) && $url[1] !== '' && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // remaining params
        $this->params = $url ? array_values($url) : array();

        // call
        call_user_func_array(array($this->controller, $this->method), $this->params);
    }

    // private function parseUrl()
    // {
    //     if (!isset($_GET['url'])) {
    //         return array();
    //     }

    //     $url = rtrim($_GET['url'], '/');
    //     $url = filter_var($url, FILTER_SANITIZE_URL);
    //     return explode('/', $url);
    // }
    private function parseUrl()
{
    // STRICT: only allow index.php at ROOT
    $script = $_SERVER['SCRIPT_NAME'];      // /index.php
    $request = $_SERVER['REQUEST_URI'];     // /dgcegd/index.php?url=...

    if ($script !== '/index.php') {
        $this->abort404();
    }

    if (!isset($_GET['url'])) {
        return [];
    }

    $url = trim($_GET['url'], '/');

    // block index.php, commas, dots, weird chars
    if (
        str_contains($url, 'index.php') ||
        preg_match('/[^a-zA-Z0-9\/_-]/', $url)
    ) {
        $this->abort404();
    }

    return explode('/', $url);
}
private function abort404()
{
    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
    exit;
}

}
