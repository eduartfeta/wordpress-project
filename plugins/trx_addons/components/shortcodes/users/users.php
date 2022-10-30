<?php
/**
 * Widget: Users list (Shortcodes)
 *
 * @package ThemeREX Addons
 * @since v1.84.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_sc_users_load_scripts_front' ) ) {
	add_action( "wp_enqueue_scripts", 'trx_addons_sc_users_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY );
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_sc_users_load_scripts_front', 10, 1 );
	function trx_addons_sc_users_load_scripts_front( $force = false ) {
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
											'sc' => 'sc_users',
											'entries' => array(
												array( 'type' => 'sc',  'sc' => 'trx_sc_users' ),
												array( 'type' => 'gb',  'sc' => 'wp:trx-addons/users' ),
												array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_users"' ),
												array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_sc_users' ),
											)
								) )
							);
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-sc_users', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'sc_users' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'sc_users', 2 );
		}
	}
}

// Enqueue responsive styles for frontend
if ( ! function_exists( 'trx_addons_sc_users_load_scripts_front_responsive' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_sc_users_load_scripts_front_responsive', TRX_ADDONS_ENQUEUE_RESPONSIVE_PRIORITY );
	add_action( 'trx_addons_action_load_scripts_front_sc_users', 'trx_addons_sc_users_load_scripts_front_responsive', 10, 1 );
	function trx_addons_sc_users_load_scripts_front_responsive( $force = false ) {
		static $loaded = false;
		if ( ! $loaded && (
			current_action() == 'wp_enqueue_scripts' && trx_addons_need_frontend_scripts( 'sc_users' )
			||
			current_action() != 'wp_enqueue_scripts' && $force === true
			)
		) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-sc_users-responsive', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users.responsive.css'), array(), null, trx_addons_media_for_load_css_responsive( 'sc-users', 'xl' ) );
		}
	}
}

// Merge widget specific styles into single stylesheet
if ( !function_exists( 'trx_addons_sc_users_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_sc_users_merge_styles');
	function trx_addons_sc_users_merge_styles($list) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users.css' ] = false;
		return $list;
	}
}

// Merge widget's specific styles to the single stylesheet (responsive)
if ( !function_exists( 'trx_addons_sc_users_merge_styles_responsive' ) ) {
	add_filter("trx_addons_filter_merge_styles_responsive", 'trx_addons_sc_users_merge_styles_responsive');
	function trx_addons_sc_users_merge_styles_responsive($list) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users.responsive.css' ] = false;
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu
if ( !function_exists( 'trx_addons_sc_users_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_sc_users_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_sc_users_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_sc_users_check_in_html_output', 10, 1 );
	function trx_addons_sc_users_check_in_html_output( $content = '' ) {
		if ( ! trx_addons_need_frontend_scripts( 'sc_users' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*sc_users'
							),
							'sc_users'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_sc_users_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}



// trx_sc_users
//-------------------------------------------------------------
/*
[trx_sc_users id="unique_id" title="Widget title" number="3"]
*/
if ( !function_exists( 'trx_addons_sc_users' ) ) {
	function trx_addons_sc_users($atts, $content=null){	
		$atts = trx_addons_sc_prepare_atts('trx_sc_users', $atts, trx_addons_sc_common_atts('id,title,slider', array(
			// Individual params
			"type" => 'default',
			"roles" => array('author'),
			"number" => 4,
			"columns" => 0,
			))
		);

		// Load shortcode-specific scripts and styles
		trx_addons_sc_users_load_scripts_front( true );

		// Load template
		ob_start();
		trx_addons_get_template_part(
			array(
				TRX_ADDONS_PLUGIN_SHORTCODES . 'users/tpl.'.trx_addons_esc($atts['type']).'.php',
				TRX_ADDONS_PLUGIN_SHORTCODES . 'users/tpl.default.php'
				),
			'trx_addons_args_sc_users', 
			$atts
		);
		$output = ob_get_contents();
		ob_end_clean();

		return apply_filters('trx_addons_sc_output', $output, 'trx_sc_users', $atts, $content);
	}
}


// Add shortcode [trx_sc_users]
if (!function_exists('trx_addons_sc_users_add_shortcode')) {
	function trx_addons_sc_users_add_shortcode() {
		add_shortcode("trx_sc_users", "trx_addons_sc_users");
	}
	add_action('init', 'trx_addons_sc_users_add_shortcode', 20);
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users-sc-elementor.php';
}

// Add shortcodes to Gutenberg
if ( trx_addons_exists_gutenberg() && function_exists( 'trx_addons_gutenberg_get_param_id' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users-sc-gutenberg.php';
}

// Create our widget
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'users/users-widget.php';
