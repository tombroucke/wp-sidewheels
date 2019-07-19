<?php
function bootstrap_form($form)
{
    $inline = (isset($form['display']) && $form['display'] == 'inline'); ?>
	<form action="<?php echo esc_url($form['url']); ?>" method="<?php echo(!isset($form['method']) || $form['method'] == 'post' ? 'post' : $form['method']) ?>" enctype="<?php echo(isset($form['enctype']) ? $form['enctype'] : '') ?>" class="<?php echo($inline ? 'form-inline' : ''); ?> <?php echo(isset($form['classes']) ? implode(' ', $form['classes']) : ''); ?> jquery-validate" <?php echo(isset($form['target']) ? 'target="' . $form['target'] . '"' : ''); ?>>
		<?php if (isset($form['fields'])): ?>
			<?php if (!$inline): ?>
				<div class="row">
				<?php endif; ?>
				<?php foreach ($form['fields'] as $field): ?>
					<?php
                    $classes = (isset($field['classes']) ? implode($field['classes'], ' ') : '');
    if ($inline) {
        $classes .= ' mr-2';
    } ?>
					<?php if (!$inline): ?>
						<div class="col-sm-<?php echo 12 / $form['cols']; ?>">
						<?php endif; ?>
						<?php if (isset($field['name'])): ?>
							<div class="form-group">
								<?php if (isset($field['label']) && $field['type'] != 'checkbox'): ?>
									<label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?><?php echo(isset($field['required']) && $field['required'] ? '<span class="text-danger">*</span>' : ''); ?></label>
								<?php endif; ?>
								<?php if (isset($field['description'])): ?>
									<span class="pull-right text-secondary" data-toggle="tooltip" data-placement="left" title="<?php echo $field['description']; ?>"><i class="fa fa-question-circle"></i></span>
								<?php endif; ?>
								<?php ob_start(); ?>
								<?php switch ($field['type']) {
                                    case 'select':
                                    ?>
									<select class="form-control <?php echo $classes; ?>" name="<?php echo $field['name']; ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?>>
										<?php foreach ($field['options'] as $value => $label): ?>
											<?php $value = ($value === 0 ? '' : $value); ?>
											<option value="<?php echo $value; ?>" <?php echo(isset($field['value']) && $field['value'] == $value ? 'selected' : ''); ?>><?php echo $label; ?></option>
										<?php endforeach; ?>
									</select>
									<?php
                                    break;

                                    case 'multiselect':
                                    ?>
									<select class="form-control <?php echo $classes; ?>" name="<?php echo $field['name']; ?>[]" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> multiple <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?>>
										<?php foreach ($field['options'] as $value => $label): ?>
											<option value="<?php echo $value; ?>" <?php echo(isset($field['value']) && in_array($value, $field['value']) ? 'selected' : ''); ?>><?php echo $label; ?></option>
										<?php endforeach; ?>
									</select>
									<?php
                                    break;

                                    case 'repeater':
                                    ?>
									<div class="repeater">
										<div data-repeater-list="<?php echo $field['name']; ?>">
											<?php if (!empty($field['values'])): ?>
												<?php foreach ($field['values'] as $key => $value): ?>
													<div data-repeater-item class="input-group mb-2">
														<input class="form-control" type="text" name="label" value="<?php echo $value['label'] ?>" placeholder="<?php echo $field['placeholder'] ?>" />
														<?php if (isset($value['meta'])): ?>
															<input class="form-control" type="text" name="meta" value="<?php echo $value['meta'] ?>" placeholder="" />
														<?php endif; ?>
														<input type="hidden" name="id" value="<?php echo $value['id']; ?>">
														<div class="input-group-append">
															<input class="btn btn-danger" data-repeater-delete type="button" value="<?php _e('Delete', fa_text_domain()); ?>"/>
														</div>
													</div>
												<?php endforeach; ?>
												<?php else: ?>
													<div data-repeater-item class="input-group mb-2">
														<input class="form-control" type="text" name="label" value="" placeholder="<?php echo $field['placeholder'] ?>" />
														<input class="form-control" type="text" name="meta" value="" placeholder="<?php _e('Meta', 'frontend-app'); ?>" />
														<div class="input-group-append">
															<input class="btn btn-danger" data-repeater-delete type="button" value="<?php _e('Delete', 'frontend-app'); ?>"/>
														</div>
													</div>
												<?php endif; ?>
											</div>
											<input class="btn btn-secondary" data-repeater-create type="button" value="<?php _e('Add', 'frontend-app'); ?>"/>
										</div>
										<?php
                                        break;

                                        case 'currency':
                                        ?>
										<div class="input-group">
											<input type="number" name="<?php echo $field['name']; ?>" class="form-control <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" value="<?php echo(isset($field['value']) ? $field['value'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?>>
											<div class="input-group-append">
												<span class="input-group-text"><?php echo get_option('fa_currency'); ?></span>
											</div>
										</div>
										<?php
                                        break;

                                        case 'hours':
                                        ?>
										<div class="input-group">
											<input type="number" name="<?php echo $field['name']; ?>" class="form-control <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" value="<?php echo(isset($field['value']) ? $field['value'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?> <?php echo(!isset($field['autocomplete']) || $field['autocomplete'] ? '' : 'autocomplete="off"'); ?>>
											<div class="input-group-append">
												<span class="input-group-text"><?php _e('hrs', 'frontend-app'); ?></span>
											</div>
										</div>
										<?php
                                        break;

                                        case 'file':
                                        ?>
										<input type="file" name="<?php echo $field['name']; ?>" class="form-control <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" value="<?php echo(isset($field['value']) ? $field['value'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?>>
										<?php
                                        break;

                                        case 'textarea':
                                        ?>
										<textarea name="<?php echo $field['name']; ?>" cols="30" rows="10" class="form-control <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?> <?php echo(!isset($field['autocomplete']) || $field['autocomplete'] ? '' : 'autocomplete="off"'); ?>><?php echo(isset($field['value']) ? $field['value'] : ''); ?></textarea>
										<?php
                                        break;

                                        case 'checkbox':
                                        ?>
										<div class="form-check">
											<label class="form-check-label">
											<input type="<?php echo $field['type']; ?>" name="<?php echo $field['name']; ?>" class="form-check-input <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?> <?php echo(isset($field['value']) && $field['value'] ? 'checked' : ''); ?> <?php echo(!isset($field['autocomplete']) || $field['autocomplete'] ? '' : 'autocomplete="off"'); ?>>
											<?php echo $field['label']; ?><?php echo(isset($field['required']) && $field['required'] ? '<span class="text-danger">*</span>' : ''); ?></label>
										</div>
										<?php
                                        break;

                                        default:
                                        ?>
										<input type="<?php echo $field['type']; ?>" name="<?php echo $field['name']; ?>" class="form-control <?php echo $classes; ?>" placeholder="<?php echo(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>" value="<?php echo(isset($field['value']) ? $field['value'] : ''); ?>" <?php echo(isset($field['required']) && $field['required'] ? 'required' : ''); ?> <?php echo(isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''); ?> <?php echo(!isset($field['autocomplete']) || $field['autocomplete'] ? '' : 'autocomplete="off"'); ?>>
										<?php
                                        break;
                                    } ?>
									<?php $input = ob_get_clean(); ?>
									<?php echo apply_filters('fa_custom_input_type', $input, $field, $form); ?>
								</div>
							<?php endif; ?>

							<?php if (!$inline): ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if (!$inline): ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if (isset($form['action'])): ?>
				<input type="hidden" name="action" value="<?php echo $form['action']; ?>">
				<?php wp_nonce_field($form['action'], $form['action'] . '_nonce_field'); ?>
			<?php endif; ?>
			<?php if (isset($form['hidden_fields'])): ?>
				<?php foreach ($form['hidden_fields'] as $hidden_field => $value): ?>
					<input type="hidden" name="<?php echo $hidden_field; ?>" value="<?php echo $value; ?>">
				<?php endforeach; ?>
			<?php endif; ?>
			<button type="submit" class="btn btn-primary"><?php echo(isset($form['submit_label']) ? $form['submit_label'] : __('Submit', 'frontend-app')); ?></button>

		</form>
		<?php
}

    function bootstrap_vertical_tabs($sections)
    {
        ?>
		<div class="col-md-3">
			<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
				<?php foreach ($sections as $key => $section): ?>
					<?php if (!isset($section['capability']) || current_user_can($section['capability'])): ?>
					<?php
                    $active = (!isset($_GET[__('section', fa_text_domain())]) && isset($section['default']) && $section['default']) || (isset($_GET[__('section', fa_text_domain())]) && $_GET[__('section', fa_text_domain())] == $section['name']);
        $link = (!isset($section['url']) ? sprintf('?%s=%s', __('section', fa_text_domain()), $section['name']) : $section['url']); ?>
					<a class="nav-link <?php echo($active ? 'active' : ''); ?>" role="tab" aria-selected="<?php echo($active ? 'true' : 'false'); ?>"  href="<?php echo $link; ?>" target="<?php echo(isset($section['target']) ? $section['target'] : '_self'); ?>"><?php echo(isset($section['icon']) ? '<i class="fa fa-' . $section['icon'] . '"></i>' : ''); ?><?php echo $section['title']; ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-md-8 offset-md-1">
		<div class="tab-content" id="v-pills-tabContent">
			<?php foreach ($sections as $key => $section): ?>
				<?php $active = (!isset($_GET[__('section', fa_text_domain())]) && isset($section['default']) && $section['default']) || (isset($_GET[__('section', fa_text_domain())]) && $_GET[__('section', fa_text_domain())] == $section['name']); ?>
				<?php if (!isset($section['capability']) || current_user_can($section['capability'])): ?>
				<?php if ($active): ?>
					<div class="tab-pane fade <?php echo($active ? 'show active' : ''); ?>" role="tabpanel" aria-labelledby="v-pills-home-tab">
						<?php include $section['template']; ?>
					</div>
				<?php endif; ?>
				<?php else: ?>
					<div class="tab-pane fade <?php echo($active ? 'show active' : ''); ?>" role="tabpanel" aria-labelledby="v-pills-home-tab">
						<div class="alert alert-danger" role="alert"><?php _e('You are not authorized to access this page', 'frontend-app'); ?></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
    }

