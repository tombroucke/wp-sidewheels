<?php

if (!function_exists('sidewheelsRoute')) {
    /**
     * Get full url for route
     *
     * @param string $path
     * @param array $replacements
     * @return string
     */
    function sidewheelsRoute(string $path, array $replacements = []) : string
    {
        return home_url('/') . sidewheelsReplaceRouteParameters($path, $replacements);
    }
}

if (!function_exists('sidewheelsReplaceRouteParameters')) {
    function sidewheelsReplaceRouteParameters(string $string, array $parameters) : string
    {
        foreach ($parameters as $key => $value) {
            $string = str_replace("{{$key}}", $value, $string);
        }
        return $string;
    }
}
