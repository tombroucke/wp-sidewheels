<?php

namespace Otomaties\Sidewheels\Abstracts;

use Otomaties\Sidewheels\Sidewheels;
use Otomaties\Sidewheels\Route;
use \Twig\TwigFunction;

abstract class Controller
{
    /**
     * The route which triggered this controller
     *
     * @var Route
     */
    private $route;

    /**
     * Render template outside of website layout
     *
     * @param string $template The template path
     * @param array $params Optional parameters to be used in the template
     * @return void
     */
    final protected function render(string $template, ...$params) : void
    {
        $params = apply_filters('sidewheels_twig_params', $params);
        do_action('sidewheel_before_template_full', $template, $params);
        $this->partial($template, $params);
        do_action('sidewheel_after_template_full', $template, $params);
        die();
    }

    /**
     * Return template for shortcode
     *
     * @param string $template The template path
     * @param array $params Optional parameters to be used in the template
     * @return string
     */
    final protected function renderShortcode(string $template, ...$params) : string
    {
        $params = apply_filters('sidewheels_twig_params', $params);
        ob_start();
        do_action('sidewheel_before_template_shortcode', $template, $params);
        $this->partial($template, $params);
        do_action('sidewheel_after_template_shortcode', $template, $params);
        return ob_get_clean();
    }

    /**
     * Render template instead of the_content
     *
     * @param string $template The template path
     * @param array $params Optional parameters to be used in the template
     * @return void
     */
    final protected function renderContent(string $template, ...$params) : void
    {
        add_action('the_content', function () use ($template, $params) {
            if (is_main_query()) {
                $params = apply_filters('sidewheels_twig_params', $params);
                do_action('sidewheel_before_template_content', $template, $params);
                $this->partial($template, $params);
                do_action('sidewheel_after_template_content', $template, $params);
            }
        });
    }

    /**
     * Render out the twig template
     *
     * @param string $template The template path
     * @param array $params Optional parameters to be used in the template
     * @return void
     */
    private function partial(string $template, array $params = []) : void
    {
        // Set parameters, append route parameter
        $params = empty($params) ? $params : $params[0];
        $params['route'] = $this->route();

        // Sidewheels config
        $sidewheels = Sidewheels::instance();
        $templatePath = $sidewheels->config()->templatePath();

        // Twig init
        $loader = new \Twig\Loader\FilesystemLoader($templatePath);
        $twig = new \Twig\Environment($loader);
        
        // Add wordpress functions & controller methods
        foreach (apply_filters('sidewheels_twig_functions', $this->twigFunctions()) as $key => $function) {
            $twig->addFunction($function);
        }
    
        foreach (apply_filters('sidewheels_twig_filters', []) as $key => $filter) {
            $twig->addFilter($filter);
        }
        do_action('sidewheel_before_template', $template, $params);
        echo $twig->render($template, $params);
        do_action('sidewheel_after_template', $template, $params);
    }

    private function twigFunctions()
    {

        $functions = [];
        $functions[] = new TwigFunction(
            '__',
            function ($text, $domain) {
                return __($text, $domain);
            }
        );
        
        $functions[] = new TwigFunction(
            '_x',
            function ($text, $context, $domain) {
                return _x($text, $context, $domain);
            }
        );

        $functions[] = new TwigFunction(
            '_n',
            function ($singular, $plural, $count, $domain) {
                return _n($singular, $plural, $count, $domain);
            }
        );

        $functions[] = new TwigFunction(
            'do_action',
            function (...$args) {
                return do_action(...$args);
            }
        );

        $functions[] = new TwigFunction(
            'apply_filters',
            function (...$args) {
                return apply_filters(...$args);
            }
        );

        $functions[] = new TwigFunction(
            'wp_nonce_field',
            function ($action, $name = '_wpnonce', $referer = true, $echo = true) {
                return wp_nonce_field($action, $name, $referer, $echo);
            }
        );

        $functions[] = new TwigFunction(
            'is_user_logged_in',
            function () {
                return is_user_logged_in();
            }
        );

        $functions[] = new TwigFunction(
            'get_current_user_id',
            function () {
                return get_current_user_id();
            }
        );

        $functions[] = new TwigFunction(
            'home_url',
            function ($path = '/') {
                return home_url($path);
            }
        );

        $functions[] = new TwigFunction(
            'get_permalink',
            function ($path = '/') {
                return get_permalink($path);
            }
        );

        $functions[] = new TwigFunction(
            'get_delete_post_link',
            function ($id, $deprecated = '', $force_delete = false) {
                return get_delete_post_link($id, '', $force_delete);
            }
        );

        $functions[] = new TwigFunction(
            'wpautop',
            function ($content) {
                return wpautop($content);
            }
        );

        $functions[] = new TwigFunction(
            'get_comment_date',
            function ($format = '', $comment_id = 0) {
                return get_comment_date($format, $comment_id);
            }
        );

        $functions[] = new TwigFunction(
            'get_the_date',
            function ($format = '', $post = null) {
                return get_the_date($format, $post);
            }
        );

        $functions[] = new TwigFunction(
            'wp_get_attachment_image_url',
            function ($attachmentId, $size = 'thumbnail', $icon = false) {
                return wp_get_attachment_image_url($attachmentId, $size, $icon);
            }
        );

        $functions[] = new TwigFunction(
            'sidewheelsRoute',
            function ($path, $replacements = []) {
                return sidewheelsRoute($path, $replacements);
            }
        );

        $functions[] = new TwigFunction(
            'admin_url',
            function ($path, $scheme = 'admin') {
                return admin_url($path, $scheme);
            }
        );

        $functions[] = new TwigFunction(
            'wp_head',
            function () {
                return wp_head();
            }
        );

        $functions[] = new TwigFunction(
            'wp_footer',
            function () {
                return wp_footer();
            }
        );

        $functions[] = new TwigFunction(
            'selected',
            function ($selected, $current = true, $echo = true) {
                return selected($selected, $current, $echo);
            }
        );

        $exposableMethods = array_diff(
            get_class_methods($this),
            get_class_methods('Otomaties\Sidewheels\Abstracts\Controller')
        );
        foreach ($exposableMethods as $method) {
            $functions[] = new TwigFunction(
                $method,
                function (...$args) use ($method) {
                    return call_user_func([$this, $method], ...$args);
                }
            );
        }

        return $functions;
    }

    /**
     * Throw 404
     *
     * @return void
     */
    protected function throw404() : void
    {
        sidewheelsTrigger404();
    }

    /**
     * Set route for this controller
     *
     * @param Route $route
     * @return void
     */
    public function setRoute(Route $route) : void
    {
        $this->route = $route;
    }

    /**
     * Get route which triggered this controller
     *
     * @return Route
     */
    public function route() : Route
    {
        return $this->route;
    }
}
