<?php

require_once 'Request.php';
require_once 'Response.php';

class Core
{
    public static function dispatch(array $routes)
    {
        $url = self::getUrl();
        $msgErro = '';
        $msgAuth = '';
        $routeExiste = false;

        foreach ($routes as $route) :
            if (!self::matchRoute($route, $url, $matches)) {
                continue;
            }

            $routeExiste = true;

            if (!self::checkMethod($route)) {
                $msgErro = 'Method ' . Request::method() . ' não aceito ou parâmetros inválidos.';
                continue;
            }

            if (isset($route['auth']) && !empty($route['auth'])) {
                if (!self::checkAuth($route['auth'], $msgAuth)) {
                    continue;
                }
            }

            if (!self::executeAction($route['action'], $matches, $msgErro)) {
                continue;
            }

            $msgErro = '';
            break;
        endforeach;

        self::handleErrors($routeExiste, $url, $msgErro, $msgAuth);
    }

    private static function getUrl(): string
    {
        $url = '/';
        isset($_GET['url']) && $url .= $_GET['url'];
        $url !== '/' && $url = rtrim($url, '/');

        return $url;
    }

    private static function matchRoute(array $route, string $url, ?array &$matches): bool
    {
        $regex = '#^' . preg_replace('/{(\w+)}/', '([\w\-.@]+)', $route['path']) . '$#';
        if (preg_match($regex, $url, $matches)) {
            array_shift($matches);
            return true;
        }
        return false;
    }

    private static function checkMethod(array $route): bool
    {
        return $route['method'] === Request::method();
    }

    private static function checkAuth(string $auth, string &$msgAuth): bool
    {
        [$controllerAuth, $actionAuth] = explode('@', $auth);

        if (!is_dir("./auth")) {
            $msgAuth = "Pasta 'auth' não existe.";
            return false;
        }

        if (!file_exists("./auth/$controllerAuth.php")) {
            $msgAuth = "Arquivo [$controllerAuth.php] não existe na pasta 'auth'.";
            return false;
        }

        require_once "./auth/$controllerAuth.php";

        if (!class_exists($controllerAuth)) {
            $msgAuth = "Class [$controllerAuth] não existe.";
            return false;
        }

        $authentication = new $controllerAuth();

        if (!method_exists($authentication, $actionAuth)) {
            $msgAuth = "Action [$actionAuth] não existe na class [$controllerAuth].";
            return false;
        }

        if (!$authentication->$actionAuth(new Request)) {
            $msgAuth = "Acesso não autorizado [$actionAuth].";
            return false;
        }

        return true;
    }

    private static function executeAction(string $action, array $matches, string &$msgErro): bool
    {
        [$controller, $method] = explode('@', $action);

        if (!file_exists("./controller/$controller.php")) {
            $msgErro = "Arquivo [$controller.php] não existe.";
            return false;
        }

        require_once "./controller/$controller.php";

        if (!class_exists($controller)) {
            $msgErro = "Class [$controller] não existe.";
            return false;
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            $msgErro = "Action [$method] não existe na class [$controller].";
            return false;
        }

        $instance->$method(new Request, new Response, $matches);
        return true;
    }

    private static function handleErrors(bool $routeExiste, string $url, string $msgErro, string $msgAuth): void
    {
        if (!empty($msgErro)) {
            Response::json([
                'status' => 'error',
                'message' => $msgErro
            ], 405);
            return;
        }

        if (!$routeExiste) {
            Response::json([
                'status' => 'error',
                'message' => "Rota '$url' não existe."
            ], 404);
            return;
        }

        if (!empty($msgAuth)) {
            Response::json([
                'status' => 'error',
                'message' => $msgAuth
            ], 401);
            return;
        }
    }
}
