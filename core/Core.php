<?php

require_once 'Request.php';
require_once 'Response.php';

class Core
{
    public static function dispatch(array $routes)
    {
        $route_existe = false;
        $msg_erro = '';
        $url = '/';

        isset($_GET['url']) && $url .= $_GET['url'];

        $url !== '/' && $url = rtrim($url, '/');

        foreach ($routes as $route) :
            $regex = '#^' . preg_replace('/{(\w+)}/', '([\w\-.@]+)', $route['path']) . '$#';

            if (preg_match($regex, $url, $matches)) :
                $route_existe = true;
                array_shift($matches);

                if ($route['method'] !== Request::method()) :
                    $msg_erro .= 'Method ' . Request::method() . ' não aceito ou parâmetros inválidos.';
                    continue;
                endif;

                [$controller, $action] = explode('@', $route['action']);

                if (!file_exists("./controller/$controller.php")):
                    $msg_erro .= "Arquivo [$controller.php] não existe.";
                    continue;
                endif;

                require_once "./controller/$controller.php";

                if (!class_exists($controller)) :
                    $msg_erro .= "Class [$controller] não existe.";
                    continue;
                endif;

                if (isset($route['auth']) && !empty($route['auth'])) {
                    [$controllerAuth, $actionAuth] = explode('@', $route['auth']);

                    if (!is_dir("./auth")):
                        $msg_erro .= "Pasta 'auth' não existe.";
                        continue;
                    endif;

                    if (!file_exists("./auth/$controllerAuth.php")):
                        $msg_erro .= "Arquivo [$controllerAuth.php] não existe na pasta 'auth'.";
                        continue;
                    endif;

                    require_once "./auth/$controllerAuth.php";

                    if (!class_exists($controllerAuth)) :
                        $msg_erro .= "Class [$controllerAuth] não existe.";
                        continue;
                    endif;

                    $authentication = new $authentication();

                    if (!method_exists($authentication, $actionAuth)) {
                        $msg_erro .= "Action [$actionAuth] não existe na class [$controllerAuth].";
                        continue;
                    }

                    if (!$authentication->$actionAuth(new Request)) {
                        $msg_auth .= "Acesso não autorizado [$actionAuth].";
                        continue;
                    }
                }

                $controller = new $controller();

                if (!method_exists($controller, $action)) {
                    $msg_erro .= "Action [$action] não existe na class [$controller].";
                    continue;
                }

                $controller->$action(new Request, new Response, $matches);
                $msg_erro = '';
                break;
            endif;
        endforeach;

        if (!empty($msg_erro)) :
            Response::json([
                'status' => 'error',
                'message' => $msg_erro
            ], 405);
            return;
        endif;

        if (!$route_existe) :
            Response::json([
                'status' => 'error',
                'message' => "Rota '$url' não existe."
            ], 404);
            return;
        endif;

        if (!empty($msg_auth)) :
            Response::json([
                'status' => 'error',
                'message' => $msg_auth
            ], 401);
            return;
        endif;
    }
}
