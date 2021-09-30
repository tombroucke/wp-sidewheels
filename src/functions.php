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
        foreach ($replacements as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        return home_url('/') . $path;
    }
}
