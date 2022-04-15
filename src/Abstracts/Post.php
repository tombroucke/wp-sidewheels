<?php
namespace Otomaties\Sidewheels\Abstracts;

abstract class Post
{

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
    public function __construct(int $id)
    {
        $this->ID = $id;
        $this->validatePostType();
    }

    /**
     * Returns the post ID
     *
     * @return integer Post ID
     */
    public function getId() : int
    {
        return $this->ID;
    }

    public function validatePostType() : void
    {
        if (get_post_type($this->getId()) != $this->postType()) {
            // TODO: Implement a better way to display an error message. Probably too late for 404
            die(sprintf('<code>%s is not a valid %s ID</code>', $this->getId(), $this->postType()));
        }
    }

    abstract public static function postType() : string;

    /**
     * Get post meta
     *
     * @param  string  $key    The meta key.
     * @param  boolean $single Whether the result is a single record or an array of records.
     * @return mixed           The meta value for given key.
     */
    public function get(string $key, bool $single = true)
    {
        return get_post_meta($this->getId(), $key, $single);
    }

    /**
     * Get ACF field
     *
     * @param  string $key The meta key.
     * @return mixed       The meta value for given key.
     */
    public function getField(string $key)
    {
        return get_field($key, $this->getId());
    }

    /**
     * Retrieve the date on which the post was written.
     *
     * @param string $format
     * @return string|null
     */
    public function getDate(string $format = '') : ?string
    {
        return get_the_date($format, $this->getId());
    }

    /**
     * Set post meta
     *
     * @param string $key   The meta key.
     * @param string  $value The meta value.
     * @return boolean       True on success, false on failure.
     */
    public function set(string $key, $value) : bool
    {
        return update_post_meta($this->getId(), $key, $value);
    }

    /**
     * Add post meta
     *
     * @param string $key   The meta key.
     * @param mixed  $value The meta value.
     * @return boolean       True on success, false on failure.
     */
    public function add(string $key, $value) : bool
    {
        return add_post_meta($this->getId(), $key, $value);
    }

    /**
     * Remove post meta
     *
     * @param integer     $key    The meta key.
     * @param string|null $value  The meta value.
     * @return boolean            True on success, false on failure.
     */
    public function remove($key, $value = null) : bool
    {
        if ($value) {
            return delete_post_meta($this->getId(), $key, $value);
        }
        return delete_post_meta($this->getId(), $key);
    }

    /**
     * Get post title
     *
     * @return string The post title.
     */
    public function title() : string
    {
        return get_the_title($this->getId());
    }

    /**
     * Get post content
     *
     * @return string The post content.
     */
    public function content() : string
    {
        $post_object = get_post($this->getId());
        return $post_object->post_content;
    }

    /**
     * Get post name
     *
     * @return string The post slug.
     */
    public function name() : string
    {
        $post_object = get_post($this->getId());
        return $post_object->post_name;
    }

    /**
     * Get post url
     *
     * @return string The permalink.
     */
    public function url() : string
    {
        return get_the_permalink($this->getId());
    }

    public function author() : string
    {
        return get_post_field('post_author', $this->getId());
    }


    public static function find($args = array(), $limit = -1, $paged = 0) : array
    {
        $class = get_called_class();
        $defaults = array(
            'post_type' => static::postType(),
            'posts_per_page' => $limit,
            'paged' => $paged
        );
        $args = wp_parse_args($args, $defaults);
        
        // Shouldn't be overridden.
        $args['fields'] = 'ids';

        $post_ids = get_posts($args);
        return array_map(
            function ($post_id) use ($class) {
                return new $class($post_id);
            },
            $post_ids
        );
    }

    public static function insert($args)
    {
        $class = get_called_class();
        $defaults = array(
            'post_type' => static::postType(),
            'post_status' => 'publish',
            'post_title' => '',
            'post_content' => '',
        );

        $args = wp_parse_args($args, $defaults);
        $post_id = wp_insert_post($args);

        return new $class($post_id);
    }
}
