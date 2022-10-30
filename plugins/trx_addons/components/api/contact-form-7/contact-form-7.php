<?php
/**
 * Plugin support: Contact Form 7
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Check if Contact Form 7 installed and activated
if ( !function_exists( 'trx_addons_exists_cf7' ) ) {
	function trx_addons_exists_cf7() {
		return class_exists('WPCF7') && class_exists('WPCF7_ContactForm');
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_cf7_load_scripts_front' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_cf7_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_cf7_load_scripts_front', 10, 1 );
	function trx_addons_cf7_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_cf7() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'cf7',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'contact-form-7' ),
										//array( 'type' => 'gb',  'sc' => 'wp:trx-addons/events' ),	// This sc is not exists for GB
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_contact_form_7"' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[contact-form-7' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-cf7', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.css'), array(), null );
			wp_enqueue_script( 'trx_addons-cf7', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.js'), array('jquery'), null, true );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'cf7' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'cf7', 2 );
		}
	}
}

// Merge specific styles into single stylesheet
if ( !function_exists( 'trx_addons_cf7_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_cf7_merge_styles');
	function trx_addons_cf7_merge_styles($list) {
		if ( trx_addons_exists_cf7() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.css' ] = false;
		}
		return $list;
	}
}

// Merge plugin's specific scripts into single file
if ( !function_exists( 'trx_addons_cf7_merge_scripts' ) ) {
	add_action("trx_addons_filter_merge_scripts", 'trx_addons_cf7_merge_scripts');
	function trx_addons_cf7_merge_scripts($list) {
		if ( trx_addons_exists_cf7() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.js' ] = false;
		}
		return $list;
	}
}


// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_cf7_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_cf7_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_cf7_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_cf7_check_in_html_output', 10, 1 );
	function trx_addons_cf7_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_cf7()
			&& ! trx_addons_need_frontend_scripts( 'cf7' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*wpcf7-form',
							'class=[\'"][^\'"]*type\\-wpcf7_contact_form',
							),
							'cf7'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_cf7_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_cf7_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_cf7_filter_head_output', 10, 1 );
	function trx_addons_cf7_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_cf7()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'cf7' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'cf7' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/contact-form-7/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_cf7_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_cf7_filter_body_output', 10, 1 );
	function trx_addons_cf7_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_cf7()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'cf7' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'cf7' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/contact-form-7/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/contact-form-7/[^>]*>[\\s\\S]*</script>#U', '', $content );
			$content = preg_replace( '#<script[^>]*id=[\'"]contact-form-7-[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}


// Return forms list, prepended inherit (if need)
if ( !function_exists( 'trx_addons_get_list_cf7' ) ) {
	function trx_addons_get_list_cf7($prepend_inherit=false) {
		static $list = false;
		if ($list === false) {
			$list = array();
			if (trx_addons_exists_cf7()) {
				// Attention! Using WP_Query is damage 'post_type' in the main query
				global $wpdb;
				$rows = $wpdb->get_results( 'SELECT id, post_title'
												. ' FROM ' . esc_sql($wpdb->prefix . 'posts') 
												. ' WHERE post_type="' . esc_sql(WPCF7_ContactForm::post_type) . '"'
														. ' AND post_status' . (current_user_can('read_private_pages') && current_user_can('read_private_posts') ? ' IN ("publish", "private")' : '="publish"')
														. ' AND post_password=""'
												. ' ORDER BY post_title' );
				if (is_array($rows) && count($rows) > 0) {
					foreach ($rows as $row) {
						$list[$row->id] = $row->post_title;
					}
				}
			}
		}
		return $prepend_inherit ? trx_addons_array_merge(array('inherit' => esc_html__("Inherit", 'trx_addons')), $list) : $list;
	}
}


// Add styles and scripts to the AJAX response with a post details (for services and dishes)
// if a CF7 form is present in the content
if ( ! function_exists( 'trx_addons_wpcf7_in_post_details_in_popup' ) ) {
	add_filter( 'trx_addons_filter_post_details_in_popup',	'trx_addons_wpcf7_in_post_details_in_popup', 10, 2 );
	function trx_addons_wpcf7_in_post_details_in_popup( $response, $type ) {
		if ( ! empty( $response['data'] ) && strpos( $response['data'], 'wpcf7-form' ) !== false ) {
			// Add styles
			//--------------------------------
			if ( ! isset( $response['css'] ) ) {
				$response['css'] = array();
			}
			// Add CF7 styles
			if ( function_exists( 'wpcf7_plugin_url' ) ) {
				$response['css']['contact-form-7'] = array( 'url' => wpcf7_plugin_url( 'includes/css/styles.css' ) );
				if ( function_exists( 'wpcf7_is_rtl' ) && wpcf7_is_rtl() ) {
					$response['css']['contact-form-7-rtl'] = array( 'url' => wpcf7_plugin_url( 'includes/css/styles-rtl.css' ) );
				}
			}
			// Add our styles
			$response['css']['trx_addons-cf7'] = trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.css');

			// Add scripts
			//--------------------------------
			if ( ! isset( $response['js'] ) ) {
				$response['js'] = array();
			}
			// Add CF7 scripts
			if ( function_exists( 'wpcf7_plugin_url' ) ) {
				$wpcf7 = array(
							'api' => array(
								'root' => esc_url_raw( get_rest_url() ),
								'namespace' => 'contact-form-7/v1',
							)
						);
				if ( defined( 'WP_CACHE' ) and WP_CACHE ) {
					$wpcf7['cached'] = 1;
				}
				$response['js']['contact-form-7-extra'] = array(
					'code' => 'var wpcf7 = ' . json_encode( $wpcf7 ) . ';'
				);
				$response['js']['contact-form-7'] = array(
					'url' => wpcf7_plugin_url( 'includes/js/index.js' )
				);
			}
			// Add out scripts
			$response['js']['trx_addons-cf7'] = trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7.js');
		}
		return $response;
	}
}


// Filter 'wpcf7_mail_components' before Contact Form 7 send mail
// to replace recipient for 'Cars' and 'Properties'
// Also customer can use the '{{ title }}' in the 'Subject' and 'Message'
// to replace it with the post title when send a mail
if ( !function_exists( 'trx_addons_cpt_properties_wpcf7_mail_components' ) ) {
	add_filter('wpcf7_mail_components',	'trx_addons_cpt_properties_wpcf7_mail_components', 10, 3);
	function trx_addons_cpt_properties_wpcf7_mail_components($components, $form, $mail_obj=null) {
		if (is_object($form) && method_exists($form, 'id') && (int)$form->id() > 0 ) {
			$data = get_transient(sprintf('trx_addons_cf7_%d_data', (int) $form->id()));
			if (!empty($data['agent'])) {
				$agent_id = (int) $data['agent'];
				$agent_email = '';
				if ($agent_id > 0) {			// Agent
					$meta = (array)get_post_meta($agent_id, 'trx_addons_options', true);
					$agent_email = $meta['email'];
				} else if ($agent_id < 0) {		// Author
					$user_id = abs($agent_id);
					$user_data = get_userdata($user_id);
					$agent_email = $user_data->user_email;
				}
				if (!empty($agent_email)) $components['recipient'] = $agent_email;
			}
			if (!empty($data['item']) && (int) $data['item'] > 0) {
				$post = get_post($data['item']);
				foreach(array('subject', 'body') as $k) {
					$components[$k] = str_replace(
													array(
														'{{ title }}'
													),
													array(
														$post->post_title
													),
													$components[$k]
												);
				}
			}
		}
		return $components;
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_cf7() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7-sc-elementor.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_cf7() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'contact-form-7/contact-form-7-demo-ocdi.php';
}
