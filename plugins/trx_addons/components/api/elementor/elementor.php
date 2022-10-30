<?php
/**
 * Plugin support: Elementor
 *
 * @package ThemeREX Addons
 * @since v1.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

define( 'ELEMENTOR_GO_PRO_METHOD', 'link' );	// ref  - add URL param ref=xxx
												// link - replace all go_pro URLs to new link
define( 'ELEMENTOR_GO_PRO_REF',  '2496' );
define( 'ELEMENTOR_GO_PRO_LINK', 'https://be.elementor.com/visit/?bta=2496&nci=5383&brand=elementor&utm_campaign=theme' );	// https://trk.elementor.com/2496

// Check if plugin 'Elementor' is installed and activated
// Attention! This function is used in many files and was moved to the api.php
/*
if ( !function_exists( 'trx_addons_exists_elementor' ) ) {
	function trx_addons_exists_elementor() {
		return class_exists('Elementor\Plugin');
	}
}
*/

// Return true if Elementor exists and current mode is preview
if ( !function_exists( 'trx_addons_elm_is_preview' ) ) {
	function trx_addons_elm_is_preview() {
		static $is_preview = -1;
		if ( $is_preview === -1 ) {
			if ( trx_addons_exists_elementor() ) {
				$elementor = \Elementor\Plugin::instance();
				$is_preview = is_object( $elementor )
								&& ! empty( $elementor->preview )
								&& is_object( $elementor->preview )
								&& ( $elementor->preview->is_preview_mode()
									|| trx_addons_get_value_gp( 'elementor-preview' ) > 0
									|| (trx_addons_get_value_gp( 'post' ) > 0
										&& trx_addons_get_value_gp( 'action' ) == 'elementor'
										)
									|| ( is_admin()
										&& in_array( trx_addons_get_value_gp( 'action' ), array( 'elementor', 'elementor_ajax', 'wp_ajax_elementor_ajax' ) )
										)
									);
			} else {
				$is_preview = false;
			}
		}
		return $is_preview;
	}
}

// Return true if Elementor exists and current mode is edit
if ( !function_exists( 'trx_addons_elm_is_edit_mode' ) ) {
	function trx_addons_elm_is_edit_mode() {
		static $is_edit_mode = -1;
		if ( $is_edit_mode === -1 ) {
			$is_edit_mode = trx_addons_exists_elementor()
								&& ( \Elementor\Plugin::instance()->editor->is_edit_mode()
									|| ( trx_addons_get_value_gp( 'post' ) > 0
										&& trx_addons_get_value_gp( 'action' ) == 'elementor'
										)
									|| ( is_admin()
										&& in_array( trx_addons_get_value_gp( 'action' ), array( 'elementor', 'elementor_ajax', 'wp_ajax_elementor_ajax' ) )
										)
									);
		}
		return $is_edit_mode;
	}
}

