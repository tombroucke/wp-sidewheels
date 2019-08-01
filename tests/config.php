<?php
return array(
	'text-domain' => 'test',
	'templates' => dirname(__FILE__) . '/templates',
	'controllers' => dirname(__FILE__) . '/controllers',
	'endpoints' => array(
		'dashboard' => array(
			'slug' => __('dashboard', 'test'),
			'label' => __('Dashboard', 'test'),
			'template' => 'dashboard',
			'capability' => 'read_dashboard',
			'children' => array(
				'contacts' => array(
					'slug' => __('contacts', 'test'),
					'label' => __('Contacts', 'test'),
					'template' => 'dashboard/contacts',
					'post_type' => 'contact',
					'capability' => 'read_contacts',
					'children' => array(
						'[id]' => array(
							'template' => 'dashboard/contact/detail',
							'controller' => 'dashboard/ContactController'
						)
					)
				),
				'calendar' => array(
					'slug' => __('calendar', 'test'),
					'label' => __('Calendar', 'test'),
					'template' => 'dashboard/calendar'
				)
			)
		),
	),
	'post_types' => array(
		'location' => array(
			'args' => array(
				'labels' => array(
					'singular_name' => __('Location', 'test'),
					'plural_name' => __('Locations', 'test'),
				),
				'menu_icon' => 'dashicons-location',
				'supports' => array( 'title' ),
				'show_in_menu' => true,
				'public' => false,
				'publicly_queryable' => false,
				'has_archive' => false
			),
			'field_groups' => array(
				'location' => array(
					'map' => array(
						'type' => 'google_map',
						'name' => 'location_map',
						'instructions' => 'Select the location on a map',
						'menu_order' => -1
					),
					'id' => array(
						'type' => 'text',
						'name' => 'id',
					),
				),
				'common' => array(
					'id' => array(
						'type' => 'text',
						'name' => 'id',
					),
				)
			)
		),
		'contact' => array(
			'url' => sprintf('%s/%s/[id]', __('dashboard', 'test'), __('contacts', 'test')),
			'args' => array(
				'labels' => array(
					'singular_name' => __('Contact', 'test'),
					'plural_name' => __('Contacts', 'test'),
				),
				'menu_icon' => 'dashicons-groups',
				'supports' => array( 'title' ),
				'show_in_menu' => true,
				'public' => true,
				'publicly_queryable' => true,
				'has_archive' => false
			),
			'field_groups' => array(
				'main fields' => array(
					'tab1' => array(
						'type' => 'tab',
						'name' => 'tab 1',
					),
					'id' => array(
						'type' => 'text',
						'name' => 'id',
					),
					'tab2' => array(
						'type' => 'tab',
						'name' => 'tab 2',
					),
					'location' => array(
						'type' => 'post_object',
						'name' => 'location',
						'args' => array(
							'post_type' => 'location',
							'allow_null' => true
						)
					)
				),
				'common' => array(
					'sid' => array(
						'type' => 'text',
						'name' => 'id',
					),
				)
			)
		),
	),
	'taxonomies' => array(
		'role' => array(
			'singular_label' => __('Role', 'test'),
			'plural_label' => __('Roles', 'test'),
			'post_type' => 'contact',
			'options' => array()
		)
	),
	'roles' => array(
		'client' => array(
			'label' => __('Client', 'test'),
			'capabilities' => array(
				'read' => true,
				'read_dashboard' => true,
				'read_contacts' => true
			)
		),
		'manager' => array(
			'label' => __('Manager', 'test'),
			'capabilities' => array(
				'read' => true,
				'edit_contacts' => true
			)
		)
	)
);