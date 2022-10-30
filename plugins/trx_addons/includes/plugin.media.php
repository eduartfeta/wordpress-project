<?php
/**
 * Media utilities
 *
 * @package ThemeREX Addons
 * @since v1.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Set quality to save cropped images
if (!function_exists('trx_addons_set_images_quality')) {
	add_filter( 'wp_editor_set_quality', 'trx_addons_set_images_quality', 10, 2 );
	function trx_addons_set_images_quality($defa=90, $mime='') {
		$q = (int) trx_addons_get_option('images_quality');
		if ($q == 0) $q = 90;
		return max(1, min(100, $q));
	}
}

// Allow upload SVG images
if (!function_exists('trx_addons_allow_upload_svg')) {
	add_filter('upload_mimes', 'trx_addons_allow_upload_svg');
	function trx_addons_allow_upload_svg( $mime_types ) {
		if ( trx_addons_get_setting( 'allow_upload_svg', false ) ) $mime_types['svg'] = 'image/svg+xml';
		return $mime_types;
	}
}

// Allow upload ANI images
if (!function_exists('trx_addons_allow_upload_ani')) {
	add_filter('upload_mimes', 'trx_addons_allow_upload_ani');
	function trx_addons_allow_upload_ani( $mime_types ) {
		if ( trx_addons_get_setting( 'allow_upload_ani', false ) ) $mime_types['ani'] = 'application/x-navi-animation';
		return $mime_types;
	}
}


// Allow upload archive
if ( ! function_exists( 'trx_addons_allow_upload_archives' ) ) {
	function trx_addons_allow_upload_archives() {
		add_filter( 'upload_mimes', 'trx_addons_add_archives_to_mimes', 10, 2 );
	}
}


// Disallow upload archive
if ( ! function_exists( 'trx_addons_disallow_upload_archives' ) ) {
	function trx_addons_disallow_upload_archives() {
		remove_action( 'upload_mimes', 'trx_addons_add_archives_to_mimes', 10 );
	}
}


// Allow upload archive
if ( ! function_exists( 'trx_addons_add_archives_to_mimes' ) ) {
	function trx_addons_add_archives_to_mimes( $mime_types, $user_id = 0 ) {
		$mime_types['zip'] = 'application/zip';
		return $mime_types;
	}
}

// Check if image in the uploads folder
if (!function_exists('trx_addons_is_from_uploads')) {
	function trx_addons_is_from_uploads($url) {
		$local = false;
		$url = trx_addons_remove_protocol($url);
		$parts = explode( '?', $url );
		$url = $parts[0];
		$uploads_info = wp_upload_dir();
		$uploads_url = trx_addons_remove_protocol($uploads_info['baseurl']);
		$uploads_dir = $uploads_info['basedir'];
		return $local = strpos($url, $uploads_url)!==false && file_exists(str_replace($uploads_url, $uploads_dir, $url));
	}
}

// Fix the img's attribute 'sizes' to display images with more quality on the small screen (less then 768px)
if (!function_exists('trx_addons_add_image_sizes')) {
	add_filter( 'wp_calculate_image_sizes', 'trx_addons_add_image_sizes', 10, 5 );
	function trx_addons_add_image_sizes( $sizes, $size, $image_src, $image_meta, $attachment_id ) {
		$tmp = array_map( 'trim', explode( ',', $sizes ) );
		$max_one_column_width = apply_filters( 'trx_addons_filter_max_one_column_width', 767 );
		if ( count($tmp) == 2 && trx_addons_parse_num( $tmp[1] ) > 300 && trx_addons_parse_num( $tmp[1] ) <= $max_one_column_width ) {
			$sizes = sprintf( '(max-width: %1$dpx) 100vw, %2$s', $max_one_column_width, $tmp[1] );
		}
		return $sizes;
	}
}

// Get an image sizes from the image url (if the image is in the uploads ot a theme folder)
if (!function_exists('trx_addons_getimagesize')) {
	function trx_addons_getimagesize($url, $echo=false) {
		$img_size = false;
		$img_path = trx_addons_is_url( $url ) ? trx_addons_url_to_local_path( $url ) : $url;
		if ( ! empty( $img_path ) && file_exists( $img_path ) ) {
			$img_size = getimagesize( $img_path );
		}
		if ( $echo && $img_size !== false && ! empty( $img_size[3] ) ) {
			echo ' ' . trim( $img_size[3] );
		}
		return $img_size;
	}
}

// Return image size name with @retina modifier (if need)
if (!function_exists('trx_addons_get_thumb_size')) {
	function trx_addons_get_thumb_size($ts) {
		static $template_thumb_prefix = false;
		if ( $template_thumb_prefix === false ) {
			$template_thumb_prefix = sprintf( '%s-thumb-', get_template() );
		}
		$retina = trx_addons_get_retina_multiplier() > 1 ? '-@retina' : '';
		$is_external = apply_filters(
							'trx_addons_filter_is_external_thumb_size',
							in_array( $ts,
										apply_filters(
											'trx_addons_filter_external_thumb_sizes',
											array( 'full', 'post-thumbnail', 'thumbnail', 'large' )   // Don't add 'medium' to this array
										)
									)
							|| strpos( $ts, 'woocommerce' ) === 0
							|| strpos( $ts, 'yith' ) === 0
							|| strpos( $ts, 'course' ) === 0
							|| strpos( $ts, 'trx_demo' ) === 0,
							$ts
						);
		$is_internal = strpos( $ts, 'trx_addons-thumb-' ) === 0 || strpos( $ts, $template_thumb_prefix ) === 0;
		return apply_filters(
					'trx_addons_filter_get_thumb_size',
					( $is_external || $is_internal ? '' : 'trx_addons-thumb-' )
					. $ts
					. ( $is_internal ? $retina : '' )
				);
	}
}

// Clear thumb sizes from image name
if (!function_exists('trx_addons_clear_thumb_size')) {
	function trx_addons_clear_thumb_size( $url, $remove_protocol = true ) {
		$pi = pathinfo($url);
		if ( $remove_protocol ) {
			$pi['dirname'] = trx_addons_remove_protocol($pi['dirname']);
		}
		$parts = explode('-', $pi['filename']);
		$suff = explode('x', $parts[count($parts)-1]);
		if (count($suff)==2 && (int) $suff[0] > 0 && (int) $suff[1] > 0) {
			array_pop($parts);
			$url = $pi['dirname'] . '/' . join('-', $parts) . ( ! empty( $pi['extension'] ) ? '.' . $pi['extension'] : '' );
		}
		return $url;
	}
}

// Add thumb sizes to image name
if ( ! function_exists( 'trx_addons_add_thumb_size' ) ) {
	function trx_addons_add_thumb_size( $url, $thumb_size, $check_exists = true ) {

		if ( empty( $url ) ) return '';

		$pi = pathinfo( $url );

		// Remove image sizes from filename
		$parts = explode( '-', $pi['filename'] );
		$suff = explode( 'x', $parts[ count( $parts ) - 1 ] );
		if ( count( $suff ) == 2 && (int) $suff[0] > 0 && (int) $suff[1] > 0) {
			array_pop( $parts );
		}
		$url = $pi['dirname'] . '/' . join( '-', $parts ) . ( ! empty( $pi['extension'] ) ? '.' . $pi['extension'] : '' );

		// Add new image sizes
		global $_wp_additional_image_sizes;
		if ( isset( $_wp_additional_image_sizes[$thumb_size] ) && is_array( $_wp_additional_image_sizes[$thumb_size] ) ) {
			if ( empty( $_wp_additional_image_sizes[ $thumb_size ]['height'] ) || empty( $_wp_additional_image_sizes[ $thumb_size ]['crop'] ) ) {
				$image_id = trx_addons_attachment_url_to_postid( $url );
				if ( is_numeric( $image_id ) && (int) $image_id > 0 ) {
					$attach = wp_get_attachment_image_src( $image_id, $thumb_size );
					if ( ! empty( $attach[0] ) ) {
						$pi = pathinfo( $attach[0] );
						$pi['dirname'] = trx_addons_remove_protocol( $pi['dirname'] );
						$parts = explode( '-', $pi['filename'] );
					}
				}
			} else {
				$parts[] = intval( $_wp_additional_image_sizes[ $thumb_size ]['width'] ) . 'x' . intval( $_wp_additional_image_sizes[ $thumb_size ]['height'] );
			}
		}
		$pi['filename'] = join( '-', $parts );
		$new_url = trx_addons_remove_protocol( $pi['dirname'] . '/' . $pi['filename'] . ( ! empty( $pi['extension'] ) ? '.' . $pi['extension'] : '' ) );

		// Check exists
		if ( $check_exists ) {
			$uploads_info = wp_upload_dir();
			$uploads_url = trx_addons_remove_protocol( $uploads_info['baseurl'] );
			$uploads_dir = $uploads_info['basedir'];
			if ( strpos( $new_url, $uploads_url ) !== false ) {
				if ( ! file_exists( str_replace( $uploads_url, $uploads_dir, $new_url ) ) ) {
					$new_url = trx_addons_remove_protocol( $url );
				}
			} else {
				$new_url = trx_addons_remove_protocol( $url );
			}
		}
		return $new_url;
	}
}

// Return thumb dimensions by thumb size name
if (!function_exists('trx_addons_get_thumb_dimensions')) {
	function trx_addons_get_thumb_dimensions($thumb_size) {
		$dim = array('width' => 0, 'height' => 0);
		global $_wp_additional_image_sizes;
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $thumb_size, array_keys( $_wp_additional_image_sizes ) ) ) {
			$dim['width']  = intval( $_wp_additional_image_sizes[$thumb_size]['width'] );
			$dim['height'] = intval( $_wp_additional_image_sizes[$thumb_size]['height'] );
		}
		return $dim;
	}
}

// Return image size multiplier
if (!function_exists('trx_addons_get_retina_multiplier')) {
	function trx_addons_get_retina_multiplier($force_retina=0) {
		$mult = min(4, max(1, $force_retina > 0 ? $force_retina : trx_addons_get_option("retina_ready")));
		if ($mult > 1 && (int) trx_addons_get_value_gpc('trx_addons_is_retina', 0) == 0) {
			$mult = 1;
		}
		return $mult;
	}
}

// Return 'no-image'
if (!function_exists('trx_addons_get_no_image')) {
	function trx_addons_get_no_image( $img = '', $need = false ) {
		if ( empty( $img ) && ! $need ) {
			$img = 'css/images/no-image.jpg';
		}
		return apply_filters( 'trx_addons_filter_no_image', ! empty( $img ) ? trx_addons_get_file_url( $img ) : '', $need );
	}
}

// Return slider layout
if (!function_exists('trx_addons_get_slider_layout')) {
	function trx_addons_get_slider_layout($args=array(), $images=array()) {
		$args = apply_filters('trx_addons_filter_slider_args', array_merge(array(
			'engine' => 'swiper',			// swiper | elastistack - slider's engine
			'style' => 'default',			// default | modern - style of the slider Swiper
			'mode' => 'gallery',			// gallery | posts | custom - fromwhere get images for slider - from current post's gallery or from featured images or from custom array with images
			'effect' => 'slide',			// slide | fade | cube | coverflow | flip - change slides effect
			'direction' => 'horizontal',	// horizontal | vertical - direction of slides change
			'per_view' => 1,				// Slides per view
			'slides_type' => 'bg',			// images|bg - Use image as slide's content or as slide's background
			'slides_ratio' => '16:9',		// Ratio to resize slides on the tabs and mobile
			'slides_space' => 0,			// Space between slides
			'slides_centered' => 'no',		// Put active slide to the center. With an even number of slides makes the slide halves on the sides
			'slides_overflow' => 'no',		// Don't hide slides outside the borders of the viewport
			'mouse_wheel' => 'no',			// Enable mouse wheel to control slider
			'free_mode' => 'no',			// Enable free mode
//			'parallax' => 0,				// Parallax shift (in %)
			'noresize' => 'no',				// Disable resize slider
			'noswipe' => 'no',				// Disable swipe guestures
			'autoplay' => 'yes',			// Autoplay slides
			'loop' => 'yes',				// Loop slides
			'controls' => 'yes',			// Show Prev/Next arrows
			'controls_pos' => 'side',		// side | outside | top | bottom - position of the slider controls
			'label_prev' => esc_html__('Prev|PHOTO', 'trx_addons'),				// Label of the 'Prev Slide' button (Modern style)
			'label_next' => esc_html__('Next|PHOTO', 'trx_addons'),				// Label of the 'Next Slide' button (Modern style)
			'pagination' => 'no',			// Show pagination bullets
			'pagination_type' => 'bullets',	// bullets | fraction | progress - type of the pagination
			'pagination_pos' => 'bottom',	// bottom | bottom_outside | left | right - position of the pagination
			'controller' => 'no',			// Show controller with slides images and title
			'controller_pos' => 'right',	// left | right | bottom - position of the slider controller
			'controller_style' => 'default',// Style of controller
			'controller_controls' => 'yes', // Show arrows in the controller
			'controller_effect' => 'slide',	// slide | fade | cube | coverflow | flip - change slides effect for the controller
			'controller_per_view' => 3, 	// Slides per view in the controller
			'controller_space' => 0, 		// Space between slides in the controller
			'controller_margin' => -1, 		// Space between slider and controller
			'controller_height' => '', 		// Height of the the controller
			'titles' => 'no',				// no | center | bottom | lb | rb | outside - where put post's title on slide
			'large' => 'no',				// Show large title on the slides
			'speed' => '',					// Slides change interval
			'interval' => '',				// Slides change interval
			'height' => '',					// Slides height (if empty - auto)
			'thumb_size' => '',				// Size of images (if empty - big)
			'post_type' => 'post',			// Post type to get posts
			'taxonomy' => 'category',		// Taxonomy to get posts
			'cat' => '',					// Category to get posts
			'ids' => '',					// Comma separated posts IDs
			'count' => 5,					// Posts number to show in slider
			'orderby' => 'date',			// Posts order by
			'order' => 'desc',				// Posts order
			'class' => '',					// Additional classes for slider container
			'id' => ''						// ID of the slider container
			), $args));

		if ($args['engine']=='swiper') {
			if ($args['pagination_type']=='progress') {
				if ($args['direction']=='vertical' && !in_array($args['pagination_pos'], array('left', 'right')))
					$args['pagination_pos'] = 'left';
				if ($args['direction']=='horizontal' && $args['pagination_pos']!='bottom')
					$args['pagination_pos'] = 'bottom';
			}
			$args['per_view'] = empty($args['per_view']) ? 1 : max(1, min(8, (int) $args['per_view']));
			if ( in_array( $args['effect'], array( 'fade', 'flip', 'cube' ) ) ) {
				$args['per_view'] = 1;	
			}
			$args['controller_per_view'] = empty($args['controller_per_view']) ? 1 : max(1, min(8, (int) $args['controller_per_view']));
			if ( in_array( $args['controller_effect'], array( 'fade', 'flip', 'cube' ) ) ) {
				$args['controller_per_view'] = 1;	
			}
			$args['speed'] = $args['speed']=='' ? 600 : max(300, (int) $args['speed']);
			$args['interval'] = $args['interval']=='' ? mt_rand(5000, 10000) : max(0, (int) $args['interval']);
			if (empty($args['id']) && trx_addons_is_on($args['controller'])) {
				$args['id'] = trx_addons_generate_id( 'sc_slider_' );
			}
		} else {
			$args['controller'] = 'no';
		}

		if (empty($args['thumb_size'])) {
			$args['thumb_size'] = trx_addons_get_thumb_size( empty($args['height']) || intval($args['height']).'_' != $args['height'].'_' || $args['height'] >= 630
																	? 'full'
																	: ( $args['height'] >= 420
																		? 'huge'
																		: ( $args['height'] >= 210
																			? 'big'
																			: 'medium' 
																			) 
																		) 
															);
		}

		global $post;

		// Get images from first gallery in the current post
		if ( empty($images) || ! is_array($images) ) {

			if ($args['mode'] == 'gallery') {						// Get images from first gallery in the current post

				$gallery = $images = array();

				$meta = (array)get_post_meta( get_the_ID(), 'trx_addons_options', true );
				if ( ! empty( $meta['gallery_list'] ) ) {
					$gallery = explode( '|', $meta['gallery_list'] );
				} else {
					$post_content = $post->post_content;
					if ( has_shortcode($post_content, 'gallery') ) {	// Standard WordPress shortcode [gallery]
						$gallery = get_post_gallery_images( $post );
						if (count($gallery) == 0) {
							$ids = trx_addons_get_tag_attrib($post_content, '[gallery]', 'ids');
							if (!empty($ids)) {
								$ids = explode(',', $ids);
								foreach ( $ids as $id ) {
									$attach = wp_get_attachment_image_src($id, 'full');
									if ( ! empty( $attach[0] ) ) {
										$gallery[] = trx_addons_remove_protocol($attach[0]);
									}
								}
							}
						}
					} else if ( ( $pos = strpos($post_content, '<!-- wp:gallery') ) !== false && ( $pos2 = strpos($post_content, '<!-- /wp:gallery') ) !== false ) {	// Gallery from Gutenberg
						$html = substr($post_content, $pos, $pos2 - $pos);
						if (preg_match_all('/src="([^"]*)"/', $post_content, $matches) && !empty($matches[1]) && is_array($matches[1])) {
							$gallery = $matches[1];
						}
					}
				}
				if (is_array($gallery) && count($gallery) > 0) {
					$num = 0;
					foreach ( $gallery as $image_url ) {
						$num++;
						$images[] = array(
							'url' => trx_addons_add_thumb_size($image_url, $args['thumb_size']),
							'title' => '',
							'link' => trx_addons_is_singular() ? '' : get_permalink()
							);
						if ($num >= $args['count']) break;
					}
				}

			} else {												// Get featured images from posts in the specified category

				if (!empty($args['ids'])) {
					if ( is_array( $args['ids'] ) ) {
						$args['ids'] = join(',', $args['ids']);
					}
					$posts = explode(',', $args['ids']);
					$args['count'] = count($posts);
				}
			
				$q_args = array(
					'post_type' => $args['post_type'],
					'post_status' => 'publish',
					'posts_per_page' => $args['count'],
					'ignore_sticky_posts' => true,
					'order' => $args['order'] == 'asc' ? 'asc' : 'desc',
				);
		
				$q_args = trx_addons_query_add_sort_order($q_args, $args['orderby'], $args['order']);
				//$q_args = trx_addons_query_add_filters($q_args, 'thumbs');
				$q_args = trx_addons_query_add_posts_and_cats($q_args, $args['ids'], $args['post_type'], $args['cat'], $args['taxonomy']);

				$q_args = apply_filters( 'trx_addons_filter_query_args', $q_args, 'get_slides_slider_layout' );
				
				$query  = new WP_Query( apply_filters( 'trx_addons_filter_slides_query_args', $q_args, $args ) );

				$num = 0;
				
				$images = array();
				while ( $query->have_posts() ) { $query->the_post();
					$num++;
					$images[] = apply_filters('trx_addons_filter_slider_content', array(
						'url'  => trx_addons_get_attachment_url(get_post_thumbnail_id(get_the_ID()), $args['thumb_size']),
						'title'=> get_the_title(),
						'cats' => trx_addons_get_post_terms(', ', get_the_ID(), $args['taxonomy']),	//get_the_category_list(', '),
						'date' => apply_filters('trx_addons_filter_get_post_date', get_the_date()),
						'link' => get_permalink()
						),
						$args);
					if ($num >= $args['count']) break;
				}
				wp_reset_postdata();
			}

		} else {													// Get images from specified array

			foreach ( $images as $k=>$v ) {
				if ( ! is_array( $v ) ) {
					$images[$k] = apply_filters(
										'trx_addons_filter_slider_content',
										array(
											'url' => trx_addons_add_thumb_size($v, $args['thumb_size']),
											'title' => '',
											'link' => ''
											),
										$args,
										$v
									);
				}
				if (empty($v['url']) && !empty($v['image'])) {
					$images[$k]['url'] = trx_addons_get_attachment_url($v['image'], $args['thumb_size']);
				}
				if (empty($v['cats']) && !empty($v['subtitle'])) {
					$images[$k]['cats'] = $v['subtitle'];
				}
				if (empty($v['date']) && !empty($v['meta'])) {
					$images[$k]['date'] = $v['meta'];
				}
				$images[$k]['link_atts'] = '';
				if ( ! empty( $v['new_window'] ) || ! empty( $v['link_extra']['is_external'] ) ) {
					$images[$k]['link_atts'] .= ' target="_blank"';
				}
                if ( ! empty( $v['nofollow'] ) || ! empty( $v['link_extra']['nofollow'] ) ) {
					$images[$k]['link_atts'] .= ' rel="nofollow"';
                }
			}
		}

		$num = 0;
		$output = '';
		if (is_array($images) && count($images) > 0) {

			$images_present = false;

			foreach ( $images as $k=>$v ) {
				if ( !empty($v['url']) || !empty($v['bg_color']) ) {
					$images_present = true;
					break;
				}
			}
			if ( ! $images_present ) {
				$args['slides_type'] = 'text';
				$args['noresize'] = 1;
				$args['height'] = 0;
			}
			
			$dim = trx_addons_get_thumb_dimensions($args['thumb_size']);
			if ($dim['height'] == 0) $dim['height'] = $dim['width'] / 16 * 9;

			if ( trx_addons_is_on($args['controller']) ) {
				$output .= '<div' . (!empty($args['id']) ? ' id="' . esc_attr($args['id']) . '_' . esc_attr($args['engine']) . '_outer_wrap"' : '')
							. ' class="slider_outer_wrap'
									. ' slider_'.esc_attr($args['engine']).'_outer_wrap'
									. ' slider_outer_wrap_controller_' .  esc_attr($args['controller_style'])
									. ' slider_outer_wrap_controller_pos_' .  esc_attr($args['controller_pos'])
							. '">';
			}

			$output .= '<div' . (!empty($args['id']) ? ' id="' . esc_attr($args['id']) . '_' . esc_attr($args['engine']) . '_outer"' : '')
						. ' class="slider_outer slider_'.esc_attr($args['engine']).'_outer'
							. ($args['engine'] == 'swiper'
								? ' slider_style_' . esc_attr($args['style'])
									. ' slider_outer_direction_' . esc_attr($args['direction']) 
									. ' slider_outer_' . esc_attr($args['per_view']==1 
											? 'one' 
											: 'multi')
									. ' slider_outer_' . esc_attr(trx_addons_is_on($args['pagination']) 
											? 'pagination slider_outer_pagination_'.esc_attr($args['pagination_type']).' slider_outer_pagination_pos_'.esc_attr($args['pagination_pos']) 
											: 'nopagination')
								: '' )
							. ' slider_outer_' . esc_attr(trx_addons_is_on($args['controls']) 
									? 'controls slider_outer_controls_' . esc_attr($args['controls_pos']) 
									: 'nocontrols')
							. ' slider_outer_' . esc_attr(trx_addons_is_on($args['slides_centered']) 
									? 'centered' 
									: 'nocentered')
							. ' slider_outer_overflow_' . esc_attr(trx_addons_is_on($args['slides_overflow']) 
									? 'visible' 
									: 'hidden')
							. ' slider_outer_' . esc_attr(!trx_addons_is_off($args['titles']) 
									? 'titles_'.$args['titles'] 
									: 'notitles')
							. '"'
						. '>'
					. '<div' 
						. ( ! empty($args['id']) 
							? ' id="' . esc_attr($args['id']) . '_' . esc_attr($args['engine']) . '"' 
							: '')
						. ' class="slider_container slider_'.esc_attr($args['engine'])
							. (!empty($args['class']) ? ' '.esc_attr($args['class']) : '')
							. ' ' . esc_attr($args['engine']) . '-slider-container'
							. ' slider_' . esc_attr(trx_addons_is_on($args['controls']) 
									? 'controls slider_controls_' . esc_attr($args['controls_pos']) 
									: 'nocontrols')
							. ' slider_' . esc_attr(trx_addons_is_on($args['slides_centered']) 
									? 'centered' 
									: 'nocentered')
							. ' slider_overflow_' . esc_attr(trx_addons_is_on($args['slides_overflow']) 
									? 'visible' 
									: 'hidden')
							. ' slider_' . esc_attr(!trx_addons_is_off($args['titles']) 
									? 'titles_'.$args['titles'] 
									: 'notitles')
							. ' slider_' . esc_attr(trx_addons_is_on($args['noresize']) || $args['noresize'] == 1
									? 'noresize' 
									: 'resize')
							. ' slider_' . esc_attr(trx_addons_is_on($args['noswipe'])
									? 'noswipe' 
									: 'swipe')
							. ' slider_height_' . esc_attr( (int) $args['height']==0		// || $args['slides_type']!='bg'
									? 'auto' 
									: 'fixed')
							. ( $args['engine'] == 'swiper'
									? ' slider_direction_' . esc_attr($args['direction']) 
										. ' slider_' . esc_attr(trx_addons_is_on($args['pagination']) 
												? 'pagination slider_pagination_'.esc_attr($args['pagination_type']).' slider_pagination_pos_' . esc_attr($args['pagination_pos']) 
												: 'nopagination')
										. ' slider_' . esc_attr($args['per_view']==1 
												? 'one' 
												: 'multi')
										. ' slider_type_' . esc_attr($args['slides_type'])
									: '' )
							.'"'
						. ( ! empty($args['slides_ratio']) || (!empty($dim['width']) && !empty($dim['height']) )
							? ' data-ratio="'.esc_attr(!empty($args['slides_ratio']) ? $args['slides_ratio'] : $dim['width'].':'.$dim['height']).'"'
							: '')
						. ( $args['engine'] == 'swiper'
							? ' data-interval="'.esc_attr($args['interval']).'"'
								. ' data-speed="'.esc_attr($args['speed']).'"'
								. ' data-effect="'.esc_attr($args['effect']).'"'
								. ' data-pagination="'.esc_attr($args['pagination_type']).'"'
								. ' data-direction="'.esc_attr($args['direction']).'"'
								. ' data-slides-per-view="'.esc_attr($args['per_view']).'"'
								. ' data-slides-space="'.esc_attr($args['slides_space']).'"'
								. ' data-slides-centered="'.esc_attr(trx_addons_is_on($args['slides_centered']) ? '1' : '0').'"'
								. ' data-slides-overflow="'.esc_attr(trx_addons_is_on($args['slides_overflow']) ? '1' : '0').'"'
								. ' data-mouse-wheel="'.esc_attr(trx_addons_is_on($args['mouse_wheel']) ? '1' : '0').'"'
								. ' data-autoplay="'.esc_attr(trx_addons_is_on($args['autoplay']) ? '1' : '0').'"'
								. ' data-loop="'.esc_attr(trx_addons_is_on($args['loop']) ? '1' : '0').'"'
								. ' data-free-mode="'.esc_attr(trx_addons_is_on($args['free_mode']) ? '1' : '0').'"'
						   		. ' data-slides-min-width="' . esc_attr(!empty($args['slides_min_width'])
						   													? $args['slides_min_width']
						   													: apply_filters( 'trx_addons_filter_slider_slide_width', 220 )
						   													) . '"'
						   		. ( trx_addons_is_on($args['controller']) 
						   			? ' data-controller="' . esc_attr( $args['id'] . '_' . esc_attr($args['engine']) . '_controller' ) . '"' 
						   			: '' )
							: '' )
						. ( (int)$args['height'] > 0 
							? ' style="'.esc_attr(trx_addons_get_css_position_from_values('', '', '', '', '', $args['height'])).'"' 
							: '' )
						. '>'
						. '<div class="slider-wrapper'
									. ' ' . esc_attr($args['engine']) . '-wrapper'
									. ($args['engine'] == 'elastistack' ? ' stack' : '')
									. '">'
						. ( $args['engine'] == 'elastistack' 
							? '<ul class="stack__images">' 
							: '' );
			$titles_outside = '';
			foreach ($images as $image) {
				$num++;
				$titles = '';
				if (!trx_addons_is_off($args['titles']) ) {
					$titles_content = apply_filters('trx_addons_filter_slider_title', '', $image, $args);
					if (empty($titles_content)) {
						if (!empty($image['cats'])) {
							$titles_content .= '<div class="slide_cats">' . trim($image['cats']) . '</div>';
						}
						if (!empty($image['title'])) {
							$titles_content .= '<h3 class="slide_title">'
										. ( ! empty( $image['link'] )
												? '<a href="' . esc_url($image['link']) . '"'
													. ( ! empty( $image['link_atts'] )
															? $image['link_atts']
															: ''
															)
													. '>'
												: ''
												)
										. trim( $image['title'] )
										. ( ! empty( $image['link'] ) ? '</a>' : '')
										. '</h3>';
						}
						if (!empty($image['date'])) {
							$titles_content .= '<div class="slide_date">' . trim($image['date']) . '</div>';
						}
					}
					if (!empty($titles_content)) {
						$titles = '<div class="slide_info slide_info_'.(trx_addons_is_on($args['large']) ? 'large' : 'small').'">' 
										. trim($titles_content) 
									. '</div>';
						$titles_outside .= $titles;
					}
				}

				$video = trx_addons_get_video_layout( array(
														'link' => isset($image['video_url']) ? $image['video_url'] : '',
														'embed' => isset($image['video_embed']) ? $image['video_embed'] : '',
														'cover' => !empty($image['url']) ? $image['url'] : '',
														'show_cover' => false
														)
													);
				$attr_wh = '';
				//if ($args['slides_type'] == 'images' && !empty($image['url'])) {
				//	$attr_wh = trx_addons_getimagesize($image['url']);
				//	$attr_wh = !empty($attr_wh[3]) ? ' '.$attr_wh[3] : '';
				//}

				$output .= apply_filters( 'trx_addons_filter_slider_slide',
								($args['engine'] == 'elastistack' ? '<li ' : '<div ')
								. (!empty($image['id']) ? ' id="' . esc_attr($image['id']) . '"' : '') 
								. ' class="slider-slide '.esc_attr($args['engine']).'-slide'
									. (!empty($image['class']) ? ' ' . esc_attr($image['class']) : '') 
									. (!empty($image['content']) ? ' with_content' : '')
									. (!empty($titles) && $args['titles']!='outside' ? ' with_titles' : '')
									. '"'
								. (!empty($image['url']) ? ' data-image="' . esc_url($image['url']) . '"' : '')
								. (!empty($image['cats']) ? ' data-cats="' . esc_attr($image['cats']) . '"' : '')
								. (!empty($image['title']) ? ' data-title="' . esc_attr($image['title']) . '"' : '')
								. (!empty($image['date']) ? ' data-date="' . esc_attr($image['date']) . '"' : '')
								. ' style="'
									. ( $args['slides_type'] == 'bg' && ( ! empty($image['url']) || empty( $image['bg_color'] ) )
												? 'background-image:url(' . esc_url( ! empty($image['url']) ? $image['url'] : trx_addons_get_no_image() ) . ');' 
												: ''
												)
									. ( ! empty($image['bg_color']) 
												? 'background-color:' . esc_attr($image['bg_color']) . ';' 
												: ''
												)
//									. ((int)$args['height']>0 
//											? 'min-'.esc_attr(trx_addons_get_css_position_from_values('', '', '', '', '', $args['height'])) 
//											: '')
									. ( ! empty($image['css']) ? esc_attr($image['css']) : '' )
									. '"'
								. '>'
								. ( $args['slides_type'] == 'bg' || empty($image['url'] )
									? '' 
									: apply_filters( 'trx_addons_filter_slider_image', '<img src="' . esc_url($image['url']) . '"' 
												. $attr_wh 
												. ' alt="' . ( ! empty($image['title']) ? esc_attr($image['title']) : '') . '">' )
									)
								. ( ! empty($video) ? $video : '')
								. ( empty($video) || ! empty($image['url'])
									? (
										( ! empty($image['content']) || ( ! empty($titles) && $args['titles'] != 'outside' )
											? ( ! empty($image['url']) && empty($video)
												? '<div class="slide_overlay slide_overlay_'.(trx_addons_is_on($args['large']) ? 'large' : 'small').'"></div>'
												: ''
												)
												. ( ! empty($titles) && $args['titles'] != 'outside' ? trim($titles) : '' )
											: '' )
										. ( ! empty($image['link']) && $args['engine'] != 'elastistack' 
												? '<a href="'.esc_url($image['link']).'" class="slide_link"'
													. ( ! empty( $image['link_atts'] )
															? $image['link_atts']
															: ''
															)
													. '></a>' 
												: ''
												)
										)
									: ''
									)
								. ( ! empty($image['content'] ) 
										? '<div class="slide_content">' . trim($image['content']) . '</div>'
										: ''
										)
								. ( $args['engine'] == 'elastistack' ? '</li>' : '</div>' ),
							$image,
							$args
							);
			}
			
			$output .= ($args['engine'] == 'elastistack' 
							? '</ul>' 
							: '')
						. '</div>';
			// Prepare controls
			if ($args['style']=='modern' && trx_addons_is_on($args['controls'])) {
				$prev = explode('|', $args['label_prev']);
				$next = explode('|', $args['label_next']);
			}
			$controls_output = trx_addons_is_on($args['controls'])
								? apply_filters( 'trx_addons_filter_slider_controls',
									'<div class="slider_controls_wrap">'
										. '<a class="slider_prev '.esc_attr($args['engine']).'-button-prev" href="#">'
											. ($args['style']=='modern' && !empty($args['label_prev']) 
												? '<span class="slider_controls_label"><span>' . esc_html($prev[0]).'</span>' 
													. (!empty($prev[1]) ? '<span>' . esc_html($prev[1]).'</span>' : '') . '</span>' 
												: '' )
										. '</a>'
										. '<a class="slider_next '.esc_attr($args['engine']).'-button-next" href="#">'
											. ($args['style']=='modern' && !empty($args['label_next']) 
												? '<span class="slider_controls_label"><span>' . esc_html($next[0]).'</span>' 
													. (!empty($next[1]) ? '<span>' . esc_html($next[1]).'</span>' : '') . '</span>' 
												: '' )
										. '</a>'
									. '</div>',
									$args
									)
								: '';
			
			// Prepare pagination
			$pagination_output = ($args['engine'] == 'swiper' && trx_addons_is_on($args['pagination']))
										? '<div class="slider_pagination_wrap swiper-pagination"></div>'
										: '';
			
			$out_pagination = $out_controls = false;

			// Output inside controls and pagination
			if ($args['pagination_type']=='progress' || $args['pagination_pos']!='bottom_outside') {
				$output .= $pagination_output;
				$out_pagination = true;
			}
			if ($args['style']!='modern' && $args['controls_pos'] == 'side') {
				$output .= $controls_output;
				$out_controls = true;
			}
		
			// Close inner container
			$output .= '</div>';
			
			// Output outside titles, controls and pagination
			if (!$out_controls && $args['style']=='modern') {
				$output .= $controls_output;
			}
			if (!$out_pagination) {
				$output .= $pagination_output;
			}
			if (!$out_controls && $args['style']!='modern') {
				$output .= $controls_output;
			}
			if (!empty($titles_outside) && $args['titles']=='outside') {
				$output .= '<div class="slider_titles_outside_wrap">' . trim($titles_outside) . '</div>';
			}
			
			// Close outer container
			$output .= '</div>';
			
			// Output controller (if present)
			if ( trx_addons_is_on($args['controller']) ) {
				$output .= 	'<div'
								. ( !empty($args['id']) ? ' id="' . esc_attr($args['id']) . '_' . esc_attr($args['engine']) . '_controller"' : '' )
								. ' class="sc_slider_controller'
									. ' sc_slider_controller_' . esc_attr($args['controller_style']) 
									. ' sc_slider_controller_' . esc_attr( ( $args['controller_pos'] == 'bottom' ? 'horizontal' : 'vertical' ) ) 
									. ' sc_slider_controller_height_' . ( (int)$args['controller_height'] > 0 && $args['controller_pos'] == 'bottom' ? 'fixed' : 'auto')
									. ( ! empty($args['controller_height']) && $args['controller_pos'] == 'bottom'
											? ' ' . trx_addons_add_inline_css_class( '--sc-slider-controller-height:' . esc_attr( trx_addons_prepare_css_value( $args['controller_height'] ) ) )
											: ''
										)
									. ( isset( $args['controller_margin'] ) && $args['controller_margin'] >= 0
											? ' ' . trx_addons_add_inline_css_class( 'padding-' . ( $args['controller_pos'] == 'bottom' ? 'top' : ( $args['controller_pos'] == 'left' ? 'right' : 'left' ) ) . ':' . trx_addons_prepare_css_value( $args['controller_margin'] ) . ' !important;' )
											: ''
										)
									. '"'
								. ' data-slider-id="' . esc_attr( preg_replace('/_sc$/', '', $args['id']) ) . '_' . esc_attr($args['engine']) . '"'
								. ' data-style="' . esc_attr( $args['controller_style'] ) . '"'
								. ' data-controls="' . esc_attr( trx_addons_is_on( $args['controller_controls'] ) ? 1 : 0 ) . '"'
								. ' data-interval="' . esc_attr( $args['interval'] ) . '"'
								. ' data-effect="' . esc_attr( $args['controller_effect'] ) . '"'
								. ' data-direction="' . esc_attr( $args['controller_pos'] == 'bottom' ? 'horizontal' : 'vertical' ) . '"'
								. ' data-slides-per-view="' . esc_attr( $args['controller_per_view'] ) . '"'
								. ' data-slides-space="' . esc_attr( $args['controller_space'] ) . '"'
// Moved to CSS var
//								. ( (int)$args['controller_height'] > 0 && $args['controller_pos'] == 'bottom' ? ' data-height="' . esc_attr(trx_addons_prepare_css_value($args['controller_height'])) . '"' : '')
							. '>'
								. '<div'
										. ( !empty($args['id']) ? ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr($args['engine']) . '_controller_outer"' : '' )
										. ' class="slider_outer slider_swiper_outer slider_style_controller'
														. ' slider_outer_' . ( trx_addons_is_on( $args['controller_controls'] ) ? 'controls slider_outer_controls_side' : 'nocontrols' )
														. ' slider_outer_nocentered'
														. ' slider_outer_overflow_hidden'
														. ' slider_outer_nopagination'
														. ' slider_outer_' . ( $args['controller_per_view'] == 1 ? 'one' : 'multi' )
														. ' slider_outer_direction_' . ( $args['controller_pos'] == 'bottom' ? 'horizontal' : 'vertical' )
														. '"'
								. '>'
									. '<div'
											. ( !empty($args['id']) ? ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr($args['engine']) . '_controller_container"' : '' )
											. ' class="slider_container slider_controller_container slider_swiper swiper-slider-container'
													. ' slider_' . ( trx_addons_is_on( $args['controller_controls'] ) ? 'controls slider_controls_side' : 'nocontrols' )
													. ' slider_nocentered'
													. ' slider_overflow_hidden'
													. ' slider_nopagination'
													. ' slider_notitles'
													. ' slider_noresize'
													. ' slider_' . ( $args['controller_per_view'] == 1 ? 'one' : 'multi' )
													. ' slider_direction_' . ( $args['controller_pos'] == 'bottom' ? 'horizontal' : 'vertical' )
													. '"'
											. ' data-slides-min-width="' . esc_attr( apply_filters( 'trx_addons_filter_slider_controller_slide_width', 150 ) ) . '"'
											. ' data-controlled-slider="' . esc_attr($args['id']) . '_' . esc_attr($args['engine']) . '"'
											. ' data-direction="' . ( $args['controller_pos'] == 'bottom' ? 'horizontal' : 'vertical' ) . '"'
											. ' data-mouse-wheel="' . esc_attr(trx_addons_is_on($args['mouse_wheel']) ? '1' : '0') . '"'
											. ' data-autoplay="' . esc_attr(trx_addons_is_on($args['autoplay']) ? '1' : '0') . '"'
											. ' data-loop="' . esc_attr(trx_addons_is_on($args['loop']) ? '1' : '0') . '"'
											. ( ! empty( $args['controller_effect'] ) ? ' data-effect="' . esc_attr( $args['controller_effect'] ) . '"' : '')
											. ( ! empty( $args['interval'] ) ? ' data-interval="' . esc_attr( $args['interval'] ) . '"' : '')
											. ( ! empty( $args['controller_per_view'] ) ? ' data-slides-per-view="' . esc_attr( $args['controller_per_view'] ) . '"' : '')
											. ( ! empty( $args['controller_space'] ) ? ' data-slides-space="' . esc_attr( $args['controller_space'] ) . '"' : '')
// Moved to CSS-var
//											. ( ! empty( $args['controller_height'] ) && $args['controller_pos'] == 'bottom' ? ' style="height:' . esc_attr( trx_addons_prepare_css_value( $args['controller_height'] ) ) . '"' : '')
									. '>'
										. '<div class="slider-wrapper swiper-wrapper">';
				$num = 0;
				foreach ($images as $image) {
					$num++;
					$output .= apply_filters( 'trx_addons_filter_slider_toc_slide',
								'<div class="slider-slide swiper-slide'
									. ( ! empty( $image['url'] ) && empty($image['cats']) && empty( $image['title'] ) && empty($image['date'])
										? ' slider-slide-bg ' . trx_addons_add_inline_css_class('background-image:url(' . esc_url( $image['url'] ) . ');')
										: ''
										)
								. '">'
									. ( ! empty($image['cats']) || ! empty( $image['title'] ) || ! empty($image['date'])
										? '<div class="sc_slider_controller_item">'
												. ( ! empty( $image['url'] )
													? '<img class="sc_slider_controller_item_image"'
														. ' src="' . esc_url( trx_addons_add_thumb_size( $image['url'], apply_filters( 'trx_addons_filter_slider_controller_image_size', trx_addons_get_thumb_size( ( ! empty( $args['controller_height'] ) && $args['controller_height'] > 100 && $args['controller_pos'] == 'bottom' ? 'avatar' : 'tiny' ) ) ) ) ) . '"'
														. ' alt="' . esc_attr( $image['title'] ) . '"'
// Moved to CSS-var
//														. ( ! empty( $args['controller_height'] ) && $args['controller_pos'] == 'bottom' ? ' style="height: calc( ' . esc_attr( trx_addons_prepare_css_value( $args['controller_height'] ) ) . ' - 2em); width: auto;"' : '')
														. '>'
													: ''
													)
												. '<div class="sc_slider_controller_item_info">'
													. ( ! empty($image['cats'])
														? '<div class="sc_slider_controller_item_info_cats">' . trim( $image['cats'] ) . '</div>'
														: ''
														)
													. ( ! empty( $image['title'] )
														? '<h5 class="sc_slider_controller_item_info_title">'
																. '<span class="sc_slider_controller_item_info_number">' . ( $num < 9 ? '0' : '' ) . $num . '.</span>'
																. esc_html( trx_addons_strwords( $image['title'], apply_filters( 'trx_addons_filter_slider_controller_title_length', $args['controller_pos'] == 'bottom' ? 6 : 10, $args ) ) )
															. '</h5>'
														: ''
														)
													. ( ! empty($image['date'])
														? '<div class="sc_slider_controller_item_info_date">' . trim( $image['date'] ) . '</div>'
														: ''
														)
												. '</div>'
											. '</div>'
										: ''
										)
								. '</div>',
								$image,
								$args
								);
				}
				$output .= 				'</div>'
								. '</div>'
								. ( trx_addons_is_on( $args['controller_controls'] )
									? '<div class="slider_controls_wrap"><a class="slider_prev swiper-button-prev" href="#"></a><a class="slider_next swiper-button-next" href="#"></a></div>'
									: ''
									)
							. '</div>'
						. '</div>';
				// Close outer_wrap container (only if controller present)
				$output .= '</div>';
			}
		}
		if ( ! empty($output) ) {
			trx_addons_enqueue_slider( $args['engine'] );
		}

		return apply_filters('trx_addons_filter_slider_layout', $output, $args);
	}
}


// Prepare slides with video for post-format == 'video'
if (!function_exists('trx_addons_slider_content_post_format_video')) {
	add_filter('trx_addons_filter_slider_content', 'trx_addons_slider_content_post_format_video', 10, 3);
	function trx_addons_slider_content_post_format_video($image, $args, $data='') {
		$post_format = get_post_format();
		$post_format = empty( $post_format ) ? 'standard' : str_replace( 'post-format-', '', $post_format );
		if (get_post_type() == 'post' && $post_format == 'video') {
			$rez = trx_addons_extract_post_video();
			if ( ! empty( $rez['video_url'] ) ) {
				$image['video_url'] = $rez['video_url'];
			} else if ( ! empty( $rez['video_embed'] ) ) {
				$image['video_embed'] = $rez['video_embed'];
			}
		}
		return $image;
	}
}


// Extract video from current post
if (!function_exists('trx_addons_extract_post_video')) {
	function trx_addons_extract_post_video() {
		$rez = array();
		$post_content = trx_addons_get_post_content();
		$post_content_parsed = $post_content;
		$video = trx_addons_get_post_video( $post_content, true );
		if ( ! empty( $video ) ) {
			$rez['video_url'] = $video;
		} else {
			$video = trx_addons_get_post_iframe( $post_content, false );
			if ( ! empty( $video ) ) {
				$rez['video_embed'] = $video;
			} else {
				// Only get video from the content if a playlist isn't present.
				$post_content_parsed = trx_addons_filter_post_content( $post_content );
				if ( false === strpos( $post_content_parsed, 'wp-playlist-script' ) ) {
					$videos = get_media_embedded_in_content( $post_content_parsed, array( 'video', 'object', 'embed', 'iframe' ) );
					if ( ! empty( $videos ) && is_array( $videos ) ) {
						$video = trx_addons_array_get_first( $videos, false );
						if ( ! empty( $video ) ) {
							$rez['video_embed'] = $video;
						}
					}
				}
			}
		}
		return $rez;
	}
}


// Return video frame layout
if (!function_exists('trx_addons_get_video_layout')) {
	function trx_addons_get_video_layout($args=array()) {
		$args = array_merge(array(
			'link' => '',					// Link to the video on Youtube or Vimeo
			'embed' => '',					// Embed code instead link
			'cover' => '',					// URL or ID of the cover image
			'cover_size' => 'masonry-big',	// Thumb size of the cover image
			'show_cover' => true,			// Show cover image or only add classes
			'popup' => false,				// Open video in the popup window or insert instead cover image (default)
			'autoplay' => false,			// Make video autoplay
			'mute' => false,				// Mute video
			'class' => '',					// Additional classes for slider container
			'id' => ''						// ID of the slider container
			), $args);

		if ( empty($args['embed']) && empty($args['link']) ) {
			return '';
		}
		if ( empty($args['cover']) ) {
			$args['popup'] = false;
		} else {
			$args['cover'] = trx_addons_get_attachment_url($args['cover'], 
										apply_filters('trx_addons_filter_video_cover_thumb_size', trx_addons_get_thumb_size($args['cover_size'])));
		}
		if ( empty($args['id']) ) {
			$args['id'] = trx_addons_generate_id( 'sc_video_' );
		}

		$output = '<div'
					. ( !empty($args['id']) ? ' id="' . esc_attr($args['id']) . '"' : '' )
					. ' class="trx_addons_video_player' 
								. (!empty($args['cover']) ? ' with_cover hover_play' : ' without_cover')
								. (!empty($args['autoplay']) && !empty($args['mute']) ? ' with_video_autoplay' : '')
								. (!empty($args['class']) ? ' ' . esc_attr($args['class']) : '')
							. '"'
					. '>';
		$args['embed'] = trx_addons_get_embed_layout(array(
														'link' => $args['link'],
														'embed' => $args['embed']
													));
		if ( ! empty($args['cover']) ) {
			$args['embed'] = trx_addons_make_video_autoplay($args['embed']);
			if ( $args['show_cover'] ) {
				$attr = trx_addons_getimagesize($args['cover']);
				$output .= '<img src="' . esc_url($args['cover']) . '" alt="' . esc_attr__("Video cover", 'trx_addons') . '"' . (!empty($attr[3]) ? ' '.trim($attr[3]) : '').'>';
			}
			$output .= apply_filters('trx_addons_filter_video_mask',
							'<div class="video_mask"></div>'
							. ($args['popup']
									? '<a class="video_hover trx_addons_popup_link" href="#'.esc_attr($args['id']).'_popup"></a>'
									: '<div class="video_hover" data-video="'.esc_attr($args['embed']).'"></div>'
							),
							$args);
		} else if ( ! empty($args['autoplay']) ) {
			$args['embed'] = trx_addons_make_video_autoplay( $args['embed'], $args['mute'] );
		}
		if ( empty($args['popup']) ) {
			$output .= '<div class="video_embed video_frame">'
							. ( empty($args['cover']) ? $args['embed'] : '' )
						. '</div>';
		}
		$output .= '</div>';
		// Add popup
		if ( ! empty($args['popup']) ) {
			// Attention! Don't remove comment <!-- .sc_layouts_popup --> - it used to split output on parts in the sc_promo
			$output .= '<!-- .sc_layouts_popup -->'
						. '<div' . ( !empty($args['id']) ? ' id="' . esc_attr($args['id']) . '_popup"' : '' ) . ' class="sc_layouts_popup">'
							. '<div'
								. ( !empty($args['id']) ? ' id="' . esc_attr($args['id']) . '_popup_player"' : '' )
								. ' class="trx_addons_video_player without_cover'
											. (!empty($args['class']) ? ' ' . esc_attr($args['class']) : '')
										. '"'
							. '>'
								. '<div class="video_embed video_frame">'
									. str_replace(
										array(
											'wp-video',
											'src=',
											'style="width: 640px;"',
										),
										array(
											'trx_addons_video',
											'data-src=',
											'',
										),
										$args['embed']
									)
								. '</div>'
							. '</div>'
						. '</div>';
		}
		return apply_filters('trx_addons_filter_video_layout', $output, $args);
	}
}


// Return embeded code layout
if (!function_exists('trx_addons_get_embed_layout')) {
	function trx_addons_get_embed_layout($args=array()) {
		$args = array_merge(array(
			'link' => '',					// Link to the video on Youtube or Vimeo
			'embed' => '',					// Embed code instead link
			), $args);

		if (empty($args['embed']) && empty($args['link'])) {
			return '';
		}
		if ( ! empty($args['embed']) ) {
			$args['embed'] = str_replace("`", '"', $args['embed']);
		} else {
			global $wp_embed;
			if (is_object($wp_embed)) {
				$args['embed'] = do_shortcode( $wp_embed->run_shortcode( '[embed]' . trim( $args['link'] ) . '[/embed]' ) );
				if ( strpos( $args['embed'], '<iframe' ) !== false ) {
					$dim = apply_filters( 'trx_addons_filter_video_dimensions', array(
						'width'  => trx_addons_get_tag_attrib( $args['embed'], '<iframe>', 'width' ),
						'height' => trx_addons_get_tag_attrib( $args['embed'], '<iframe>', 'height' )
					) );
					if ( $dim['width'] > 0 ) {
						$args['embed'] = trx_addons_set_tag_attrib( $args['embed'], '<iframe>', 'width', $dim['width'] );
						$args['embed'] = trx_addons_set_tag_attrib( $args['embed'], '<iframe>', 'height', $dim['height'] );
					}
				} else if ( strpos( $args['embed'], '<video' ) === false ) {
					$args['embed'] = apply_filters( 'trx_addons_filter_embed_video_link',
										'<video src="' . esc_url( $args['link'] ) . '" controls="controls" loop="loop"></video>',
										$args
										);
				}
			}
		}
		return apply_filters('trx_addons_filter_embed_layout', $args['embed'], $args);
	}
}


// Return the image url by attachment ID or URL
if (!function_exists('trx_addons_get_attachment_url')) {
	function trx_addons_get_attachment_url($image_id, $size='full') {
		if ( is_array( $image_id ) ) {
			$image_id = ! empty( $image_id[ 'id' ] )
							? (int) $image_id[ 'id' ]
							: ( ! empty( $image_id[ 'url' ] )
									? $image_id[ 'url' ]
									: ''
								);
		}
		if ( is_numeric( $image_id ) && (int) $image_id > 0 ) {
			$attach = wp_get_attachment_image_src($image_id, $size);
			$image_id = empty( $attach[0] ) ? '' : $attach[0];
		} else {
			$image_id = trx_addons_add_thumb_size($image_id, $size);
		}
		return $image_id;
	}
}


// Return the image tag by attachment ID or URL
if ( ! function_exists( 'trx_addons_get_attachment_img' ) ) {
	function trx_addons_get_attachment_img( $image_id, $size='full', $args=array() ) {
		$args = array_merge( array(
									'filter' => '',
									'class'  => '',
									'alt'    => ''
								),
								$args
							);
		$image = '';
		if ( is_numeric( $image_id ) && (int) $image_id > 0 ) {
			$image = wp_get_attachment_image( $image_id, apply_filters('trx_addons_filter_thumb_size', $size, $args['filter'] ), false, array( 'class' => $args['class'] ) );
		} else {
			$image = trx_addons_get_attachment_url( $image_id, apply_filters('trx_addons_filter_thumb_size', $size, $args['filter'] ) );
			if ( ! empty( $image ) ) {
				$attr = trx_addons_getimagesize( $image );
				$image = '<img src="' . esc_url( $image ) . '"'
							. ' class="' . esc_attr( $args['class'] ) . '"'
							. ' alt="' . esc_attr( $args['alt'] ) . '"'
							. ( ! empty( $attr[3] ) ? ' ' . trim( $attr[3] ) : '' )
							. ' />';
			}
		}
		return $image;
	}
}


// Return attachment id by url
if (!function_exists('trx_addons_attachment_url_to_postid')) {
	function trx_addons_attachment_url_to_postid($url) {
		static $images = array();
		if ( ! isset( $images[$url] ) ) {
			$images[$url] = attachment_url_to_postid( trx_addons_clear_thumb_size( $url, false ) );
		}
		return $images[$url];
	}
}

// Return the media caption for the specified id
if (!function_exists( 'trx_addons_get_attachment_caption' ) ) {
	function trx_addons_get_attachment_caption( $id ) {
		$caption = '';
		if ( (int) $id > 0 ) {
			$meta = get_post_meta( intval( $id ) );
			$alt = '';
			if ( ! empty( $meta['_wp_attachment_image_alt'][0] ) ) {
				$caption = $meta['_wp_attachment_image_alt'][0];
			} else if ( ! empty( $meta['_wp_attachment_metadata'][0] ) ) {
				$meta = trx_addons_unserialize( $meta['_wp_attachment_metadata'][0] );
				if ( ! empty( $meta['image_meta']['caption'] ) ) {
					$caption = $meta['image_meta']['caption'];
				}
			}
		}
		return $caption;
	}
}


// Return url from first <img> tag inserted in post
if (!function_exists('trx_addons_get_post_image')) {
	function trx_addons_get_post_image($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) {
			$post_text = $post->post_content;
		}
		if (preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"][^>]*>/is', $post_text, $matches)) {
			$img = $matches[$src ? 1 : 0][0];
		}
		return $img;
	}
}


// Return url from first <audio> tag inserted in post
if ( ! function_exists( 'trx_addons_get_post_audio' ) ) {
	function trx_addons_get_post_audio( $post_text = '', $src = true, $from_meta = true ) {
		global $post;
		$img = $from_meta ? trx_addons_get_post_audio_list_first( ! $src ) : '';
		if ( is_array( $img ) ) {
			if ( ! empty( $img['url'] ) ) {
				$img = $img['url'];
			} else if ( ! empty( $img['embed'] ) ) {
				$img = trx_addons_get_post_iframe( $img['embed'], true );
			} else {
				$img = '';
			}
		}
		if ( empty( $img ) ) {
			if ( empty( $post_text ) ) {
				$post_text = $post->post_content;
			}
			if ( $src ) {
				if ( preg_match_all( '/<audio.+src=[\'"]([^\'"]+)[\'"][^>]*>/is', $post_text, $matches ) ) {
					$img = $matches[1][0];
				} else if ( preg_match_all( '/<!\\-\\- wp:trx-addons\\/audio-item.+"url":"([^"]*)"/is', $post_text, $matches ) ) {
					$img = $matches[1][0];
				}
			} else {
				$img = trx_addons_get_tag( $post_text, '<audio', '</audio>' );
				if ( empty( $img ) ) {
					$img = do_shortcode( trx_addons_get_tag( $post_text, '[audio', '[/audio]' ) );
				}
				if ( empty( $img ) ) {
					$img = trx_addons_get_tag_attrib( $post_text, '[trx_widget_audio]', 'url' );
					if ( empty( $img ) && preg_match_all( '/<!\\-\\- wp\\:trx-addons\\/audio-item.+"url"\\:"([^"]*)"/is', $post_text, $matches ) ) {
						$img = $matches[1][0];
					}
					if ( ! empty( $img ) ) {
						$img = '<audio src="' . esc_url( $img ) . '"></audio>';
					}
				}
			}
		}
		return $img;
	}
}


// Return list audios from post attributes
if ( ! function_exists( 'trx_addons_get_post_audio_list' ) ) {
	function trx_addons_get_post_audio_list( $args=array() ) {
		$list = array();
		$meta = (array)get_post_meta( get_the_ID(), 'trx_addons_options', true );
		if ( ! empty( $meta['audio_list'][0]['url'] ) || ! empty( $meta['audio_list'][0]['embed'] ) ) {
			$list = $meta['audio_list'];
		}
		return apply_filters( 'trx_addons_filter_get_post_audio_list', $list, $args );
	}
}


// Return first audio from post attributes
if ( ! function_exists( 'trx_addons_get_post_audio_list_first' ) ) {
	function trx_addons_get_post_audio_list_first( $src = false, $args = array() ) {
		$list  = trx_addons_get_post_audio_list( array_merge( $args, array(
					'posts_per_page' => 1
					) ) );
		$audio = '';
		if ( ! $src ) {
			$audio = ! empty( $list[0]['url'] ) || ! empty( $list[0]['embed'] ) ? $list[0] : array();
		} else {
			if ( ! empty( $list[0]['url'] ) ) {
				$audio = trx_addons_get_embed_audio( $list[0]['url'] );
			} else if ( ! empty( $list[0]['embed'] ) ) {
				$audio = $list[0]['embed'];
			}
		}
		return $audio;
	}
}


// Return layout with embeded audio
if ( ! function_exists( 'trx_addons_get_embed_audio' ) ) {
	function trx_addons_get_embed_audio( $audio, $use_wp_embed = false ) {
		global $wp_embed;
		if ( $use_wp_embed && is_object( $wp_embed ) ) {
			$embed_audio = do_shortcode( $wp_embed->run_shortcode( '[embed]' . trim( $audio ) . '[/embed]' ) );
		} else {
			$embed_audio = '<audio src="' . esc_url( $audio ) . '" controls="controls"></audio>';
		}
		return $embed_audio;
	}
}


// Return url from first <video> tag inserted in post
if ( ! function_exists( 'trx_addons_get_post_video' ) ) {
	function trx_addons_get_post_video( $post_text = '', $src = true, $from_meta = true ) {
		global $post;
		$img = $from_meta ? trx_addons_get_post_video_list_first( ! $src ) : '';
		if ( is_array( $img ) ) {
			if ( ! empty( $img['video_url'] ) ) {
				$img = $img['video_url'];
			} else if ( ! empty( $img['video_embed'] ) ) {
				$img = trx_addons_get_post_iframe( $img['video_embed'], true );
			} else {
				$img = '';
			}
		}
		if ( empty( $img ) ) {
			if ( empty( $post_text ) ) {
				$post_text = $post->post_content;
			}
			if ( $src ) {
				if ( preg_match_all( '/<video.+src=[\'"]([^\'"]+)[\'"][^>]*>/is', $post_text, $matches ) ) {
					$img = $matches[1][0];
				} else if ( preg_match_all( '/<!\\-\\- wp:trx-addons\\/video.+"link":"([^"]*)"/i', $post_text, $matches ) ) {
					$img = $matches[1][0];
				} else if ( preg_match_all( '/<!\\-\\- wp:core-embed\\/(youtube|vimeo|dailymotion|facebook).+"url":"([^"]*)"/is', $post_text, $matches ) ) {
					$img = $matches[2][0];
				} else if ( preg_match_all( '/<!-- wp:embed {"url":"([^"]*(youtube|vimeo|dailymotion|facebook)[^"]*)"/is', $post_text, $matches ) ) {
					$img = $matches[1][0];
				} else if ( preg_match_all( '/<iframe.+src=[\'"]([^\'"]+(youtube|vimeo|dailymotion|facebook)[^\'"]+)[\'"][^>]*>/is', $post_text, $matches ) ) {
					$img = $matches[1][0];
				}
			} else {
				$img = trx_addons_get_tag( $post_text, '<video', '</video>' );
				if ( empty( $img ) ) {
					$sc = trx_addons_get_tag( $post_text, '[video', '[/video]' );
					if ( empty( $sc ) ) {
						$sc = trx_addons_get_tag( $post_text, '[trx_widget_video', '' );
					}
					if ( ! empty( $sc ) ) {
						$img = do_shortcode( $sc );
					}
					if ( empty( $img ) && preg_match_all( '/<!\\-\\- wp\\:trx-addons\\/video.+"link"\\:"([^"]*)"/is', $post_text, $matches ) ) {
						$img = trx_addons_get_embed_video( $matches[1][0] );
					}
					if ( empty( $img ) && preg_match_all( '/<!\\-\\- wp:core-embed\\/(youtube|vimeo|dailymotion|facebook).+"url":"([^"]*)"/is', $post_text, $matches ) ) {
						$img = trx_addons_get_embed_video( $matches[2][0] );	// , true
					}
					if ( empty( $img ) && preg_match_all( '/<!-- wp:embed {"url":"([^"]*(youtube|vimeo|dailymotion|facebook)[^"]*)"/is', $post_text, $matches ) ) {
						$img = trx_addons_get_embed_video( $matches[1][0] );	// , true
					}
					if ( empty( $img ) && preg_match_all( '/(<iframe.+src=[\'"][^\'"]+(youtube|vimeo|dailymotion|facebook)[^\'"]+[\'"][^>]*>[^<]*<\\/iframe>)/is', $post_text, $matches ) ) {
						$img = $matches[1][0];
					}
				}
			}
		}
		return apply_filters( 'trx_addons_filter_get_post_video', $img );
	}
}


// Return list videos from post attributes
if ( ! function_exists( 'trx_addons_get_post_video_list' ) ) {
	function trx_addons_get_post_video_list( $args=array() ) {
		$list = array();
		$meta = (array)get_post_meta( get_the_ID(), 'trx_addons_options', true );
		if ( ! empty( $meta['video_source'] ) ) {
			if ( empty( $args['video_source'] ) ) {
				$args['video_source'] = '';
			}
			if ( $meta['video_source'] == 'manual' || $args['video_source'] == 'manual' ) {
				if ( ! empty( $meta['video_list'][0]['video_url'] ) || ! empty( $meta['video_list'][0]['video_embed'] ) ) {
					$list = $meta['video_list'];
				}
			} else {
				$args = array_merge(
					array(
						//  Attention! Parameter 'suppress_filters' is damage WPML-queries!
						'ignore_sticky_posts' => true,
						'posts_per_page'      => ! empty( $meta['video_total'] ) ? max( 1, (int) $meta['video_total'] ) : 10,
						'orderby'             => 'date',
						'order'               => 'DESC',
						'post_type'           => 'post',
						'post_status'         => 'publish',
						'post__not_in'        => array(),
						'category__in'        => array(),
					), $args
				);

				if ( ! in_array( get_the_ID(), $args['post__not_in'] ) ) {
					$args['post__not_in'][] = get_the_ID();
				}
				
				$args = trx_addons_query_add_filters( $args, 'video' );

				if ( $meta['video_source'] == 'related_posts' ) {
					if ( empty( $args['category__in'] ) || is_array( $args['category__in'] ) && count( $args['category__in'] ) == 0 ) {
						$post_categories_ids = array();
						$terms               = get_the_terms( get_the_ID(), 'category' );
						if ( is_array( $terms ) && ! empty( $terms ) ) {
							foreach ( $terms as $cat ) {
								$post_categories_ids[] = $cat->term_id;
							}
						}
						$args['category__in'] = $post_categories_ids;
					}
				}

				$args = apply_filters( 'trx_addons_filter_get_post_video_list_args', $args );

				$args = apply_filters( 'trx_addons_filter_query_args', $args, 'get_post_video_list' );

				$query = new WP_Query( $args );

				if ( $query->found_posts > 0 ) {

					// Save current post object to restore it after the posts loop
					// Do it manually, because this function trx_addons_get_post_video_list()
					// can be called recursive and wp_reset_postdata() is not work correct
					// (it restore to the main post, but not to the current post)
					$old_post = $GLOBALS['post'];					

					while ( $query->have_posts() ) {
						$query->the_post();
						$video = trx_addons_get_post_video_list_first( false, array(
									'post__not_in' => $args['post__not_in']
									) );
						// Old posts - without video in the meta
						if ( empty( $video ) ) {
							$src = trx_addons_get_post_video( '', true, false );
							if ( ! empty( $src ) ) {
								$video = apply_filters( 'trx_addons_filter_get_post_video_meta', array(
									'title' => get_the_title(),
									'subtitle' => trx_addons_sc_show_post_meta(
													'post_video_list',
													apply_filters( 'trx_addons_filter_post_meta_args', array(
														'components' => 'categories',
														'theme_specific' => false,
														'echo' => false
														), 'post_video_list_categories', 1 )
													),
									'meta' => trx_addons_sc_show_post_meta(
													'post_video_list',
													apply_filters( 'trx_addons_filter_post_meta_args', array(
														'components' => 'author,date,views,likes',
														'theme_specific' => false,
														'echo' => false
														), 'post_video_list_meta', 1 )
													),
									'image' => has_post_thumbnail() ? get_post_thumbnail_id( get_the_ID() ) : '',
									'link' => get_permalink(),
									'video_url' => strpos( $src, '<' ) === false ? $src : '',
									'video_embed' => strpos( $src, '<' ) !== false ? $src : '',
								), $args );
							}
						}
						if ( ! empty( $video ) ) {
							if ( empty( $video['link'] ) ) {
								$video['link'] = get_permalink( get_the_ID() );
							}
							$list[] = $video;
						}
					}

					// Restore current post manually instead wp_reset_postdata() (see comments before the posts loop)
					//wp_reset_postdata();
					$GLOBALS['post'] = $old_post;
					setup_postdata($old_post);
				}
			}
		}
		return apply_filters( 'trx_addons_filter_get_post_video_list', $list, $args );
	}
}


// Return first video from post attributes
if ( ! function_exists( 'trx_addons_get_post_video_list_first' ) ) {
	function trx_addons_get_post_video_list_first( $src = false, $args = array() ) {
		$list  = trx_addons_get_post_video_list( array_merge( $args, array(
					'posts_per_page' => 1
					) ) );
		$video = '';
		if ( ! $src ) {
			$video = ! empty( $list[0]['video_url'] ) || ! empty( $list[0]['video_embed'] ) ? $list[0] : array();
		} else {
			if ( ! empty( $list[0]['video_url'] ) ) {
				$video = trx_addons_get_embed_video( $list[0]['video_url'] );
			} else if ( ! empty( $list[0]['video_embed'] ) ) {
				$video = $list[0]['video_embed'];
			}
		}
		return $video;
	}
}


if ( ! function_exists( 'trx_addons_get_embed_video_parameters' ) ) {
	/**
	 * Return an array with a query parameters for an embed video.
	 *
	 * @return array  An array with parameters for an embed video.
	 */
	function trx_addons_get_embed_video_parameters() {
		return array(
				'feature'        => 'oembed',
				'wmode'          => 'transparent',
				'origin'         => esc_url( home_url() ),
				'widgetid'       => 1,
				'enablejsapi'    => 1,
				'disablekb'      => 1,
				'modestbranding' => 1,
				'iv_load_policy' => 3,
				'rel'            => 0,
				'showinfo'       => 0,
		);
	}
}