// Return true if specified post/page is built with Elementor
if ( !function_exists( 'trx_addons_is_built_with_elementor' ) ) {
	function trx_addons_is_built_with_elementor( $post_id ) {
		// Elementor\DB::is_built_with_elementor` is soft deprecated since 3.2.0
		// Use `Plugin::$instance->documents->get( $post_id )->is_built_with_elementor()` instead
		// return trx_addons_exists_elementor() && \Elementor\Plugin::instance()->db->is_built_with_elementor( $post_id );
		$rez = false;
		if ( trx_addons_exists_elementor() && ! empty( $post_id ) ) {
			$document = \Elementor\Plugin::instance()->documents->get( $post_id );
			if ( is_object( $document ) ) {
				$rez = $document->is_built_with_elementor();
			}
		}
		return $rez;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_elm_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_elm_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	function trx_addons_elm_load_scripts_front() {
		if ( trx_addons_exists_elementor() ) {
			if ( trx_addons_is_on( trx_addons_get_option('debug_mode') ) ) {
				wp_enqueue_style( 'trx_addons-elementor', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor.css'), array(), null );
			}
			if ( trx_addons_elm_is_preview() ) {
				wp_enqueue_style(  'trx_addons-msgbox', trx_addons_get_file_url('js/msgbox/msgbox.css'), array(), null );
				wp_enqueue_script( 'trx_addons-msgbox', trx_addons_get_file_url('js/msgbox/msgbox.js'), array('jquery'), null, true );
			}
		}
	}
}

// Load responsive styles for the frontend
if ( !function_exists( 'trx_addons_elm_load_responsive_styles' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_elm_load_responsive_styles', TRX_ADDONS_ENQUEUE_RESPONSIVE_PRIORITY);
	function trx_addons_elm_load_responsive_styles() {
		if ( trx_addons_exists_elementor() && trx_addons_is_on(trx_addons_get_option('debug_mode'))) {
			wp_enqueue_style( 'trx_addons-elementor-responsive', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor.responsive.css'), array(), null, trx_addons_media_for_load_css_responsive( 'elementor', 'lg' ) );
		}
	}
}

// Merge specific styles into single stylesheet
if ( !function_exists( 'trx_addons_elm_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_elm_merge_styles');
	function trx_addons_elm_merge_styles($list) {
		if ( trx_addons_exists_elementor() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'elementor/elementor.css' ] = true;
		}
		return $list;
	}
}


// Merge shortcode's specific styles to the single stylesheet (responsive)
if ( !function_exists( 'trx_addons_elm_merge_styles_responsive' ) ) {
	add_filter("trx_addons_filter_merge_styles_responsive", 'trx_addons_elm_merge_styles_responsive');
	function trx_addons_elm_merge_styles_responsive($list) {
		if ( trx_addons_exists_elementor() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'elementor/elementor.responsive.css' ] = true;
		}
		return $list;
	}
}

	
// Merge plugin's specific scripts to the single file
if ( !function_exists( 'trx_addons_elm_merge_scripts' ) ) {
	add_action("trx_addons_filter_merge_scripts", 'trx_addons_elm_merge_scripts');
	function trx_addons_elm_merge_scripts($list) {
		if ( trx_addons_exists_elementor() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'elementor/elementor.js' ] = true;
			$list[ TRX_ADDONS_PLUGIN_API . 'elementor/elementor-parallax.js' ] = true;
		}
		return $list;
	}
}

	
// Add plugin-specific slugs to the list of the scripts, that don't move to the footer and don't add 'defer' param
if ( !function_exists( 'trx_addons_elm_not_defer_scripts' ) ) {
	add_filter("trx_addons_filter_skip_move_scripts_down", 'trx_addons_elm_not_defer_scripts');
	add_filter("trx_addons_filter_skip_async_scripts_load", 'trx_addons_elm_not_defer_scripts');
	function trx_addons_elm_not_defer_scripts($list) {
		$list[] = 'elementor';
		$list[] = 'backbone';
		$list[] = 'underscore';
		return $list;
	}
}

// Add responsive sizes
if ( !function_exists( 'trx_addons_elm_sass_responsive' ) ) {
	add_filter("trx_addons_filter_responsive_sizes", 'trx_addons_elm_sass_responsive', 11);
	function trx_addons_elm_sass_responsive($list) {
		if (!isset($list['md_lg']))
			$list['md_lg'] = array(
									'min' => $list['sm']['max']+1,
									'max' => $list['lg']['max']
									);
		return $list;
	}
}

// Load required styles and scripts for Elementor Editor mode
if ( !function_exists( 'trx_addons_elm_editor_load_scripts' ) ) {
	add_action("elementor/editor/before_enqueue_scripts", 'trx_addons_elm_editor_load_scripts');
	function trx_addons_elm_editor_load_scripts() {
		trx_addons_load_scripts_admin(true);
		trx_addons_localize_scripts_admin();
		wp_enqueue_style(  'trx_addons-elementor-editor', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor.editor.css'), array(), null );
		wp_enqueue_script( 'trx_addons-elementor-editor', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor.editor.js'), array('jquery'), null, true );
		do_action('trx_addons_action_pagebuilder_admin_scripts');
	}
}

// Add vars to the Elementors editor
if ( !function_exists( 'trx_addons_elm_localize_admin_scripts' ) ) {
	add_filter( 'trx_addons_filter_localize_script_admin',	'trx_addons_elm_localize_admin_scripts');
	function trx_addons_elm_localize_admin_scripts($vars = array()) {
		$vars['add_hide_on_xxx'] = trx_addons_get_setting('add_hide_on_xxx');
		return $vars;
	}
}

// Load required scripts for Elementor Preview mode
if ( !function_exists( 'trx_addons_elm_preview_load_scripts' ) ) {
	add_action("elementor/frontend/after_enqueue_scripts", 'trx_addons_elm_preview_load_scripts');
	function trx_addons_elm_preview_load_scripts() {
		if ( trx_addons_is_on(trx_addons_get_option('debug_mode')) ) {
			wp_enqueue_script( 'trx_addons-elementor-preview', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor.js'), array('jquery'), null, true );
			wp_enqueue_script( 'trx_addons-elementor-parallax', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'elementor/elementor-parallax.js'), array('jquery'), null, true );
		}
		trx_addons_enqueue_tweenmax();
		if ( trx_addons_elm_is_preview() ) {
			do_action('trx_addons_action_pagebuilder_preview_scripts', 'elementor');
		}
	}
}

// Add shortcode's specific vars into JS storage
if ( !function_exists( 'trx_addons_elm_localize_script' ) ) {
	add_filter("trx_addons_filter_localize_script", 'trx_addons_elm_localize_script');
	function trx_addons_elm_localize_script($vars) {
		$vars['elementor_stretched_section_container'] = get_option('elementor_stretched_section_container');
		$vars['pagebuilder_preview_mode'] = ! empty( $vars['pagebuilder_preview_mode'] ) || trx_addons_elm_is_preview();
		// List of selectors for items inside block with specified motion effect (entrance animation)
		// and animated separately (item by item or random, not a whole block)
		$vars['elementor_animate_items'] = join( ',', apply_filters( 'trx_addons_filter_elementor_animate_items', array(
																		'.elementor-heading-title',
																		'.sc_item_subtitle',
																		'.sc_item_title',
																		'.sc_item_descr',
																		'.sc_item_posts_container + .sc_item_button',
																		'.sc_item_button.sc_title_button',
																		'.social_item',
																		'nav > ul > li'
												) ) );
		$vars['msg_change_layout'] = esc_html__( 'After changing the layout, the page will be reloaded! Continue?', 'trx_addons' );
		$vars['msg_change_layout_caption'] = esc_html__( 'Change layout', 'trx_addons' );
		return $vars;
	}
}

// Return url with post edit link
if ( !function_exists( 'trx_addons_elm_post_edit_link' ) ) {
	add_filter( 'trx_addons_filter_post_edit_link', 'trx_addons_elm_post_edit_link', 10, 2 );
	function trx_addons_elm_post_edit_link( $link, $post_id ) {
		if ( trx_addons_is_built_with_elementor( $post_id ) ) {
			$link = str_replace( 'action=edit', 'action=elementor', $link );
		}
		return $link;
	}
}


// Return list of saved templates.
// @param type - all | page | section
if ( !function_exists( 'trx_addons_get_list_elementor_templates' ) ) {
	function trx_addons_get_list_elementor_templates( $not_selected=false, $type='all', $order='ID' ) {
		if ( trx_addons_exists_elementor() ) {
			$args = array(
						'post_type' => 'elementor_library',
						'posts_per_page' => -1,
						'orderby' => $order,
						'order' => 'asc',
						'not_selected' => $not_selected
						);
			if ( $type != 'all' ) {
				$args['taxonomy'] = 'elementor_library_type';
				$args['taxonomy_value'] = $type;
			}
			$list = trx_addons_get_list_posts(false, $args );
		} else {
			$list = array();
		}
		return $list;
	}
}


// Fix for the Elementor Pro widget wrappers
//----------------------------------------------
if ( !function_exists( 'trx_addons_elm_pro_woocommerce_wordpress_widget_css_class' ) ) {
	add_filter( 'elementor/widgets/wordpress/widget_args', 'trx_addons_elm_pro_woocommerce_wordpress_widget_css_class', 11, 2 );
	function trx_addons_elm_pro_woocommerce_wordpress_widget_css_class( $default_widget_args, $widget ) {
		if ( is_object( $widget ) ) {
			$widget_instance = $widget->get_widget_instance();
			if ( ! empty( $widget_instance->widget_cssclass ) ) {
				$open_tag = sprintf( '<div class="%s">', $widget_instance->widget_cssclass );
				if ( substr( $default_widget_args['before_widget'], -strlen( $open_tag ) ) == $open_tag
					&& $default_widget_args['after_widget'] == '</aside></div>'
				) {
					$default_widget_args['after_widget'] = '</div></aside>';
				}
			}
		}
		return $default_widget_args;
	}
}


// Change "Go Pro" links
//----------------------------------------------
if ( !function_exists( 'trx_addons_elm_change_gopro_plugins' ) && defined('ELEMENTOR_PLUGIN_BASE') ) {
	add_filter( 'plugin_action_links_' . ELEMENTOR_PLUGIN_BASE, 'trx_addons_elm_change_gopro_plugins', 11 );
	function trx_addons_elm_change_gopro_plugins($links) {
		if (!empty($links['go_pro']) && preg_match('/href="([^"]*)"/', $links['go_pro'], $matches) && !empty($matches[1])) {
			$links['go_pro'] = ELEMENTOR_GO_PRO_METHOD == 'link'
								? ELEMENTOR_GO_PRO_LINK
								: str_replace($matches[1], trx_addons_add_to_url($matches[1], array('ref' => ELEMENTOR_GO_PRO_REF)), $links['go_pro']);
		}
		return $links;
	}
}
if ( !function_exists('trx_addons_elm_change_gopro_dashboard') ) {
	add_filter( 'elementor/admin/dashboard_overview_widget/footer_actions', 'trx_addons_elm_change_gopro_dashboard', 11 );
	function trx_addons_elm_change_gopro_dashboard($actions) {
		if (!empty($actions['go-pro']['link'])) {
			$actions['go-pro']['link'] = ELEMENTOR_GO_PRO_METHOD == 'link'
											? ELEMENTOR_GO_PRO_LINK
											: trx_addons_add_to_url($actions['go-pro']['link'], array('ref' => ELEMENTOR_GO_PRO_REF));
		}
		return $actions;
	}
}
if ( !function_exists('trx_addons_elm_change_gopro_menu') ) {
	add_filter( 'wp_redirect', 'trx_addons_elm_change_gopro_menu', 11, 2 );
	function trx_addons_elm_change_gopro_menu($link, $status=0) {
		if ( strpos($link, '//elementor.com/pro/') !== false || strpos($link, '//go.elementor.com/') !== false ) {
			$link = ELEMENTOR_GO_PRO_METHOD == 'link'
								? ELEMENTOR_GO_PRO_LINK
								: trx_addons_add_to_url($link, array('ref' => ELEMENTOR_GO_PRO_REF));
		}
		return $link;
	}
}
if ( !function_exists('trx_addons_elm_change_gopro_control') ) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_change_gopro_control', 10, 3 );
	function trx_addons_elm_change_gopro_control($element, $section_id, $args) {
		if (!is_object($element)) return;
		$el_name = $element->get_name();
		if ( $section_id == 'section_custom_css_pro') {
			$control = $element->get_controls( 'custom_css_pro' );
			if (!empty($control['raw']) && strpos($control['raw'], '//elementor.com/pro/') !== false) {
				$control['raw'] = preg_replace_callback(
					'~href="([^"]*)"~',
					function($matches) {
						return 'href="' . ( ELEMENTOR_GO_PRO_METHOD == 'link'
											? ELEMENTOR_GO_PRO_LINK
											: trx_addons_add_to_url( $matches[1], array('ref' => ELEMENTOR_GO_PRO_REF) )
											)
										. '"';
					},
					$control['raw']
				);
				$element->update_control( 'custom_css_pro', array(
									'raw' => $control['raw']
								) );
			}
		}
	}
}
if ( !function_exists('trx_addons_elm_change_gopro_url_in_config') ) {
	add_filter( 'elementor/editor/localize_settings', 'trx_addons_elm_change_gopro_url_in_config' );
	function trx_addons_elm_change_gopro_url_in_config($config) {
		if ( is_array( $config ) ) {
			foreach( $config as $k => $v ) {
				if ( is_array( $v ) ) {
					$config[ $k ] = trx_addons_elm_change_gopro_url_in_config( $v );
				} else if ( is_string( $v )
							&& strpos( $v, ' ' ) === false
							&& strpos( $v, '<' ) === false
							&& strpos( $v, '>' ) === false
							&& strpos( $v, '://' ) !== false
							&& strpos( $v, 'elementor.com/' ) !== false
				) {
					$config[ $k ] = ELEMENTOR_GO_PRO_METHOD == 'link'
										? ELEMENTOR_GO_PRO_LINK
										: trx_addons_add_to_url( $v, array( 'ref' => ELEMENTOR_GO_PRO_REF ) );
				}
			}
		}
		return $config;
	}
}
if ( !function_exists('trx_addons_elm_change_gopro_url_in_js') ) {
	add_filter( 'trx_addons_filter_localize_script', 'trx_addons_elm_change_gopro_url_in_js' );
	add_filter( 'trx_addons_filter_localize_script_admin', 'trx_addons_elm_change_gopro_url_in_js' );
	function trx_addons_elm_change_gopro_url_in_js($vars) {
		if ( ! isset( $vars['add_to_links_url'] ) ) {
			$vars['add_to_links_url'] = array();
		}
		$args = array(
			//'page' => 'admin.php?page=elementor',
			'mask' => 'elementor.com/',
		);
		if ( ELEMENTOR_GO_PRO_METHOD == 'link' ) {
			$args['link'] = ELEMENTOR_GO_PRO_LINK;
		} else {
			$args['args'] = array( 'ref' => ELEMENTOR_GO_PRO_REF );
		}
		$vars['add_to_links_url'][] = $args;
		return $vars;
	}
}

// Disable menu cache on the Elementor's preview screen
//-------------------------------------------------------------------------------------------------------
if (!function_exists('trx_addons_elm_use_menu_cache')) {
	add_filter('trx_addons_add_menu_cache', 'trx_addons_elm_use_menu_cache');
	function trx_addons_elm_use_menu_cache($use, $args=array()) {
		if ( trx_addons_elm_is_preview() ) {
			$use = false;
		}
		return $use;
	}
}


// Generate action on save document from Elementor Editor
//---------------------------------------------------------------------------------------------------------
if ( !function_exists( 'trx_addons_elm_save_post_from_editor' ) ) {
	add_action( 'wp_ajax_elementor_ajax', 'trx_addons_elm_save_post_from_editor' );
	function trx_addons_elm_save_post_from_editor() {
		$id = trx_addons_get_value_gp('editor_post_id');
		if ( (int) $id > 0 ) {
			$actions = trim( trx_addons_get_value_gp('actions') );
			if ( ! empty($actions) && substr($actions, 0, 1) == '{' && substr($actions, -1) == '}' ) {
				$actions = json_decode( $actions, true);
				if ( is_array($actions) && ! empty($actions['save_builder']) ) {
					do_action( 'trx_addons_action_save_post_from_elementor', $id, $actions );
				}
			}
		}
	}
}


// Add Elementor's filter 'the_content' to the posts inside shortcodes (like "Blogger", "Services", etc)
//-------------------------------------------------------------------------------------------------------

// Add handler
if ( ! function_exists( 'trx_addons_elm_before_full_post_content' ) ) {
	add_action( 'trx_addons_action_before_full_post_content', 'trx_addons_elm_before_full_post_content' );
	function trx_addons_elm_before_full_post_content() {
		if ( trx_addons_is_built_with_elementor( get_the_ID() ) && ! has_filter( 'the_content', array( \Elementor\Plugin::instance()->frontend, 'apply_builder_in_content' ) ) ) {
			set_query_var( 'trx_addons_elm_set_the_content_handler', 1 );
			add_filter( 'the_content', array( \Elementor\Plugin::instance()->frontend, 'apply_builder_in_content' ), \Elementor\Plugin::instance()->frontend->THE_CONTENT_FILTER_PRIORITY );
		}
	}
}

// Remove handler
if ( ! function_exists( 'trx_addons_elm_after_full_post_content' ) ) {
	add_action( 'trx_addons_action_after_full_post_content', 'trx_addons_elm_after_full_post_content' );
	function trx_addons_elm_after_full_post_content() {
		if ( trx_addons_exists_elementor() && get_query_var( 'trx_addons_elm_set_the_content_handler' ) == 1 ) {
			remove_filter( 'the_content', array( \Elementor\Plugin::instance()->frontend, 'apply_builder_in_content' ), \Elementor\Plugin::instance()->frontend->THE_CONTENT_FILTER_PRIORITY );
		}
	}
}


// Display additional attributes for some shortcodes
//-------------------------------------------------------------------------------------------------------
if (!function_exists('trx_addons_elm_sc_show_attributes')) {
	add_action( 'trx_addons_action_sc_show_attributes', 'trx_addons_elm_sc_show_attributes', 10, 3 );
	function trx_addons_elm_sc_show_attributes($sc, $args, $area) {
		if ( ! empty( $args['sc_elementor_object'] ) && is_object( $args['sc_elementor_object'] ) ) {
			echo ' ' . $args['sc_elementor_object']->get_render_attribute_string( $area );
		}
	}
}

// Remove elementor object from parameters before serialization
if ( !function_exists( 'trx_addons_elm_remove_object_from_args' ) ) {
	add_filter( 'trx_addons_filter_sc_args_to_serialize', 'trx_addons_elm_remove_object_from_args' );
	function trx_addons_elm_remove_object_from_args($args) {
		if ( isset( $args['sc_elementor_object'] ) ) {
			unset( $args['sc_elementor_object'] );
		}
		return $args;
	}
}


// Fixes for compatibility with new versions of Elementor
//--------------------------------------------------------

// New way: Convert an array with args to the controls of the ::REPEATER object
if ( ! function_exists( 'trx_addons_elm_get_repeater_controls' ) ) {
	add_filter( 'trx_addons_sc_param_group_params', 'trx_addons_elm_get_repeater_controls', 999, 2 );
	function trx_addons_elm_get_repeater_controls( $args ) {
		if ( trx_addons_exists_elementor() && ! empty( $args[0]['name'] ) && ! isset( $args['_id'] ) ) {
			$repeater = new \Elementor\Repeater();
			if ( is_array( $args ) ) {
				foreach ( $args as $k => $v ) {
					if ( ! empty( $v['name'] ) ) {
						$k = $v['name'];
					}
					$repeater->add_control( $k, $v );
				}
			}
			return $repeater->get_controls();
		}
		return $args;
	}
}

/*
// Old way:
// Prepare group atts for the new Elementor version: make associative array from list by key 'name'
// After the update Elementor 3.1.0+ (or near) internal structure of field type ::REPEATER was changed
// (fields list was converted to the associative array)
// and as result js-errors appears in the Elementor Editor:
// "Cannot read property 'global' of undefined"
// "TypeError: undefined is not an object (evaluating 't[o].global')"
if ( ! function_exists( 'trx_addons_elm_prepare_group_params' ) ) {
	add_filter( 'trx_addons_sc_param_group_params', 'trx_addons_elm_prepare_group_params', 999, 2 );
	function trx_addons_elm_prepare_group_params( $args, $sc = '' ) {
		if ( is_array( $args ) && ! empty( $args[0]['name'] ) ) {
			$new = array();
			foreach( $args as $item ) {
				if ( ! empty( $item['name'] ) ) {
					$new[ $item['name'] ] = $item;
				}
			}
			$args = $new;
		}
		return $args;
	}
}

// Old way:
// Add a hidden field '_id' to the group atts for the new Elementor version
if ( !function_exists( 'trx_addons_elm_prepare_group_params2' ) ) {
	add_filter( 'trx_addons_sc_param_group_params', 'trx_addons_elm_prepare_group_params2', 999, 2 );
	function trx_addons_elm_prepare_group_params2( $args, $sc = '' ) {
		if ( is_array( $args ) ) {
			$tab = '';
			$name = '';
			foreach( $args as $item ) {
				// If it's not an Elementor's arguments
				if ( empty( $item['name'] ) ) {
					break;
				} else if ( empty( $name ) ) {
					$name = $item['name'];
				}
				if ( empty( $tab ) && ! empty( $item['tab'] ) ) {
					$tab = $item['tab'];
					break;
				}
			}
			if ( ! empty( $name ) && ! isset( $args['_id'] ) ) {
				if ( ! isset( $args['_id'] ) && class_exists( '\Elementor\Controls_Manager' ) ) {
					$_id = array(
								'type' => \Elementor\Controls_Manager::HIDDEN,
								'name' => '_id',
								'default' => ''
								);
					if ( ! empty( $tab ) ) {
						$_id['tab'] = $tab;
					}
					$args = array_merge( array( '_id' => $_id ), $args );
				}
			}
		}
		return $args;
	}
}
*/

// Prepare global atts for the new Elementor version: add array keys by 'name' from __globals__
// After the update Elementor 3.0+ (or later) for settings with type ::COLOR global selector appears
// Color value from this selects is not placed to the appropriate settings
if ( !function_exists( 'trx_addons_elm_prepare_global_params' ) ) {
	add_filter( 'trx_addons_filter_sc_prepare_atts', 'trx_addons_elm_prepare_global_params', 10, 2 );
	function trx_addons_elm_prepare_global_params( $args, $sc = '' ) {
		foreach ( $args as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( is_string( $k ) && $k == '__globals__' ) {
					foreach ( $v as $k1 => $v1 ) {
						if ( ! empty( $v1 ) ) {
							$args[ $k1 ] = apply_filters( 'trx_addons_filter_prepare_global_param', $v1, $k1 );
						}
					}
				} else {
					$args[ $k ] = trx_addons_elm_prepare_global_params( $v, $sc );
				}
			}
		}
		return $args;
	}
}

// Return CSS-var from global color key, i.e. 'globals/colors?id=1855627f'
if ( !function_exists( 'trx_addons_elm_prepare_global_color' ) ) {
	add_filter( 'trx_addons_filter_prepare_global_param', 'trx_addons_elm_prepare_global_color', 10, 2 );
	function trx_addons_elm_prepare_global_color( $value, $key ) {
		$prefix = 'globals/colors?id=';
		if ( strpos( $value, $prefix ) === 0 ) {
			$id = str_replace( $prefix, '', $value );
			$value = "var(--e-global-color-{$id})";
		}
		return $value;
	}
}


// Remove conditions where key contain unavailable characters.
// After the update Elementor 3.4.1 js-errors appears in the console and the Editor stop loading
// if the condition of any option contains a key with characters outside the range a-z 0-9 - _ [ ] !
// a mask '/([a-z_\-0-9]+)(?:\[([a-z_]+)])?(!?)$/i' is used in the editor.js and controls-stack.php
// This issue is resolved in Elementor 3.4.2 (according to it author)
// I leave this code commented for future cases (if appears)
/*
if ( !function_exists('trx_addons_elm_remove_unavailable_conditions') ) {
	add_action( 'elementor/element/after_section_end', 'trx_addons_elm_remove_unavailable_conditions', 9999, 3 );
	function trx_addons_elm_remove_unavailable_conditions( $element, $section_id='', $args='' ) {
		if ( ! is_object( $element ) ) return;
		$controls = $element->get_controls();
		if ( is_array( $controls ) ) {
			foreach( $controls as $k => $v ) {
				if ( ! empty( $v['condition'] ) && is_array( $v['condition'] ) ) {
					$chg = false;
					$condition = array();
					foreach( $v['condition'] as $k1 => $v1 ) {
						// If current condition contains a selector to the field  "Page template" - replace it with 'template'
						if ( strpos( $k1, '.editor-page-attributes__template' ) !== false || strpos( $k1, '#page_template' ) !== false ) {
							$condition['template'] = $v1;
							$chg = true;
						// Else if current condition contains any other selector - remove it
						} else if ( strpos( $k1, ' ' ) !== false || strpos( $k1, '.' ) !== false || strpos( $k1, '#' ) !== false ) {
							$chg = true;
						// Else - leave all other conditions unchanged
						} else {
							$condition[ $k1 ] = $v1;
						}
					}
					// Update 'condition' in the current control if changed
					if ( $chg ) {
						$element->update_control( $k, array(
										'condition' => $condition
									) );
					}
				}
			}
		}
	}
}
*/

// Set a gap 'Extended' for sections as a default value
if ( ! function_exists( 'trx_addons_elm_set_default_gap_extended' ) ) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_set_default_gap_extended', 10, 3 );
	function trx_addons_elm_set_default_gap_extended( $element, $section_id, $args ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'section' == $el_name && 'section_layout' === $section_id ) {
				$element->update_control(
					'gap', array(
						'default' => 'extended',
					)
				);
			}
		}
	}
}

