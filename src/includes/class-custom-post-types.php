<?php   
class WP_Sidewheels_Cpts {

	private $settings;

	public function __construct() {

		$this->settings = wp_frontend_app()->settings();
		add_filter( 'post_type_link', array( $this, 'append_query_string' ), 10, 2 );

	}

	public function append_query_string( $url, $post ){
		$post_type = get_post_type($post);
		if( isset( $this->settings->get('post_types')[$post_type] ) && isset($this->settings->get('post_types')[$post_type]['url']) ){
			return home_url() . '/' . str_replace('[id]', $post->ID, $this->settings->get('post_types')[$post_type]['url'] );
		}
		return $url;
	}

	public function create_post_types() {

		$post_types = $this->settings->get( 'post_types' );
		$taxonomies = $this->settings->get( 'taxonomies' );

		if( $post_types ){
			foreach ( $post_types as $post_type_name => $post_type ){

				$this->add_post_type( $post_type_name, $post_type['args'] );

			}
		}
		if( $taxonomies ){
			foreach ( $taxonomies as $name => $taxonomy ){

				$this->add_taxonomy( $name, $taxonomy['singular_label'], $taxonomy['plural_label'], $taxonomy['post_type'], $taxonomy['options'] );

			}
		}

	}

	private function add_post_type( $post_type, $args = array() ){

		$plural_name = $args['labels']['plural_name'];
		$singular_name = $args['labels']['singular_name'];

		$default_labels = array(
			'name'               => ucfirst( $plural_name ),
			'singular_name'      => ucfirst( $singular_name ),
			'menu_name'          => ucfirst( $plural_name ),
			'name_admin_bar'     => ucfirst( $singular_name ),
			'add_new'            => __( 'Add new', 'wp-frontend-app' ),
			'add_new_item'       => sprintf( __( 'Add new %s', 'wp-frontend-app' ), $singular_name ),
			'new_item'           => sprintf( __( 'New %s', 'wp-frontend-app' ), $singular_name ),
			'edit_item'          => sprintf( __( 'Edit %s', 'wp-frontend-app' ), $singular_name ),
			'view_item'          => sprintf( __( 'View %s', 'wp-frontend-app' ), $singular_name ),
			'all_items'          => sprintf( __( 'All %s', 'wp-frontend-app' ), $plural_name ),
			'search_items'       => sprintf( __( 'Search %s', 'wp-frontend-app' ), $plural_name ),
			'parent_item_colon'  => sprintf( __( 'Parent %s', 'wp-frontend-app' ), $singular_name ),
			'not_found'          => sprintf( __( 'No %s found', 'wp-frontend-app' ), $plural_name ),
			'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'wp-frontend-app' ), $plural_name )
		);
		$labels = wp_parse_args( $args['labels'], $default_labels );

		unset($args['labels']);

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
			'exclude_from_search'=> true,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author', 'thumbnail' ),
		);
		$args = wp_parse_args( $args, $default_args );

		register_post_type( $post_type, $args );

	}

	private function add_taxonomy( $name, $singular_name, $plural_name, $post_type = array(), $options = array() ){

		$labels = array(
			'name'              => ucfirst( $plural_name ),
			'singular_name'     => ucfirst( $singular_name ),
			'search_items'      => sprintf( __( 'Search %s', 'wp-frontend-app' ), $plural_name ),
			'all_items'         => sprintf( __( 'All %s', 'wp-frontend-app' ), $plural_name ),
			'parent_item'       => sprintf( __( 'Parent %s', 'wp-frontend-app' ), $singular_name ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-frontend-app' ), $singular_name ),
			'edit_item'         => sprintf( __( 'Edit %s', 'wp-frontend-app' ), $singular_name ),
			'update_item'       => sprintf( __( 'Update %s', 'wp-frontend-app' ), $singular_name ),
			'add_new_item'      => sprintf( __( 'Add new %s', 'wp-frontend-app' ), $singular_name ),
			'new_item_name'     => sprintf( __( 'New %s', 'wp-frontend-app' ), $singular_name ),
			'menu_name'         => ucfirst( $plural_name )
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $name ),
		);

		foreach ($options as $key => $value) {
			$args[$key] = $value;
		}

		register_taxonomy( $name, $post_type, $args );

	}
}