// Add 'autoplay' feature in the video
if (!function_exists('trx_addons_make_video_autoplay')) {
	function trx_addons_make_video_autoplay($video, $muted=false) {
		if ( strpos($video, '<video') !== false ) {
			if ( strpos( $video, 'autoplay' ) === false ) {
				$video = str_replace(
									'<video',
									'<video autoplay="autoplay" onloadeddata="' . ( $muted ? 'this.muted=true;' : '' ) . 'this.play();"'
										. ( $muted
												? ' muted="muted" loop="loop" playsinline="playsinline"'
												: ' controls="controls"'
											),
									$video
									);
				if ( $muted ) {
					$video = str_replace( 'controls="controls"', '', $video );
				}
			}
		} else {
			$pos = strpos( $video, '<iframe' );
			if ( false !== $pos ) {
				if ( preg_match( '/(<iframe[^>]*src=[\'"])([^\'"]+)([\'"][^>]*>)(.*)/is', $video, $matches ) ) {
					$video = ( $pos > 0 ? substr( $video, 0, $pos ) : '' ) . $matches[1];
					if ( ! empty( $matches[2] ) ) {
						// Add parameters to the 'src'
						$matches[2] = trx_addons_add_to_url(
							$matches[2],
							array_merge(
								array(
									'autoplay' => $muted && ( strpos( $matches[2], 'youtube.com' ) !== false || strpos( $matches[2], 'youtu.be' ) !== false )
													? 0
													: 1
								),
								$muted
									? array_merge(
										trx_addons_get_embed_video_parameters(),
										array(
											'muted' => 1,
											'controls' => 0,
											'background' => 1,
											'autohide' => 1,
											'playsinline' => 1,
											'loop' => 1,
											)
										)
									: array()
							)
						);
					}
					$video .= $matches[2] . $matches[3] . $matches[4];
					if ( strpos( $video, 'autoplay"' ) === false && strpos( $video, 'autoplay;' ) === false ) {
						$video = strpos( $video, ' allow="' ) !== false
									? str_replace( ' allow="', ' allow="autoplay;', $video )
									: str_replace( '<iframe ', '<iframe allow="autoplay" ', $video );
					}
				}
			}
		}
		return $video;
	}
}


