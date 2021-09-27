<?php

namespace Otomaties\Sidewheels;

class Router
{

    public static function addRoute(string $path, mixed $callback, mixed $capability = null, mixed $methods) : Route
    {
        return new Route($path, $callback, $capability, $methods);
    }
}
