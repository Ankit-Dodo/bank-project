<?php
namespace App\Core;
class Controller
{
    public function view($view, $data = array())
    {
        // $view e.g. "auth/login"

        $viewFile = APP_ROOT . '/app/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: " . $viewFile);
        }

        // make $data keys available as variables in the view
        foreach ($data as $key => $value) {
            $$key = $value;
        }

        $layoutFile = APP_ROOT . '/app/Views/layout/main.php';

        if (!file_exists($layoutFile)) {
            die("Layout file not found: " . $layoutFile);
        }

        // $viewFile is used inside layout
        require $layoutFile;
    }

    // protected function render(string $view, array $data = []){
    //     $viewPath = APP_ROOT . "/views" . $view . ".php";
    //     extract($data);
    //     require $viewPath;
    // }

}
