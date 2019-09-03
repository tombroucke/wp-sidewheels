<?php

namespace Sidewheels;
use StoutLogic\AcfBuilder\FieldsBuilder;
use StoutLogic\AcfBuilder\GroupBuilder;

/**
 * Add ACF support for each field defined in config.php
 */
class Fields{

	/**
	 * Fetch all post types & add fields
	 */
	function __construct(){

        $post_types = wp_sidewheels()->settings()->get('post_types');
        if ($post_types) {
            foreach ($post_types as $post_type_name => $post_type) {
            	if( isset( $post_type['field_groups'] ) ) {
                	$this->build_fields($post_type_name, $post_type);
            	}
            }
        }

	}

	/**
	 * Create field group
	 * @param  string $post_type_name
	 * @param  array $post_type
	 */
	private function build_fields($post_type_name, $post_type) {
		// TODO: check logic, there seems to be an error
		foreach ($post_type['field_groups'] as $id => $field_group) {
			$group_name = apply_filters( 'sidewheels_acf_group_name', $id, $post_type_name, $field_group );
			$builder = new FieldsBuilder($group_name);
			$builder->setGroupConfig('key', $this->spaces_to_underscores($post_type_name . ' ' . $id));

			foreach ($field_group as $key => $field) {
				$method = 'add' . $field['type'];
				$args = ( isset($field['args']) ? $field['args'] : array() );
				$args['name'] = $this->spaces_to_underscores($post_type_name . ' ' . $id . ' ' . $key);
				$label = isset( $field['label'] ) ? $field['label'] : $key;
				$newfield = $builder->addField($label, $field['type'], $args);
				if( isset($field['instructions']) ) {
					$newfield->setInstructions($field['instructions']);
				}
			}

			$builder->setLocation('post_type', '==', $post_type_name);

		    acf_add_local_field_group($builder->build());
		}
	}

	/**
	 * Replace spaces by underscores
	 * @param  string $name
	 * @return string
	 */
	private function spaces_to_underscores($name){
        return strtolower(str_replace(' ', '_', $name));
	}

}