<?php
/**
 * Plugin support: ThemeREX Pop-Up (Importer support)
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_trx_popup_importer_required_plugins' ) ) {
	add_filter( 'trx_addons_filter_importer_required_plugins',	'trx_addons_trx_popup_importer_required_plugins', 10, 2 );
	function trx_addons_trx_popup_importer_required_plugins($not_installed='', $list='') {
		if (strpos($list, 'trx_popup')!==false && !trx_addons_exists_trx_popup() )
			$not_installed .= '<br>' . esc_html__('ThemeREX Pop-Up', 'trx_addons');
		return $not_installed;
	}
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_trx_popup_importer_set_options' ) ) {
	add_filter( 'trx_addons_filter_importer_options',	'trx_addons_trx_popup_importer_set_options' );
	function trx_addons_trx_popup_importer_set_options($options=array()) {
		if ( trx_addons_exists_trx_popup() && in_array('trx_popup', $options['required_plugins']) ) {
			$options['additional_options'][] = 'trx-popup-options';
		}
		return $options;
	}
}

// Prevent import plugin's specific options if plugin is not installed
if ( !function_exists( 'trx_addons_trx_popup_importer_check_options' ) ) {
	add_filter( 'trx_addons_filter_import_theme_options', 'trx_addons_trx_popup_importer_check_options', 10, 4 );
	function trx_addons_trx_popup_importer_check_options($allow, $k, $v, $options) {
		if ($allow && $k == 'trx-popup-options') {
			$allow = trx_addons_exists_trx_popup() && in_array('trx_popup', $options['required_plugins']);
		}
		return $allow;
	}
}