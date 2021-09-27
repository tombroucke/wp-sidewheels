<?php

namespace Otomaties\Sidewheels\Abstracts;

use Otomaties\Sidewheels\Sidewheels;
use \Twig\TwigFunction;

abstract class Controller
{
    public function render($template, ...$params)
    {
        do_action('sidewheel_before_template_full');
        self::partial($template, $params);
        do_action('sidewheel_after_template_full');
        die();
    }

    public function renderContent($template, ...$params)
    {
        add_action('the_content', function () use ($template, $params) {
            if (is_main_query()) {
                do_action('sidewheel_before_template_partial');
                self::partial($template, $params);
                do_action('sidewheel_after_template_partial');
            }
        });
    }

    public static function partial($template, $params = [])
    {
        $params = empty($params) ? $params : $params[0];
        $sidewheels = Sidewheels::getInstance();
        $templatePath = $sidewheels->config()->templatePath();
        $loader = new \Twig\Loader\FilesystemLoader($templatePath);
        $twig = new \Twig\Environment($loader);

        $functions = array();
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
            'print_r',
            function ($array) {
                return print_r($array);
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
    
        foreach (apply_filters('sidewheels_twig_functions', $functions) as $key => $function) {
            $twig->addFunction($function);
        }

        do_action('sidewheel_before_template');
        echo $twig->render($template, $params);
        do_action('sidewheel_after_template');
    }
}
