<?php

if (!function_exists('sidewheelsRoute')) {
    function sidewheelsRoute($path, $variables = [])
    {
        foreach ($variables as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        return home_url('/') . $path;
    }
}
