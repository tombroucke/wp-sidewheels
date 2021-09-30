<?php

namespace Otomaties\Sidewheels;

class CustomTaxonomies
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
     * Add custom taxonomy
     *
     * @param string $name          Taxonomy name.
     * @param string $singularName Singular name.
     * @param string $pluralName   Plural name.
     * @param string $postType     The post type for this new taxonomy.
     * @param array  $options       Custom options.
     */
    public function add(string $name, string $singularName, string $pluralName, string $postType, array $defaultArgs = array())
    {
        $labels = array(
            'name'              => ucfirst($pluralName),
            'singular_name'     => ucfirst($singularName),
            'search_items'      => sprintf(__('Search %s', $this->config->textDomain()), $pluralName),
            'all_items'         => sprintf(__('All %s', $this->config->textDomain()), $pluralName),
            'parent_item'       => sprintf(__('Parent %s', $this->config->textDomain()), $singularName),
            'parent_item_colon' => sprintf(__('Parent %s:', $this->config->textDomain()), $singularName),
            'edit_item'         => sprintf(__('Edit %s', $this->config->textDomain()), $singularName),
            'update_item'       => sprintf(__('Update %s', $this->config->textDomain()), $singularName),
            'add_new_item'      => sprintf(__('Add new %s', $this->config->textDomain()), $singularName),
            'new_item_name'     => sprintf(__('New %s', $this->config->textDomain()), $singularName),
            'menu_name'         => ucfirst($pluralName),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => $name],
        );

        $args = wp_parse_args($args, $defaultArgs);

        register_extended_taxonomy($name, $postType, $args);
    }
}
