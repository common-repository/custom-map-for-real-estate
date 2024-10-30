<?php
/*
Plugin Name: Custom Map for Real Estate
Description: Make any image/photo into a elegant, responsive and completely integrated maps, best fit for any real estate project.
Version:     1.1.1
Author:      Team Blueotter
Author URI:  https://teamblueotter.com
License:     GPL2
Text Domain: map4re
Domain Path: /languages
Custom Map for Real Estate is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
*/

defined('ABSPATH') or die('No script kiddies please!');

define('map4re_VER', '1.1.1');
define('map4re_DEV_MOD', false);

load_textdomain('map4re',  dirname(__FILE__) .  '/languages/' . get_locale() . '.mo');

define('map4re_POINT_DEFAULT', serialize(array(
	'countPoint'	=>	'',
	'title'		    =>	'',
	'summary'		=>	'',
	'contentType'	=>	'',
	'link'			=>	'',
	'pin_images'	=>	'',
	'content'		=>	'',
	'left'			=>	'',
	'top'			=>	'',
)));
define('MAP4RE_PINS_DEFAULT', serialize(array(
	'countPoint'	=>	'',
	'imgPoint'		=>	'',
	'top'			=>	'',
	'left'			=>	''
)));

//include
include 'admin/inc/init.php';
include 'admin/inc/add_shortcode.php';

//metabox
function map4re_meta_box()
{
	//post type
	$screens = array('all_map');

	foreach ($screens as $screen) {
		add_meta_box(
			'map4re-metabox',
			__('Map Data', 'map4re'),
			'map4re_meta_box_callback',
			$screen,
			'normal',
			'high'
		);
		add_meta_box(
			'map4re-shortcode',
			__('Shortcode', 'map4re'),
			'map4re_shortcode_callback',
			$screen,
			'side',
			'high'
		);
	}
}
add_action('add_meta_boxes', 'map4re_meta_box');

function map4re_wp_default_editor()
{
	return "tinymce";
}

