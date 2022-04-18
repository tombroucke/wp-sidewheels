<?php
require_once __DIR__ . '/../../vendor/autoload.php';

WP_Mock::bootstrap();

global $rewrite_rules;
global $query_vars;
$query_vars = [
    'sidewheels_order_id' => 69,
    'sidewheels_product_id' => 3,
    'sidewheels_route' => 'orders'
];

global $is_user_logged_in;
$is_user_logged_in = true;

function add_rewrite_rule(string $regex, string|array $query, string $after = 'bottom')
{
    global $rewrite_rules;
    $rewrite_rules[] = [$regex, $query, $after];
}

function user_can($user, $capability, ...$args)
{
    if ($user == 1 && $capability == 'manage_options') {
        return true;
    }
    return false;
}

function get_query_var($var, $default = '')
{
    global $query_vars;
    return $query_vars[$var] ?? 420;
}

function testCallback()
{
    return 'callback_has_been_called';
}

function home_url(string $append = '/')
{
    return rtrim('https://example.com', $append) . $append;
}

function current_time($type, $gmt = 0)
{
    // Don't use non-GMT timestamp, unless you know the difference and really need to.
    if ('timestamp' === $type || 'U' === $type) {
        return $gmt ? time() : time() + (int) ( get_option('gmt_offset') * HOUR_IN_SECONDS );
    }
 
    if ('mysql' === $type) {
        $type = 'Y-m-d H:i:s';
    }
 
    $timezone = $gmt ? new DateTimeZone('UTC') : wp_timezone();
    $datetime = new DateTime('now', $timezone);
 
    return $datetime->format($type);
}

function wp_timezone()
{
    return new DateTimeZone('Europe/Brussels');
}

function get_current_user_id()
{
    return 1;
}

function wp_parse_args( $args, $defaults = array() ) {
    if ( is_object( $args ) ) {
        $parsed_args = get_object_vars( $args );
    } elseif ( is_array( $args ) ) {
        $parsed_args =& $args;
    } else {
        wp_parse_str( $args, $parsed_args );
    }
 
    if ( is_array( $defaults ) && $defaults ) {
        return array_merge( $defaults, $parsed_args );
    }
    return $parsed_args;
}

function wp_parse_str( $string, &$array ) {
    parse_str( (string) $string, $array );
 
    /**
     * Filters the array of variables derived from a parsed string.
     *
     * @since 2.2.1
     *
     * @param array $array The array populated with variables.
     */
    $array = apply_filters( 'wp_parse_str', $array );
}

function did_action($action){
    return true;
}
