<?php
/**
 * Plugin support: The Events Calendar
 *
 * @package ThemeREX Addons
 * @since v1.2
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if Tribe Events installed and activated
if (!function_exists('trx_addons_exists_tribe_events')) {
	function trx_addons_exists_tribe_events() {
		return class_exists( 'Tribe__Events__Main' );
	}
}


// Return true, if current page is any Tribe Events page
if ( !function_exists( 'trx_addons_is_tribe_events_page' ) ) {
	function trx_addons_is_tribe_events_page() {
		$is = false;
		if (trx_addons_exists_tribe_events() && !is_search()) {
			$is = tribe_is_event() || tribe_is_event_query() || tribe_is_event_category() || tribe_is_event_venue() || tribe_is_event_organizer();
		}
		return $is;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_tribe_events_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_tribe_events_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_tribe_events_load_scripts_front', 10, 1 );
	function trx_addons_tribe_events_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_tribe_events() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_tribe_events_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'the-events-calendar',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'trx_sc_events' ),
										//array( 'type' => 'gb',  'sc' => 'wp:trx-addons/events' ),	// This sc is not exists for GB
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_events"' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_sc_events' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-tribe_events', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar.css'), array(), null );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'tribe_events' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'tribe_events', 2 );
		}
	}
}

// Enqueue responsive styles for frontend
if ( ! function_exists( 'trx_addons_tribe_events_load_scripts_front_responsive' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_tribe_events_load_scripts_front_responsive', TRX_ADDONS_ENQUEUE_RESPONSIVE_PRIORITY );
	add_action( 'trx_addons_action_load_scripts_front_tribe_events', 'trx_addons_tribe_events_load_scripts_front_responsive', 10, 1 );
	function trx_addons_tribe_events_load_scripts_front_responsive( $force = false ) {
		static $loaded = false;
		if ( ! $loaded && (
			current_action() == 'wp_enqueue_scripts' && trx_addons_need_frontend_scripts( 'tribe_events' )
			||
			current_action() != 'wp_enqueue_scripts' && $force === true
			)
		) {
			$loaded = true;
			wp_enqueue_style( 'trx_addons-tribe_events-responsive', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar.responsive.css'), array(), null, trx_addons_media_for_load_css_responsive( 'md' ) );
		}
	}
}


// Merge specific styles into single stylesheet
if ( !function_exists( 'trx_addons_events_merge_styles' ) ) {
	add_filter("trx_addons_filter_merge_styles", 'trx_addons_events_merge_styles');
	function trx_addons_events_merge_styles($list) {
		if ( trx_addons_exists_tribe_events() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar.css' ] = false;
		}
		return $list;
	}
}


// Merge shortcode's specific styles to the single stylesheet (responsive)
if ( !function_exists( 'trx_addons_cpt_events_merge_styles_responsive' ) ) {
	add_filter("trx_addons_filter_merge_styles_responsive", 'trx_addons_cpt_events_merge_styles_responsive');
	function trx_addons_cpt_events_merge_styles_responsive($list) {
		if ( trx_addons_exists_tribe_events() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar.responsive.css' ] = false;
		}
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_tribe_events_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_tribe_events_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_tribe_events_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_tribe_events_check_in_html_output', 10, 1 );
	function trx_addons_tribe_events_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_tribe_events()
			&& ! trx_addons_need_frontend_scripts( 'tribe_events' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*(tribe\\-events\\-|tribe\\-common\\-)',
							'class=[\'"][^\'"]*type\\-(tribe_events|tribe_venue|tribe_organizer)',
							'class=[\'"][^\'"]*tribe_events_cat\\-',
							),
							'the-events-calendar'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_tribe_events_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// Add sort in the query for the events
if ( !function_exists( 'trx_addons_events_add_sort_order' ) ) {
	add_filter('trx_addons_filter_add_sort_order',	'trx_addons_events_add_sort_order', 10, 3);
	function trx_addons_events_add_sort_order($q, $orderby, $order='desc') {
		if ( $orderby == 'event_date' ) {
			$q['order'] = $order;
			$q['orderby'] = 'meta_value';
			$q['meta_key'] = '_EventStartDate';
		}
		return $q;
	}
}

	
// Disable Tribe Events sort parameters in any shortcodes query with events post type
if ( !function_exists( 'trx_addons_events_query_args' ) ) {
	add_filter('trx_addons_filter_query_args',	'trx_addons_events_query_args', 10, 2);
	function trx_addons_events_query_args($q, $sc) {
		if ( trx_addons_exists_tribe_events() && ! empty( $q['post_type'] ) && in_array( Tribe__Events__Main::POSTTYPE, (array)$q['post_type'] ) ) {
			$q['tribe_suppress_query_filters'] = true;
		}
		return $q;
	}
}

// Return taxonomy for current post type (this post_type has 2+ taxonomies)
if ( !function_exists( 'trx_addons_events_post_type_taxonomy' ) ) {
	add_filter( 'trx_addons_filter_post_type_taxonomy',	'trx_addons_events_post_type_taxonomy', 10, 2 );
	function trx_addons_events_post_type_taxonomy($tax='', $post_type='') {
		if (trx_addons_exists_tribe_events() && $post_type == Tribe__Events__Main::POSTTYPE)
			$tax = Tribe__Events__Main::TAXONOMY;
		return $tax;
	}
}

// Return current page title
if ( !function_exists( 'trx_addons_events_get_blog_title' ) ) {
	add_filter( 'trx_addons_filter_get_blog_title', 'trx_addons_events_get_blog_title');
	function trx_addons_events_get_blog_title($title='') {
		if ( trx_addons_is_tribe_events_page() ) {
			if ( function_exists( 'tribe_get_events_title' ) ) {
				if ( trx_addons_is_single() ) {
					global $wp_query;
					if ( ! empty( $wp_query->queried_object ) ) {
						$title = $wp_query->queried_object->post_title;
					}
				} else {
					$title = apply_filters( 'tribe_events_title', tribe_get_events_title( false ) );
				}
			}
		}
		return $title;
	}
}

// Return link to the all events for the breadcrumbs
if ( !function_exists( 'trx_addons_events_get_blog_all_posts_link' ) ) {
	add_filter('trx_addons_filter_get_blog_all_posts_link', 'trx_addons_events_get_blog_all_posts_link', 10, 2);
	function trx_addons_events_get_blog_all_posts_link($link='', $args=array()) {
		if ( empty($link) && trx_addons_is_tribe_events_page() && trx_addons_is_singular( Tribe__Events__Main::POSTTYPE ) ) {
			if ( ( $url = get_post_type_archive_link( Tribe__Events__Main::POSTTYPE ) ) != '') {
				$obj = get_post_type_object(  Tribe__Events__Main::POSTTYPE );
				if ( is_object( $obj ) && ! empty( $obj->labels->all_items ) ) {
					$link = '<a href="' . esc_url( $url ) . '">' . esc_html( $obj->labels->all_items ) . '</a>';
				}
			}
		}
		return $link;
	}
}

	
// Add Google API key to the map's link
if ( !function_exists( 'trx_addons_events_google_maps_api' ) ) {
	add_filter('tribe_events_google_maps_api',	'trx_addons_events_google_maps_api');
	function trx_addons_events_google_maps_api($url) {
		$api_key = trx_addons_get_option('api_google');
		if ($api_key) {
			$url = trx_addons_add_to_url($url, array(
				'key' => $api_key
			));
		}
		return $url;
	}
}
	
// Repair current post after the Tribe Events spoofing it on priority 100!!!
if ( !function_exists( 'trx_addons_events_repair_spoofed_post' ) ) {
	add_action('wp_head', 'trx_addons_events_repair_spoofed_post', 101);
	function trx_addons_events_repair_spoofed_post() {

		if ( !trx_addons_exists_tribe_events() ) return;

		// hijack this method right up front if it's a password protected post and the password isn't entered
		if ( trx_addons_is_single() && post_password_required() || is_feed() ) {
			return;
		}

		global $wp_query;
		if ( $wp_query->is_main_query() && tribe_is_event_query() && tribe_get_option( 'tribeEventsTemplate', 'default' ) != '' ) {
			if (count($wp_query->posts) > 0) {
				$GLOBALS['post'] = $wp_query->posts[0];
			}
		}
	}
}

// Add hack on page 404 to prevent error message
if ( !function_exists( 'trx_addons_events_create_empty_post_on_404' ) ) {
	add_action( 'wp_head', 'trx_addons_events_create_empty_post_on_404', 1);
	function trx_addons_events_create_empty_post_on_404() {
		if ( trx_addons_exists_tribe_events() && is_404() && !isset($GLOBALS['post']) ) {
			$GLOBALS['post'] = new stdClass();
			$GLOBALS['post']->ID = 0;
			$GLOBALS['post']->post_type = 'unknown';
			$GLOBALS['post']->post_content = '';
		}
	}
}

// Replace post date with event's date for RevSlider
if ( !function_exists( 'trx_addons_events_revslider_date' ) ) {
	add_filter('revslider_slide_setLayersByPostData_post', 'trx_addons_events_revslider_date', 10, 4);
	function trx_addons_events_revslider_date($attr, $postData, $sliderID, $sliderObj) {
		if ( trx_addons_exists_tribe_events() && !empty($postData['ID']) && !empty($postData['post_type']) && $postData['post_type'] == Tribe__Events__Main::POSTTYPE) {
	        $attr['date_start'] = tribe_get_start_date($postData['ID'], true, get_option('date_format'));
	        $attr['date_end'] = tribe_get_end_date($postData['ID'], true, get_option('date_format'));
	        $attr['date'] = $attr['postDate'] = $attr['date_start'] . ' - ' . $attr['date_end'];
	    }
	    return $attr;
	}
}


// Fix issue in The Events Calendar 5.0+: with a new design of the calendar (appears after the update 5.0)
// any new queries before main posts loop breaks a calendar output.
// For example: the page header uses widgets that display one or more posts.
//-------------------------------------------------------------------------------

// If new (updated) view is used and a page template is not empty ( not equal to 'Default Events Template' ) -
// remove Events Calendar handler from the filter 'loop_start'
// before show a custom layout (it may contain shortcodes or widgets with a posts loop)
if ( !function_exists( 'trx_addons_events_fix_new_design_start' ) ) {
	add_action('trx_addons_action_before_show_layout', 'trx_addons_events_fix_new_design_start', 10, 4);
	function trx_addons_events_fix_new_design_start() {
		global $TRX_ADDONS_STORAGE;
		if ( ! isset( $TRX_ADDONS_STORAGE['events_show_layout_depth'] ) ) {
			$TRX_ADDONS_STORAGE['events_show_layout_depth'] = 1;
			if ( trx_addons_exists_tribe_events() ) {
				$opt = get_option( 'tribe_events_calendar_options' );
				// If new (updated) view is used and a page template is not empty ( not equal to 'Default Events Template' )
				if ( ! empty( $opt['views_v2_enabled'] ) && ! empty( $opt['tribeEventsTemplate'] ) ) {
					global $wp_filter;
					$TRX_ADDONS_STORAGE['events_filters'] = array(
						'loop_start' => array(
											'handler' => 'hijack_on_loop_start',
											'filters' => array()
											),
/*
						'the_post' => array(
											'handler' => 'hijack_the_post',
											'filters' => array()
											)
*/
					);
					foreach ( $TRX_ADDONS_STORAGE['events_filters'] as $f => $params ) {
						if ( ! empty( $wp_filter[ $f ] ) && ( is_array( $wp_filter[ $f ] ) || is_object( $wp_filter[ $f ] ) ) ) {
							foreach ( $wp_filter[ $f ] as $p => $cb ) {
								foreach ( $cb as $k => $v ) {
									if ( strpos( $k, $params['handler'] ) !== false ) {
										$TRX_ADDONS_STORAGE['events_filters'][$f]['filters'][$p] = array( $k => $v );
										remove_filter( $f, $v['function'], $p );
									}
								}
							}
						}
					}
				}
			}
		} else {
			$TRX_ADDONS_STORAGE['events_show_layout_depth']++;
		}
	}
}

