<?php

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

    public function model($model)
    {
        // looks into app/models/User.php when $model = "User"
        $file = APP_ROOT . '/app/models/' . $model . '.php';

        if (!file_exists($file)) {
            die("Model file not found: " . $file);
        }

        require_once $file;

        if (!class_exists($model)) {
            die("Model class '$model' not found in file: " . $file);
        }

        return new $model();
    }
}
