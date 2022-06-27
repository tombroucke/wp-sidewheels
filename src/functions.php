<?php

if (!function_exists('sidewheelsReplaceRouteParameters')) {
    /**
     * Replace routeparameters in a string
     *
     * @param string $string
     * @param array $parameters
     * @return void
     */
    function sidewheelsReplaceRouteParameters(string $string, array $parameters) : string
    {
        foreach ($parameters as $key => $value) {
            $string = str_replace("{{$key}}", $value, $string);
        }
        return $string;
    }
}

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
        return home_url('/') . sidewheelsReplaceRouteParameters($path, $replacements) . '/';
    }
}

if (! function_exists('sidewheelsCurrentUrl')) {
    /**
     * Get the current url
     *
     * @return string
     */
    function sidewheelsCurrentUrl() : string
    {
        $protocol    = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $host        = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
        $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
        return $protocol . '://' . $host . $request_uri;
    }
}

if (! function_exists('sidewheelsTrigger404')) {
    /**
     * Get the current url
     *
     * @return string
     */
    function sidewheelsTrigger404() : void
    {
        add_action('template_include', function () {
            global $wp, $wp_query;
            $wp_query->set_404();
            status_header(404);
            $template = locate_template('404.php');
            return $template;
        });
    }
}
