<?php
/**
 * Shortcode: Table
 *
 * @package ThemeREX Addons
 * @since v1.3
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_sc_table_load_scripts_front' ) ) {
	add_action( "wp_enqueue_scripts", 'trx_addons_sc_table_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY );
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_sc_table_load_scripts_front', 10, 1 );
	function trx_addons_sc_table_load_scripts_front( $force = false ) {
		static $loaded = false;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
											'sc' => 'sc_table',
											'entries' => array(
												array( 'type' => 'sc',  'sc' => 'trx_sc_table' ),
												array( 'type' => 'gb',  'sc' => 'wp:trx-addons/table' ),
												array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_table"' ),
												array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_sc_table' ),
											)
								) )
							);
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-sc_table', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_SHORTCODES . 'table/table.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'sc_table' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'sc_table', 2 );
		}
	}
}
	
// Merge shortcode's specific styles into single stylesheet
if ( !function_exists( 'trx_addons_sc_table_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_sc_table_merge_styles');
	function trx_addons_sc_table_merge_styles($list) {
		$list[ TRX_ADDONS_PLUGIN_SHORTCODES . 'table/table.css' ] = false;
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu
if ( !function_exists( 'trx_addons_sc_table_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_sc_table_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_sc_table_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_sc_table_check_in_html_output', 10, 1 );
	function trx_addons_sc_table_check_in_html_output( $content = '' ) {
		if ( ! trx_addons_need_frontend_scripts( 'sc_table' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*sc_table'
							),
							'sc_table'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_sc_table_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// trx_sc_table
//-------------------------------------------------------------
/*
[trx_sc_table id="unique_id" style="default" aligh="left"]
*/
if ( !function_exists( 'trx_addons_sc_table' ) ) {
	function trx_addons_sc_table($atts, $content=null){	
		$atts = trx_addons_sc_prepare_atts('trx_sc_table', $atts, trx_addons_sc_common_atts('id,title', array(
			// Individual params
			"type" => "default",
			"width" => "100%",
			"align" => "none",
			"content" => '',
			))
		);
		
		if (!empty($content)) {
			if ( strpos( $content, '&lt;table' ) !== false ) {
				$content = html_entity_decode($content, ENT_COMPAT, 'UTF-8');
			}
			$atts['content'] = do_shortcode(str_replace(
												array('<p><table', 'table></p>', '><br />'),
												array('<table', 'table>', '>'),
												$content
												)
								);
		}
		
		// Load shortcode-specific scripts and styles
		trx_addons_sc_table_load_scripts_front( true );

		// Load template
		ob_start();
		trx_addons_get_template_part(array(
										TRX_ADDONS_PLUGIN_SHORTCODES . 'table/tpl.'.trx_addons_esc($atts['type']).'.php',
										TRX_ADDONS_PLUGIN_SHORTCODES . 'table/tpl.default.php'
										),
										'trx_addons_args_sc_table', 
										$atts
									);
		$output = ob_get_contents();
		ob_end_clean();

		return apply_filters('trx_addons_sc_output', $output, 'trx_sc_table', $atts, $content);
	}
}


// Add shortcode [trx_sc_table]
if (!function_exists('trx_addons_sc_table_add_shortcode')) {
	function trx_addons_sc_table_add_shortcode() {
		add_shortcode("trx_sc_table", "trx_addons_sc_table");
	}
	add_action('init', 'trx_addons_sc_table_add_shortcode', 20);
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'table/table-sc-elementor.php';
}

// Add shortcodes to Gutenberg
if ( trx_addons_exists_gutenberg() && function_exists( 'trx_addons_gutenberg_get_param_id' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'table/table-sc-gutenberg.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_SHORTCODES . 'table/table-sc-vc.php';
}