// Return layout with embeded video
if ( ! function_exists( 'trx_addons_get_embed_video' ) ) {
	function trx_addons_get_embed_video( $video, $use_wp_embed = false ) {
		global $wp_embed;
		if ( $use_wp_embed && is_object( $wp_embed ) ) {
			$embed_video = do_shortcode( $wp_embed->run_shortcode( '[embed]' . trim( $video ) . '[/embed]' ) );
			// $embed_video = trx_addons_make_video_autoplay( $embed_video );
		} else if ( trx_addons_is_from_uploads( $video ) ) {
			$embed_video  = '<video src="' . esc_url( $video ) . '"></video>';
		} else {
			// Video link from Youtube
			$video = str_replace(
						array(
							'/watch?v=',						// Youtube watch link
							'/youtu.be/',						// Youtube share link
						),
						array(
							'/embed/',
							'/www.youtube.com/embed/',
						),
						$video
					);			
			// Video link from Vimeo
			if ( strpos( $video, 'player.vimeo.com' ) === false ) {
				$video = str_replace(
							array(
								'vimeo.com/',
							),
							array(
								'player.vimeo.com/video/',
							),
							$video
						);
			}
			// Video link from Dailymotion
			$video = str_replace(
						array(
							'dai.ly/',							// DailyMotion.com video link
							'dailymotion.com/video/',			// DailyMotion.com page link 
						),
						array(
							'dailymotion.com/embed/video/',
							'dailymotion.com/embed/video/',
						),
						$video
					);
			// Video link from Facebook
			$fb = strpos($video, 'facebook.com/');
			if ( $fb !== false ) {
				$video = substr( $video, 0, $fb ) . 'facebook.com/plugins/video.php?href=' . urlencode($video);
			}
			// Add parameters to the 'src'
			$video = trx_addons_add_to_url( $video, trx_addons_get_embed_video_parameters() );
			$dim = apply_filters( 'trx_addons_filter_video_dimensions', array(
									'width' => 1170,
									'height' => 658
									) );
			$embed_video  = '<iframe'
								. ' src="' . esc_url( $video ) . '"'
								//. ' allow="autoplay"'
								. ' width="' . esc_attr( $dim['width'] ) . '"'
								. ' height="' . esc_attr( $dim['height'] ) . '"'
								//. ' frameborder="0"
								. '>'
							. '</iframe>';
		}
		return $embed_video;
	}
}


