<?php
/**
 * Plugin support: Elegro Crypto Payment (Add Crypto payments to WooCommerce)
 *
 * @package ThemeREX Addons
 * @since v1.70.3
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_elegro_payment' ) ) {
	function trx_addons_exists_elegro_payment() {
		return class_exists( 'WC_Elegro_Payment' );
	}
}

// Add our ref to the link
if ( !function_exists( 'trx_addons_elegro_payment_add_ref' ) ) {
	add_filter( 'woocommerce_settings_api_form_fields_elegro', 'trx_addons_elegro_payment_add_ref' );
	function trx_addons_elegro_payment_add_ref( $fields ) {
		if ( ! empty( $fields['listen_url']['description'] ) ) {
			$fields['listen_url']['description'] = preg_replace(
													'/href="([^"]+)"/',
													//'href="$1/auth/sign-up?ref=246218d7-a23d-444d-83c5-a884ecfa4ebd"',
													'href="$1?ref=246218d7-a23d-444d-83c5-a884ecfa4ebd"',
													$fields['listen_url']['description']
													);
		}
		return $fields;
	}
}

// Remove API keys from dummy data
if ( !function_exists( 'trx_addons_elegro_payment_filter_export_options' ) ) {
	add_filter( 'trx_addons_filter_export_options', 'trx_addons_elegro_payment_filter_export_options' );
	function trx_addons_elegro_payment_filter_export_options( $options ) {
		if ( isset( $options['woocommerce_elegro_settings'] ) ) {
			unset( $options['woocommerce_elegro_settings'] );
		}
		return $options;
	}
}

// Disable moving scripts to the footer and async loading
if ( !function_exists( 'trx_addons_elegro_payment_filter_disable_footer_and_async' ) ) {
	add_filter( 'trx_addons_filter_skip_move_scripts_down', 'trx_addons_elegro_payment_filter_disable_footer_and_async' );
	add_filter( 'trx_addons_filter_skip_async_scripts_load', 'trx_addons_elegro_payment_filter_disable_footer_and_async' );
	function trx_addons_elegro_payment_filter_disable_footer_and_async( $list ) {
		$list[] = 'widget.acceptance.elegro';
		$list[] = 'elegro-script';
		return $list;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_elegro_payment_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_elegro_payment_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_elegro_payment_load_scripts_front', 10, 1 );
	function trx_addons_elegro_payment_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_elegro_payment() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| ( function_exists( 'trx_addons_exists_woocommerce' ) && trx_addons_exists_woocommerce() && is_cart() )
							|| ( function_exists( 'trx_addons_exists_woocommerce' ) && trx_addons_exists_woocommerce() && is_checkout() )
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'elegro-payment',
									'entries' => array(
										array( 'type' => 'text', 'sc' => 'payment_method_elegro' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'elegro_payment' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'elegro_payment', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_elegro_payment_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_elegro_payment_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_elegro_payment_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_elegro_payment_check_in_html_output', 10, 1 );
	function trx_addons_elegro_payment_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_elegro_payment()
			&& function_exists( 'trx_addons_exists_woocommerce' ) && trx_addons_exists_woocommerce()
			&& ! trx_addons_need_frontend_scripts( 'elegro_payment' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*payment_method_elegro'
							),
							'elegro-payment'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_elegro_payment_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

