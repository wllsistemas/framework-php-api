<?php

class Http
{
    private static $routes = [];

    public static function get(string $path, string $action, string $auth = '')
    {
        self::$routes[] = [
            'path' => $path,
            'action' => $action,
            'method' => 'GET',
            'auth' => $auth
        ];
    }

    public static function post(string $path, string $action, string $auth = '')
    {
        self::$routes[] = [
            'path' => $path,
            'action' => $action,
            'method' => 'POST',
            'auth' => $auth
        ];
    }

    public static function put(string $path, string $action, string $auth = '')
    {
        self::$routes[] = [
            'path' => $path,
            'action' => $action,
            'method' => 'PUT',
            'auth' => $auth
        ];
    }

    public static function patch(string $path, string $action, string $auth = '')
    {
        self::$routes[] = [
            'path' => $path,
            'action' => $action,
            'method' => 'PATCH',
            'auth' => $auth
        ];
    }

    public static function delete(string $path, string $action, string $auth = '')
    {
        self::$routes[] = [
            'path' => $path,
            'action' => $action,
            'method' => 'DELETE',
            'auth' => $auth
        ];
    }

    public static function routes()
    {
        return self::$routes;
    }
}
