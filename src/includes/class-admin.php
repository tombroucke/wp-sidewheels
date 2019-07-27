<?php
namespace SideWheels;

/**
 * Logic for administration screens
 */
class Admin{

    /**
     * Settings
     * @var \Sidewheels\Settings
     */
	private $settings;

	function __construct(){

		$this->settings = wp_sidewheels()->settings();
		$this->custom_post_types_meta_boxes();
		add_action( 'load-nav-menus.php', array( $this, 'meta_boxes' ) );
		

	}

	/**
	 * Add a meta box to nav-menus.php
	 */
	public function meta_boxes(){
		add_meta_box( 'sidewheels-metabox', __( 'Sidewheels Links', $this->settings->get('text-domain') ), array( $this, 'box_callback'), 'nav-menus', 'side', 'default' );

	}

	/**
	 * Add a meta box to every post type created by Sidewheels
	 */
	public function custom_post_types_meta_boxes(){

		$post_types = $this->settings->get('post_types');
		
		foreach ($post_types as $key => $post_type) {
			add_meta_box( 'sidewheels_meta', __( 'Information', $this->settings->get('text-domain') ), array( $this, 'display_meta_box' ), $key, 'side', 'high', null );
		}

	}

	/**
	 * Render meta box on nav-menus.php
	 */
	public function box_callback(){
		// TODO: make menus recursive
		$pages = $this->settings->get('endpoints');
		$count = 1;
		?>
		<div id="posttype-sidewheels" class="posttypediv">
			<div id="tabs-panel-sidewheels" class="tabs-panel tabs-panel-active">
				<ul id="sidewheels-checklist" class="categorychecklist form-no-clear">
					<?php foreach ( $pages as $name => $page ): ?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo $count; ?>][menu-item-object-id]" value="-<?php echo $count; ?>"> <?php echo $page['label'] ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[-<?php echo $count; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[-<?php echo $count; ?>][menu-item-title]" value="<?php echo $page['label'] ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[-<?php echo $count; ?>][menu-item-url]" value="<?php printf( '%s/%s', home_url(), $page['slug'] ); ?>">
							<?php if( isset( $page['children'] ) ): ?>
								<ul>
									<?php foreach ( $page['children'] as $additional_page ): ?>
										<?php if( $additional_page != 'edit' ): ?>
											<?php $count++; ?>
											<li>
												<label class="menu-item-title">
													<input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo $count; ?>][menu-item-object-id]" value="-<?php echo $count; ?>"> <?php echo ucfirst( $additional_page['label'] ); ?>
												</label>
												<input type="hidden" class="menu-item-type" name="menu-item[-<?php echo $count; ?>][menu-item-type]" value="custom">
												<input type="hidden" class="menu-item-title" name="menu-item[-<?php echo $count; ?>][menu-item-title]" value="<?php echo ucfirst( $additional_page['label'] ); ?>">
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
					<a href="/wp-admin/nav-menus.php?sidewheels-tab=all&amp;selectall=1#sidewheels" class="select-all aria-button-if-js" role="button"><?php _e( 'Select all' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php _e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-sidewheels">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Render meta box on each post type created by Sidewheels
	 */
	public function display_meta_box(){

		// TODO: Add a real message
		echo 'This post type is managed by WP Sidewheels';

	}

}