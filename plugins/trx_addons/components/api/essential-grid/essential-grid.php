<?php
/**
 * Plugin support: Essential Grid
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_essential_grid' ) ) {
	function trx_addons_exists_essential_grid() {
		return defined('EG_PLUGIN_PATH') || defined( 'ESG_PLUGIN_PATH' );
	}
}
	
// Add plugin-specific slugs to the list of the scripts, that don't move to the footer and don't add 'defer' param
if ( !function_exists( 'trx_addons_essential_grid_not_defer_scripts' ) ) {
	add_filter("trx_addons_filter_skip_move_scripts_down", 'trx_addons_essential_grid_not_defer_scripts');
	add_filter("trx_addons_filter_skip_async_scripts_load", 'trx_addons_essential_grid_not_defer_scripts');
	function trx_addons_essential_grid_not_defer_scripts($list) {
		if ( trx_addons_exists_essential_grid() ) {
			$list[] = 'essential-grid';
		}
		return $list;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_essential_grid_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_essential_grid_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_essential_grid_load_scripts_front', 10, 1 );
	function trx_addons_essential_grid_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_essential_grid() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'essential-grid',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'widget_ess_grid' ),
										array( 'type' => 'sc',  'sc' => 'ess_grid' ),
										array( 'type' => 'sc',  'sc' => 'ess_grid_ajax_target' ),
										array( 'type' => 'sc',  'sc' => 'ess_grid_nav' ),
										array( 'type' => 'sc',  'sc' => 'ess_grid_search' ),
										array( 'type' => 'gb',  'sc' => 'wp:themepunch/essgrid' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-ess-grid' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[ess_grid' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[widget_ess_grid' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'essential_grid' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'essential_grid', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_essential_grid_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_essential_grid_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_essential_grid_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_essential_grid_check_in_html_output', 10, 1 );
	function trx_addons_essential_grid_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_essential_grid()
			&& ! trx_addons_need_frontend_scripts( 'essential_grid' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'id=[\'"][^\'"]*ess\\-grid\\-',
							'class=[\'"][^\'"]*(ess\\-grid\\-|widget_ess_grid)',
							'class=[\'"][^\'"]*type\\-' . apply_filters( 'essgrid_PunchPost_custom_post_type', 'essential_grid' ),
							'class=[\'"][^\'"]*' . apply_filters( 'essgrid_PunchPost_category', 'essential_grid_category' ) . '\\-',
							),
							'essential-grid'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_essential_grid_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_essential_grid_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_essential_grid_filter_head_output', 10, 1 );
	function trx_addons_essential_grid_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_essential_grid()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'essential_grid' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'essential_grid' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/essential-grid/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_essential_grid_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_essential_grid_filter_body_output', 10, 1 );
	function trx_addons_essential_grid_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_essential_grid()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'essential_grid' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'essential_grid' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/essential-grid/[^>]*>#', '', $content );
			// RevSlider may use some scripts from Essential Grid (tools.js)
			if ( ! trx_addons_need_frontend_scripts( 'revslider' ) ) {
				$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/essential-grid/[^>]*>[\\s\\S]*</script>#U', '', $content );
			}
		}
		return $content;
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'essential-grid/essential-grid-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_essential_grid() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'essential-grid/essential-grid-demo-ocdi.php';
}
