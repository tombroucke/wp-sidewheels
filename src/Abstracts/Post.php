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
    public function getId()
    {
        return $this->ID;
    }

    public function validatePostType()
    {
        if (get_post_type($this->getId()) != $this->postType()) {
            // TODO: check if theres a better way to display an error message. 404?
            die(sprintf('<code>%s is not a valid %s ID</code>', $this->getId(), $this->postType()));
        }
    }

    abstract public static function postType();

    /**
     * Get post meta
     *
     * @param  string  $key    The meta key.
     * @param  boolean $single Whether the result is a single record or an array of records.
     * @return mixed           The meta value for given key.
     */
    public function get($key, $single = true)
    {
        return get_post_meta($this->getId(), $key, $single);
    }

    /**
     * Get acf field
     *
     * @param  string $key The meta key.
     * @return mixed       The meta value for given key.
     */
    public function getField($key)
    {
        return get_field($key, $this->getId());
    }

    public function getDate($format = '')
    {
        return get_the_date($format, $this->getId());
    }

    /**
     * Set post meta
     *
     * @param integer $key   The meta key.
     * @param string  $value The meta value.
     * @return boolean       Whether the meta has been updated.
     */
    public function set($key, $value)
    {
        return update_post_meta($this->getId(), $key, $value);
    }

    /**
     * Add post meta
     *
     * @param integer $key   The meta key.
     * @param string  $value The meta value.
     * @return boolean       Whether the meta has been added
     */
    public function addMeta($key, $value)
    {
        return add_post_meta($this->getId(), $key, $value);
    }

    /**
     * Remove post meta
     *
     * @param integer     $key    The meta key.
     * @param string|null $value  The meta value.
     * @return boolean            Whether the meta has been removed.
     */
    public function removeMeta($key, $value = null)
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
    public function title()
    {
        return get_the_title($this->getId());
    }

    /**
     * Get post content
     *
     * @return string The post content.
     */
    public function content()
    {
        $post_object = get_post($this->getId());
        return $post_object->post_content;
    }

    /**
     * Get post name
     *
     * @return string The post slug.
     */
    public function name()
    {
        $post_object = get_post($this->getId());
        return $post_object->post_name;
    }

    /**
     * Get post url
     *
     * @return string The permalink.
     */
    public function url()
    {
        return get_the_permalink($this->getId());
    }

    public function author()
    {
        return get_post_field('post_author', $this->getId());
    }


    public static function find($args = array(), $limit = -1, $paged = 0)
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
