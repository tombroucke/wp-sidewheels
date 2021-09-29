<?php

namespace Otomaties\Sidewheels;

class CustomPostType
{
    /**
     * Register post type
     *
     * @param string $postType The post type slug.
     * @param array  $args      Post type args.
     */
    public static function add(string $postType, array $args = array())
    {
        $textDomain = 'sidewheels';
        $pluralName = $args['labels']['plural_name'];
        $singularName = $args['labels']['singular_name'];

        $defaultLabels = array(
            'name'               => ucfirst($pluralName),
            'singular_name'      => ucfirst($singularName),
            'menu_name'          => ucfirst($pluralName),
            'name_admin_bar'     => ucfirst($singularName),
            'add_new'            => __('Add new', $textDomain),
            'add_new_item'       => sprintf(__('Add new %s', $textDomain), $singularName),
            'new_item'           => sprintf(__('New %s', $textDomain), $singularName),
            'edit_item'          => sprintf(__('Edit %s', $textDomain), $singularName),
            'view_item'          => sprintf(__('View %s', $textDomain), $singularName),
            'all_items'          => sprintf(__('All %s', $textDomain), $pluralName),
            'search_items'       => sprintf(__('Search %s', $textDomain), $pluralName),
            'parent_item_colon'  => sprintf(__('Parent %s', $textDomain), $singularName),
            'not_found'          => sprintf(__('No %s found', $textDomain), $pluralName),
            'not_found_in_trash' => sprintf(__('No %s found in trash', $textDomain), $pluralName),
        );
        $labels = wp_parse_args($args['labels'], $defaultLabels);

        unset($args['labels']);

        $default_args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => $postType ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'exclude_from_search' => true,
            'menu_position'      => null,
            'supports'           => array( 'title', 'author', 'thumbnail' ),
        );
        $args = wp_parse_args($args, $default_args);

        register_extended_post_type($postType, $args);
    }
}
