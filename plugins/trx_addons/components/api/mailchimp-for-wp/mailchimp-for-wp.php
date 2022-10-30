<?php
/**
 * Plugin support: Mail Chimp
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_mailchimp' ) ) {
	function trx_addons_exists_mailchimp() {
		return function_exists('__mc4wp_load_plugin') || defined('MC4WP_VERSION');
	}
}

// Hack for MailChimp - disable scroll to form, because it broke layout in the Chrome 
if ( !function_exists( 'trx_addons_mailchimp_scroll_to_form' ) ) {
	add_filter( 'mc4wp_form_auto_scroll', 'trx_addons_mailchimp_scroll_to_form' );
	function trx_addons_mailchimp_scroll_to_form($scroll) {
		return false;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_mailchimp_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_mailchimp_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_mailchimp_load_scripts_front', 10, 1 );
	function trx_addons_mailchimp_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_mailchimp() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'mailchimp',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'mc4wp_form' ),
										array( 'type' => 'sc',  'sc' => 'mc4wp_checkbox' ),
										array( 'type' => 'gb',  'sc' => 'wp:mailchimp-for-wp/form' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-mc4wp_form"' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[mc4wp_form' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[mc4wp_checkbox' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_script( 'trx_addons-mailchimp', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'mailchimp-for-wp/mailchimp-for-wp.js'), array('jquery'), null, true );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'mailchimp' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'mailchimp', 2 );
		}
	}
}

// Merge plugin's specific scripts into single file
if ( !function_exists( 'trx_addons_mailchimp_merge_scripts' ) ) {
	add_action("trx_addons_filter_merge_scripts", 'trx_addons_mailchimp_merge_scripts');
	function trx_addons_mailchimp_merge_scripts($list) {
		if ( trx_addons_exists_mailchimp() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'mailchimp-for-wp/mailchimp-for-wp.js' ] = false;
		}
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_mailchimp_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_mailchimp_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_mailchimp_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_mailchimp_check_in_html_output', 10, 1 );
	function trx_addons_mailchimp_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_mailchimp()
			&& ! trx_addons_need_frontend_scripts( 'mailchimp' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*mc4wp',
							'id=[\'"][^\'"]*mc4wp'
							),
							'mailchimp'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_mailchimp_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mailchimp-for-wp/mailchimp-for-wp-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_mailchimp() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'mailchimp-for-wp/mailchimp-for-wp-demo-ocdi.php';
}