// Move a column paddings from the .elementor-element-wrap to the .elementor-column-wrap to compatibility with old themes
if ( ! function_exists( 'trx_addons_elm_move_paddings_to_column_wrap' ) ) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_move_paddings_to_column_wrap', 10, 3 );
	function trx_addons_elm_move_paddings_to_column_wrap( $element, $section_id, $args ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'column' == $el_name && 'section_advanced' == $section_id ) {
				$element->update_responsive_control( 'padding', array(
											'selectors' => array(
												'{{WRAPPER}} > .elementor-element-populated.elementor-column-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',	// Elm 2.9- (or DOM Optimization == Inactive)
												'{{WRAPPER}} > .elementor-element-populated.elementor-widget-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',	// Elm 3.0+
											)
										) );
			}
		}
	}
}

// Fix for Elementor 3.6.8+ - prevent to redirect to the Elementor's setup wizard after the plugin activation
// until theme-specific plugins are installed and activated completely
if ( !function_exists( 'trx_addons_elm_prevent_redirect_to_wizard_after_activation' ) ) {
	add_action( 'admin_init', 'trx_addons_elm_prevent_redirect_to_wizard_after_activation', 1 );
	function trx_addons_elm_prevent_redirect_to_wizard_after_activation() {
		if ( trx_addons_get_value_gp( 'page' ) == 'trx_addons_theme_panel' && get_transient( 'elementor_activation_redirect' ) ) {
			delete_transient( 'elementor_activation_redirect' );
		}
	}
}

// Wrapper for Elementor widgets registration to prevent a deprecation warning
if ( !function_exists( 'trx_addons_elm_register_widget' ) ) {
	function trx_addons_elm_register_widget( $widget_class ) {
		// Plugin::$instance->widgets_manager->register_widget_type() is soft deprecated since 3.5.0
		// Use Plugin::$instance->widgets_manager->register() instead
		if ( class_exists( $widget_class ) ) {
			\Elementor\Plugin::instance()->widgets_manager->register( new $widget_class() );
		}
	}
}


// Init Elementor's support
//--------------------------------------------------------

// Set Elementor's options at once
if ( !function_exists('trx_addons_elm_init_once') ) {
	add_action( 'init', 'trx_addons_elm_init_once', 2 );
	function trx_addons_elm_init_once() {
		if (trx_addons_exists_elementor() && !get_option('trx_addons_setup_elementor_options', false)) {
			// Set components specific values to the Elementor's options
			do_action('trx_addons_action_set_elementor_options');
			// Set flag to prevent change Elementor's options again
			update_option('trx_addons_setup_elementor_options', 1);
		}
	}
}

// Add categories for widgets, shortcodes, etc.
if (!function_exists('trx_addons_elm_add_categories')) {
	add_action( 'elementor/elements/categories_registered', 'trx_addons_elm_add_categories' );
	function trx_addons_elm_add_categories($mgr = null) {

		static $added = false;

		if (!$added) {

			if ($mgr == null) $mgr = \Elementor\Plugin::instance()->elements_manager;
			
			// Add a custom category for ThemeREX Addons Shortcodes
			$mgr->add_category( 
				'trx_addons-elements',
				array(
					'title' => __( 'ThemeREX Addons Elements', 'trx_addons' ),
					'icon' => 'eicon-apps', //default icon
					'active' => true,
				)
			);

			// Add a custom category for ThemeREX Addons Widgets
			$mgr->add_category( 
				'trx_addons-widgets',
				array(
					'title' => __( 'ThemeREX Addons Widgets', 'trx_addons' ),
					'icon' => 'eicon-gallery-grid', //default icon
					'active' => false,
				)
			);

			// Add a custom category for ThemeREX Addons CPT
			$mgr->add_category( 
				'trx_addons-cpt',
				array(
					'title' => __( 'ThemeREX Addons Extensions', 'trx_addons' ),
					'icon' => 'eicon-gallery-grid', //default icon
					'active' => false,
				)
			);

			// Add a custom category for third-party shortcodes
			$mgr->add_category( 
				'trx_addons-support',
				array(
					'title' => __( 'ThemeREX Addons Support', 'trx_addons' ),
					'icon' => 'eicon-woocommerce', //default icon
					'active' => false,
				)
			);

			$added = true;
		}
	}
}


// Replace widget's args with theme-specific args
if ( !function_exists( 'trx_addons_elm_wordpress_widget_args' ) ) {
	add_filter( 'elementor/widgets/wordpress/widget_args', 'trx_addons_elm_wordpress_widget_args', 10, 2 );
	function trx_addons_elm_wordpress_widget_args($widget_args, $widget) {
		return trx_addons_prepare_widgets_args($widget->get_name(), $widget->get_name(), $widget_args);
	}
}


// Load template to create our classes with widgets
if (!function_exists('trx_addons_elm_init')) {
	add_action( 'elementor/init', 'trx_addons_elm_init' );
	function trx_addons_elm_init() {

		// Add categories (for old Elementor)
		trx_addons_elm_add_categories();

		// Define class for our shortcodes and widgets
		require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'elementor/elementor-class-widget.php';
	}
}

// Return 'icon' parameter
if ( !function_exists( 'trx_addons_get_icon_param' ) ) {
	function trx_addons_get_icon_param($name='icon', $only_socials=false, $style='') {
		$idx = $name != 'icon' ? $name : 0;
		if (trx_addons_get_setting('icons_selector') == 'internal') {
			if (empty($style)) {
				$style = $only_socials ? trx_addons_get_setting('socials_type') : trx_addons_get_setting('icons_type');
			}

			$is_edit_mode = trx_addons_elm_is_edit_mode();

			$params = array(
							$idx => array(
								'name' => $name,
								'type' => 'trx_icons',
								'label' => __( 'Icon', 'trx_addons' ),
								'label_block' => false,
								'default' => '',
								'options' => ! $is_edit_mode ? array() : trx_addons_get_list_icons($style),
								'style' => $style
							)
						);
		} else {
			$params = array(
							$idx => array(
								'name' => $name,
								'type' => \Elementor\Controls_Manager::ICON,
								'label' => __( 'Icon', 'trx_addons' ),
								'label_block' => false,
								'default' => '',
							)
						);
		}
		return apply_filters('trx_addons_filter_elementor_add_icon_param', $params, $only_socials, $style);
	}
}

// Check if icon name is from the Elementor icons
if ( !function_exists( 'trx_addons_is_elementor_icon' ) ) {
	function trx_addons_is_elementor_icon($icon) {
		$icon = trx_addons_elementor_get_settings_icon( $icon );
		return !empty($icon) && strpos($icon, 'fa ') !== false;
	}
}

// Get icon from param before and after v.2.6.0
if ( !function_exists( 'trx_addons_elementor_get_settings_icon' ) ) {
	function trx_addons_elementor_get_settings_icon( $icon ) {
		return is_array($icon)
						? ( !empty( $icon['icon'])
							? $icon['icon']
							: ''
							)
						: $icon;
	}
}

// Set icon in param before and after v.2.6.0
if ( !function_exists( 'trx_addons_elementor_set_settings_icon' ) ) {
	function trx_addons_elementor_set_settings_icon( $icon ) {
		return defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' )
					? array( 'icon' => $icon )
					: $icon;
	}
}


