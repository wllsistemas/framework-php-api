<?php

require_once 'Request.php';
require_once 'Response.php';

class Core
{
    public static function dispatch(array $routes)
    {
        $url = self::getUrl();

        foreach ($routes as $route) :
            $msgErro = [];

            if (!self::checkRoute($route, $url, $matches, $msgErro))
                continue;

            if (!self::checkMethod($route, $msgErro))
                continue;

            if (!self::checkAuth($route, $msgErro))
                break;

            self::executeAction($route, $matches, $msgErro);
            break;
        endforeach;

        if (count($msgErro) > 0) {
            Response::json([
                'message' => $msgErro['message']
            ], $msgErro['code']);
        }
    }

    private static function getUrl(): string
    {
        $url = '/';
        isset($_GET['url']) && $url .= $_GET['url'];
        $url !== '/' && $url = rtrim($url, '/');

        return $url;
    }

    private static function checkRoute(array $route, string $url, ?array &$matches, array &$msgErro): bool
    {
        $regex = '#^' . preg_replace('/{(\w+)}/', '([\w\-.@]+)', $route['path']) . '$#';
        if (preg_match($regex, $url, $matches)) {
            array_shift($matches);
            return true;
        }

        $msgErro = [
            'message' => "Rota '$url' não existe.",
            'code' => 404
        ];
        return false;
    }

    private static function checkMethod(array $route, array &$msgErro): bool
    {
        if ($route['method'] !== Request::method()) {
            $msgErro = [
                'message' => 'Method ' . Request::method() . ' não aceito.',
                'code' => 405
            ];
            return false;
        }
        return true;
    }

    private static function checkAuth(array $route, array &$msgErro): bool
    {
        if (empty($route['auth']))
            return true;

        [$controllerAuth, $actionAuth] = explode('@', $route['auth']);

        if (!is_dir("./auth")) {
            $msgErro = [
                'message' => "Pasta 'auth' não existe.",
                'code' => 404
            ];
            return false;
        }

        if (!file_exists("./auth/$controllerAuth.php")) {
            $msgErro = [
                'message' => "Arquivo [$controllerAuth.php] não existe na pasta 'auth'.",
                'code' => 404
            ];
            return false;
        }

        require_once "./auth/$controllerAuth.php";

        if (!class_exists($controllerAuth)) {
            $msgErro = [
                'message' => "Class [$controllerAuth] não existe.",
                'code' => 404
            ];
            return false;
        }

        $authentication = new $controllerAuth();

        if (!method_exists($authentication, $actionAuth)) {
            $msgErro = [
                'message' => "Action [$actionAuth] não existe na class [$controllerAuth].",
                'code' => 404
            ];
            return false;
        }

        if (!$authentication->$actionAuth(new Request)) {
            $msgErro = [
                'message' => "Não autorizado.",
                'code' => 401
            ];
            return false;
        }

        return true;
    }

    private static function executeAction(array $route, array $matches, array &$msgErro): bool
    {
        [$controller, $method] = explode('@', $route['action']);

        if (!file_exists("./controller/$controller.php")) {
            $msgErro = [
                'message' => "Arquivo [$controller.php] não existe.",
                'code' => 404
            ];
            return false;
        }

        require_once "./controller/$controller.php";

        if (!class_exists($controller)) {
            $msgErro = [
                'message' => "Class [$controller] não existe.",
                'code' => 404
            ];
            return false;
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            $msgErro = [
                'message' => "Action [$method] não existe na class [$controller].",
                'code' => 404
            ];
            return false;
        }

        $instance->$method(new Request, new Response, $matches);
        return true;
    }
}
