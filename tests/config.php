<?php
return [
    'templatePath' => 'custom-directory/views',
    'textDomain' => 'test',
    'routes' => [
        [
            'path' => 'dash/orders',
            'callback' => 'testCallback',
            'capability' => 'manage_options',
            'method' => 'GET',
            'title' => 'Orders',
        ],
        [
            'path' => 'dash/orders/{order_id}',
            'callback' => 'testCallback',
            'capability' => 'manage_options',
            'method' => 'GET',
            'title' => 'Order #{order_id',
        ],
    ],
    'postTypes' => [
        'shop_order' => [
            'args' => [
                'labels' => [
                    'singular_name' => 'Order',
                    'plural_name' => 'Orders',
                ],
                'menu_icon' => 'dashicons-cart',
                'supports' => ['title', 'author'],
                'show_in_menu' => true,
                'public' => false,
                'publicly_queryable' => false,
                'has_archive' => false,
                'admin_cols' => [
                    'name' => [
                        'title' => 'Name',
                        'meta_key' => 'name',
                    ],
                    'total' => [
                        'title' => 'Total',
                        'function' => function () {
                            return 'Calculated total';
                        },
                    ],
                ],
            ],
        ],
    ],
    'taxonomies' => [
        'product_cat' => [
            'post_type' => 'product',
            'singular_label' => 'Product category',
            'plural_label' => 'Product categories',
            'options' => [
                'meta_box' => 'simple',
            ]
        ]
    ],
    'roles' => [
        'administrator' => [
            'label' => 'Administrator',
            'capabilities' => [
                'manage_debouvrie' => true,
                'administer_debouvrie' => true,
            ],
        ],
        'customer' => [
            'label' => __('Customer', 'debouvrie-order-system'),
            'capabilities' => [
                'read' => true
            ]
        ],
    ],
];