// Output inline CSS
// if current action is 'wp_ajax_elementor_render_widget' or 'admin_action_elementor' (old Elementor) or 'elementor_ajax' (new Elementor)
// (called from Elementor Editor via AJAX or first load page content to the Editor)
//---------------------------------------------------------------------------------------
if (!function_exists('trx_addons_elm_print_inline_css')) {
	add_filter( 'elementor/widget/render_content', 'trx_addons_elm_print_inline_css', 10, 2 );
	function trx_addons_elm_print_inline_css($content, $widget=null) {
		if (doing_action('wp_ajax_elementor_render_widget') || doing_action('admin_action_elementor') || doing_action('elementor_ajax') || doing_action('wp_ajax_elementor_ajax')) {
			$css = trx_addons_get_inline_css(true);
			if (!empty($css)) {
				$content .= sprintf('<style type="text/css">%s</style>', $css);
			}
		}
		return $content;
	}
}


// Register custom controls for Elementor
//------------------------------------------------------------------------
if (!function_exists('trx_addons_elm_register_custom_controls')) {
	add_action( 'elementor/controls/controls_registered', 'trx_addons_elm_register_custom_controls' );
	function trx_addons_elm_register_custom_controls( $controls_manager ) {
		$controls = array('trx_icons');
		foreach ( $controls as $control_id ) {
			$control_filename = str_replace('_', '-', $control_id);
			require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . "elementor/params/{$control_filename}/{$control_filename}.php";
			$class_name = 'Trx_Addons_Elementor_Control_' . ucwords( $control_id );
			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
				$controls_manager->register( new $class_name() );	
			} else {
				$controls_manager->register_control( $control_id, new $class_name() );
			}
		}
	}
}



//========================================================================
//  Stack Sections
//========================================================================

// Stack section
if (!function_exists('trx_addons_elm_add_params_stack_section')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_stack_section', 10, 3 );
	function trx_addons_elm_add_params_stack_section($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Stack section' to the sections
		if ( $el_name == 'section' && $section_id == 'section_advanced' ) {
			$element->add_control( 'stack_section', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __("Stack section", 'trx_addons'),
									'label_on' => __( 'On', 'trx_addons' ),
									'label_off' => __( 'Off', 'trx_addons' ),
									'return_value' => 'on',
									'prefix_class' => 'sc_stack_section_',
								) );
			$element->add_control( 'stack_section_effect', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Stack effect", 'trx_addons'),
									'options' => apply_filters( 'trx_addons_filter_stack_section_effects', array(
													'slide' => __( 'Slide', 'trx_addons' ),
													'fade' => __( 'Fade', 'trx_addons' ),
													) ),
									'default' => 'slide',
									'condition' => array(
										'stack_section' => array( 'on' )
									),
									'prefix_class' => 'sc_stack_section_effect_',
								) );
		}
	}
}



//========================================================================
//  Content width and alignment for Columns
//========================================================================

// Content width and alignment
if (!function_exists('trx_addons_elm_add_params_content_width_and_align')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_content_width_and_align', 10, 3 );
	function trx_addons_elm_add_params_content_width_and_align($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Content width' and 'Content alignment' to the columns
		// to enable align columns in the stretched rows on the page content area
		if ( $el_name == 'column' && $section_id == 'layout' ) {

			$is_edit_mode = trx_addons_elm_is_edit_mode();

			$element->add_responsive_control( 'content_width', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Content width", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_widths('none', false),
									'default' => 'none',
									'prefix_class' => 'sc%s_inner_width_',
								) );
			$element->add_responsive_control( 'content_align', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Content alignment", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : array(
										'inherit' => __("Default", 'trx_addons'),
										'left'    => __("Left", 'trx_addons'),
										'center'  => __("Center", 'trx_addons'),
										'right'   => __("Right", 'trx_addons'),
									),
									'default' => 'inherit',
									'prefix_class' => 'sc%s_content_align_',
								) );
		}
	}
}



//========================================================================
//  Gradient animation
//========================================================================

// Gradient animation for sections and columns
if (!function_exists('trx_addons_elm_add_params_gradient_animation')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_gradient_animation', 10, 3 );
	function trx_addons_elm_add_params_gradient_animation($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Gradient animation'
		if ( ($el_name == 'section' && $section_id == 'section_background')
			|| ($el_name == 'column' && $section_id == 'section_style')
			) {

			$element->add_control( 'gradient_animation', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Gradient animation", 'trx_addons'),
									'options' => array(
										'none'       => __("None", 'trx_addons'),
										'horizontal' => __("Horizontal", 'trx_addons'),
										'vertical'   => __("Vertical", 'trx_addons'),
										'diagonal'   => __("Diagonal", 'trx_addons'),
									),
									'default' => 'none',
									'condition' => array(
										'background_background' => array( 'gradient' ),
									),
									'prefix_class' => 'sc_gradient_animation_',
								) );

			$element->add_control( 'gradient_animation_speed', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Animation speed", 'trx_addons'),
									'options' => array(
										'slow'   => __("Slow", 'trx_addons'),
										'normal' => __("Normal", 'trx_addons'),
										'fast'   => __("Fast", 'trx_addons'),
									),
									'default' => 'normal',
									'condition' => array(
										'background_background' => array( 'gradient' ),
										'gradient_animation!' => array( 'none' ),
									),
									'prefix_class' => 'sc_gradient_speed_',
								) );
		}
	}
}


// Gradient animation for all other blocks
if (!function_exists('trx_addons_elm_add_params_gradient_animation_common')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_gradient_animation_common', 10, 3 );
	function trx_addons_elm_add_params_gradient_animation_common($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Gradient animation'
		if ( $el_name == 'common' && $section_id == '_section_background' ) {

			$element->add_control( 'gradient_animation', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Gradient animation", 'trx_addons'),
									'options' => array(
										'none'       => __("None", 'trx_addons'),
										'horizontal' => __("Horizontal", 'trx_addons'),
										'vertical'   => __("Vertical", 'trx_addons'),
										'diagonal'   => __("Diagonal", 'trx_addons'),
									),
									'default' => 'none',
									'condition' => array(
										'_background_background' => array( 'gradient' ),
									),
									'prefix_class' => 'sc_gradient_animation_',
								) );

			$element->add_control( 'gradient_animation_speed', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Animation speed", 'trx_addons'),
									'options' => array(
										'slow'   => __("Slow", 'trx_addons'),
										'normal' => __("Normal", 'trx_addons'),
										'fast'   => __("Fast", 'trx_addons'),
									),
									'default' => 'normal',
									'condition' => array(
										'_background_background' => array( 'gradient' ),
										'gradient_animation!' => array( 'none' ),
									),
									'prefix_class' => 'sc_gradient_speed_',
								) );
		}
	}
}



//========================================================================
//  Background image for Sections and Columns
//========================================================================

// Hide bg image
if (!function_exists('trx_addons_elm_add_params_hide_bg_image')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_hide_bg_image', 10, 3 );
	function trx_addons_elm_add_params_hide_bg_image($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Hide bg image on XXX' to the rows
		if ( ($el_name == 'section' && $section_id == 'section_background')
			|| ($el_name == 'column' && $section_id == 'section_style')
			) {

			$element->add_control( 'hide_bg_image_on_desktop', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __( 'Hide bg image on the desktop', 'trx_addons' ),
									'label_on' => __( 'Hide', 'trx_addons' ),
									'label_off' => __( 'Show', 'trx_addons' ),
									'return_value' => 'desktop',
									'prefix_class' => 'hide_bg_image_on_',
								) );
			$element->add_control( 'hide_bg_image_on_tablet', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __( 'Hide bg image on the tablet', 'trx_addons' ),
									'label_on' => __( 'Hide', 'trx_addons' ),
									'label_off' => __( 'Show', 'trx_addons' ),
									'return_value' => 'tablet',
									'prefix_class' => 'hide_bg_image_on_',
								) );
			$element->add_control( 'hide_bg_image_on_mobile', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __( 'Hide bg image on the mobile', 'trx_addons' ),
									'label_on' => __( 'Hide', 'trx_addons' ),
									'label_off' => __( 'Show', 'trx_addons' ),
									'return_value' => 'mobile',
									'prefix_class' => 'hide_bg_image_on_',
								) );
		}
	}
}


// Extend background
if (!function_exists('trx_addons_elm_add_params_extend_bg')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_extend_bg', 10, 3 );
	function trx_addons_elm_add_params_extend_bg($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Extend background' and 'Background mask' to the rows, columns and text-editor
		if ( ($el_name == 'section' && $section_id == 'section_background')
			|| ($el_name == 'column' && $section_id == 'section_style')
			|| ($el_name == 'text-editor' && $section_id == 'section_background')
			) {

			$is_edit_mode = trx_addons_elm_is_edit_mode();

			$element->add_control( 'extra_bg', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Extend background", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_extra_bg(''),
									'default' => '',
									'prefix_class' => 'sc_extra_bg_'
									) );
			$element->add_control( 'extra_bg_mask', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Background mask", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_extra_bg_mask(''),
									'default' => '',
									'prefix_class' => 'sc_bg_mask_'
									) );
		}
	}
}



//========================================================================
//  Positioning effects
//========================================================================

// Sections and Columns: Shift and push
if (!function_exists('trx_addons_elm_add_columns_position')) {
	add_action( 'elementor/element/before_section_start', 'trx_addons_elm_add_columns_position', 10, 3 );
	function trx_addons_elm_add_columns_position($element, $section_id, $args) {

		if ( !is_object($element) ) return;
		
		if ( in_array( $element->get_name(), array( 'section', 'column' ) ) && $section_id == '_section_responsive' ) {
			
			$element->start_controls_section( 'section_trx_layout',	array(
																		'tab' => !empty($args['tab']) ? $args['tab'] : \Elementor\Controls_Manager::TAB_ADVANCED,
																		'label' => __( 'Position', 'trx_addons' )
																	) );

			// Detect edit mode
			$is_edit_mode = trx_addons_elm_is_edit_mode();

			// Add 'Fix column' to the columns
			if ($element->get_name() == 'column') {
				$element->add_control( 'fix_column', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __( 'Fix column', 'trx_addons' ),
									'description' => wp_kses_data( __("Fix this column when page scrolling. Attention! At least one column in the row must have a greater height than this column", 'trx_addons') ),
									'label_on' => __( 'Fix', 'trx_addons' ),
									'label_off' => __( 'No', 'trx_addons' ),
									'return_value' => 'fixed',
									'prefix_class' => 'sc_column_',
									) );
			}
			$element->add_control( 'shift_x', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Shift block along the X-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_shift_x_'
									) );
			$element->add_control( 'shift_y', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Shift block along the Y-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_shift_y_'
									) );
			
			$element->add_control( 'push_x', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Push block along the X-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_push_x_'
									) );
			$element->add_control( 'push_y', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Push block along the Y-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_push_y_'
									) );
			
			$element->add_control( 'pull_x', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Pull next block along the X-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_pull_x_'
									) );
			$element->add_control( 'pull_y', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Pull next block along the Y-axis", 'trx_addons'),
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_content_shift(''),
									'default' => '',
									'prefix_class' => 'sc_pull_y_'
									) );

			$element->end_controls_section();
		}
	}
}


