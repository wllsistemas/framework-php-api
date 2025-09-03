<?php

class Authentication
{
    public static function bearer(Request $request): bool
    {
        return true;
    }

    public static function basic(Request $request): bool
    {
        return false;
    }
}
