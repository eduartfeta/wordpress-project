<?php
/**
 * Plugin support: Content Timeline
 *
 * @package ThemeREX Addons
 * @since v1.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin is installed and activated
if ( !function_exists( 'trx_addons_exists_content_timeline' ) ) {
	function trx_addons_exists_content_timeline() {
		return class_exists( 'ContentTimelineAdmin' );
	}
}

// Return Content Timelines list, prepended inherit (if need)
if ( !function_exists( 'trx_addons_get_list_content_timelines' ) ) {
	function trx_addons_get_list_content_timelines($prepend_inherit=false) {
		static $list = false;
		if ($list === false) {
			$list = array();
			if (trx_addons_exists_content_timeline()) {
				global $wpdb;
				$rows = $wpdb->get_results( "SELECT id, name FROM " . esc_sql($wpdb->prefix . 'ctimelines') );
				if (is_array($rows) && count($rows) > 0) {
					foreach ($rows as $row) {
						$list[$row->id] = $row->name;
					}
				}
			}
		}
		return $prepend_inherit ? array_merge(array('inherit' => esc_html__("Inherit", 'trx_addons')), $list) : $list;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_content_timeline_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_content_timeline_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_content_timeline_load_scripts_front', 10, 1 );
	function trx_addons_content_timeline_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_content_timeline() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
								'sc' => 'content_timeline',
								'entries' => array(
									array( 'type' => 'sc',  'sc' => 'content_timeline' ),
									//array( 'type' => 'gb',  'sc' => 'wp:trx-addons/events' ),	// This sc is not exists for GB
									array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_content_timeline"' ),
									array( 'type' => 'elm', 'sc' => '"shortcode":"[content_timeline' ),
								)
							) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'content_timeline' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'content_timeline', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_content_timeline_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_content_timeline_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_content_timeline_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_content_timeline_check_in_html_output', 10, 1 );
	function trx_addons_content_timeline_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_content_timeline()
			&& ! trx_addons_need_frontend_scripts( 'content_timeline' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*timeline ',
							'<!-- BEGIN TIMELINE -->'
							),
							'content_timeline'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_content_timeline_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_content_timeline() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'content_timeline/content_timeline-sc-elementor.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_content_timeline() && trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'content_timeline/content_timeline-sc-vc.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'content_timeline/content_timeline-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_content_timeline() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'content_timeline/content_timeline-demo-ocdi.php';
}