// Any: Fly params to widgets
if (!function_exists('trx_addons_elm_add_params_fly')) {
	add_action( 'elementor/element/before_section_start', 'trx_addons_elm_add_params_fly', 10, 3 );
	add_action( 'elementor/widget/before_section_start', 'trx_addons_elm_add_params_fly', 10, 3 );
	function trx_addons_elm_add_params_fly($element, $section_id, $args) {

		if ( !is_object($element) ) return;

		if ( in_array( $element->get_name(), array( 'section', 'column', 'common' ) ) && $section_id == '_section_responsive' ) {

			// Detect edit mode
			$is_edit_mode = trx_addons_elm_is_edit_mode();

			// Register controls
			$element->start_controls_section( 'section_trx_fly', array(
																		'tab' => !empty($args['tab']) ? $args['tab'] : \Elementor\Controls_Manager::TAB_ADVANCED,
																		'label' => __( 'Fly', 'trx_addons' )
																	) );
			$element->add_responsive_control(
				'fly',
				array(
					'label' => __( 'Fly', 'trx_addons' ),
					'label_block' => false,
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => ! $is_edit_mode ? array() : array_merge(
									array('static' => __('Static', 'trx_addons')),
									array('custom' => __('Custom', 'trx_addons')),
									trx_addons_get_list_sc_positions()
								),
					'default' => 'static',
					'prefix_class' => 'sc%s_fly_',
				)
			);
			$coord = array(
							'label' => __( 'Left', 'trx_addons' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'default' => array(
								'size' => '',
								'unit' => 'px'
							),
							'size_units' => array( 'px', 'em', '%' ),
							'range' => array(
								'px' => array(
									'min' => -500,
									'max' => 500
								),
								'em' => array(
									'min' => -50,
									'max' => 50
								),
								'%' => array(
									'min' => -100,
									'max' => 100
								)
							),
							'condition' => array(
								'fly' => array( 'custom', 'tl', 'tr', 'bl', 'br' )
							),
							'selectors' => array(
								'{{WRAPPER}}' => 'left: {{SIZE}}{{UNIT}};'
							),
						);
			$element->add_responsive_control( 'fly_left', $coord );
			$coord['label'] = __( 'Right', 'trx_addons' );
			$coord['selectors'] = array( '{{WRAPPER}}' => 'right: {{SIZE}}{{UNIT}};' );
			$element->add_responsive_control( 'fly_right', $coord );
			$coord['label'] = __( 'Top', 'trx_addons' );
			$coord['selectors'] = array( '{{WRAPPER}}' => 'top: {{SIZE}}{{UNIT}};' );
			$element->add_responsive_control( 'fly_top', $coord );
			$coord['label'] = __( 'Bottom', 'trx_addons' );
			$coord['selectors'] = array( '{{WRAPPER}}' => 'bottom: {{SIZE}}{{UNIT}};' );
			$element->add_responsive_control( 'fly_bottom', $coord );

			$element->add_responsive_control( 'fly_scale', array(
													'label' => __( 'Scale', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => '',
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => 0,
															'max' => 10,
															'step' => 0.1
														)
													),
													'selectors' => array(
														'{{WRAPPER}} .elementor-widget-container' => 'transform: scale({{SIZE}}, {{SIZE}});'
													),
									) );

			$element->add_responsive_control( 'fly_rotate', array(
													'label' => __( 'Rotation (in deg)', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => '',
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => -360,
															'max' => 360,
															'step' => 1
														)
													),
													'selectors' => array(
														'{{WRAPPER}} .elementor-widget-container' => 'transform: rotate({{SIZE}}deg);'
													),
									) );

			$element->end_controls_section();
		}
	}
}


// Any: Animation type
if (!function_exists('trx_addons_elm_add_params_animation_type')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_animation_type', 10, 3 );
	function trx_addons_elm_add_params_animation_type($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		if ( $section_id == 'section_effects' && $el_name == 'common' ) {
			$element->add_control( '_animation_type', array(
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => __("Animation type", 'trx_addons'),
				'label_block' => false,
				'description' => __("Animate whole block or split animation by items (if possible)", 'trx_addons'),
				'options' => array(
					'block'     => __( 'Whole block', 'trx_addons' ),
					'sequental' => __( 'Item by item', 'trx_addons' ),
					'random'    => __( 'Random items', 'trx_addons' ),
				),
				'condition' => array(
					'_animation!' => array( '', 'none' )
				),
				'default' => 'block',
				'prefix_class' => 'animation_type_'
			) );
		}
	}
}


// Any: Hide on XXX
if (!function_exists('trx_addons_elm_add_params_hide_on_xxx')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_hide_on_xxx', 10, 3 );
	function trx_addons_elm_add_params_hide_on_xxx($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Hide on XXX' to the any elements
		$add_hide_on_xxx = trx_addons_get_setting('add_hide_on_xxx');
		if ( ! trx_addons_is_off($add_hide_on_xxx) && class_exists( 'TRX_Addons_Elementor_Widget' ) ) {
			if ($section_id == '_section_responsive') { // && $el_name == 'section'
				$params = TRX_Addons_Elementor_Widget::get_hide_param(false);
				if (is_array($params)) {
					if ($add_hide_on_xxx == 'add') {
						$element->add_control(
							'trx_addons_responsive_heading',
							array(
								'label' => __( 'Theme-specific params', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							)
						);
						$element->add_control(
							'trx_addons_responsive_description',
							array(
								'raw' => __( "Theme-specific parameters - you can use them instead of the Elementor's parameters above.", 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::RAW_HTML,
								'content_classes' => 'elementor-descriptor',
							)
						);
					}
					foreach ($params as $p) {
						$element->add_control( $p['name'], array_merge($p, array(
																				'return_value' => $p['name'],
																				'prefix_class' => 'sc_layouts_',
																				))
											);
					}
				}
			}
		}
	}
}



//========================================================================
//  Tabs: (from Elementor core widgets)
//========================================================================

// Tabs: Open on hover
if (!function_exists('trx_addons_elm_add_params_tabs_open_on_hover')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_tabs_open_on_hover', 10, 3 );
	function trx_addons_elm_add_params_tabs_open_on_hover($element, $section_id, $args) {

		if ( ! is_object($element) ) return;
		
		$el_name = $element->get_name();

		// Add 'Open on hover' to the tabs
		if ( $el_name == 'tabs' && $section_id == 'section_tabs' ) {
			$element->add_control( 'open_on_hover', array(
									'type' => \Elementor\Controls_Manager::SWITCHER,
									'label' => __("Open on hover", 'trx_addons'),
									'label_on' => __( 'On', 'trx_addons' ),
									'label_off' => __( 'Off', 'trx_addons' ),
									'return_value' => 'on',
									'prefix_class' => 'sc_tabs_open_on_hover_',
								) );
		}
	}
}


// Tabs: Icon position
if (!function_exists('trx_addons_elm_add_params_tabs_icon_position')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_tabs_icon_position', 10, 3 );
	function trx_addons_elm_add_params_tabs_icon_position($element, $section_id, $args) {

		if ( ! trx_addons_get_option('sc_tabs_layouts') || ! is_object($element) ) return;
		
		$el_name = $element->get_name();

		// Add 'Icon position' to the tabs
		if ( $el_name == 'tabs' && $section_id == 'section_tabs' ) {
			$element->add_control( 'icon_position', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => __("Icon position", 'trx_addons'),
									'label_block' => false,
									'options' => array(
										'left'  => __( 'Left', 'trx_addons' ),
										'top' => __( 'Top', 'trx_addons' ),
									),
									'default' => 'left',
									'prefix_class' => 'sc_tabs_icon_position_',
								) );
		}
	}
}


// Tabs: use saved templates and custom layouts as a tab content
if (!function_exists('trx_addons_elm_add_params_tab_template')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_tab_template', 10, 3 );
	function trx_addons_elm_add_params_tab_template($element, $section_id, $args) {

		if ( ! trx_addons_get_option('sc_tabs_layouts') || ! is_object($element) ) return;
		
		$el_name = $element->get_name();
		
		// Add template selector
		if ( $el_name == 'tabs' && $section_id == 'section_tabs' ) {

			// Detect edit mode
			$is_edit_mode = trx_addons_elm_is_edit_mode();

			$control   = $element->get_controls( 'tabs' );
			$fields    = $control['fields'];
			$default   = $control['default'];
			$templates = ! $is_edit_mode ? array() : trx_addons_get_list_elementor_templates();
			$layouts   = ! $is_edit_mode ? array() : trx_addons_get_list_layouts();
			if ( count($templates) > 1 || count($layouts) > 1 ) {
				if ( ! isset( $fields['tab_content']['condition'] ) ) {
					$fields['tab_content']['condition'] = array();
				}
				$fields['tab_content']['condition']['tab_content_type!'] = array( 'layout', 'template' );
				if ( is_array( $default ) ) {
					for( $i=0; $i < count($default); $i++ ) {
						$default[$i]['tab_content_type'] = 'content';
						$default[$i]['tab_template'] = 0;
						$default[$i]['tab_layout'] = 0;
						$default[$i]['tab_icon'] = '';
					}
				}
				$fields['tab_title']['label_block'] = false;
				$fields['tab_title']['label'] = __( 'Title', 'trx_addons' );
				trx_addons_array_insert_before( $fields, 'tab_title', trx_addons_get_icon_param('tab_icon') );
				trx_addons_array_insert_after( $fields, 'tab_title', array(
					'tab_content_type' => array(
						'type' => \Elementor\Controls_Manager::SELECT,
						'label' => __("Content type", 'trx_addons'),
						'label_block' => false,
						'options' => array(
							'content'  => __( 'Content', 'trx_addons' ),
							'template' => __( 'Saved Template', 'trx_addons' ),
							'layout'   => __( 'Saved Layout', 'trx_addons' ),
						),
						'default' => 'content',
						'name' => 'tab_content_type'
					),					
					'tab_template' => array(
						'type' => \Elementor\Controls_Manager::SELECT,
						'label' => __("Template", 'trx_addons'),
						'label_block' => false,
						'options' => $templates,
						'default' => 0,
						'name' => 'tab_template',
						'condition' => array(
							'tab_content_type' => 'template'
						)
					),
					'tab_layout' => array(
						'type' => \Elementor\Controls_Manager::SELECT,
						'label' => __("Layout", 'trx_addons'),
						'label_block' => false,
						'options' => $layouts,
						'default' => 0,
						'name' => 'tab_layout',
						'condition' => array(
							'tab_content_type' => 'layout'
						)
					),
				) );
				$element->update_control( 'tabs', array(
								'default' => $default,
								'fields' => $fields
							) );
			}
		}
	}
}

// Substitute tab content with layout
if (!function_exists('trx_addons_elm_tab_template_add_layout')) {
	add_filter( 'elementor/widget/render_content', 'trx_addons_elm_tab_template_add_layout', 10, 2 );
	function trx_addons_elm_tab_template_add_layout($html, $element) {
		if ( trx_addons_get_option('sc_tabs_layouts') > 0 && is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( $el_name == 'tabs' ) {
				//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
				$tabs = $element->get_settings( 'tabs' );
				if ( is_array( $tabs ) ) {
					foreach( $tabs as $k => $tab ) {
						$layout = '';
						if ( ! empty( $tab['tab_content_type'] ) && $tab['tab_content_type'] == 'template' && ! empty( $tab['tab_template'] ) ) {
							$layout = trx_addons_cpt_layouts_show_layout($tab['tab_template'], 0, false);
						} else if ( ! empty( $tab['tab_content_type'] ) && $tab['tab_content_type'] == 'layout' && ! empty( $tab['tab_layout'] ) ) {
							$layout = trx_addons_cpt_layouts_show_layout($tab['tab_layout'], 0, false);
						}
						if ( ! empty( $layout ) ) {
							// Old way: preg_replace is broke a layout (if price $XX is present in the layout)
							/*
							$html = preg_replace(
										'~(<div[^>]*class="elementor-tab-content[^>]*data-tab="'.($k+1).'"[^>]*>)([\s\S]*)(</div>)~U',
										'$1' . trim( $layout ) . '$3',
										$html
									);
							*/
							// New way: use preg_match and str_replace instead preg_replace
							if ( preg_match(
										'~(<div[^>]*class="elementor-tab-content[^>]*data-tab="'.($k+1).'"[^>]*>)([\s\S]*)(</div>)~U',
										$html,
										$matches
									)
							) {
								$html = str_replace( $matches[0], $matches[1] . trim( $layout ) . $matches[3], $html );
							}
						}
						if ( ! empty( $tab['tab_icon'] ) && ! trx_addons_is_off( $tab['tab_icon'] ) ) {
							$html = preg_replace(
										'~(<div[^>]*class="elementor-tab-title[^>]*data-tab="'.($k+1).'"[^>]*>[\s]*)(<a href="">)~U',
										'$1' . apply_filters('trx_addons_filter_tab_link',
													'<a href="" class="' . esc_attr( $tab['tab_icon'] ) . '">',
													$k,
													$tab
												),
										$html
									);							
						}
					}
				}
			}
		}
		return $html;
	}
}

// Substitute tab content with layout: Redefine core class Tabs - disable js-template (any change need reload template)
if ( !function_exists( 'trx_addons_elm_modify_tabs' ) ) {
	add_action( 'elementor/widgets/widgets_registered', 'trx_addons_elm_modify_tabs' );
	function trx_addons_elm_modify_tabs( $widgets_manager ) {
		if ( trx_addons_get_option('sc_tabs_layouts') > 0
			&& class_exists('\Elementor\Widget_Tabs') 
			&& ! class_exists('TRX_Addons_Elementor_Widget_Tabs') 
			&& $widgets_manager->unregister( 'tabs' )	//unregister_widget_type
		) {
			class TRX_Addons_Elementor_Widget_Tabs extends \Elementor\Widget_Tabs {
				// Disable js-template - widget need reload on any parameter change
				protected function content_template() { return ''; }
			}
			$widgets_manager->register( new TRX_Addons_Elementor_Widget_Tabs() );	//register_widget_type
		}
	}
}



//========================================================================
//  Spacer and divider
//========================================================================

// Alter height to the spacer and divider
if (!function_exists('trx_addons_elm_add_params_alter_height')) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_alter_height', 10, 3 );
	function trx_addons_elm_add_params_alter_height($element, $section_id, $args) {

		if (!is_object($element)) return;
		
		$el_name = $element->get_name();

		// Add 'Alter height/gap' to the spacer and divider
		if ( ($el_name == 'spacer' && $section_id == 'section_spacer')
			  || ($el_name == 'divider' && $section_id == 'section_divider')
		) {
			$is_edit_mode = trx_addons_elm_is_edit_mode();
			$element->add_control( 'alter_height', array(
									'type' => \Elementor\Controls_Manager::SELECT,
									'label' => $el_name == 'divider' ? __("Alter gap", 'trx_addons') : __("Alter height", 'trx_addons'),
									'label_block' => true,
									'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_empty_space_heights(''),
									'default' => '',
									'prefix_class' => 'sc_height_'
									) );
		}
	}
}



