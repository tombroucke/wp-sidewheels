<?php

namespace Otomaties\Sidewheels;

class CustomTaxonomy
{

    /**
     * Add custom taxonomy
     *
     * @param string $name          Taxonomy name.
     * @param string $singular_name Singular name.
     * @param string $plural_name   Plural name.
     * @param string $post_type     The post type for this new taxonomy.
     * @param array  $options       Custom options.
     */
    public static function add(string $name, string $singular_name, string $plural_name, string $post_type, array $default_args = array())
    {
		$textDomain = 'sidewheels';
        $labels = array(
            'name'              => ucfirst($plural_name),
            'singular_name'     => ucfirst($singular_name),
			'search_items'      => sprintf( __( 'Search %s', $textDomain ), $plural_name ), // phpcs:ignore
			'all_items'         => sprintf( __( 'All %s', $textDomain ), $plural_name ), // phpcs:ignore
			'parent_item'       => sprintf( __( 'Parent %s', $textDomain ), $singular_name ), // phpcs:ignore
			'parent_item_colon' => sprintf( __( 'Parent %s:', $textDomain ), $singular_name ), // phpcs:ignore
			'edit_item'         => sprintf( __( 'Edit %s', $textDomain ), $singular_name ), // phpcs:ignore
			'update_item'       => sprintf( __( 'Update %s', $textDomain ), $singular_name ), // phpcs:ignore
			'add_new_item'      => sprintf( __( 'Add new %s', $textDomain ), $singular_name ), // phpcs:ignore
			'new_item_name'     => sprintf( __( 'New %s', $textDomain ), $singular_name ), // phpcs:ignore
            'menu_name'         => ucfirst($plural_name),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => $name ),
        );

        $args = wp_parse_args($args, $default_args);

        register_extended_taxonomy($name, $post_type, $args);
    }
}
