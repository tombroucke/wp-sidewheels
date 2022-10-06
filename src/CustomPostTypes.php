<?php

namespace Otomaties\Sidewheels;

class CustomPostTypes
{
    
    /**
     * Configuration object
     *
     * @var Config
     */
    private $config;

    /**
     * Set config file
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Register post type
     *
     * @param string $postType The post type slug.
     * @param array  $args      Post type args.
     */
    public function add(string $postType, array $args = array()) : void
    {
        $pluralName = $args['labels']['plural_name'];
        $singularName = $args['labels']['singular_name'];

        $defaultLabels = array(
            'name'               => ucfirst($pluralName),
            'singular_name'      => ucfirst($singularName),
            'menu_name'          => ucfirst($pluralName),
            'name_admin_bar'     => ucfirst($singularName),
            'add_new'            => __('Add new', $this->config->textDomain()),
            'add_new_item'       => sprintf(__('Add new %s', $this->config->textDomain()), $singularName),
            'new_item'           => sprintf(__('New %s', $this->config->textDomain()), $singularName),
            'edit_item'          => sprintf(__('Edit %s', $this->config->textDomain()), $singularName),
            'view_item'          => sprintf(__('View %s', $this->config->textDomain()), $singularName),
            'all_items'          => sprintf(__('All %s', $this->config->textDomain()), $pluralName),
            'search_items'       => sprintf(__('Search %s', $this->config->textDomain()), $pluralName),
            'parent_item_colon'  => sprintf(__('Parent %s', $this->config->textDomain()), $singularName),
            'not_found'          => sprintf(__('No %s found', $this->config->textDomain()), $pluralName),
            'not_found_in_trash' => sprintf(__('No %s found in trash', $this->config->textDomain()), $pluralName),
        );
        $labels = wp_parse_args($args['labels'], $defaultLabels);

        unset($args['labels']);

        $defaultArgs = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => $postType],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'exclude_from_search' => true,
            'menu_position'      => null,
            'supports'           => ['title', 'author', 'thumbnail'],
        );
        $args = wp_parse_args($args, $defaultArgs);

        register_extended_post_type($postType, $args);
    }
}
