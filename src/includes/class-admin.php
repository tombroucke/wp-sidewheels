<?php
class Sidewheels_Admin{

	private $settings;

	function __construct(){

		$this->settings = wp_frontend_app()->settings();
		$this->custom_post_types_meta_boxes();
		add_action( 'load-nav-menus.php', array( $this, 'meta_boxes' ) );
		

	}

	public function meta_boxes(){
		add_meta_box( 'frontend-app-metabox', __( 'Frontend App Links', 'wp-frontend-app' ), array( $this, 'box_callback'), 'nav-menus', 'side', 'default' );

	}

	public function custom_post_types_meta_boxes(){

		$post_types = $this->settings->get('post_types');
		
		foreach ($post_types as $key => $post_type) {
			add_meta_box( 'fa_meta', __( 'Meta', 'drivingschool' ), array( $this, 'display_meta_box' ), $key, 'advanced', 'default', null );
		}

	}

	public function box_callback(){

		$pages = $this->settings->get('endpoints');
		$count = 1;
		?>
		<div id="posttype-frontend_app" class="posttypediv">
			<div id="tabs-panel-frontend_app" class="tabs-panel tabs-panel-active">
				<ul id="frontend_app-checklist" class="categorychecklist form-no-clear">
					<?php foreach ( $pages as $name => $page ): ?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo $count; ?>][menu-item-object-id]" value="-<?php echo $count; ?>"> <?php echo ucfirst($page['slug']) ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[-<?php echo $count; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[-<?php echo $count; ?>][menu-item-title]" value="<?php echo $page['plural_label'] ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[-<?php echo $count; ?>][menu-item-url]" value="<?php printf( '%s/%s', home_url(), $page['plural_slug'] ); ?>">
							<?php if( isset( $page['children'] ) ): ?>
								<ul>
									<?php foreach ( $page['children'] as $additional_page ): ?>
										<?php if( $additional_page != 'edit' ): ?>
											<?php $count++; ?>
											<li>
												<label class="menu-item-title">
													<input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo $count; ?>][menu-item-object-id]" value="-<?php echo $count; ?>"> <?php echo ucfirst( $additional_page['slug'] ); ?>
												</label>
												<input type="hidden" class="menu-item-type" name="menu-item[-<?php echo $count; ?>][menu-item-type]" value="custom">
												<input type="hidden" class="menu-item-title" name="menu-item[-<?php echo $count; ?>][menu-item-title]" value="<?php echo ucfirst( $additional_page['slug'] ); ?>">
												<input type="hidden" class="menu-item-url" name="menu-item[-<?php echo $count; ?>][menu-item-url]" value="<?php printf( '%s/%s/%s', home_url(), $name, $additional_page['slug'] ); ?>">
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
						<?php $count++; ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="/wp-admin/nav-menus.php?frontend-app-tab=all&amp;selectall=1#frontend-app" class="select-all aria-button-if-js" role="button"><?php _e( 'Select all' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php _e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-frontend_app">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	public function display_meta_box(){

		$post_type = get_post_type( get_the_ID() );
		$meta = get_post_meta( get_the_ID() );
		unset( $meta['_edit_lock'] );
		unset( $meta['_edit_last'] );
		?>
		<table class="widefat fixed striped">
			<?php foreach ($meta as $key => $value): ?>
				<tr>
					<th><?php echo apply_filters( 'fa_' . $post_type . '_meta_key', $key); ?></th>
					<td>
						<ul>
							<?php foreach( $value as $thevalue ): ?>
								<?php if( !$this->is_serial( $thevalue ) ): ?>
								<li><?php echo apply_filters( 'fa_' . $post_type . '_meta_value', $thevalue, $key ); ?></li>
								<?php else: ?>
									<?php foreach( unserialize( $thevalue ) as $serialized_key => $serizalized_value ): ?>
										<li>
											<?php if( is_array( $serizalized_value ) ): ?>
												<?php print_r( apply_filters( 'fa_' . $post_type . '_meta_serialized_key', $serizalized_value, $key ) ); ?>
											<?php else: ?>
											<?php echo apply_filters( 'fa_' . $post_type . '_meta_serialized_key', $serialized_key, $key ); ?>: <?php echo apply_filters( 'fa_' . $post_type . '_meta_value', $serizalized_value, $key ); ?></li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>				
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php

	}

	public static function is_serial($string) {
	    return (@unserialize($string) !== false);
	}

}