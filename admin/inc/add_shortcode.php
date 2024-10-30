<?php defined('ABSPATH') or die('No script kiddies please!'); ?>
<?php
function map4re_shortcode_func($atts)
{

	$atts = shortcode_atts(array(
		'id' => '',
	), $atts, 'map4re');

	$idPost =  intval($atts['id']);

	if (get_post_status($idPost) != "publish") return;

	$data_post = get_post_meta($idPost, 'hotspot_content', true);

	if (!$data_post) {
		$data_post = maybe_unserialize(get_post_field('post_content', $idPost));
	}

	$maps_images = (isset($data_post['maps_images'])) ? $data_post['maps_images'] : '';
	$data_points = (isset($data_post['data_points'])) ? $data_post['data_points'] : '';
	ob_start();
	if ($maps_images) :
		?>
		<div class="wrap_svl_center">
			<?php
			?>
			<div class="wrap_svl_center_box">
				<div class="wrap_svl" id="body_drag_<?php echo $idPost; ?>">
					<div class="images_wrap">
						<img src="<?php echo $maps_images; ?>">
					</div>
					<?php if (is_array($data_points)) : ?>
						<?php $stt = 1;
						foreach ($data_points as $point) :
							$pins_title = (isset($point['title'])) ? $point['title'] : '';
							$pins_summary = (isset($point['summary'])) ? $point['summary'] : '';
							$pins_content_type = (isset($point['contentType'])) ? $point['contentType'] : '';
							$pins_link = (isset($point['link'])) ? $point['link'] : '';
							$pins_content = (isset($point['content'])) ? $point['content'] : '';
							$pin_images = (isset($point['pin_images'])) ? $point['pin_images'] : '';

							if ($pins_content_type == 'link') { ?>
								<a class="drag_element tips" target="_blank" href="<?php echo $pins_link; ?>" style=" top:<?php echo $point['top'] ?>%;left:<?php echo $point['left'] ?>%;">
								<?php } else { ?>
									<a class="drag_element tips" data-modal="<?php echo $stt; ?>" style="top:<?php echo $point['top'] ?>%;left:<?php echo $point['left'] ?>%;">
									<?php } ?>
									<div class="point_style point" data-html="<?php echo $pins_title; ?>,<?php echo $pins_summary; ?>">
										<div class="pins_animation" style="top:calc(<?php echo $point['top'] ?>% - 3px);left:calc(<?php echo $point['left'] ?>% - 3px);"></div>
										<img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '/images/map-pin.png'; ?>" class="pins_image " style="top:<?php echo $point['top'] ?>%;left:<?php echo $point['left'] ?>%;">
									</div>
								</a>
								<div class="md-modal md-effect" id="modal<?php echo $stt; ?>">
									<button class="md-close btn-close">&times;</button>
									<div class="modal">
										<?php if (count(json_decode($pin_images)) > 0) { ?>
											<div id="slideshow<?php echo $stt; ?>" class="slideshow">
												<div id="slideshow-container">
													<?php foreach (json_decode($pin_images) as $image) {
														?>
														<div class="slide fade" style="background-image: url('<?php echo $image; ?>') ">
														</div>
													<?php } ?>

													<a class="prev">&#10094;</a>
													<a class="next">&#10095;</a>

													<div class="dot-container">
														<?php
														if (count(json_decode($pin_images)) > 0) {
															foreach (json_decode($pin_images) as $index => $image) {
																?>
																<span class="dot currentSlide" data-id="<?php echo $index; ?>"></span>

															<?php }
														}
														?>
													</div>
												</div>
											</div>
										<?php } else { ?>
											<div id="slideshow<?php echo $stt; ?>" class="slideshow">
												<div id="slideshow-container">
													<div class="slide fade" style="background-image: url('<?php echo plugin_dir_url(dirname(__FILE__)) . 'images/placeholder.jpg'; ?>') ">
													</div>
													<div class="dot-container">
														<span class="dot currentSlide" data-id="1"></span>
													</div>
												</div>
											</div>
										<?php } ?>
										<div class="info">
											<div class="content">
												<h3><?php echo $pins_title; ?></h3>
												<pre><?php echo $pins_content; ?></pre>
											</div>
										</div>
										<button class="btn-close md-close-mobile">Close</button>
									</div>
								</div>
								<?php $stt++;
							endforeach; ?>
							<div class="md-overlay"></div>
						<?php endif; ?>
				</div>
			</div>
		</div>
	<?php
	endif;
	return ob_get_clean();
}
add_shortcode('map4re', 'map4re_shortcode_func');