function frontend_app_url($page = null, $scope = null, $id = null)
{
    $base = home_url();
    $query = '';

    if (isset($page)) {
        $page = '/' . __($page, fa_text_domain());
    }

    if (isset($scope) && $scope == 'edit' && isset($id)) {
        $query = '/' . $id . '/' . __($scope, fa_text_domain());
    } else {
        if (isset($scope) && $scope != 'section') {
            $query .= '/' . __($scope, fa_text_domain());
        } elseif (isset($scope)) {
            $query .= '?' . __($scope, fa_text_domain());
        }
        if (isset($id) && $scope != 'section' || isset($scope) && $scope == 'search') {
            $query .= '/' . $id;
        } elseif (isset($id)) {
            $query .= '=' . __($id, fa_text_domain());
        }
    }

    return $base . $page . $query;
}

function frontend_app_strip_querystring($url)
{
    $parsed_url = parse_url($url);

    if (!isset($parsed_url['query'])) {
        return $url;
    }

    parse_str($parsed_url['query'], $parameters);
    $querystring = '';
    if (isset($parameters[__('section', fa_text_domain())])) {
        $querystring = '?' . __('section', fa_text_domain()) . '=' . $parameters[__('section', fa_text_domain())];
    }

    return strtok($url, '?') . $querystring;
}

