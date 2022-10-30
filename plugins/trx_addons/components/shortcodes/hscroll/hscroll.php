<?php
/**
 * Shortcode: HScroll
 *
 * @package ThemeREX Addons
 * @since v2.5.2
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Load required styles and scripts for the frontend
if ( ! function_exists( 'trx_addons_sc_hscroll_load_scripts_front2' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_sc_hscroll_load_scripts_front2', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY - 1 );
	function trx_addons_sc_hscroll_load_scripts_front2() {
		if ( trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) ) ) {
//			wp_enqueue_script( 'jquery-ui-touch-punch', trx_addons_get_file_url( 'js/touch-punch/jquery.ui.touch-punch.min.js' ), array( 'jquery', 'jquery-ui-draggable' ), null, true );
		}
	}
}


// Load required styles and scripts for the frontend
if ( ! function_exists( 'trx_addons_sc_hscroll_load_scripts_front' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_sc_hscroll_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY );
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_sc_hscroll_load_scripts_front', 10, 1 );
	function trx_addons_sc_hscroll_load_scripts_front( $force = false ) {
		static $loaded = false, $loaded2 = false;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
											'sc' => 'sc_hscroll',
											'entries' => array(
												array( 'type' => 'sc',  'sc' => 'trx_sc_hscroll' ),
												array( 'type' => 'gb',  'sc' => 'wp:trx-addons/hscroll' ),
												array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_hscroll"' ),
												array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_sc_hscroll' ),
											)
								) )
							);
		if ( ! $loaded2 && ( ! $optimize || $need || $preview_elm ) ) {
			$loaded2 = true;
//			wp_enqueue_script( 'jquery-ui-touch-punch', trx_addons_get_file_url( 'js/touch-punch/jquery.ui.touch-punch.min.js' ), array( 'jquery', 'jquery-ui-draggable' ), null, true );
		}
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style(  'trx_addons-sc_hscroll', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.css' ), array(), null );
			wp_enqueue_script( 'trx_addons-sc_hscroll', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.js' ), array( 'jquery' ), null, true );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'sc_hscroll' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'sc_hscroll', 2 );
		}
	}
}

// Enqueue responsive styles for frontend
if ( ! function_exists( 'trx_addons_sc_hscroll_load_scripts_front_responsive' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_sc_hscroll_load_scripts_front_responsive', TRX_ADDONS_ENQUEUE_RESPONSIVE_PRIORITY );
	add_action( 'trx_addons_action_load_scripts_front_sc_hscroll', 'trx_addons_sc_hscroll_load_scripts_front_responsive', 10, 1 );
	function trx_addons_sc_hscroll_load_scripts_front_responsive( $force = false ) {
		static $loaded = false;
		if ( ! $loaded && (
			current_action() == 'wp_enqueue_scripts' && trx_addons_need_frontend_scripts( 'sc_hscroll' )
			||
			current_action() != 'wp_enqueue_scripts' && $force === true
			)
		) {
			$loaded = true;
			wp_enqueue_style(  'trx_addons-sc_hscroll-responsive', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.responsive.css' ), array(), null, trx_addons_media_for_load_css_responsive( 'sc-hscroll', 'lg' ) );
		}
	}
}
	
// Merge shortcode's specific styles to the single stylesheet
if ( ! function_exists( 'trx_addons_sc_hscroll_merge_styles' ) ) {
	add_filter( 'trx_addons_filter_merge_styles', 'trx_addons_sc_hscroll_merge_styles' );
	function trx_addons_sc_hscroll_merge_styles( $list ) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.css' ] = false;
		return $list;
	}
}

// Merge shortcode's specific styles to the single stylesheet (responsive)
if ( ! function_exists( 'trx_addons_sc_hscroll_merge_styles_responsive' ) ) {
	add_filter( 'trx_addons_filter_merge_styles_responsive', 'trx_addons_sc_hscroll_merge_styles_responsive' );
	function trx_addons_sc_hscroll_merge_styles_responsive( $list ) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.responsive.css' ] = false;
		return $list;
	}
}

// Merge shortcode's specific scripts into single file
if ( ! function_exists( 'trx_addons_sc_hscroll_merge_scripts' ) ) {
	add_action( 'trx_addons_filter_merge_scripts', 'trx_addons_sc_hscroll_merge_scripts' );
	function trx_addons_sc_hscroll_merge_scripts( $list ) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll.js' ] = false;
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu
if ( !function_exists( 'trx_addons_sc_hscroll_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_sc_hscroll_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_sc_hscroll_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_sc_hscroll_check_in_html_output', 10, 1 );
	function trx_addons_sc_hscroll_check_in_html_output( $content = '' ) {
		if ( ! trx_addons_need_frontend_scripts( 'sc_hscroll' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*sc_hscroll'
							),
							'sc_hscroll'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_sc_hscroll_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// trx_sc_hscroll
//-------------------------------------------------------------
/*
[trx_sc_hscroll id="unique_id" slides="encoded_json_data"]
*/
if ( ! function_exists( 'trx_addons_sc_hscroll' ) ) {
	function trx_addons_sc_hscroll( $atts, $content = null ) {	
		$atts = trx_addons_sc_prepare_atts( 'trx_sc_hscroll', $atts, trx_addons_sc_common_atts( 'id', array(
			// Individual params
			"type" => "default",
			"slides" => "",
			"bullets" => "",
			"bullets_position" => "left",
			"numbers" => "",
			"numbers_position" => "center",
			"bg_color" => "",
			"bg_image" => "",
			"reverse" => "",
			"progress" => "",
			"progress_position" => "bottom",
			) )
		);
		if ( function_exists( 'vc_param_group_parse_atts' ) && ! is_array( $atts['slides'] ) ) {
			$atts['slides'] = (array) vc_param_group_parse_atts( $atts['slides'] );
		}
		// Load shortcode-specific scripts and styles
		trx_addons_sc_hscroll_load_scripts_front( true );
		// Load template
		$output = '';
		ob_start();
		trx_addons_get_template_part( array(
										TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/tpl.' . trx_addons_esc( $atts['type'] ) . '.php',
										TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/tpl.default.php'
										),
										'trx_addons_args_sc_hscroll',
										$atts
									);
		$output = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'trx_addons_sc_output', $output, 'trx_sc_hscroll', $atts, $content );
	}
}


// Add shortcode [trx_sc_hscroll]
if ( ! function_exists( 'trx_addons_sc_hscroll_add_shortcode' ) ) {
	function trx_addons_sc_hscroll_add_shortcode() {
		add_shortcode( 'trx_sc_hscroll', 'trx_addons_sc_hscroll' );
	}
	add_action( 'init', 'trx_addons_sc_hscroll_add_shortcode', 20 );
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_elementor() && function_exists( 'trx_addons_elm_init' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'hscroll/hscroll-sc-elementor.php';
}
