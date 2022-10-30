<?php
/* WP GDPR Compliance support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'qwery_wp_gdpr_compliance_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'qwery_wp_gdpr_compliance_theme_setup9', 9 );
	function qwery_wp_gdpr_compliance_theme_setup9() {
		if ( is_admin() ) {
			add_filter( 'qwery_filter_tgmpa_required_plugins', 'qwery_wp_gdpr_compliance_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'qwery_wp_gdpr_compliance_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('qwery_filter_tgmpa_required_plugins',	'qwery_wp_gdpr_compliance_tgmpa_required_plugins');
	function qwery_wp_gdpr_compliance_tgmpa_required_plugins( $list = array() ) {
		if ( qwery_storage_isset( 'required_plugins', 'wp-gdpr-compliance' ) && qwery_storage_get_array( 'required_plugins', 'wp-gdpr-compliance', 'install' ) !== false ) {
			$list[] = array(
				'name'     => qwery_storage_get_array( 'required_plugins', 'wp-gdpr-compliance', 'title' ),
				'slug'     => 'wp-gdpr-compliance',
				'required' => false,
			);
		}
		return $list;
	}
}

// Check if this plugin installed and activated
if ( ! function_exists( 'qwery_exists_wp_gdpr_compliance' ) ) {
	function qwery_exists_wp_gdpr_compliance() {
//		Old way (before v.2.0)
//		Attention! In the v.2.0 and v.2.0.1 this check throw fatal error in their autoloader!
//		return class_exists( 'WPGDPRC\WPGDPRC' );
//		New way (to avoid error in wp_gdpr_compliance autoloader)
//		Check constants:	before v.2.0						after v.2.0
		return defined( 'WP_GDPR_C_ROOT_FILE' ) || defined( 'WPGDPRC_ROOT_FILE' );
	}
}