function map4re_meta_box_callback($post)
{
	add_filter('wp_default_editor', 'map4re_wp_default_editor');
	//add none field
	wp_nonce_field('map4re_save_meta_box_data', 'map4re_meta_box_nonce');

	$data_post = get_post_meta($post->ID, 'map_content', true);

	if (!$data_post) {
		$data_post = maybe_unserialize($post->post_content);
	}


	$maps_images = (isset($data_post['maps_images'])) ? $data_post['maps_images'] : '';
	$data_points = (isset($data_post['data_points'])) ? $data_post['data_points'] : '';
	?>
<div class="svl-image-wrap <?= ($maps_images) ? 'has-image' : '' ?>">
	<div class="svl-control">
		<input type="button" id="meta-image-button" class="button" value="<?php _e('Upload Image', 'map4re') ?>" />
		<input type="hidden" name="maps_images" class="maps_images" id="maps_images" value="<?php echo $maps_images; ?>" />
		<input type="button" name="add_point" class="add_point button view-has-value button-primary" value="<?php _e('Add Point', 'map4re'); ?>" />
		<span class="spinner"></span>
	</div>
	<div class="svl-control">
		<label><?php _e('Drag the Point to move, Click on it to add/edit information', 'map4re'); ?></label>
	</div>
	<div class="wrap_svl view-has-value" id="body_drag">
		<div class="images_wrap">
			<?php if ($maps_images) : ?>
			<img src="<?php echo $maps_images; ?>">
			<?php endif; ?>
		</div>
		<?php if (is_array($data_points)) : ?>
		<?php $stt = 1;
				foreach ($data_points as $point) : ?>
		<?php
					$data_input = array(
						'countPoint'	=>	$stt,
						'title'			=>	$point['title'],
						'summary'		=>	$point['summary'],
						'contentType'	=>	$point['contentType'],
						'link'			=>	$point['link'],
						'pin_images'	=>	$point['pin_images'],
						'top'			=>	$point['top'],
						'left'			=>	$point['left'],
					);
					echo map4re_get_pins_default($data_input); ?>
		<?php $stt++;
				endforeach; ?>
		<?php endif; ?>
	</div>
	<div class="all_points">
		<?php if (is_array($data_points)) : ?>
		<?php $stt = 1;
				foreach ($data_points as $point) : ?>
		<?php
					$data_input = array(
						'countPoint'	=>	$stt,
						'title'			=>	$point['title'],
						'summary'		=>	$point['summary'],
						'contentType'	=>	$point['contentType'],
						'link'			=>	$point['link'],
						'pin_images'	=>	$point['pin_images'],
						'content'		=>	$point['content'],
						'left'			=>	$point['left'],
						'top'			=>	$point['top'],
					);
					echo map4re_get_input_point_default($data_input); ?>
		<?php $stt++;
				endforeach; ?>
		<?php else : ?>
		<div style="display: none;"><?php wp_editor('', '_map4re_default_content'); ?></div>
		<?php endif; ?>
	</div>
	<?php
	}
	function map4re_shortcode_callback($post)
	{
		if (get_post_status($post->ID) == "publish") :
			?>
	<span><?php _e('Copy shortcode to view', 'map4re') ?></span>
	<div class="shortcodemap">
		<input readonly="readonly" value='[map4re id="<?= $post->ID ?>"]' id="copy_shortcode" />
		<button class="button" id="btn_shortcode"><?php _e('Copy', 'map4re'); ?></button>
	</div>
	<?php else : ?>
	<span><?php _e('Publish to view shortcode', 'map4re') ?></span>
	<?php
		endif;
	}
	function map4re_save_meta_box_data($post_id)
	{

		if (!isset($_POST['map4re_meta_box_nonce'])) {
			return;
		}
		if (!wp_verify_nonce($_POST['map4re_meta_box_nonce'], 'map4re_save_meta_box_data')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (isset($_POST['post_type']) && 'all_map' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}
		if (!isset($_POST['maps_images'])) {
			return;
		}

		$my_data = esc_url((isset($_POST['maps_images'])) ? $_POST['maps_images'] : '');
		$dataPoints = array();

		/*sanitize in map4re_convert_array_data*/
		$pointdata = (isset($_POST['pointdata'])) ? $_POST['pointdata'] : '';

		if (is_array($pointdata)) {
			$dataPoints = map4re_convert_array_data($pointdata);
		}
		$data_post = array(
			'maps_images'		=>	$my_data,
			'data_points'		=>	$dataPoints
		);
		update_post_meta($post_id, 'map_content', $data_post);
	}
	add_action('save_post', 'map4re_save_meta_box_data');

	function map4re_editor_styles()
	{

		global $wp_version;

		$baseurl = includes_url('js/tinymce');

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$version = 'ver=' . $wp_version;
		$dashicons = includes_url("css/dashicons$suffix.css?$version");

		// WordPress default stylesheet and dashicons
		$mce_css = array(
			$dashicons,
			$baseurl . '/skins/wordpress/wp-content.css?' . $version
		);

		$editor_styles = get_editor_stylesheets();
		if (!empty($editor_styles)) {
			foreach ($editor_styles as $style) {
				$mce_css[] = $style;
			}
		}

		$mce_css = trim(apply_filters('map4re_mce_css', implode(',', $mce_css)), ' ,');

		if (!empty($mce_css))
			return $mce_css;
		else
			return false;
	}

	/*Add admin script*/
	function map4re_admin_script()
	{
		global $typenow;
		if ($typenow == 'all_map') {
			wp_enqueue_media();

			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-droppable');

			wp_register_script('maps_points', plugin_dir_url(__FILE__) . 'admin/js/maps_points.js', array('jquery'), map4re_VER, true);
			wp_localize_script(
				'maps_points',
				'meta_image',
				array(
					'title' 		=> __('Select image', 'map4re'),
					'button' 		=> __('Select', 'map4re'),
					'site_url'		=>	home_url(),
					'ajaxurl'		=>	admin_url('admin-ajax.php'),
					'editor_style'	=>	map4re_editor_styles()
				)
			);
			wp_enqueue_script('maps_points');
		}
	}
	add_action('admin_enqueue_scripts', 'map4re_admin_script');

	/*Add admin style*/
	function map4re_admin_styles()
	{
		global $typenow;
		if ($typenow == 'all_map') {
			wp_enqueue_style('maps_points', plugin_dir_url(__FILE__) . 'admin/css/maps_points_style.css', array(), map4re_VER, 'all');
		}
	}
	add_action('admin_print_styles', 'map4re_admin_styles');

	/*Add frontend scripts*/
	function map4re_frontend_scripts()
	{
		wp_enqueue_style('maps-points', plugin_dir_url(__FILE__) . 'frontend/css/maps_points.css', array(), map4re_VER, 'all');
		wp_enqueue_script('maps-points', plugin_dir_url(__FILE__) . 'frontend/js/maps_points.js', array('jquery'), map4re_VER, true);
	}
	add_action('wp_enqueue_scripts', 'map4re_frontend_scripts');

	function map4re_get_input_point_default($data = array())
	{
		if (!is_array($data)) $data = array();
		$data = wp_parse_args($data, unserialize(map4re_POINT_DEFAULT));
		$countPoint 				= isset($data['countPoint']) ? $data['countPoint'] : '';
		$pointTitle				    = isset($data['title']) ? $data['title'] : '';
		$pointSummary				= isset($data['summary']) ? $data['summary'] : '';
		$pointContentType			= isset($data['contentType']) ? $data['contentType'] : '';
		$pointLink					= isset($data['link']) ? $data['link'] : '';
		$pin_images 				= isset($data['pin_images']) ? $data['pin_images'] : '';
		$pointContent 				= isset($data['content']) ? $data['content'] : '';
		$pointLeft 					= isset($data['left']) ? $data['left'] : '';
		$pointTop 					= isset($data['top']) ? $data['top'] : '';
		ob_start();
		?>
	<div class="map4re-popup list_points" tabindex="-1" role="dialog" id="info_draggable<?php echo $countPoint ?>" data-popup="info_draggable<?php echo $countPoint ?>" data-points="<?php echo $countPoint ?>">
		<div class="map4re-popup-inner">
			<div class="map4re-popup-modal-content">
				<div class="map4re-popup-modal-header">
					<h3 class="modal-title"><?php _e('Content', 'map4re') ?></h3>
				</div>
				<div class="map4re-popup-modal-body">
					<div class="map4re_row">
						<div>
							<label><?php _e('Room Title', 'map4re') ?></label><br>
							<input type="text" name="pointdata[title][]" value="<?php echo $pointTitle ?>" placeholder="<?php _e('Luxury, Deluxe...', 'map4re') ?>" />
						</div>
						<div>
							<label><?php _e('Room Summary', 'map4re') ?></label><br>
							<input type="text" name="pointdata[summary][]" value="<?php echo $pointSummary ?>" placeholder="<?php _e('Total size: 35m2. Available for 2 people...', 'map4re') ?>" />
						</div>
						<div>
							<label><?php _e('Content Type', 'map4re') ?></label><br>
							<select name="pointdata[contentType][]">
								<?php $types = array('link' => __('Link', 'map4re'), 'description' => __('Description', 'map4re')); ?>
								<?php foreach ($types as $key => $type) { ?>
								<option value="<?php echo $key; ?>" <?= $key == $pointContentType ? ' selected="selected"' : ''; ?>><?php echo $type; ?></option>
								<?php } ?>
							</select>
						</div>
						<div>
							<label><?php _e('Link', 'map4re') ?></label><br>
							<input type="text" name="pointdata[link][]" value="<?php echo $pointLink ?>" placeholder="abc.com" />
						</div>
						<div>
							<label><?php _e('Room Images', 'map4re') ?></label><br>
							<div class="svl-upload-image <?= (count(($pin_images)) > 0) ? 'has-image' : '' ?>">

								<input type="hidden" name="pointdata[pin_images][]" class="pin_images" value='<?php echo $pin_images; ?>' />
								<?php
									if (count(json_decode($pin_images)) > 0) {
										foreach (json_decode($pin_images) as $key => $image) {
											?>
								<div class="view-has-value">
									<img src="<?php echo $image;
															?>" class="image_view pins_img" />
									<a href="#" data-index="<?php echo $key;
																		?>" class="delete-image">x</a>
								</div>
								<?php }
									}
									?>
								<div class="hidden-has-value"><input type="button" class="button-upload button" value="<?php _e('Select image', 'map4re') ?>" /></div>
							</div>
						</div>
					</div>
					<?php
						add_filter('wp_default_editor', 'map4re_wp_default_editor');
						$settings = array(
							'textarea_name'	=>	'pointdata[content][]',
							'tabindex' => 4,
							'tinymce' => array(
								'min_height'	=>	200,
								'toolbar1'		=>	'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,wp_more'
							),
						);
						wp_editor($pointContent, 'point_content' . $countPoint, $settings);
						?>
					<p>
						<input type="hidden" name="pointdata[top][]" min="0" max="100" step="any" value="<?php echo $pointTop ?>" />
					</p>
					<p>
						<input type="hidden" name="pointdata[left][]" min="0" max="100" step="any" value="<?php echo $pointLeft ?>" />
					</p>
				</div>
				<div class="map4re-popup-modal-footer">
					<button type="button" class="button button-large button_delete "><?php _e('Delete', 'map4re') ?></button>
					<button type="button" class="button button-primary button-large button_save" data-popup-close="info_draggable<?php echo $countPoint ?>"><?php _e('Save', 'map4re') ?></button>
					<button type="button" class="button button-large button-close" data-popup-close="info_draggable<?php echo $countPoint ?>"><?php _e('Close', 'map4re') ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
		return ob_get_clean();
	}

	function map4re_get_pins_default($datapin = array())
	{
		if (!is_array($datapin)) $datapin = array();
		$datapin = wp_parse_args($datapin, unserialize(MAP4RE_PINS_DEFAULT));
		$countPoint = $datapin['countPoint'];
		$topPin = $datapin['top'];
		$leftPin = $datapin['left'];
		ob_start();
		?>
	<div id="draggable<?php echo $countPoint ?>" data-points="<?php echo $countPoint ?>" class="drag_element" <?php if ($topPin && $leftPin) : ?> style="top:<?php echo $topPin ?>%; left:<?php echo $leftPin ?>%;" <?php endif; ?>>
		<div class="point_style">
			<a href="#" class="pins_click_to_edit" data-popup-open="info_draggable<?php echo $countPoint ?>" data-target="#info_draggable<?php echo $countPoint ?>">
				<img src="<?php echo plugins_url('admin/images/map-pin.png', __FILE__); ?>">
			</a>
		</div>
	</div>
	<?php
		return ob_get_clean();
	}
	//Clone Point
	add_action('wp_ajax_map4re_clone_point', 'map4re_clone_point_func');
	function map4re_clone_point_func()
	{
		if (!wp_verify_nonce($_REQUEST['nonce'], "map4re_save_meta_box_data")) {
			exit();
		}
		if (!is_user_logged_in()) {
			wp_send_json_error();
		}
		$countPoint = intval($_POST['countpoint']);
		$countPoint = (isset($countPoint) && !empty($countPoint)) ? $countPoint : mt_rand();
		$datapin = array(
			'countPoint'	=>	$countPoint
		);
		$data_input = array(
			'countPoint'	=>	$countPoint,
		);
		wp_send_json_success(array(
			'point_pins'	=>	map4re_get_pins_default($datapin),
			'point_data'	=>	map4re_get_input_point_default($data_input)
		));
		die();
	}

	function map4re_convert_array_data($inputArray = array())
	{
		$aOutput =  array();
		$firstKey = null;
		foreach ($inputArray as $key => $value) {
			$firstKey = $key;
			break;
		}
		$nCountKey = count($inputArray[$firstKey]);
		for ($i = 0; $i < $nCountKey; $i++) {
			$element = array();
			foreach ($inputArray as $key => $value) {
				$element[$key] = wp_kses_post($value[$i]);
			}
			array_push($aOutput, $element);
		}
		return $aOutput;
	}
