<?php
/**
 * Plugin support: Instagram Feed
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if Instagram Feed installed and activated
if ( !function_exists( 'trx_addons_exists_instagram_feed' ) ) {
	function trx_addons_exists_instagram_feed() {
		return defined('SBIVER');
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_instagram_feed_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_instagram_feed_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_instagram_feed_load_scripts_front', 10, 1 );
	function trx_addons_instagram_feed_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_instagram_feed() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'instagram-feed',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'instagram-feed' ),
										array( 'type' => 'gb',  'sc' => 'wp:sbi/sbi-feed-block' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-instagram-feed' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[instagram-feed' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'instagram_feed' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'instagram_feed', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_instagram_feed_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_instagram_feed_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_instagram_feed_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_instagram_feed_check_in_html_output', 10, 1 );
	function trx_addons_instagram_feed_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_instagram_feed()
			&& ! trx_addons_need_frontend_scripts( 'instagram_feed' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*sbi_item',
							'id=[\'"][^\'"]*sbi_',
							'id=[\'"][^\'"]*sb_instagram'
							),
							'instagram-feed'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_instagram_feed_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_instagram_feed_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_instagram_feed_filter_head_output', 10, 1 );
	function trx_addons_instagram_feed_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_instagram_feed()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'instagram_feed' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'instagram-feed' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/instagram-feed/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_instagram_feed_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_instagram_feed_filter_body_output', 10, 1 );
	function trx_addons_instagram_feed_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_instagram_feed()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'instagram_feed' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'instagram-feed' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/instagram-feed/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/instagram-feed/[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'instagram-feed/instagram-feed-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_instagram_feed() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'instagram-feed/instagram-feed-demo-ocdi.php';
}