// Return url from first <iframe> tag inserted in post
if (!function_exists('trx_addons_get_post_iframe')) {
	function trx_addons_get_post_iframe($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) {
			$post_text = do_shortcode($post->post_content);
		}
		if ($src) {
			if (preg_match_all('/<iframe.+src=[\'"]([^\'"]+)[\'"][^>]*>/is', $post_text, $matches)) {
				$img = $matches[1][0];
			}
		} else {
			$img = trx_addons_get_tag($post_text, '<iframe', '</iframe>');
		}
		return apply_filters('trx_addons_filter_get_post_iframe', $img);
	}
}


// Clear iframe html from deprecated params
if (!function_exists('trx_addons_clear_iframe_layout')) {
	add_filter('trx_addons_filter_get_post_iframe', 'trx_addons_clear_iframe_layout', 10, 1);
	add_filter('trx_addons_filter_embed_layout', 'trx_addons_clear_iframe_layout', 10, 2);
	function trx_addons_clear_iframe_layout($html, $args=array()) {
		return str_ireplace(array(
								'frameborder="0"',
								'webkitallowfullscreen="webkitallowfullscreen"',
								'webkitallowfullscreen="true"',
								'webkitallowfullscreen="1"',
								'webkitallowfullscreen',
								'mozallowfullscreen="mozallowfullscreen"',
								'mozallowfullscreen="true"',
								'mozallowfullscreen="1"',
								'mozallowfullscreen'
								),
							'',
							$html);
	}
}


