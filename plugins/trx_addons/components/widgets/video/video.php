<?php
/**
 * Widget: Video player for Youtube, Vimeo, etc. embeded video
 *
 * @package ThemeREX Addons
 * @since v1.1
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Load widget
if (!function_exists('trx_addons_widget_video_load')) {
	add_action( 'widgets_init', 'trx_addons_widget_video_load' );
	function trx_addons_widget_video_load() {
		register_widget( 'trx_addons_widget_video' );
	}
}


// Widget 'Video' Class
//-------------------------------------------------------
class trx_addons_widget_video extends TRX_Addons_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_video', 'description' => esc_html__('Show video from Youtube, Vimeo, etc.', 'trx_addons') );
		parent::__construct( 'trx_addons_widget_video', esc_html__('ThemeREX Video player', 'trx_addons'), $widget_ops );
	}

	// Show widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', isset($instance['title']) ? $instance['title'] : '', $instance, $this->id_base );
		$embed = isset($instance['embed']) ? $instance['embed'] : '';
		$link = isset($instance['link']) ? $instance['link'] : '';
		$autoplay = isset($instance['autoplay']) ? $instance['autoplay'] : 0;
		$cover = isset($instance['cover']) && ! $autoplay ? $instance['cover'] : '';
		$popup = isset($instance['popup']) && ! $autoplay ? $instance['popup'] : 0;
		$media_from_post = isset( $instance['media_from_post'] ) ? $instance['media_from_post'] : 0;
		if ( empty( $embed ) && empty( $link ) && (int)$media_from_post == 0 ) return;

		// Get video from post if parameter is empty
		if ( empty( $link ) && empty( $embed ) && (int) $media_from_post > 0 ) {
			$video_from_post = trx_addons_get_post_video_list_first();
			if ( ! empty( $video_from_post ) ) {
				if ( ! empty( $video_from_post['video_url'] ) ) {
					$link = $video_from_post['video_url'];
				} else if ( ! empty( $video_from_post['video_embed'] ) ) {
					$embed = $video_from_post['video_embed'];
				}
				if ( empty( $cover ) && ! empty( $video_from_post['image'] ) ) {
					$cover = $video_from_post['image'];
				}
			} else {
				$video_from_post = trx_addons_get_post_video();
				if ( ! empty( $video_from_post ) ) {
					$link = $video_from_post;
				}
			}
		}

		// Load widget-specific scripts and styles
		trx_addons_widget_video_load_scripts_front( true );

		// Load template
		trx_addons_get_template_part(TRX_ADDONS_PLUGIN_WIDGETS . 'video/tpl.default.php',
										'trx_addons_args_widget_video',
										apply_filters('trx_addons_filter_widget_args',
											array_merge($args, compact('title', 'embed', 'link', 'cover', 'popup', 'autoplay')),
											$instance, 'trx_addons_widget_video')
									);
	}

	// Update the widget settings.
	function update( $new_instance, $instance ) {
		$instance = array_merge($instance, $new_instance);
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['cover'] = strip_tags( $new_instance['cover'] );
		$instance['link']  = trim( $new_instance['link'] );
		$instance['embed'] = trim( $new_instance['embed'] );
		$instance['popup'] = intval( $new_instance['popup'] );
		$instance['autoplay'] = intval( $new_instance['autoplay'] );
		return apply_filters('trx_addons_filter_widget_args_update', $instance, $new_instance, 'trx_addons_widget_video');
	}

	// Displays the widget settings controls on the widget panel.
	function form( $instance ) {
		// Set up some default widget settings
		$instance = wp_parse_args( (array) $instance, apply_filters('trx_addons_filter_widget_args_default', array(
			'title' => '',
			'cover' => '',
			'link' => '',
			'embed' => '',
			'popup' => 0,
			'autoplay' => 0
			), 'trx_addons_widget_video')
		);
		
		do_action('trx_addons_action_before_widget_fields', $instance, 'trx_addons_widget_video', $this);
		
		$this->show_field(array('name' => 'title',
								'title' => __('Title:', 'trx_addons'),
								'value' => $instance['title'],
								'type' => 'text'));
		
		do_action('trx_addons_action_after_widget_title', $instance, 'trx_addons_widget_video', $this);

		$this->show_field(array('name' => 'autoplay',
								'title' => '',
								'label' => __('Autoplay on load', 'trx_addons'),
								'value' => (int) $instance['autoplay'],
								'type' => 'checkbox'));

		$this->show_field(array('name' => 'cover',
								'title' => __('Cover image URL:<br>(leave empty if you not need the cover)', 'trx_addons'),
								'value' => $instance['cover'],
								'dependency' => array(
									'autoplay' => array( 0 ),
								),
								'type' => 'image'));
		
		$this->show_field(array('name' => 'link',
								'title' => __('Link to video:', 'trx_addons'),
								'value' => $instance['link'],
								'type' => 'text'));

		$this->show_field(array('name' => 'embed',
								'title' => __('or paste HTML code to embed video:', 'trx_addons'),
								'value' => $instance['embed'],
								'type' => 'textarea'));

		$this->show_field(array('name' => 'popup',
								'title' => '',
								'label' => __('Video in the popup', 'trx_addons'),
								'value' => (int) $instance['popup'],
								'dependency' => array(
									'cover' => array( 'not_empty' ),
								),
								'type' => 'checkbox'));
		
		do_action('trx_addons_action_after_widget_fields', $instance, 'trx_addons_widget_video', $this);
	}
}


// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_widget_video_load_scripts_front' ) ) {
	add_action( "wp_enqueue_scripts", 'trx_addons_widget_video_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY );
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_widget_video_load_scripts_front', 10, 1 );
	function trx_addons_widget_video_load_scripts_front( $force = false ) {
		static $loaded = false;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
											'sc' => 'widget_video',
											'entries' => array(
												array( 'type' => 'sc',  'sc' => 'trx_widget_video' ),
												array( 'type' => 'gb',  'sc' => 'wp:trx-addons/video' ),
												array( 'type' => 'elm', 'sc' => '"widgetType":"trx_widget_video"' ),
												array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_widget_video' ),
											)
								) )
							);
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-widget_video', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_WIDGETS . 'video/video.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'widget_video' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'widget_video', 2 );
		}
	}
}
	
// Merge widget specific styles into single stylesheet
if ( !function_exists( 'trx_addons_widget_video_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_widget_video_merge_styles');
	function trx_addons_widget_video_merge_styles($list) {
		$list[ TRX_ADDONS_PLUGIN_WIDGETS . 'video/video.css' ] = false;
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu
if ( !function_exists( 'trx_addons_widget_video_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_widget_video_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_widget_video_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_widget_video_check_in_html_output', 10, 1 );
	function trx_addons_widget_video_check_in_html_output( $content = '' ) {
		if ( ! trx_addons_need_frontend_scripts( 'widget_video' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*widget_video'
							),
							'widget_video'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_widget_video_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// Add shortcodes
//----------------------------------------------------------------------------
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_WIDGETS . 'video/video-sc.php';

// Add shortcodes to Elementor
if ( trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_WIDGETS . 'video/video-sc-elementor.php';
}

// Add shortcodes to Gutenberg
if ( trx_addons_exists_gutenberg() && function_exists( 'trx_addons_gutenberg_get_param_id' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_WIDGETS . 'video/video-sc-gutenberg.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_WIDGETS . 'video/video-sc-vc.php';
}