//========================================================================
//  Shape divider
//========================================================================

// Add new shape dividers
if ( ! function_exists('trx_addons_elm_add_new_shape_dividers')) {
	add_filter( 'elementor/shapes/additional_shapes', 'trx_addons_elm_add_new_shape_dividers' );
	function trx_addons_elm_add_new_shape_dividers( $shapes ) {
		global $TRX_ADDONS_STORAGE;
		if ( ! empty( $TRX_ADDONS_STORAGE['shapes_list'] ) && is_array( $TRX_ADDONS_STORAGE['shapes_list'] ) ) {
			foreach( $TRX_ADDONS_STORAGE['shapes_list'] as $k => $shape ) {
				$shape_name = pathinfo( $shape, PATHINFO_FILENAME );
				$shapes[ "trx_addons_{$shape_name}" ] = array(
					'title' => ucfirst( str_replace( '_', ' ', $shape_name ) ),
					'has_negative' => false,
					'has_flip' => true,
					'url' => ! empty( $TRX_ADDONS_STORAGE['shapes_urls'][ $k ] ) ? $TRX_ADDONS_STORAGE['shapes_urls'][ $k ] : '',
					'path' => $shape
				);
			}
		}
		return $shapes;
	}
}



//========================================================================
//  Parallax layers for Sections and Columns
//========================================================================

