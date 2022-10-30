<?php
/**
 * ThemeREX Addons Third-party plugins API
 *
 * @package ThemeREX Addons
 * @since v1.6.29
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Define list with api
if ( ! function_exists( 'trx_addons_api_load' ) ) {
	add_action( 'after_setup_theme', 'trx_addons_api_load', 2 );
	function trx_addons_api_load() {
		static $loaded = false;
		if ($loaded) return;
		$loaded = true;
		global $TRX_ADDONS_STORAGE;
		$TRX_ADDONS_STORAGE['api_list'] = apply_filters('trx_addons_api_list', array(

			'elementor' => array(
							'title' => __('Elementor (free Page Builder)', 'trx_addons'),
							// Always enabled!!!
							'std' => 1,
							'hidden' => false
						),
			'gutenberg' => array(
							'title' => __('Gutenberg', 'trx_addons'),
							// Always enabled!!!
							'std' => 1,
							'hidden' => false
						),
			'js_composer' => array(
							'title' => __('WPBakery PageBuilder', 'trx_addons'),
							// Always enabled!!!
							'std' => 1,
							'hidden' => false
						),
			'vc-extensions-bundle' => array(
							'title' => __('VC Extensions Bundle', 'trx_addons')
						),
			'backstage' => array(
							'title' => __('Backstage', 'trx_addons'),
							// Preloaded
							'preloaded' => true,
							// Always enabled!!!
							'std' => 1,
							'hidden' => true
						),

			'bbpress' => array(
							'title' => __('BB Press & Buddy Press', 'trx_addons')
						),
			'booked' => array(
							'title' => __('Booked Appointments', 'trx_addons')
						),
			'calculated-fields-form' => array(
							'title' => __('Calculated Fields Form', 'trx_addons')
						),
			'contact-form-7' => array(
							'title' => __('Contact Form 7', 'trx_addons')
						),
			'content_timeline' => array(
							'title' => __('Content Timeline', 'trx_addons')
						),
			'easy-digital-downloads' => array(
							'title' => __('Easy Digital Downloads', 'trx_addons')
						),
			'elegro-payment' => array(
							'title' => __('Elegro Crypto Payment', 'trx_addons')
						),
			'essential-grid' => array(
							'title' => __('Essential Grid', 'trx_addons')
						),
			'give' => array(
							'title' => __('Give (Donations)', 'trx_addons')
						),
			'instagram-feed' => array(
							'title' => __('Instagram Feed', 'trx_addons')
						),
			'learnpress' => array(
							'title' => __('LearnPress', 'trx_addons')
						),
			'mailchimp-for-wp' => array(
							'title' => __('MailChimp for WordPress', 'trx_addons')
						),
			'mp-timetable' => array(
							'title' => __('MP TimeTable', 'trx_addons')
						),
			'powerkit' => array(
							'title' => __('PowerKit', 'trx_addons')
						),
			'revslider' => array(
							'title' => __('Revolution Slider', 'trx_addons')
						),
			'the-events-calendar' => array(
							'title' => __('The Events Calendar', 'trx_addons'),
							'layouts_sc' => array(
								'default'	=> esc_html__('Default', 'trx_addons'),
								'classic'	=> esc_html__('Classic', 'trx_addons'),
							)
						),
			'tourmaster' => array(
							'title' => __('Tour Master', 'trx_addons')
						),
			'trx_donations' => array(
							'title' => __('ThemeREX Donations', 'trx_addons')
						),
			'trx_popup' => array(
							'title' => __('ThemeREX Pop-Up', 'trx_addons')
						),
			'twitter' => array(
							'title' => __('Twitter', 'trx_addons'),
							// Always enabled!!!
							'std' => 1,
							'hidden' => false
						),
			'ubermenu' => array(
							'title' => __('UberMenu', 'trx_addons')
						),
			'woocommerce' => array(
							'title' => __('WooCommerce', 'trx_addons')
						),
			'sitepress-multilingual-cms' => array(
							'title' => __('WPML - Sitepress Multilingual CMS', 'trx_addons')
						),
			// GDPR Support: uncomment only one of the following plugins
			//'gdpr-framework' => array(
			//				'title' => __( 'The GDPR Framework', 'trx_addons' ),
			//			),
			'wp-gdpr-compliance' => array(
							'title' =>  esc_html__( 'WP GDPR Compliance', 'trx_addons' ),
						),
			'sitepress-multilingual-cms' => array(
							'title' => __('WPML - Sitepress Multilingual CMS', 'trx_addons')
						),
			)
		);
		if ( is_array( $TRX_ADDONS_STORAGE['api_list'] ) && count($TRX_ADDONS_STORAGE['api_list']) > 0 ) {
			foreach ( $TRX_ADDONS_STORAGE['api_list'] as $w => $params ) {
				if ( empty( $params['preloaded'] ) && trx_addons_components_is_allowed( 'api', $w )
					&& ( $fdir = trx_addons_get_file_dir( TRX_ADDONS_PLUGIN_API . "{$w}/{$w}.php" ) ) != '' ) { 
					include_once $fdir;
					trx_addons_api_is_loaded( $w, true );
				}
			}
		}
	}
}


// Return true if component is loaded
if ( ! function_exists( 'trx_addons_api_is_loaded' ) ) {
	function trx_addons_api_is_loaded( $slug, $set = -1 ) {
		return trx_addons_components_is_loaded( 'api', $slug, $set );
	}
}


// Preload some api immediately
$trx_addons_preload_api = array(
	'backstage'
);
foreach( $trx_addons_preload_api as $trx_addons_preload_api_item ) {
	if ( ( $trx_addons_fdir = trx_addons_get_file_dir( TRX_ADDONS_PLUGIN_API . "{$trx_addons_preload_api_item}/{$trx_addons_preload_api_item}.php" ) ) != '' ) {
		include_once $trx_addons_fdir;
		trx_addons_api_is_loaded($trx_addons_preload_api_item, true);
	}
}


// Add 'Third-party API' block in the ThemeREX Addons Components
if ( ! function_exists( 'trx_addons_api_components' ) ) {
	add_filter( 'trx_addons_filter_components_blocks', 'trx_addons_api_components');
	function trx_addons_api_components( $blocks = array() ) {
		$blocks['api'] = __( 'Third-party plugins API', 'trx_addons' );
		return $blocks;
	}
}


// Store shapes list to use it in the Page Builders
if ( ! function_exists( 'trx_addons_api_shapes_list' ) ) {
	add_action( 'init', 'trx_addons_api_shapes_list' );
	function trx_addons_api_shapes_list() {
		$shapes_cache = get_transient( 'trx_addons_shapes' );
		if ( ! is_array( $shapes_cache ) || count( $shapes_cache ) == 0 ) {
			$shapes_path = apply_filters( 'trx_addons_filter_shapes_path', 'css/shapes' );
			$shapes_dir  = trx_addons_get_folder_dir( $shapes_path );
			$shapes_url  = trx_addons_get_folder_url( $shapes_path );
			$shapes_list = ! empty( $shapes_dir ) ? trx_addons_list_files( $shapes_dir ) : array();
			$shapes_urls = array();
			if ( is_array( $shapes_list ) ) {
				foreach ( $shapes_list as $k => $v ) {
					if ( trx_addons_get_file_ext( $v ) != 'svg' ) {
						unset( $shapes_list[ $k ] );
					} else {
						$shapes_urls[ $k ] = str_replace( $shapes_dir, $shapes_url, $v );
					}
				}
			} else {
				$shapes_list = array();
			}
			$shapes_cache = array(
								'shapes_list' => $shapes_list,
								'shapes_urls' => $shapes_urls,
								'shapes_url'  => esc_url( trailingslashit( $shapes_url ) )
							);
			set_transient( 'trx_addons_shapes', $shapes_cache, 8 * 60 * 60 );       // Store to the cache for 8 hours
		}
		if ( ! empty( $shapes_cache['shapes_list'] ) && count( $shapes_cache['shapes_list'] ) > 0 ) {
			global $TRX_ADDONS_STORAGE;
			$TRX_ADDONS_STORAGE['shapes_list'] = $shapes_cache['shapes_list'];
			$TRX_ADDONS_STORAGE['shapes_urls'] = $shapes_cache['shapes_urls'];
			$TRX_ADDONS_STORAGE['shapes_url']  = $shapes_cache['shapes_url'];
		}
	}
}

// Remove cached shapes list
if ( ! function_exists( 'trx_addons_api_clear_saved_shapes_list' ) ) {
	add_action( 'trx_addons_action_importer_import_end', 'trx_addons_api_clear_saved_shapes_list', 10, 1 );
	function trx_addons_api_clear_saved_shapes_list( $importer = false ) {
		delete_transient( 'trx_addons_shapes' );
	}
}

// Add shapes url to use it in the js
if ( ! function_exists( 'trx_addons_api_localize_scripts' ) ) {
	add_filter( 'trx_addons_filter_localize_script_admin',	'trx_addons_api_localize_scripts');
	add_filter( 'trx_addons_filter_localize_script', 		'trx_addons_api_localize_scripts');
	function trx_addons_api_localize_scripts( $vars = array() ) {
		global $TRX_ADDONS_STORAGE;
		$vars['shapes_url']  = !empty($TRX_ADDONS_STORAGE['shapes_url']) ? $TRX_ADDONS_STORAGE['shapes_url'] : '';
		return $vars;
	}
}



//-----------------------------------------------------------------------------------
//--  CHECK FOR COMPONENTS EXISTS
//--  Attention! This functions are used in many files and must be declared here!!!
//-----------------------------------------------------------------------------------

// Check if plugin 'WPBakery PageBuilder' is installed and activated
if ( ! function_exists( 'trx_addons_exists_vc' ) ) {
	function trx_addons_exists_vc() {
		return class_exists( 'Vc_Manager' );
	}
}

// Check if plugin 'Elementor' is installed and activated
if ( ! function_exists( 'trx_addons_exists_elementor' ) ) {
	function trx_addons_exists_elementor() {
		return class_exists( 'Elementor\Plugin' );
	}
}

// Check if plugin 'Elementor Pro' is installed and activated
if ( ! function_exists( 'trx_addons_exists_elementor_pro' ) ) {
	function trx_addons_exists_elementor_pro() {
		return class_exists( 'ElementorPro\Modules\ThemeBuilder\Module' );
	}
}

// Check if plugin 'Gutenberg' is installed and activated
if ( ! function_exists( 'trx_addons_exists_gutenberg' ) ) {
	function trx_addons_exists_gutenberg() {
		return function_exists( 'register_block_type' );	// && function_exists( 'the_gutenberg_project' )
	}
}

// Check if any PageBuilder is installed and activated
if ( ! function_exists( 'trx_addons_exists_page_builder' ) ) {
	function trx_addons_exists_page_builder() {
		return     ( trx_addons_exists_elementor() && trx_addons_components_is_allowed( 'api', 'elementor' ) )
				|| ( trx_addons_exists_gutenberg() && trx_addons_components_is_allowed( 'api', 'gutenberg' ) )
				|| ( trx_addons_exists_vc()        && trx_addons_components_is_allowed( 'api', 'js_composer' ) );
	}
}

// Check if RevSlider installed and activated
if ( ! function_exists( 'trx_addons_exists_revslider' ) ) {
	function trx_addons_exists_revslider() {
		return function_exists( 'rev_slider_shortcode' ) || class_exists( 'RevSliderData' );
	}
}

// Check if plugin installed and activated
if ( ! function_exists( 'trx_addons_exists_woocommerce' ) ) {
	function trx_addons_exists_woocommerce() {
		return class_exists('Woocommerce');
	}
}

// Check if plugin installed and activated
if ( ! function_exists( 'trx_addons_exists_edd' ) ) {
	function trx_addons_exists_edd() {
		return class_exists('Easy_Digital_Downloads');
	}
}

// Check if plugin installed and activated
if ( ! function_exists( 'trx_addons_exists_wpml' ) ) {
	function trx_addons_exists_wpml() {
		return defined('ICL_SITEPRESS_VERSION') || class_exists('sitepress');
	}
}



//-----------------------------------------------------------------------------------
//--  CHECK FOR PREVIEW MODE OF ANY BUILDER
//--  Attention! This functions are used in many files and must be declared here!!!
//-----------------------------------------------------------------------------------

// Check if current page is a PageBuilder preview mode
if ( ! function_exists( 'trx_addons_is_preview' ) ) {
	function trx_addons_is_preview( $builder = 'any' ) {
		return ( in_array( $builder, array( 'any', 'elm', 'elementor' ) ) && function_exists( 'trx_addons_elm_is_preview' ) && trx_addons_elm_is_preview() )
				||
				( in_array( $builder, array( 'any', 'gb', 'gutenberg' ) ) && function_exists( 'trx_addons_gutenberg_is_preview' ) && trx_addons_gutenberg_is_preview() );
	}
}

// Return a preview holder - block to representate a shortcode in the PageBuilder preview mode if a shortcode content is empty
// Fix for Elementor Pro: return empty string instead a <div> layout
if ( ! function_exists( 'trx_addons_get_preview_placeholder' ) ) {
	function trx_addons_get_preview_placeholder( $sc = '', $atts = array() ) {
		$title = apply_filters(
					'trx_addons_filter_pb_preview_placeholder_title',
					( ! empty( $sc ) ? ucfirst( str_replace( array( 'trx_sc_', 'trx_widget_', '_' ), array( '', '', ' ' ), $sc ) ) . ': ' : '' )
					. ( ! empty( $atts['type'] ) ? esc_html( ucfirst( $atts['type'] ) ) : '' )
				);
		return apply_filters( 'trx_addons_filter_pb_preview_placeholder',
								'<div class="trx_addons_pb_preview_placeholder sc_placeholder"'
									. ' title="' . esc_attr( $title ) . '"'
								. '>'
									. '<p>'
										. $title
									. '</p>'
								. '</div>',
								$sc,
								$atts
							);
	}
}
