<?php
/**
 * Plugin support: PowerKit
 *
 * @package ThemeREX Addons
 * @since v1.75.3
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if PowerKit installed and activated
if ( !function_exists( 'trx_addons_exists_powerkit' ) ) {
	function trx_addons_exists_powerkit() {
		return class_exists( 'Powerkit' );
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'powerkit/powerkit-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_powerkit() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'powerkit/powerkit-demo-ocdi.php';
}
