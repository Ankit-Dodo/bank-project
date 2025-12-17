<?php
namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller {

    public function notFound(): void {
        http_response_code(404);
        require APP_ROOT . "/app/Views/404.php";
    }
}