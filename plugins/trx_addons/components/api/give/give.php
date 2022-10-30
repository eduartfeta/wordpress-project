<?php
/**
 * Plugin support: Give
 *
 * @package ThemeREX Addons
 * @since v1.6.50
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

if ( ! defined( 'TRX_ADDONS_GIVE_FORMS_PT_FORMS' ) )			define( 'TRX_ADDONS_GIVE_FORMS_PT_FORMS', 'give_forms' );
if ( ! defined( 'TRX_ADDONS_GIVE_FORMS_PT_PAYMENT' ) )			define( 'TRX_ADDONS_GIVE_FORMS_PT_PAYMENT', 'give_payment' );
if ( ! defined( 'TRX_ADDONS_GIVE_FORMS_TAXONOMY_CATEGORY' ) )	define( 'TRX_ADDONS_GIVE_FORMS_TAXONOMY_CATEGORY', 'give_forms_category' );
if ( ! defined( 'TRX_ADDONS_GIVE_FORMS_TAXONOMY_TAG' ) )		define( 'TRX_ADDONS_GIVE_FORMS_TAXONOMY_TAG', 'give_forms_tag' );


// Check if plugin is installed and activated
if ( !function_exists( 'trx_addons_exists_give' ) ) {
	function trx_addons_exists_give() {
		return class_exists( 'Give' );
	}
}

// Return true, if current page is Give plugin's page
if ( !function_exists( 'trx_addons_is_give_page' ) ) {
	function trx_addons_is_give_page() {
		$rez = false;
		if ( trx_addons_exists_give() && ! is_search() ) {
			$rez = ( trx_addons_is_single() && in_array( get_query_var('post_type'), array( TRX_ADDONS_GIVE_FORMS_PT_FORMS, TRX_ADDONS_GIVE_FORMS_PT_PAYMENT ) ) ) 
					|| trx_addons_check_url( array( 'donation', 'donor' ) )
					|| is_post_type_archive(TRX_ADDONS_GIVE_FORMS_PT_FORMS) 
					|| is_post_type_archive(TRX_ADDONS_GIVE_FORMS_PT_PAYMENT) 
					|| is_tax(TRX_ADDONS_GIVE_FORMS_TAXONOMY_CATEGORY)
					|| is_tax(TRX_ADDONS_GIVE_FORMS_TAXONOMY_TAG)
					|| ( function_exists( 'is_give_form' ) && is_give_form() )
					|| ( function_exists( 'is_give_category' ) && is_give_category() )
					|| ( function_exists( 'is_give_tag' ) && is_give_tag() )
					|| ( function_exists( 'is_give_taxonomy' ) && is_give_taxonomy() );
		}
		return $rez;
	}
}

// Return forms list, prepended inherit (if need)
if ( !function_exists( 'trx_addons_get_list_give_forms' ) ) {
	function trx_addons_get_list_give_forms($prepend_inherit=false) {
		static $list = false;
		if ($list === false) {
			$list = array();
			if (trx_addons_exists_give()) {
				$list = trx_addons_get_list_posts(false, array(
														'post_type' => TRX_ADDONS_GIVE_FORMS_PT_FORMS,
														'not_selected' => false
														));
			}
		}
		return $prepend_inherit ? trx_addons_array_merge(array('inherit' => esc_html__("Inherit", 'trx_addons')), $list) : $list;
	}
}



// Load required scripts and styles
//------------------------------------------------------------------------

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_give_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_give_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_give_load_scripts_front', 10, 1 );
	function trx_addons_give_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_give() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_give_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'give',
									'entries' => array(
										// Shortcodes in the content
										array( 'type' => 'sc',  'sc' => 'give_form' ),
										array( 'type' => 'sc',  'sc' => 'give_form_grid' ),
										array( 'type' => 'sc',  'sc' => 'give_login' ),
										array( 'type' => 'sc',  'sc' => 'give_register' ),
										array( 'type' => 'sc',  'sc' => 'give_receipt' ),
										array( 'type' => 'sc',  'sc' => 'give_goal' ),
										array( 'type' => 'sc',  'sc' => 'give_multi_form_goal' ),
										array( 'type' => 'sc',  'sc' => 'give_totals' ),
										array( 'type' => 'sc',  'sc' => 'give_donor_dashboard' ),
										array( 'type' => 'sc',  'sc' => 'give_donor_wall' ),
										array( 'type' => 'sc',  'sc' => 'give_profile_editor' ),
										array( 'type' => 'sc',  'sc' => 'donation_history' ),
										// Gutenberg blocks
										array( 'type' => 'gb',  'sc' => 'wp:give/donation-form' ),
										array( 'type' => 'gb',  'sc' => 'wp:give/donation-form-grid' ),
										array( 'type' => 'gb',  'sc' => 'wp:give/donor-wall' ),
										array( 'type' => 'gb',  'sc' => 'wp:give/donor-dashboard' ),
										array( 'type' => 'gb',  'sc' => 'wp:give/multi-form-goal' ),
										array( 'type' => 'gb',  'sc' => 'wp:give/progress-bar' ),
										// Elementor modules and widgets
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_give"' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-give_forms' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[give_' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[donation_' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-give', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'give/give.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'give' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'give', 2 );
		}
	}
}

// Merge specific styles into single stylesheet
if ( !function_exists( 'trx_addons_give_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_give_merge_styles');
	function trx_addons_give_merge_styles($list) {
		if ( trx_addons_exists_give() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'give/give.css' ] = false;
		}
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_give_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_give_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_give_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_give_check_in_html_output', 10, 1 );
	function trx_addons_give_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_give()
			&& ! trx_addons_need_frontend_scripts( 'give' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*(give\\-form\\-|widget_give_forms)',
							'id=[\'"][^\'"]*give\\-',
							'class=[\'"][^\'"]*type\\-(give_forms|give_payment)',
							'class=[\'"][^\'"]*(give_forms_category|give_forms_tag)\\-',
							),
							'give'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_give_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_give_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_give_filter_head_output', 10, 1 );
	function trx_addons_give_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_give()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'give' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'give' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/give/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_give_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_give_filter_body_output', 10, 1 );
	function trx_addons_give_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_give()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'give' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'give' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/give/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/give/[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}



// Support utils
//------------------------------------------------------------------------

// Plugin init
if ( !function_exists( 'trx_addons_give_init' ) ) {
	add_action("init", 'trx_addons_give_init');
	function trx_addons_give_init() {
		if (trx_addons_exists_give()) {
			remove_action( 'give_single_form_summary', 'give_template_single_title', 5 );
		}
	}
}

// Replace single title with h2 instead h1
if ( !function_exists( 'trx_addons_give_single_title' ) ) {
	add_action("give_single_form_summary", 'trx_addons_give_single_title', 5);
	function trx_addons_give_single_title() {
		?><h2 itemprop="name" class="give-form-title entry-title"><?php the_title(); ?></h2><?php
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_give() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'give/give-sc-elementor.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_give() && trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'give/give-sc-vc.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'give/give-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_give() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'give/give-demo-ocdi.php';
}
