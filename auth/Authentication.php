<?php

class Authentication
{
    public static function bearer(Request $request): bool
    {
        $token = $request->header('Authorization');
        if ($token) {
            return true;
        }

        return false;
    }

    public static function basic(Request $request): bool
    {
        $token = $request->header('Authorization');
        if ($token) {
            return true;
        }

        return false;
    }
}