// Return tag SVG from specified file
if (!function_exists('trx_addons_get_svg_from_file')) {
	function trx_addons_get_svg_from_file( $svg ) {
		if ( trx_addons_is_url( $svg ) && ! trx_addons_is_external_url( $svg ) ) {
			$svg_path = trx_addons_url_to_local_path( $svg );
			if ( ! empty( $svg_path ) ) $svg = $svg_path;
		}
		$content = trx_addons_fgc( $svg );
		preg_match("#<\s*?svg\b[^>]*>(.*?)</svg\b[^>]*>#is", $content, $matches);
		return !empty($matches[0]) ? str_replace(array("\r", "\n"), array('', ' '), $matches[0]) : '';
	}
}


// Return icon name without prefixes and escape it
if (!function_exists('trx_addons_clear_icon_name')) {
	function trx_addons_clear_icon_name($icon) {
		return trx_addons_esc(apply_filters('trx_addons_filter_clear_icon_name', str_replace('trx_addons_icon-', '', $icon)));
	}
}


//  Add attachment's "alt" as attribute "title" to the links in WP gallery output
if (!function_exists( 'trx_addons_add_title_to_gallery_links' ) ) {
	add_filter( 'wp_get_attachment_link', 'trx_addons_add_title_to_gallery_links', 10, 2 );
	function trx_addons_add_title_to_gallery_links($link, $id) {
		$caption = trx_addons_get_attachment_caption( $id );
		if ( ! empty( $caption ) ) {
			$link = str_replace( '<a','<a title="' . esc_attr( $caption ) . '"', $link );
		}
		return $link;
	}
}



/* Lazy load images
----------------------------------------------------------------------------------------------------- */

// Disable lazy load for images
if ( ! function_exists( 'trx_addons_lazy_load_off' ) ) {
	function trx_addons_lazy_load_off() {
		if ( ! trx_addons_lazy_load_is_off() ) {
			add_filter( 'wp_lazy_loading_enabled', 'trx_addons_lazy_load_disabled' );
		}
	}
}

if ( ! function_exists( 'trx_addons_lazy_load_disabled' ) ) {
	function trx_addons_lazy_load_disabled() {
		return false;
	}
}

// Enable lazy load for images
if ( ! function_exists( 'trx_addons_lazy_load_on' ) ) {
	function trx_addons_lazy_load_on() {
		if ( trx_addons_lazy_load_is_off() ) {
			remove_filter( 'wp_lazy_loading_enabled', 'trx_addons_lazy_load_disabled' );
		}
	}
}

// Check state of lazy load
if ( ! function_exists( 'trx_addons_lazy_load_is_off' ) ) {
	function trx_addons_lazy_load_is_off() {
		return has_filter( 'wp_lazy_loading_enabled', 'trx_addons_lazy_load_disabled' );
	}
}
