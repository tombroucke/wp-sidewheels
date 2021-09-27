<?php

if (!function_exists('sidewheelsUrl')) {
    function sidewheelsUrl($path, $variables = [])
    {
        foreach ($variables as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        return home_url('/') . $path;
    }
}