// Restore Events Calendar handler to the filter 'loop_start'
// after the custom layout is showed
if ( !function_exists( 'trx_addons_events_fix_new_design_end' ) ) {
	add_action('trx_addons_action_after_show_layout', 'trx_addons_events_fix_new_design_end', 10, 4);
	function trx_addons_events_fix_new_design_end() {
		global $TRX_ADDONS_STORAGE, $wp_filter;
		$TRX_ADDONS_STORAGE['events_show_layout_depth']--;
		if ( $TRX_ADDONS_STORAGE['events_show_layout_depth'] == 0 && isset( $TRX_ADDONS_STORAGE['events_filters'] ) && is_array( $TRX_ADDONS_STORAGE['events_filters'] ) ) {
			foreach ( $TRX_ADDONS_STORAGE['events_filters'] as $f => $params ) {
				if ( ! empty( $params['filters'] ) && is_array( $params['filters'] ) ) {
					foreach ( $params['filters'] as $p => $cb ) {
						foreach ( $cb as $k => $v ) {
							if ( ! isset( $wp_filter[ $f ][ $p ][ $k ] ) ) {
								add_filter( $f, $v['function'], $p, $v['accepted_args'] );
							}
						}
					}
				}
			}
			unset( $TRX_ADDONS_STORAGE['events_filters'] );
		}
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar-sc.php';

// Add shortcodes to Elementor
if ( trx_addons_exists_tribe_events() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar-sc-elementor.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_tribe_events() && trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar-sc-vc.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_tribe_events() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'the-events-calendar/the-events-calendar-demo-ocdi.php';
}