function frontend_app_current_url()
{
    return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
}

function frontend_app_pagination()
{
    global $queried_post_type;
    global $fa_posts_query;

    $posts_per_page = apply_filters('fa_posts_per_page', get_option('posts_per_page'), $queried_post_type);

    $fa_posts_query['posts_per_page'] = -1;
    $active = (isset($_GET['paged']) ? $_GET['paged'] : 1);

    $posts_count = count(get_posts($fa_posts_query));

    $paginator = new FA_Paginator($posts_count, $posts_per_page, $active);
    $url = frontend_app_current_url(); ?>
	<nav aria-label="Page navigation example" class="mt-3">
		<?php
    echo $paginator->createLinks($url); ?>
	</nav>
		<?php
}

function frontend_app_search($args = array())
{
    global $queried_post_type;
    $url = frontend_app_current_url();

    if (!$queried_post_type) {
        _e('This content can not be searched', 'frontend-app');
        return;
    } ?>
	<form method="get" class="form-inline mb-3">
		<input type="text" name="s" class="form-control" value="<?php echo(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="<?php echo(isset($args['placeholder']) ? $args['placeholder'] : ''); ?>">
		<?php
        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key != 's') {
                    ?>
					<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
					<?php
                }
            }
        } ?>
		<button type="submit" class="btn btn-secondary ml-2"><i class="fa fa-search"></i><?php _e('Search', 'frontend-app'); ?></button>
	</form>
	<?php if (isset($_GET['s']) && $_GET['s'] != ''): ?>
		<p>
			<span><?php _e('Searching for', 'frontend-app') ?>:</span>
			<br><a href="<?php echo preg_replace('/&?s=[^&]*/', '', $url); ?>" class="btn btn-danger btn-sm"><i class="fa fa-times"></i><?php echo $_GET['s']; ?></a>
		</p>
		<?php
    endif;
}

function frontend_app_sort_url($orderby, $inversed = false)
{
    $args = array(
        'orderby' => $orderby
    );

    if (isset($_GET['orderby']) && $_GET['orderby'] == $orderby) {
        if (!isset($_GET['order']) || $_GET['order'] == 'ASC') {
            $args['order'] = 'DESC';
        } else {
            $args['order'] = 'ASC';
        }
    } else {
        if (!isset($_GET['order']) || $_GET['order'] == 'ASC') {
            $args['order'] = 'ASC';
        } else {
            $args['order'] = 'DESC';
        }
    }
    $url = frontend_app_current_url();
    return add_query_arg($args, $url, 302);
}

function frontend_app_check_parameters($params, $method = 'post')
{
    $missing = array();
    foreach ($params as $key => $param) {
        if ($method == 'post') {
            if (!isset($_POST[$param]) || $_POST[$param] == '') {
                array_push($missing, $param);
            }
        } else {
            if (!isset($_GET[$param]) || $_GET[$param] == '') {
                array_push($missing, $param);
            }
        }
    }
    if (!empty($missing)) {
        return json_encode(array( 'success' => false, 'message' => __('Missing parameters: ', 'drivingschool') . implode(',', $missing) ));
        die();
    }
}

function frontend_app_trigger_404() {
	global $wp_query;
	$wp_query = new WP_Query();
	$wp_query->set_404();
	status_header(404);
	include( get_query_template( '404' ) );
	exit();
}

function wp_sidewheels()
{
	return WP_Sidewheels::get_instance();
}