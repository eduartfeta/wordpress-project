<?php
/**
 * Plugin support: MP Timetable
 *
 * @package ThemeREX Addons
 * @since v1.6.30
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


if (!defined('TRX_ADDONS_MPTT_PT_EVENT')) define('TRX_ADDONS_MPTT_PT_EVENT', 'mp-event');
if (!defined('TRX_ADDONS_MPTT_PT_COLUMN')) define('TRX_ADDONS_MPTT_PT_COLUMN', 'mp-column');
if (!defined('TRX_ADDONS_MPTT_TAXONOMY_CATEGORY')) define('TRX_ADDONS_MPTT_TAXONOMY_CATEGORY', 'mp-event_category');


// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_mptt' ) ) {
	function trx_addons_exists_mptt() {
		return class_exists('Mp_Time_Table');
	}
}

// Return true, if current page is any mp_timetable page
if ( !function_exists( 'trx_addons_is_mptt_page' ) ) {
	function trx_addons_is_mptt_page() {
		$rez = false;
		if (trx_addons_exists_mptt())
			return !is_search()
						&& (
							(trx_addons_is_single() && get_post_type()==TRX_ADDONS_MPTT_PT_EVENT)
							|| is_post_type_archive(TRX_ADDONS_MPTT_PT_EVENT)
							|| is_tax(TRX_ADDONS_MPTT_TAXONOMY_CATEGORY)
							);
		return $rez;
	}
}


// Return taxonomy for current post type
if ( !function_exists( 'trx_addons_mptt_post_type_taxonomy' ) ) {
	add_filter( 'trx_addons_filter_post_type_taxonomy',	'trx_addons_mptt_post_type_taxonomy', 10, 2 );
	function trx_addons_mptt_post_type_taxonomy($tax='', $post_type='') {
		if ($post_type == TRX_ADDONS_MPTT_PT_EVENT)
			$tax = TRX_ADDONS_MPTT_TAXONOMY_CATEGORY;
		return $tax;
	}
}



// Load required scripts and styles
//------------------------------------------------------------------------

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_mptt_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_mptt_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_mptt_load_scripts_front', 10, 1 );
	function trx_addons_mptt_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_mptt() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_mptt_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'mptt',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'mp-timetable' ),
										array( 'type' => 'gb',  'sc' => 'wp:mp-timetable/timetable' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_mptt"' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-mp-timetable"' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"timetable"' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[mp-timetable' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-mp-timetable', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'mptt' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'mptt', 2 );
		}
	}
}
	
// Merge specific styles into single stylesheet
if ( !function_exists( 'trx_addons_mptt_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_mptt_merge_styles');
	function trx_addons_mptt_merge_styles($list) {
		if ( trx_addons_exists_mptt() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable.css' ] = false;
		}
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_mptt_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_mptt_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_mptt_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_mptt_check_in_html_output', 10, 1 );
	function trx_addons_mptt_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_mptt()
			&& ! trx_addons_need_frontend_scripts( 'mptt' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*(timeslot|mptt)',
							'class=[\'"][^\'"]*type\\-(mp\\-event|mp\\-column)',
							'class=[\'"][^\'"]*(mp\\-event_category|mp\\-event_tag)\\-',
							),
							'mptt'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_mptt_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_mptt_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_mptt_filter_head_output', 10, 1 );
	function trx_addons_mptt_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_mptt()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'mptt' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'mptt' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/mp-timetable/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_mptt_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_mptt_filter_body_output', 10, 1 );
	function trx_addons_mptt_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_mptt()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'mptt' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'mptt' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/mp-timetable/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/mp-timetable/[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_mptt() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable-sc-elementor.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_mptt() && trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable-sc-vc.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_mptt() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mp-timetable/mp-timetable-demo-ocdi.php';
}
