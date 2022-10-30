<?php
/**
 * Plugin support: PowerKit (Importer supoort)
 *
 * @package ThemeREX Addons
 * @since v1.75.3
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_powerkit_importer_required_plugins' ) ) {
	add_filter( 'trx_addons_filter_importer_required_plugins',	'trx_addons_powerkit_importer_required_plugins', 10, 2 );
	function trx_addons_powerkit_importer_required_plugins($not_installed='', $list='') {
		if (strpos($list, 'powerkit')!==false && !trx_addons_exists_powerkit() ) {
			$not_installed .= '<br>' . esc_html__('PowerKit', 'trx_addons');
		}
		return $not_installed;
	}
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_powerkit_importer_set_options' ) ) {
	add_filter( 'trx_addons_filter_importer_options',	'trx_addons_powerkit_importer_set_options' );
	function trx_addons_powerkit_importer_set_options($options=array()) {
		if (trx_addons_exists_powerkit() && in_array('powerkit', $options['required_plugins'])) {
			if (is_array($options)) {
				$options['additional_options'][] = 'powerkit_enabled_%';		// Add slugs to export options for this plugin
			}
		}
		return $options;
	}
}

// Prevent import plugin's specific options if plugin is not installed
if ( !function_exists( 'trx_addons_powerkit_importer_check_options' ) ) {
	add_filter( 'trx_addons_filter_import_theme_options', 'trx_addons_powerkit_importer_check_options', 10, 4 );
	function trx_addons_powerkit_importer_check_options($allow, $k, $v, $options) {
		if ($allow && strpos($k, 'powerkit_') === 0) {
			$allow = trx_addons_exists_powerkit() && in_array('powerkit', $options['required_plugins']);
		}
		return $allow;
	}
}
