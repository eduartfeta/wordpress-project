<?php
/**
 * Plugin support: Backstage
 *
 * @package ThemeREX Addons
 * @since v1.88.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin is installed and activated
if ( !function_exists( 'trx_addons_exists_backstage' ) ) {
	function trx_addons_exists_backstage() {
		return function_exists( 'Backstage_Plugin' );
	}
}


// Disallow some components from load in the demo mode
if ( ! function_exists( 'trx_addons_backstage_is_demo' ) ) {
	function trx_addons_backstage_is_demo() {
		$is_demo = false;
		if ( trx_addons_exists_backstage() && ( is_customize_preview() || is_admin() ) ) {
			$user = wp_get_current_user();
			$is_demo = is_object( $user ) && ! empty( $user->data->user_login ) && 'backstage_customizer_user' == $user->data->user_login;
		}
		return $is_demo;
	}
}


// Add 'Backstage' parameters in the ThemeREX Addons Options
if (!function_exists('trx_addons_backstage_options')) {
	add_filter( 'trx_addons_filter_options', 'trx_addons_backstage_options');
	function trx_addons_backstage_options($options) {
		if (trx_addons_exists_backstage()) {
			trx_addons_array_insert_before( $options, 'theme_specific_section', array(
					'backstage_section' => array(
						"title" => esc_html__('Backstage demo', 'trx_addons'),
						"desc" => wp_kses_data( __("Backstage demo settings", 'trx_addons') ),
						'icon' => 'trx_addons_icon-customizer',
						"type" => "section"
					),
					'backstage_additional_info' => array(
						"title" => esc_html__('Backstage demo parameters', 'trx_addons'),
						"desc" => wp_kses_data( __("Settings for the Backstage plugin", 'trx_addons') ),
						"type" => "info"
					),
					'backstage_return_url' => array(
						"title" => esc_html__('URL to backstage demo',  'trx_addons'),
						"desc" => wp_kses_data( __('URL of the page for customizer demo. If empty - current page url is used',  'trx_addons') ),
						"std" => "",
						"type" => "text"
					),
				)
			);
		}
		return $options;
	}
}


// Change url-parameter 'return_url' in the backstage link
if (!function_exists('trx_addons_backstage_change_return_url')) {
	add_filter( 'backstage_get_customizer_link', 'trx_addons_backstage_change_return_url');
	function trx_addons_backstage_change_return_url($url) {
		$return_url = trx_addons_get_option( 'backstage_return_url' );
		if ( ! empty( $return_url ) && function_exists( 'backstage_get_setting' ) ) {
			$auto_login_key = backstage_get_setting( 'auto_login_key' );
			if ( empty( $auto_login_key ) && class_exists( 'Backstage' ) ) {
				$auto_login_key = Backstage::$default_auto_login_key;
			}
			if ( ! empty( $auto_login_key ) ) {
				$auto_login_hash = wp_hash( $return_url );
				$url = add_query_arg( $auto_login_key, rawurlencode( $auto_login_hash ), remove_query_arg( $auto_login_key, $url ) );
				$url = add_query_arg( 'return_url', rawurlencode( $return_url ), remove_query_arg( 'return_url', $url ) );
			}
		}
		return $url;
	}
}


// Disallow some components from load in the demo mode
if ( ! function_exists( 'trx_addons_backstage_disallow_widgets_and_menus_in_demo' ) ) {
	add_filter( 'customize_loaded_components', 'trx_addons_backstage_disallow_widgets_and_menus_in_demo', 1000, 2 );
	function trx_addons_backstage_disallow_widgets_and_menus_in_demo( $components, $wp_customize ) {
		if ( trx_addons_backstage_is_demo() ) {
			$components = array();
		}
		return $components;
	}
}


// Disallow register WordPress core controls for the customizer in the demo mode
if ( ! function_exists( 'trx_addons_backstage_disallow_core_components_in_demo' ) ) {
	add_action( 'customize_register', 'trx_addons_backstage_disallow_core_components_in_demo', 1 );
	function trx_addons_backstage_disallow_core_components_in_demo( $wp_customize ) {
		if ( trx_addons_backstage_is_demo() ) {
			// Disabled, because after remove action backstage user is unlogged
			// after each page reloading
			//remove_action( 'customize_register', array( $wp_customize, 'register_controls' ) );
		}
	}
}


// Add class to the frontend body if the customizer in the demo mode
if ( ! function_exists( 'trx_addons_backstage_add_front_body_class_in_demo' ) ) {
	add_filter( 'body_class', 'trx_addons_backstage_add_front_body_class_in_demo', 1 );
	function trx_addons_backstage_add_front_body_class_in_demo( $classes ) {
		if ( trx_addons_backstage_is_demo() ) {
			$classes[] = 'trx_addons_customizer_demo';
		}
		return $classes;
	}
}

// Load script to Customizer
if ( ! function_exists( 'trx_addons_backstage_customizer_control_js' ) ) {
	add_action( 'customize_controls_enqueue_scripts', 'trx_addons_backstage_customizer_control_js' );
	function trx_addons_backstage_customizer_control_js() {
		if ( trx_addons_backstage_is_demo() ) {
			wp_enqueue_style(
				'trx_addons-backstage-customizer',
				trx_addons_get_file_url( TRX_ADDONS_PLUGIN_API . 'backstage/backstage.css' ),
				array(), null
			);
			wp_enqueue_script(
				'trx_addons-backstage-customizer',
				trx_addons_get_file_url( TRX_ADDONS_PLUGIN_API . 'backstage/backstage.js' ),
				array( 'customize-controls', 'iris', 'underscore', 'wp-util' ), null, true
			);
			wp_localize_script(
				'trx_addons-backstage-customizer', 'trx_addons_customizer_vars', apply_filters(
					'trx_addons_filter_customizer_vars', array(
						'msg_refresh_preview_area' => esc_html__( "Reload preview area", 'trx_addons' ),
						'msg_welcome_hi'           => esc_html__( "Hello", 'trx_addons' ),
						'msg_welcome_text'         => esc_html__( "Here you can customize the look and feel of your website. More options become available after purchasing the theme!", 'trx_addons' ),
						'msg_welcome_button'       => esc_html__( "Get Started", 'trx_addons' ),
					)
				)
			);
		}
	}
}
