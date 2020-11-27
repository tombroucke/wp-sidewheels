<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels\Abstracts;

/**
 * Logic for Sidewheels post types
 */
abstract class Sidewheels_Post_Type {

	/**
	 * Post ID
	 *
	 * @var integer
	 */
	protected $ID;

	/**
	 * Initialize post type
	 *
	 * @param integer $id Post ID.
	 */
	public function __construct( int $id ) {
		$this->ID = $id;
	}

	/**
	 * Returns the post ID
	 *
	 * @return integer Post ID
	 */
	public function get_ID() { // phpcs:ignore
		return $this->ID;
	}

	/**
	 * Get post meta
	 *
	 * @param  string  $key    The meta key.
	 * @param  boolean $single Whether the result is a single record or an array of records.
	 * @return mixed           The meta value for given key.
	 */
	public function get( $key, $single = true ) {
		return get_post_meta( $this->get_ID(), $key, $single );
	}

	/**
	 * Get acf field
	 *
	 * @param  string $key The meta key.
	 * @return mixed       The meta value for given key.
	 */
	public function get_field( $key ) {
		return get_field( $key, $this->get_ID() );
	}

	public function get_date( $format = '' ) {
		return get_the_date( $format, $this->get_ID() );
	}

	/**
	 * Set post meta
	 *
	 * @param integer $key   The meta key.
	 * @param string  $value The meta value.
	 * @return boolean       Whether the meta has been updated.
	 */
	public function set( $key, $value ) {
		return update_post_meta( $this->get_ID(), $key, $value );
	}

	/**
	 * Add post meta
	 *
	 * @param integer $key   The meta key.
	 * @param string  $value The meta value.
	 * @return boolean       Whether the meta has been added
	 */
	public function add_meta( $key, $value ) {
		return add_post_meta( $this->get_ID(), $key, $value );
	}

	/**
	 * Remove post meta
	 *
	 * @param integer     $key    The meta key.
	 * @param string|null $value  The meta value.
	 * @return boolean            Whether the meta has been removed.
	 */
	public function remove_meta( $key, $value = null ) {
		if ( $value ) {
			return delete_post_meta( $this->get_ID(), $key, $value );
		}
		return delete_post_meta( $this->get_ID(), $key );
	}

	/**
	 * Get post type
	 *
	 * @return string The post type.
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get post title
	 *
	 * @return string The post title.
	 */
	public function get_title() {
		return get_the_title( $this->get_ID() );
	}

	/**
	 * Get post content
	 *
	 * @return string The post content.
	 */
	public function get_content() {
		$post_object = get_post( $this->get_ID() );
		return $post_object->post_content;
	}

	/**
	 * Get post name
	 *
	 * @return string The post slug.
	 */
	public function get_name() {
		$post_object = get_post( $this->get_ID() );
		return $post_object->post_name;
	}

	/**
	 * Get post url
	 *
	 * @return string The permalink.
	 */
	public function get_url() {
		return get_the_permalink( $this->get_ID() );
	}

	abstract public static function post_type();

	public static function find( $args = array(), $limit = -1, $paged = 0 ) {
		$class = get_called_class();
		$defaults = array(
			'post_type' => static::post_type(),
			'posts_per_page' => $limit,
			'paged' => $paged
		);
		$args = wp_parse_args( $args, $defaults );
		
		// Shouldn't be overridden.
		$args['fields'] = 'ids';

		$post_ids = get_posts( $args );
		return array_map(
			function( $post_id ) use ( $class ) {
				return new $class( $post_id );
			},
			$post_ids
		);

	}

	public static function find_one( $args = array() ) {

		$args['posts_per_page'] = 1;
		$result = self::find( $args );
		if ( empty( $result ) ) {
			return false;
		}
		return $result[0];

	}

	public static function insert( $args ) {

		$class = get_called_class();
		$defaults = array(
			'post_type' => static::post_type(),
			'post_status' => 'publish',
			'post_title' => '',
			'post_content' => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$post_id = wp_insert_post( $args );

		return new $class( $post_id );
	}
}
