<?php
/**
 * Plugin's admin functions
 *
 * @package ThemeREX Addons
 * @since v1.6.17
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Load icons to the admin mode before all other styles
if ( !function_exists( 'trx_addons_load_icons_admin' ) ) {
	add_action("admin_enqueue_scripts", 'trx_addons_load_icons_admin', 0);
	function trx_addons_load_icons_admin($all=false) {
		// Font with icons must be loaded before main stylesheet
		if ($all
			|| strpos($_SERVER['REQUEST_URI'], 'post.php')!==false 
			|| strpos($_SERVER['REQUEST_URI'], 'post-new.php')!==false
			|| strpos($_SERVER['REQUEST_URI'], 'edit-tags.php')!==false
			|| strpos($_SERVER['REQUEST_URI'], 'term.php')!==false
			|| strpos($_SERVER['REQUEST_URI'], 'widgets.php')!==false
			|| strpos($_SERVER['REQUEST_URI'], 'customize.php')!==false
			|| (isset($_REQUEST['page']) && $_REQUEST['page']=='trx_addons_options')
			|| (isset($_REQUEST['page']) && $_REQUEST['page']=='trx_addons_theme_panel')
		) {
			wp_enqueue_style( 'trx_addons-icons', trx_addons_get_file_url('css/font-icons/css/trx_addons_icons.css'), array(), null );
			wp_enqueue_style( 'trx_addons-icons-animation', trx_addons_get_file_url('css/font-icons/css/animation.css'), array(), null );
		}
	}
}


// Load required styles and scripts in the admin mode
if ( !function_exists( 'trx_addons_load_scripts_admin' ) ) {
	add_action("admin_enqueue_scripts", 'trx_addons_load_scripts_admin');
	function trx_addons_load_scripts_admin($all=false) {
		static $loaded = false;
		if ( $loaded ) return;
		$loaded = true;
		// Font with icons must be loaded before main stylesheet
		if ( $all ) {
			trx_addons_load_icons_admin( true );
		}
		wp_enqueue_style(  'trx_addons-admin',  trx_addons_get_file_url('css/trx_addons.admin.css'), array(), null );

		wp_enqueue_script( 'trx_addons-admin',  trx_addons_get_file_url('js/trx_addons.admin.js'), array('jquery', 'wp-color-picker'), null, true );
		wp_enqueue_script( 'trx_addons-utils',  trx_addons_get_file_url('js/trx_addons.utils.js'), array('jquery'), null, true );
		wp_enqueue_style(  'trx_addons-msgbox', trx_addons_get_file_url('js/msgbox/msgbox.css'), array(), null );
		wp_enqueue_script( 'trx_addons-msgbox', trx_addons_get_file_url('js/msgbox/msgbox.js'), array('jquery'), null, true );

		// Fire action to load all other scripts from components
		do_action('trx_addons_action_load_scripts_admin', $all);
	}
}


// Load RTL-styles in the admin mode
if ( !function_exists( 'trx_addons_load_scripts_admin_rtl' ) ) {
	add_action("trx_addons_action_load_scripts_admin", 'trx_addons_load_scripts_admin_rtl', 100 );
	function trx_addons_load_scripts_admin_rtl( $all = false ) {
		static $loaded = false;
		if ( $loaded ) return;
		$loaded = true;
		if ( is_rtl() ) {
			wp_enqueue_style( 'trx_addons-admin-rtl', trx_addons_get_file_url( 'css/trx_addons.admin-rtl.css' ), array(), null );
		}
	}
}

	
// Add variables in the admin mode
if ( !function_exists( 'trx_addons_localize_scripts_admin' ) ) {
	add_action( 'customize_controls_print_footer_scripts', 'trx_addons_localize_scripts_admin' );
	add_action( 'admin_footer', 'trx_addons_localize_scripts_admin' );
	function trx_addons_localize_scripts_admin() {
		static $loaded = false;
		if ( $loaded ) return;
		$loaded = true;
		// Add variables into JS
		wp_localize_script( 'trx_addons-admin', 'TRX_ADDONS_STORAGE', apply_filters('trx_addons_filter_localize_script_admin', array(
			// AJAX parameters
			'ajax_url'						=> esc_url(admin_url('admin-ajax.php')),
			'ajax_nonce'					=> esc_attr(wp_create_nonce(admin_url('admin-ajax.php'))),
			// Admin base url
			'admin_url'						=> esc_url(admin_url()),
			// Site base url
			'site_url'						=> esc_url(get_home_url()),
			// Theme-specific columns class
			'columns_wrap_class' 			=> trx_addons_get_columns_wrap_class(),
			'column_class_template' 		=> trx_addons_get_column_class_template(),
			// E-mail mask to validate forms
			'email_mask'					=> '^([a-zA-Z0-9_\\-]+\\.)*[a-zA-Z0-9_\\-]+@[a-zA-Z0-9_\\-]+(\\.[a-zA-Z0-9_\\-]+)*\\.[a-zA-Z0-9]{2,6}$',
			// Messages
			'msg_ajax_error'				=> addslashes(esc_html__('Invalid server answer!', 'trx_addons')),
			'msg_caption_yes'				=> addslashes(esc_html__( 'Yes', 'trx_addons' )),
			'msg_caption_no'				=> addslashes(esc_html__( 'No', 'trx_addons' )),
			'msg_caption_ok'				=> addslashes(esc_html__( 'OK', 'trx_addons' )),
			'msg_caption_apply'				=> addslashes(esc_html__( 'Apply', 'trx_addons' )),
			'msg_caption_cancel'			=> addslashes(esc_html__( 'Cancel', 'trx_addons' )),
			'msg_caption_attention'			=> addslashes(esc_html__( 'Attention!', 'trx_addons' )),
			'msg_caption_warning'			=> addslashes(esc_html__( 'Warning!', 'trx_addons' )),
			'msg_reset'						=> addslashes(esc_html__( 'Reset', 'trx_addons' )),
			'msg_reset_confirm'				=> addslashes(esc_html__( 'Are you sure you want to reset all Theme Options?', 'trx_addons' )),
			'msg_export'					=> addslashes(esc_html__( 'Export', 'trx_addons' )),
			'msg_export_options'			=> addslashes(esc_html__( 'Copy options and save to the text file.', 'trx_addons' )),
			'msg_import'					=> addslashes(esc_html__( 'Import', 'trx_addons' )),
			'msg_import_options'			=> addslashes(esc_html__( 'Paste previously saved options from the text file.', 'trx_addons' )),
			'msg_import_error'				=> addslashes(esc_html__( 'Error occurs while import options!', 'trx_addons' )),
			'msg_activate_theme'			=> addslashes(esc_html__( 'Theme Activation', 'trx_addons' )),
			'msg_activate_theme_agree'		=> addslashes(esc_html__( 'If you do not check the checkbox - your name and email will not be transferred to the server and we will not be able to automatically register you in our support system. Continue?', 'trx_addons' )),
			'msg_deactivate_theme'			=> addslashes(esc_html__( 'Attention!', 'trx_addons' )),
			'msg_deactivate_theme_agree'	=> addslashes(esc_html__( "After the domain disconnection, you won't be able to install demo data and bundled plugins, as well as receive support for the theme installed on this domain.", 'trx_addons' )),
			'msg_deactivate_theme_bt_yes'	=> addslashes(esc_html__( 'Yes, I understand. Disconnect domain', 'trx_addons' )),
			'msg_deactivate_theme_bt_no'	=> addslashes(esc_html__( 'Go back', 'trx_addons' )),
			'msg_deactivate_theme_error'	=> addslashes(esc_html__( 'Theme deactivation error! Unexpected server answer!', 'trx_addons' )),
			'msg_field_email_not_valid'		=> addslashes(esc_html__( 'Invalid email address', 'trx_addons' )),
			'msg_specify_purchase_code'		=> addslashes(esc_html__( 'Please, specify the purchase code!', 'trx_addons' )),
			'msg_exit_not_saved_options'	=> addslashes(esc_html__( 'Changes not saved! Are you sure you want to leave this page?', 'trx_addons' )),
			) )
		);
	}
}


// Add scripts and styles to the Customizer
if ( ! function_exists( 'trx_addons_add_admin_scripts_to_customizer' ) ) {
	add_action( 'customize_controls_enqueue_scripts', 'trx_addons_add_admin_scripts_to_customizer' );
	function trx_addons_add_admin_scripts_to_customizer() {
		trx_addons_load_scripts_admin(true);
		trx_addons_localize_scripts_admin();
	}
}


// Refresh taxonomies list on change parent list
//--------------------------------------------------------------------------------------------

// Get specified list's items
if ( !function_exists( 'trx_addons_callback_refresh_list' ) ) {
	add_action('wp_ajax_trx_addons_refresh_list', 		'trx_addons_callback_refresh_list');
	add_action('wp_ajax_nopriv_trx_addons_refresh_list','trx_addons_callback_refresh_list');
	function trx_addons_callback_refresh_list() {
		trx_addons_verify_nonce();
		$need_not_selected = is_string( $_REQUEST['list_not_selected'] )
											? $_REQUEST['list_not_selected'] === 'true' 
											: $_REQUEST['list_not_selected'];
		if ( empty( $_REQUEST['parent_value'] ) && ! $need_not_selected ) {
			$new_list = array();
		} else {
			$list = apply_filters( 'trx_addons_filter_refresh_list_' . trim( $_REQUEST['parent_type'] ),
									array(),
									$_REQUEST['parent_value'],
									$need_not_selected
								);
			// Make simple list to save sort order of items
			$new_list = array();
			foreach ( $list as $k => $v ) {
				$new_list[] = array( 'key' => $k, 'value' => strip_tags( $v ) );
			}
		}
		if ( count( $new_list ) == 0 ) {
			$new_list[] = array( 'key' => '', 'value' => '' );
		}
		$response = array(
			'error' => '',
			'data' => $new_list
		);
		trx_addons_ajax_response( $response );
	}
}

// Get list taxonomies
if ( !function_exists( 'trx_addons_admin_refresh_list_taxonomies' ) ) {
	add_filter('trx_addons_filter_refresh_list_taxonomies', 'trx_addons_admin_refresh_list_taxonomies', 10, 3);
	function trx_addons_admin_refresh_list_taxonomies($list, $post_type, $not_selected=false) {
		return trx_addons_get_list_taxonomies(false, $post_type);
	}
}

// Get list terms
if ( !function_exists( 'trx_addons_admin_refresh_list_terms' ) ) {
	add_filter('trx_addons_filter_refresh_list_terms', 'trx_addons_admin_refresh_list_terms', 10, 3);
	function trx_addons_admin_refresh_list_terms($list, $taxonomy, $not_selected=false) {
		$rez = array();
		if ($not_selected && !empty( $taxonomy ) ) {
			$tax_obj = get_taxonomy($taxonomy);
			if ( is_object( $tax_obj ) ) {
//				$rez[0]  = trx_addons_get_not_selected_text( ! empty( $tax_obj->label ) ? $tax_obj->label : __( '- Not Selected -', 'trx_addons' ) );
				$rez[''] = trx_addons_get_not_selected_text( ! empty( $tax_obj->label ) ? $tax_obj->label : __( '- Not Selected -', 'trx_addons' ) );
			}
		}
		return trx_addons_array_merge(
					$rez, 
					trx_addons_get_list_terms(false, $taxonomy)
					);
	}
}


// Show <select> with categories in the admin filters area
//-----------------------------------------------------------------------------------
if (!function_exists('trx_addons_admin_filters')) {
	function trx_addons_admin_filters($post_type, $tax) {
		if (get_query_var('post_type') != $post_type) return;

		if ( !($terms = get_transient("trx_addons_terms_filter_".trim($tax)))) {
			$terms = get_terms($tax);
			set_transient("trx_addons_terms_filter_".trim($tax), $terms, 24*60*60);
		}

		$list = '';
		if (is_array($terms) && count($terms) > 0) {
			$tax_obj = get_taxonomy($tax);
			$list .= '<select name="'.esc_attr($tax).'" id="'.esc_attr($tax).'" class="postform">'
					.  "<option value=''>" . esc_html($tax_obj->labels->all_items) . "</option>";
			foreach ($terms as $term) {
				$list .= '<option value="'. esc_attr($term->slug) . '"'
							. (isset($_REQUEST[$tax]) 
								&& $_REQUEST[$tax] == $term->slug 
								|| (isset($_REQUEST['taxonomy']) 
										&& $_REQUEST['taxonomy'] == $tax 
										&& isset($_REQUEST['term']) 
										&& $_REQUEST['term'] == $term->slug
									) 
								? ' selected="selected"' 
								: '') 
							. '>' . esc_html($term->name) . '</option>';
			}
			$list .=  "</select>";
		}
		trx_addons_show_layout($list);
	}
}
  
// Clear terms cache on the taxonomy save
if (!function_exists('trx_addons_admin_clear_cache_terms')) {
	function trx_addons_admin_clear_cache_terms($tax) {  
		// verify nonce
		$ok = true;
		if (!empty($_REQUEST['_wpnonce_add-tag'])) {
			check_admin_referer( 'add-tag', '_wpnonce_add-tag' );
		} else if (!empty($_REQUEST['_wpnonce']) && !empty($_REQUEST['tag_ID'])) {
			$tag_ID = (int) $_REQUEST['tag_ID'];
			if ($_POST['action'] == 'editedtag') {
				check_admin_referer( 'update-tag_' . $tag_ID );
			} else if ($_POST['action'] == 'delete-tag') {
				check_admin_referer( 'delete-tag_' . $tag_ID );
			} else if ($_POST['action'] == 'delete') {
				check_admin_referer( 'bulk-tags' );
			} else if ($_POST['action'] == 'bulk-delete') {
				check_admin_referer( 'bulk-tags' );
			} else {
				$ok = false;
			}
		} else {
			$ok = false;
		}
		if ($ok) {
			set_transient("trx_addons_terms_filter_".trim($tax), '', 24*60*60);
			do_action( 'trx_addons_action_clear_cache_taxonomy', $tax );
		}
	}
}