// Add "Parallax" params to rows and columns
if (!function_exists('trx_addons_elm_add_parallax_blocks')) {
	add_action( 'elementor/element/before_section_start', 'trx_addons_elm_add_parallax_blocks', 10, 3 );
	function trx_addons_elm_add_parallax_blocks($element, $section_id, $args) {

		if ( !is_object($element) ) return;

		if ( in_array( $element->get_name(), array( 'section', 'column' ) ) && $section_id == '_section_responsive' ) {

			$element->start_controls_section( 'section_trx_parallax', array(
																		// Don't add a field of type 'REPEATER' to tabs other
																		// than TAB_CONTENT in any Elementor widget, section or column.
																		// Because an error appears on action 'Paste style' executed.
																		// Also an error message appears when saving the document
																		// after copying the styles of one widget to another.

																		// This way with no errors, but a new tab 'Content' appears
																		// in sections and columns with single controls section
																		//'tab' => \Elementor\Controls_Manager::TAB_CONTENT,

																		// This way may generate js-errors when a user copying styles
																		// from one section or column to another. But no new tab appears.
																		'tab' => ! empty( $args['tab'] )
																					? $args['tab']
																					: \Elementor\Controls_Manager::TAB_ADVANCED,

																		'label' => __( 'Background layers', 'trx_addons' )
																	) );

			$element->add_control(
				'parallax_blocks',
				array(
					'label' => __( 'Layers', 'trx_addons' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => apply_filters('trx_addons_sc_param_group_params',
						array(
							array(
								'name' => 'type',
								'label' => __( 'Layer handle', 'trx_addons' ),
								'label_block' => false,
								'type' => \Elementor\Controls_Manager::SELECT,
								'options' => array(
									'none'   => __('None', 'trx_addons'),
									'mouse'  => __('Mouse events', 'trx_addons'),
									'scroll' => __('Scroll events', 'trx_addons'),
									'motion' => __('Permanent motion', 'trx_addons'),
								),
								'default' => 'none',
							),
							array(
								'name' => 'animation_prop',
								'label' => __( 'Animation', 'trx_addons' ),
								'label_block' => false,
								'type' => \Elementor\Controls_Manager::SELECT,
								'options' => array(
									'background'  => __('Background', 'trx_addons'),
									'transform'   => __('Transform', 'trx_addons'),
									'transform3d' => __('Transform3D', 'trx_addons'),
								),
								'default' => 'background',
							),
							array(
								'name' => 'image',
								'label' => __( 'Background image', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::MEDIA,
								'default' => array(
									'url' => '',
								),
							),
							array(
								'name' => 'bg_size',
								'label' => __( 'Background size', 'trx_addons' ),
								'label_block' => false,
								'type' => \Elementor\Controls_Manager::SELECT,
								'options' => array(
									'auto'    => __('Auto', 'trx_addons'),
									'cover'   => __('Cover', 'trx_addons'),
									'contain' => __('Contain', 'trx_addons'),
								),
								'default' => 'cover',
							),
							array(
								'name' => 'left',
								'label' => __( 'Left position (in %)', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::SLIDER,
								'default' => array(
									'size' => 0,
									'unit' => 'px'
								),
								'range' => array(
									'px' => array(
										'min' => 0,
										'max' => 100
									),
								),
								'size_units' => array( 'px' )
							),
							array(
								'name' => 'top',
								'label' => __( 'Top position (in %)', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::SLIDER,
								'default' => array(
									'size' => 0,
									'unit' => 'px'
								),
								'range' => array(
									'px' => array(
										'min' => 0,
										'max' => 100
									),
								),
								'size_units' => array( 'px' ),
							),
							array(
								'name' => 'speed',
								'label' => __( 'Shift speed', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::SLIDER,
								'default' => array(
									'size' => 50,
									'unit' => 'px'
								),
								'range' => array(
									'px' => array(
										'min' => -500,
										'max' => 500,
										'step' => 10
									),
								),
								'size_units' => array( 'px' ),
							),
							array(
								'name' => 'z_index',
								'label' => __( 'Z-index', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::SLIDER,
								'default' => array(
									'size' => '',
									'unit' => 'px'
								),
								'range' => array(
									'px' => array(
										'min' => -1,
										'max' => 100
									),
								),
								'size_units' => array( 'px' ),
							),
							array(
								'name' => 'motion_dir',
								'type' => \Elementor\Controls_Manager::SELECT,
								'label' => __( 'Motion direction', 'trx_addons' ),
								'label_block' => false,
								'options' => array(
									'vertical' => __( 'Vertical', 'trx_addons'),
									'horizontal' => __( 'Horizontal', 'trx_addons'),
									'round' => __( 'Round', 'trx_addons'),
									'random' => __( 'Random', 'trx_addons'),
								),
								'default' => 'round',
								'condition' => array(
									'type' => 'motion'
								),
							),
							array(
								'name' => 'motion_time',
								'label' => __( 'Motion time', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::SLIDER,
								'default' => array(
									'size' => 5,
									'unit' => 'px'
								),
								'size_units' => array( 'px' ),
								'range' => array(
									'px' => array(
										'min' => 0.1,
										'max' => 20,
										'step' => 0.1
									)
								),
								'condition' => array(
									'type' => 'motion'
								),
							),
							array(
								'name' => 'class',
								'label' => __( 'CSS class', 'trx_addons' ),
								'description' => __( 'Class name to assign additional rules to this layer. For example: "hide_on_notebook", "hide_on_tablet", "hide_on_mobile" to hide block on the relative device', 'trx_addons' ),
								'type' => \Elementor\Controls_Manager::TEXT,
								'default' => '',
							),
						),
						'trx_sc_parallax_row'
					),
					'title_field' => '{{{ left.size }}}x{{{ top.size }}} / {{{ type }}} / {{{ animation_prop }}}',
				)
			);

			$element->end_controls_section();
		}
	}
}

// Add "data-parallax-blocks" to the wrapper of the row
if ( !function_exists( 'trx_addons_elm_add_parallax_blocks_data' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render',  'trx_addons_elm_add_parallax_blocks_data', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/section/before_render', 'trx_addons_elm_add_parallax_blocks_data', 10, 1 );
	add_action( 'elementor/frontend/column/before_render', 'trx_addons_elm_add_parallax_blocks_data', 10, 1 );
	function trx_addons_elm_add_parallax_blocks_data($element) {
		if ( is_object( $element ) && in_array( $element->get_name(), array( 'section', 'column' ) ) ) {
			//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
			$parallax_blocks = $element->get_settings( 'parallax_blocks' );
			if ( ! empty( $parallax_blocks ) 
				&& is_array( $parallax_blocks ) 
				&& count( $parallax_blocks ) > 0 
				&& ( $parallax_blocks[0]['type'] != 'none' || ! empty( $parallax_blocks[0]['image']['url'] ) )
			) {
				$element->add_render_attribute( '_wrapper', 'class', 'sc_parallax' );
				$element->add_render_attribute( '_wrapper', 'class', 'sc_parallax_blocks' );
				$element->add_render_attribute( '_wrapper', 'data-parallax-blocks', json_encode( $parallax_blocks ) );
			}
		}
	}
}

// Add class "elementor-custom-width" to the wrapper of the row if a parameter 'size' is specified
if ( !function_exists( 'trx_addons_elm_add_custom_width_to_sections' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render',  'trx_addons_elm_add_custom_width_to_sections', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/section/before_render', 'trx_addons_elm_add_custom_width_to_sections', 10, 1 );
	function trx_addons_elm_add_custom_width_to_sections( $element ) {
		if ( is_object( $element ) && $element->get_name() == 'section' ) {
			//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
			$content_width = $element->get_settings( 'content_width' );
			if ( ! empty( $content_width['size'] ) && (int)$content_width['size'] > 0 ) {
				$element->add_render_attribute( '_wrapper', 'class', 'elementor-section-with-custom-width' );
			}
		}
	}
}



//========================================================================
//  Parallax blocks for all elements
//========================================================================

// Add "Parallax" params to widgets
if (!function_exists('trx_addons_elm_add_parallax_params_to_widgets')) {
	add_action( 'elementor/element/before_section_start', 'trx_addons_elm_add_parallax_params_to_widgets', 10, 3 );
	add_action( 'elementor/widget/before_section_start', 'trx_addons_elm_add_parallax_params_to_widgets', 10, 3 );
	function trx_addons_elm_add_parallax_params_to_widgets($element, $section_id, $args) {

		if ( !is_object($element) ) return;

		if ( in_array( $element->get_name(), array( 'section', 'column', 'common' ) ) && $section_id == '_section_responsive' ) {
			
			// Detect edit mode
			$is_edit_mode = trx_addons_elm_is_edit_mode();

			// Register controls
			$element->start_controls_section( 'section_trx_entrance', array(
																		'tab' => !empty($args['tab']) ? $args['tab'] : \Elementor\Controls_Manager::TAB_ADVANCED,
																		'label' => __( 'Parallax or Entrance', 'trx_addons' )
																	) );
			$element->add_control( 'parallax', array(
													'type' => \Elementor\Controls_Manager::SWITCHER,
													'label' => __( 'Parallax', 'trx_addons' ),
													'label_on' => __( 'On', 'trx_addons' ),
													'label_off' => __( 'Off', 'trx_addons' ),
													'return_value' => 'parallax',
													'prefix_class' => 'sc_',
									) );
			$element->add_control( 'parallax_entrance', array(
													'type' => \Elementor\Controls_Manager::SWITCHER,
													'label' => __( 'Entrance', 'trx_addons' ),
													'label_on' => __( 'On', 'trx_addons' ),
													'label_off' => __( 'Off', 'trx_addons' ),
													'return_value' => 'entrance',
													'prefix_class' => 'sc_parallax_',
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_start', array(
													'type' => \Elementor\Controls_Manager::SWITCHER,
													'label' => __( 'Values below are', 'trx_addons' ),
													'label_on' => __( 'Start', 'trx_addons' ),
													'label_off' => __( 'End', 'trx_addons' ),
													'return_value' => 'start',
													'prefix_class' => 'sc_parallax_',
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_ease', array(
													'type' => \Elementor\Controls_Manager::SELECT,
													'label' => __( 'Entrance ease', 'trx_addons' ),
													'label_block' => false,
													'options' => ! $is_edit_mode ? array() : trx_addons_get_list_ease(),
													'default' => 'power2',
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_entrance' => 'entrance'
													),
									) );
			$element->add_control( 'parallax_text', array(
													'type' => \Elementor\Controls_Manager::SELECT,
													'label' => __( 'Text animation', 'trx_addons' ),
													'label_block' => false,
													'options' => array(
														'block' => __( 'Whole block', 'trx_addons'),
														'words' => __( 'Word by word', 'trx_addons'),
														'chars' => __( 'Char by char', 'trx_addons'),
													),
													'default' => 'block',
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_x', array(
													'label' => __( 'The shift along the X-axis', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 0,
														'unit' => 'px'
													),
													'size_units' => array( 'px', 'vw' ),
													'range' => array(
														'px' => array(
															'min' => -500,
															'max' => 500
														),
														'vw' => array(
															'min' => -200,
															'max' => 200
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_y', array(
													'label' => __( 'The shift along the Y-axis', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 0,
														'unit' => 'px'
													),
													'size_units' => array( 'px', 'vh' ),
													'range' => array(
														'px' => array(
															'min' => -500,
															'max' => 500
														),
														'vh' => array(
															'min' => -200,
															'max' => 200
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_opacity', array(
													'label' => __( 'Change the opacity', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 0,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => -1,
															'max' => 0,
															'step' => 0.05
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_scale', array(
													'label' => __( 'Change the scale (in %)', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 0,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => -100,
															'max' => 1000,
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_rotate', array(
													'label' => __( 'Change the rotation (in deg)', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 0,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => -360,
															'max' => 360,
															'step' => 1
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_duration', array(
													'label' => __( 'Duration (in sec)', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 1,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => 0.1,
															'max' => 10,
															'step' => 0.1
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_squeeze', array(
													'label' => __( 'Squeeze interval', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 1,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => 0,
															'max' => 3,
															'step' => 0.05
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );

			$element->add_control( 'parallax_amplitude', array(
													'label' => __( 'Amplitude', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 40,
														'unit' => 'px'
													),
													'size_units' => array( 'px' ),
													'range' => array(
														'px' => array(
															'min' => 1,
															'max' => 100,
															'step' => 1
														)
													),
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_mouse', array(
													'type' => \Elementor\Controls_Manager::SWITCHER,
													'label' => __( 'Mouse events', 'trx_addons' ),
													'label_on' => __( 'On', 'trx_addons' ),
													'label_off' => __( 'Off', 'trx_addons' ),
													'return_value' => 'mouse',
													'prefix_class' => 'sc_parallax_',
													'condition' => array(
														'parallax' => 'parallax'
													),
									) );
			$element->add_control( 'parallax_mouse_type', array(
													'type' => \Elementor\Controls_Manager::SELECT,
													'label' => __( 'Transform type', 'trx_addons' ),
													'label_block' => false,
													'options' => array(
														'transform'   => esc_html__( 'Transform', 'trx_addons' ),
														'transform3d' => esc_html__( 'Transform 3D', 'trx_addons' ),
														'tilt'        => esc_html__( 'Tilt', 'trx_addons' ),
													),
													'default' => 'transform3d',
													'prefix_class' => 'sc_parallax_type_',
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_mouse' => 'mouse',
													),
									) );
			$element->add_control( 'parallax_mouse_tilt_amount', array(
													'label' => __( 'Amount', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 70,
														'unit' => 'px'
													),
													'range' => array(
														'px' => array(
															'min' => 10,
															'max' => 500
														),
													),
													'size_units' => array( 'px' ),
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_mouse' => 'mouse',
														'parallax_mouse_type' => 'tilt',
													),
									) );
			$element->add_control( 'parallax_mouse_speed', array(
													'label' => __( 'Momentum', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => 10,
														'unit' => 'px'
													),
													'range' => array(
														'px' => array(
															'min' => -100,
															'max' => 100
														),
													),
													'size_units' => array( 'px' ),
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_mouse' => 'mouse',
														'parallax_mouse_type!' => 'tilt',
													),
									) );
			$element->add_control( 'parallax_mouse_z', array(
													'label' => __( 'Z-index', 'trx_addons' ),
													'type' => \Elementor\Controls_Manager::SLIDER,
													'default' => array(
														'size' => '',
														'unit' => 'px'
													),
													'range' => array(
														'px' => array(
															'min' => -1,
															'max' => 100
														),
													),
													'size_units' => array( 'px' ),
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_mouse' => 'mouse',
														'parallax_mouse_type' => array('tilt', 'transform3d'),
													),
									) );
			$element->add_control( 'parallax_mouse_handler', array(
													'type' => \Elementor\Controls_Manager::SELECT,
													'label' => __( 'Mouse handler', 'trx_addons' ),
													'label_block' => false,
													'options' => array(
														'self'    => esc_html__( 'Self', 'trx_addons' ),
														'parent'  => esc_html__( 'Parent', 'trx_addons' ),
														'column'  => esc_html__( 'Current column', 'trx_addons' ),
														'row'     => esc_html__( 'Current row', 'trx_addons' ),
														'content' => esc_html__( 'Content area', 'trx_addons' ),
														'window'  => esc_html__( 'Whole window', 'trx_addons' ),
													),
													'default' => 'row',
													'condition' => array(
														'parallax' => 'parallax',
														'parallax_mouse' => 'mouse',
													),
									) );

			$element->end_controls_section();
		}
	}
}

// Add "data-parallax-params" to the wrapper of the widget
if ( !function_exists( 'trx_addons_elm_add_parallax_data_to_widgets' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render',  'trx_addons_elm_add_parallax_data_to_widgets', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/section/before_render',  'trx_addons_elm_add_parallax_data_to_widgets', 10, 1 );
	add_action( 'elementor/frontend/column/before_render',  'trx_addons_elm_add_parallax_data_to_widgets', 10, 1 );
	add_action( 'elementor/frontend/widget/before_render',  'trx_addons_elm_add_parallax_data_to_widgets', 10, 1 );
	function trx_addons_elm_add_parallax_data_to_widgets($element) {
		//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
		$parallax = $element->get_settings( 'parallax' );
		if ( ! empty( $parallax ) ) {
			$settings = $element->get_settings();
			$element->add_render_attribute( '_wrapper', 'data-parallax-params', json_encode(array(
				'x' => !empty($settings['parallax_x']) ? $settings['parallax_x']['size'] : 0,
				'x_unit' => !empty($settings['parallax_x']) ? $settings['parallax_x']['unit'] : 0,
				'y' => !empty($settings['parallax_y']) ? $settings['parallax_y']['size'] : 0,
				'y_unit' => !empty($settings['parallax_y']) ? $settings['parallax_y']['unit'] : 0,
				'scale' => !empty($settings['parallax_scale']) ? $settings['parallax_scale']['size'] : 0,
				'rotate' => !empty($settings['parallax_rotate']) ? $settings['parallax_rotate']['size'] : 0,
				'opacity' => !empty($settings['parallax_opacity']) ? $settings['parallax_opacity']['size'] : 0,
				'duration' => !empty($settings['parallax_duration']) ? $settings['parallax_duration']['size'] : 1,
				'squeeze' => !empty($settings['parallax_squeeze']) ? $settings['parallax_squeeze']['size'] : 1,
				'amplitude' => !empty($settings['parallax_amplitude']) ? $settings['parallax_amplitude']['size'] : 40,
				'text' => !empty($settings['parallax_text']) ? $settings['parallax_text'] : 'block',
				'mouse' => !empty($settings['parallax_mouse']) && $settings['parallax_mouse'] == 'mouse' ? 1 : 0,
				'mouse_type' => !empty($settings['parallax_mouse_type']) ? $settings['parallax_mouse_type'] : 'transform3d',
				'mouse_tilt_amount' => !empty($settings['parallax_mouse_tilt_amount']) ? $settings['parallax_mouse_tilt_amount']['size'] : 70,
				'mouse_speed' => !empty($settings['parallax_mouse_speed']) ? $settings['parallax_mouse_speed']['size'] : 10,
				'mouse_z' => !empty($settings['parallax_mouse_z']) ? $settings['parallax_mouse_z']['size'] : '',
				'mouse_handler' => !empty($settings['parallax_mouse_handler']) ? $settings['parallax_mouse_handler'] : 'row',
				'ease' => !empty($settings['parallax_ease']) ? $settings['parallax_ease'] : 'power2',
			)) );
		}
	}
}



//========================================================================
//  Parallax image
//========================================================================

// Parallax controls to the Image
if ( ! function_exists( 'trx_addons_elm_add_params_parallax_to_image' ) ) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_parallax_to_image', 10, 3 );
	function trx_addons_elm_add_params_parallax_to_image( $element, $section_id, $args ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'image' == $el_name && 'section_image' === $section_id ) {
				$element->add_control(
					'parallax_heading',
					array(
						'label' => esc_html__( 'Shift image on scroll', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					)
				);
				$element->add_control(
					'parallax_speed',
					array(
						'label' => __( 'Shift speed', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'default' => array(
							'size' => 0
						),
						'range' => array(
							'px' => array(
								'min' => -50,
								'max' => 50
							)
						),
					)
				);
				$element->add_responsive_control(
					'parallax_height',
					array(
						'label' => __( 'Max.height', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'default' => array(
							'size' => 0,
							'unit' => 'px'
						),
						'size_units' => array( 'px', 'em', 'vh' ),
						'range' => array(
							'px' => array(
								'min' => 50,
								'max' => 1000
							),
							'em' => array(
								'min' => 1,
								'max' => 100
							),
							'vh' => array(
								'min' => 1,
								'max' => 100
							),
						),
						'condition' => array(
							'parallax_speed[size]!' => 0,
						),						
						'selectors' => array(
							'{{WRAPPER}} .elementor-image, {{WRAPPER}} .elementor-image > .wp-caption' => 'display: flex; align-items: center; justify-content: center; overflow: hidden; max-height: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .elementor-image > .wp-caption > img' => 'width: 100%;'
						),
					)
				);
			}
		}
	}
}


// Add parallax classes and data parameters to the Image
if ( ! function_exists( 'trx_addons_elm_add_params_parallax_to_image_before_render' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render', 'trx_addons_elm_add_params_parallax_to_image_before_render', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/widget/before_render', 'trx_addons_elm_add_params_parallax_to_image_before_render', 10, 1 );
	function trx_addons_elm_add_params_parallax_to_image_before_render( $element ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'image' == $el_name ) {
				//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
				$parallax_speed = $element->get_settings( 'parallax_speed' );
				if ( ! empty( $parallax_speed['size'] ) ) {
					$parallax_height = $element->get_settings( 'parallax_height' );
					if ( ! empty( $parallax_height['size'] ) && ! empty( $parallax_height['unit'] ) ) {
						trx_addons_enqueue_parallax();
						$element->add_render_attribute( 'wrapper', 'class', 'sc_parallax_wrap' );
						$element->add_render_attribute( 'wrapper', 'data-parallax', $parallax_speed['size'] );
					}
				}
			}
		}
	}
}



//========================================================================
//  Background text and marquee for Sections
//========================================================================

// Add "Background text" params to the section
if (!function_exists('trx_addons_elm_add_bg_text')) {
	add_action( 'elementor/element/before_section_start', 'trx_addons_elm_add_bg_text', 10, 3 );
	function trx_addons_elm_add_bg_text($element, $section_id, $args) {

		if ( !is_object($element) ) return;

		if ( in_array( $element->get_name(), array( 'section' ) ) && $section_id == '_section_responsive' ) {

			// Detect edit mode
			$is_edit_mode = trx_addons_elm_is_edit_mode();

			// Register controls
			$element->start_controls_section( 'section_trx_bg_text', array(
				'tab' => !empty($args['tab']) ? $args['tab'] : \Elementor\Controls_Manager::TAB_ADVANCED,
				'label' => __( 'Background text', 'trx_addons' )
			) );

			$element->add_control( 'bg_text', array(
				'type' => \Elementor\Controls_Manager::TEXT,
				'label' => __( "Text", 'trx_addons' ),
				'label_block' => false,
				'default' => ''
			) );

			$element->add_control(
				'bg_text_color',
				array(
					'label' => __( 'Text color', 'trx_addons' ),
					'label_block' => false,
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => array(
						'{{WRAPPER}} .trx_addons_bg_text_char' => 'color: {{VALUE}};',
					)
				)
			);

			if ( class_exists('\Elementor\Group_Control_Typography') && class_exists('\Elementor\Core\Schemes\Typography') ) {
				$element->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name' => 'bg_text_typography',
						'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
						'selector' => '{{WRAPPER}} .trx_addons_bg_text_char',
					)
				);
			}

			if ( class_exists('\Elementor\Group_Control_Text_Shadow') ) {
				$element->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name' => 'bg_text_shadow',
						'selector' => '{{WRAPPER}} .trx_addons_bg_text_char',
					)
				);
			}

			$element->add_responsive_control( 'bg_text_top', array(
				'type' => \Elementor\Controls_Manager::SLIDER,
				'label' => __( 'Top offset', 'trx_addons' ),
				'default' => array(
					'size' => '',
					'unit' => '%'
				),
				'size_units' => array( 'px', 'em', '%' ),
				'range' => array(
					'px' => array(
						'min' => -200,
						'max' => 200
					),
					'em' => array(
						'min' => -100,
						'max' => 100
					),
					'%' => array(
						'min' => -100,
						'max' => 100
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .trx_addons_bg_text_inner' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			) );

			$element->add_responsive_control( 'bg_text_left', array(
				'type' => \Elementor\Controls_Manager::SLIDER,
				'label' => is_rtl() ? __( 'Right offset', 'trx_addons' ) : __( 'Left offset', 'trx_addons' ),
				'default' => array(
					'size' => '',
					'unit' => '%'
				),
				'size_units' => array( 'px', 'em', '%' ),
				'range' => array(
					'px' => array(
						'min' => -200,
						'max' => 200
					),
					'em' => array(
						'min' => -100,
						'max' => 100
					),
					'%' => array(
						'min' => -100,
						'max' => 100
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .trx_addons_bg_text_inner' => is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
				),
			) );

			$element->add_control( 'bg_text_effect', array(
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => __( 'Entrance effect', 'trx_addons' ),
				'label_block' => false,
				'options' => apply_filters( 'trx_addons_filter_bg_text_effects', array(
					'none'   => esc_html__( 'None', 'trx_addons' ),
					'rotate' => esc_html__( 'Rotate', 'trx_addons' ),
					'slide'  => esc_html__( 'Slide', 'trx_addons' ),
				) ),
				'default' => 'slide',
			) );

			$element->add_control( 'bg_text_marquee', array(
				'label' => __( 'Marquee speed', 'trx_addons' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => array(
					'size' => '',
					'unit' => 'px'
				),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 10
					),
				),
				'size_units' => array( 'px' )
			) );

			$element->add_control( 'bg_text_reverse', array(
				'label' => __( 'Reverse movement', 'trx_addons' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_block' => false,
				'label_off' => __( 'Off', 'trx_addons' ),
				'label_on' => __( 'On', 'trx_addons' ),
				'default' => '',
			) );

			$element->add_control( 'bg_text_overlay', array(
				'type' => \Elementor\Controls_Manager::MEDIA,
				'label' => __( "Overlay image", 'trx_addons' ),
				'default' => array(
					'url' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .trx_addons_bg_text_overlay' => 'background-image: url({{URL}});',
				),
			) );

			$element->add_control( 'bg_text_overlay_position', array(
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => __( 'Overlay position', 'trx_addons' ),
				'label_block' => false,
				'options' => ! $is_edit_mode ? array() : apply_filters( 'trx_addons_filter_bg_text_position', trx_addons_get_list_background_positions() ),
				'default' => '',
				'selectors' => array(
					'{{WRAPPER}} .trx_addons_bg_text_overlay' => 'background-position: {{VALUE}};',
				),
				'condition' => array(
					'bg_text_overlay[url]!' => ''
				),
			) );

			$element->end_controls_section();
		}
	}
}

// Add "data-bg-text" to the wrapper of the row
if ( !function_exists( 'trx_addons_elm_add_bg_text_data' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render',  'trx_addons_elm_add_bg_text_data', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/section/before_render', 'trx_addons_elm_add_bg_text_data', 10, 1 );
	function trx_addons_elm_add_bg_text_data($element) {
		if ( is_object( $element ) && in_array( $element->get_name(), array( 'section' ) ) ) {
			//$settings = trx_addons_elm_prepare_global_params( $element->get_settings() );
			$bg_text = $element->get_settings( 'bg_text' );
			if ( ! empty( $bg_text ) ) {
				$settings = $element->get_settings();
				$element->add_render_attribute( '_wrapper', 'class', 'trx_addons_has_bg_text' );
				$element->add_render_attribute( '_wrapper', 'data-bg-text', json_encode( array(
					'bg_text'         => $settings['bg_text'],
					'bg_text_effect'  => $settings['bg_text_effect'],
					'bg_text_marquee' => $settings['bg_text_marquee'],
					'bg_text_reverse' => ! empty( $settings['bg_text_reverse'] ) ? 1 : 0,
					'bg_text_overlay' => $settings['bg_text_overlay'],
					'bg_text_left'    => $settings['bg_text_left'],
					'bg_text_top'     => $settings['bg_text_top'],
					)
				) );
			}
		}
	}
}



//========================================================================
//  Add custom layouts to the button "Edit with Elementor" on the admin bar
//========================================================================

/*
// Add params to the ThemeREX Addons Options
if ( ! function_exists( 'trx_addons_elm_allow_submenu_add_options' ) ) {
	add_filter( 'trx_addons_filter_options', 'trx_addons_elm_allow_submenu_add_options' );
	function trx_addons_elm_allow_submenu_add_options( $options ) {
		if ( trx_addons_exists_elementor() ) {// && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.8.1', '<' ) ) {
			trx_addons_array_insert_after($options, 'layouts_info', array(
				'wp_admin_bar_render_to_the_footer' => array(
					"title" => esc_html__('Add Layouts to the button "Edit with Elementor"', 'trx_addons'),
					"desc" => wp_kses_data( __("Enable admin bar elements that depend on the content of the current page (e.g. Layouts)", 'trx_addons') ),
					"std" => "0",
					"type" => "switch"
				),
			) );
		}
		return $options;
	}
}

// Move native 'wp_admin_bar_render' back to action 'wp_footer' to enable 'Edit with Elementor' for our layouts.
// Otherwise only current page link is available on this button.
if (!function_exists('trx_addons_elm_allow_submenu_to_edit_layouts')) {
	add_action( 'init', 'trx_addons_elm_allow_submenu_to_edit_layouts' );
	function trx_addons_elm_allow_submenu_to_edit_layouts() {
		if ( trx_addons_exists_elementor() && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.8.1', '<' ) ) {
			if ( trx_addons_is_on(trx_addons_get_option('wp_admin_bar_render_to_the_footer')) ) {
				$priority = has_action( 'wp_body_open', 'wp_admin_bar_render' );
				if ( $priority !== false ) {
					remove_action( 'wp_body_open', 'wp_admin_bar_render', $priority );
					if ( ! has_action( 'wp_footer', 'wp_admin_bar_render' ) ) {
						add_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
					}
				}
			}
		}
	}
}
*/



//========================================================================
//  Button "Edit layout in the new tab"
//========================================================================

// Add button to edit layout to the Elementor preview area
if ( !function_exists( 'trx_addons_elm_add_layout_editor_link' ) ) {
	add_filter( 'trx_addons_filter_sc_layout_content_from_builder', 'trx_addons_elm_add_layout_editor_link', 10, 3 );
	function trx_addons_elm_add_layout_editor_link($post_content, $post_id, $builder) {
		$output = '';
		if ( $builder == 'elementor' && trx_addons_elm_is_preview() && strpos($post_content, 'trx_addons_layout_editor_mask') === false ) {
			$meta = (array)get_post_meta( $post_id, 'trx_addons_options', true );
			if ( ! empty( $meta['layout_type'] ) && in_array( $meta['layout_type'], array( 'header', 'footer', 'sidebar' ) ) ) {
				if ( trx_addons_get_value_gp( 'elementor-preview' ) != $post_id ) {
					$output = '<div class="trx_addons_layout_editor_mask">'
								. '<div class="trx_addons_layout_editor_selector">'
									. '<a href="' . esc_url( admin_url( sprintf( "post.php?post=%d&amp;action=elementor", $post_id ) ) ) . '"'
										. ' target="_blank"'
										. ' class="trx_addons_layout_editor_link"'
										. ' data-layout-type="' . esc_attr( $meta['layout_type'] ) . '"'
									. '>'
										. sprintf( esc_html__('Edit "%s" in a new tab', 'trx_addons'),
													//$meta['layout_type'] == 'header' ? esc_html__( 'Header', 'trx_addons' ) : ( $meta['layout_type'] == 'footer' ? esc_html__( 'Footer', 'trx_addons' ) : esc_html__( 'Sidebar', 'trx_addons' ) )
													trx_addons_strshort( get_the_title($post_id), 30 )
												)
									. '</a>';
					// Add layouts list (if change layouts is supported)
					if ( apply_filters( 'trx_addons_filter_layout_editor_selector_supported', true ) ) {
						$list = trx_addons_get_list_layouts( false, $meta['layout_type'], 'title' );
						if ( isset( $list[$post_id] ) ) {
							unset( $list[ $post_id ] );
						}
						if ( count( $list ) > 0 ) {
							$output .= '<span class="trx_addons_layout_editor_selector_trigger"></span>'
										. '<span class="trx_addons_layout_editor_selector_list">';
							foreach( $list as $id => $title ) {
								$output .= '<span class="trx_addons_layout_editor_selector_list_item"'
												. ' data-post-id="' . esc_attr( $post_id ) . '"'
												. ' data-layout-id="' . esc_attr( $id ) . '"'
												. ' data-layout-type="' . esc_attr( $meta['layout_type'] ) . '"'
												. ' data-layout-url="' . esc_url( admin_url( sprintf( "post.php?post=%d&amp;action=elementor", $id ) ) ) . '"'
											. '>'
												. esc_html( $title )
											. '</span>';
							}
							$output .= '</span>';
						}
					}
					$output .= '</div>'
							. '</div>';
				}
			}
		}
		return $post_content . $output;
	}
}



// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'elementor/elementor-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_elementor() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'elementor/elementor-demo-ocdi.php';
}
