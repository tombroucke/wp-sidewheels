<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

/**
 * Add Custom Post Type for each post type defined in config.php
 */
class Custom_Post_Types {


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
		add_filter( 'post_type_link', array( $this, 'custom_post_type_link' ), 10, 2 );
	}

	/**
	 * Change custom post type link
	 *
	 * @param  string   $url     Default url.
	 * @param  \WP_Post $post    Post object.
	 * @return string Custom url Customized url.
	 */
	public function custom_post_type_link( string $url, \WP_Post $post ) {
		$post_type  = get_post_type( $post );
		$post_types = $this->settings->get( 'post_types' );
		if ( isset( $post_types[ $post_type ] ) && isset( $post_types[ $post_type ]['url'] ) ) {
			return home_url() . '/' . str_replace( '[id]', $post->ID, $post_types[ $post_type ]['url'] );
		}
		return $url;
	}

	/**
	 * Fetch all post types defined in config & call add_post_type
	 */
	public function create_post_types() {
		$post_types = $this->settings->get( 'post_types' );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type_name => $post_type ) {
				$this->add_post_type( $post_type_name, $post_type['args'] );
			}
		}
	}

	/**
	 * Fetch all taxonomies defined in config & call add_taxonomy
	 */
	public function create_taxonomies() {
		$taxonomies = $this->settings->get( 'taxonomies' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $name => $taxonomy ) {
				$this->add_taxonomy( $name, $taxonomy['singular_label'], $taxonomy['plural_label'], $taxonomy['post_type'], $taxonomy['options'] );
			}
		}
	}

	/**
	 * Register post type
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $args      Post type args.
	 */
	private function add_post_type( string $post_type, array $args = array() ) {

		$plural_name = $args['labels']['plural_name'];
		$singular_name = $args['labels']['singular_name'];

		$default_labels = array(
			'name'               => ucfirst( $plural_name ),
			'singular_name'      => ucfirst( $singular_name ),
			'menu_name'          => ucfirst( $plural_name ),
			'name_admin_bar'     => ucfirst( $singular_name ),
			'add_new'            => __( 'Add new', $this->settings->get_textdomain() ), // phpcs:ignore
			'add_new_item'       => sprintf( __( 'Add new %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'new_item'           => sprintf( __( 'New %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'edit_item'          => sprintf( __( 'Edit %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'view_item'          => sprintf( __( 'View %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'all_items'          => sprintf( __( 'All %s', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
			'search_items'       => sprintf( __( 'Search %s', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
			'parent_item_colon'  => sprintf( __( 'Parent %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'not_found'          => sprintf( __( 'No %s found', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
			'not_found_in_trash' => sprintf( __( 'No %s found in trash', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
		);
		$labels = wp_parse_args( $args['labels'], $default_labels );

		unset( $args['labels'] );

		$default_args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $post_type ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'exclude_from_search' => true,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author', 'thumbnail' ),
		);
		$args = wp_parse_args( $args, $default_args );

		register_extended_post_type( $post_type, $args );
	}

	/**
	 * [add_taxonomy description]
	 *
	 * @param string $name          Taxonomy name.
	 * @param string $singular_name Singular name.
	 * @param string $plural_name   Plural name.
	 * @param string $post_type     The post type for this new taxonomy.
	 * @param array  $options       Custom options.
	 */
	private function add_taxonomy( string $name, string $singular_name, string $plural_name, string $post_type, array $default_args = array() ) {
		$labels = array(
			'name'              => ucfirst( $plural_name ),
			'singular_name'     => ucfirst( $singular_name ),
			'search_items'      => sprintf( __( 'Search %s', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
			'all_items'         => sprintf( __( 'All %s', $this->settings->get_textdomain() ), $plural_name ), // phpcs:ignore
			'parent_item'       => sprintf( __( 'Parent %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'parent_item_colon' => sprintf( __( 'Parent %s:', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'edit_item'         => sprintf( __( 'Edit %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'update_item'       => sprintf( __( 'Update %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'add_new_item'      => sprintf( __( 'Add new %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'new_item_name'     => sprintf( __( 'New %s', $this->settings->get_textdomain() ), $singular_name ), // phpcs:ignore
			'menu_name'         => ucfirst( $plural_name ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $name ),
		);

		$args = wp_parse_args( $args, $default_args );

		register_extended_taxonomy( $name, $post_type, $args );
	}
}
