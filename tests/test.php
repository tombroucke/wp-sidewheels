<?php

// Autoload files using the Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';


function post_syncing(){

	$posts = array(
		array(
			'post_title' => 'test',
			'qualifier' => array(
				'by' => 'meta_value',
				'key' => 'unique_id',
				'value' => '1',
				'post_status' => array( 'publish' )
			),
			'meta_input' => array(
				'unique_id' => '1'
			)	
		)
	);

	$syncer = new WP_Post_Syncer();
	foreach ( $posts as $post ) {

		$syncer->sync_post( $post );

	}
	$syncer->clean_up();

}

function product_syncing(){
	
	$posts = array(
		array(
			'qualifier' => array(
				'by' => 'sku',
				'value' => 'sku1'
			),
			'post_title' => 'test3as',
			'woocommerce' => array(
				'_sku' => 'sku1',
				'_sale_price' => '',
				'product_cat' => array( 16,17 ),
				'product_type' => 'variable',
				'available_attributes' => array( 'color', 'size', 'weight' ),
				'variations' => array(
					array(
						'attributes' => array(
							'color' => 'blue',
							'size' => '10',
							'weight' => '20g'
						),
						'_regular_price' => '13',
						'_sku' => 'varsku1',
						'qualifier' => array(
							'by' => 'sku',
							'value' => 'varsku1'
						),
						'_stock' => '8'
					),
					array(
						'attributes' => array(
							'color' => 'blue',
							'size' => '11',
							'weight' => '20g'
						),
						'_regular_price' => '14',
						'_sku' => 'varsku2',
						'qualifier' => array(
							'by' => 'sku',
							'value' => 'varsku2'
						),
						'_stock' => '8'
					),
					array(
						'attributes' => array(
							'color' => 'blue',
							'size' => '12',
							'weight' => '20g'
						),
						'_regular_price' => '25',
						'_sku' => 'varsku3',
						'qualifier' => array(
							'by' => 'sku',
							'value' => 'varsk3'
						),
						'_stock' => '8'
					),
					array(
						'attributes' => array(
							'color' => 'blellow',
							'size' => '12',
							'weight' => '40g'
						),
						'_regular_price' => '26',
						'_sku' =>  'varsku4',
						'qualifier' => array(
							'by' => 'sku',
							'value' =>  'varsku4'
						),
						'_stock' => '8',
						'_weight' => '0.250',
						'_length' => '50',
						'_width' => '10',
						'_height' => '20',
						'_variation_description' => 'Description',
						'_backorders' => 'no'
					),
				)
			)

		)
	);

	$syncer = new WP_Product_Syncer();
	foreach ( $posts as $post ) {

		$syncer->sync_post( $post );

	}
	$syncer->clean_up( array( 'force_delete' => true ) );

}