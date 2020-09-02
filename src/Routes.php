<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

/**
 * Create routes to each endpoint in config.php
 */
class Routes {


	/**
	 * Sidewheel settings object
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Define settings
	 *
	 * @param Settings $settings Sidewheel settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Create endpoint for each endpoint in Sidewheels config file
	 */
	public function create() {
		if ( function_exists( 'icl_get_languages' ) ) {
			global $sitepress;
			$current_language = $sitepress->get_current_language();
			foreach ( icl_get_languages() as $language_code => $language ) {
				$sitepress->switch_lang( $language_code, true );
				$this->add_endpoints( $this->settings->get( 'endpoints' ) );
			}
			$sitepress->switch_lang( $current_language, true );
		} else {
			$this->add_endpoints( $this->settings->get( 'endpoints' ) );
		}
	}

	/**
	 * Add query args for custom routing
	 *
	 * @param  array $vars WP query vars.
	 * @return array
	 */
	public function custom_query_vars( $vars ) {
		$vars[] = 'sidewheels_endpoint';

		// All handles for [id] endpoints.
		$endpoints = $this->settings->get( 'endpoints' );
		array_walk_recursive(
			$endpoints,
			function( $endpoint, $key ) use ( &$vars ) {
				if ( 'handle' == $key ) {
					$vars[] = $endpoint;
				}
			}
		);
		return $vars;
	}

	/**
	 * Iterate endpoints & add rewrite rule for each one
	 *
	 * @param array $endpoints array with config's endpoints.
	 * @param array $parents   Endpoint parents.
	 * @param array $hierachy  Hierarchy.
	 * @param int   $depth     Depth of current endpoint.
	 */
	private function add_endpoints( array $endpoints, array $parents = array(), array $hierachy = array(), int $depth = 0 ) {
		global $sitepress;

		$depth++;

		if ( empty( $endpoints ) ) {
			return;
		}

		foreach ( $endpoints as $endpoint_name => $endpoint ) {

			if ( ! isset( $endpoint['public'] ) || $endpoint['public'] ) {

				$items = $parents;
				$items[] = array(
					'slug' => str_replace( '[id]', '([0-9]+)', $endpoint_name ),
					// TODO: try to get translation from config, not from here.
					'translated_slug' => str_replace( '[id]', '([0-9]+)', __( $endpoint_name, $this->settings->get_textdomain() ) ), // phpcs:ignore
					'handle' => ( isset( $endpoint['handle'] ) ? $endpoint['handle'] : false ),
				);

				$regex = '^';
				$sidewheels_endpoint = '';
				$redirect_vars = array();
				foreach ( $items as $key => $item ) {
					$regex .= $item['translated_slug'] . '/';
					$sidewheels_endpoint .= $item['slug'] . '/';
					if ( strpos( $item['slug'], '([0-9]+)' ) !== false || strpos( $item['slug'], '[id]' ) !== false ) {
						array_push( $redirect_vars, sprintf( '&%s=$matches[%s]', $item['handle'], count( $redirect_vars ) + 1 ) );
					}
				}
				$regex .= '?$';

				$redirect = 'index.php?sidewheels_endpoint=' . urlencode( rtrim( str_replace( '([0-9]+)', '[id]', $sidewheels_endpoint ), '/' ) ) . implode( $redirect_vars );
				if ( ! isset( $endpoint['disable'] ) || ! $endpoint['disable'] ) {
					add_rewrite_rule( $regex, $redirect, 'top' );
				}

				if ( ! isset( $parents[ $endpoint_name ] ) ) {
					$parents[] = array(
						'slug' => str_replace( '[id]', '([0-9]+)', $endpoint_name ),
						// TODO: try to get translation from config, not from here.
						'translated_slug' => str_replace( '[id]', '([0-9]+)', __( $endpoint_name, $this->settings->get_textdomain() ) ), // phpcs:ignore
						'handle' => ( isset( $endpoint['handle'] ) ? $endpoint['handle'] : false ),
					);
				}
			}

			if ( isset( $endpoint['children'] ) ) {
				$this->add_endpoints( $endpoint['children'], $parents, $hierachy, $depth );
			} else {
				$depth--;
			}

			array_pop( $parents );
			array_pop( $hierachy );
		}

		$depth--;
	}
}
