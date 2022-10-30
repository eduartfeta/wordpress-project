<?php
/**
 * Plugin support: WooCommerce
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin installed and activated
// Attention! This function is used in many files and was moved to the api.php
/*
if ( !function_exists( 'trx_addons_exists_woocommerce' ) ) {
	function trx_addons_exists_woocommerce() {
		return class_exists('Woocommerce');
	}
}
*/

// Return true, if current page is any woocommerce page
if ( !function_exists( 'trx_addons_is_woocommerce_page' ) ) {
	function trx_addons_is_woocommerce_page() {
		$rez = false;
		if (trx_addons_exists_woocommerce()) {
			$rez = is_woocommerce() || is_shop() || is_product() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_cart() || is_checkout() || is_account_page();
		}
		return $rez;
	}
}


// Return taxonomy for current post type (this post_type have 2+ taxonomies)
if ( !function_exists( 'trx_addons_woocommerce_post_type_taxonomy' ) ) {
	add_filter( 'trx_addons_filter_post_type_taxonomy',	'trx_addons_woocommerce_post_type_taxonomy', 10, 2 );
	function trx_addons_woocommerce_post_type_taxonomy($tax='', $post_type='') {
		if ($post_type == 'product') {
			$tax = 'product_cat';
		}
		return $tax;
	}
}

// Return link to main shop page for the breadcrumbs
if ( !function_exists( 'trx_addons_woocommerce_get_blog_all_posts_link' ) ) {
	add_filter('trx_addons_filter_get_blog_all_posts_link', 'trx_addons_woocommerce_get_blog_all_posts_link', 10, 2);
	function trx_addons_woocommerce_get_blog_all_posts_link($link='', $args=array()) {
		if (empty($link) && trx_addons_is_woocommerce_page() && !is_shop()) {
			if (($url = trx_addons_woocommerce_get_shop_page_link()) != '') {
				$id = trx_addons_woocommerce_get_shop_page_id();
				$front_id = get_option( 'show_on_front' ) == 'page' ? (int) get_option( 'page_on_front' ) : 0;
				if ( $front_id == 0 || $id == 0 || $front_id != $id ) {
					$link = '<a href="'.esc_url($url).'">'.($id ? get_the_title($id) : esc_html__('Shop', 'trx_addons')).'</a>';
				} else {
					$link = '#';	// To disable link
				}
			}
		}
		return $link;
	}
}

// Return URL to main shop page for the search form
if ( ! function_exists( 'trx_addons_woocommerce_search_form_url' ) ) {
	add_filter( 'trx_addons_filter_search_form_url', 'trx_addons_woocommerce_search_form_url' );
	function trx_addons_woocommerce_search_form_url( $url ) {
		if ( trx_addons_exists_woocommerce() && trx_addons_is_woocommerce_page() && is_shop() ) {
			$shop_url = trx_addons_woocommerce_get_shop_page_link();
			if ( ! empty( $shop_url ) ) {
				$url = $shop_url;
			}
		}
		return $url;
	}
}

// Return shop page ID
if ( !function_exists( 'trx_addons_woocommerce_get_shop_page_id' ) ) {
	function trx_addons_woocommerce_get_shop_page_id() {
		return get_option('woocommerce_shop_page_id');
	}
}

// Return shop page link
if ( !function_exists( 'trx_addons_woocommerce_get_shop_page_link' ) ) {
	function trx_addons_woocommerce_get_shop_page_link() {
		$url = '';
		$id = trx_addons_woocommerce_get_shop_page_id();
		if ($id) $url = get_permalink($id);
		return $url;
	}
}

// Return current page title
if ( !function_exists( 'trx_addons_woocommerce_get_blog_title' ) ) {
	add_filter( 'trx_addons_filter_get_blog_title', 'trx_addons_woocommerce_get_blog_title');
	function trx_addons_woocommerce_get_blog_title($title='') {
		if ( trx_addons_exists_woocommerce() && trx_addons_is_woocommerce_page() && is_shop() ) {
			$id = trx_addons_woocommerce_get_shop_page_id();
			$title = $id ? get_the_title($id) : esc_html__('Shop', 'trx_addons');
		}
		return $title;
	}
}

// Return filter name from attribute taxonomy name
if ( !function_exists( 'trx_addons_woocommerce_get_filter_name_from_attribute' ) ) {
	function trx_addons_woocommerce_get_filter_name_from_attribute($tax_name, $reverse = false) {
		return ( ! $reverse ? 'filter_' : '' )
				. ( function_exists( 'wc_attribute_taxonomy_slug' )
					? wc_attribute_taxonomy_slug( $tax_name )
					: ( substr( $tax_name, 0, 3 ) == 'pa_' ? substr($tax_name, 3) : $tax_name )
					)
				. ( $reverse ? '_filter' : '' );
	}
}

// Add item to the current user menu
if ( !function_exists( 'trx_addons_woocommerce_login_menu_settings' ) ) {
	add_action("trx_addons_action_login_menu_settings", 'trx_addons_woocommerce_login_menu_settings');
	function trx_addons_woocommerce_login_menu_settings() {
		if (trx_addons_exists_woocommerce()) {
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			if ( !empty( $myaccount_page_id ) ) {
				?><li class="menu-item trx_addons_icon-edit"><a href="<?php echo esc_url( get_permalink( $myaccount_page_id ) ); ?>"><span><?php esc_html_e('My account', 'trx_addons'); ?></span></a></li><?php
			}
		}
	}
}


// Return value of the custom field for the custom blog items
if ( !function_exists( 'trx_addons_woocommerce_custom_meta_value' ) ) {
	add_filter( 'trx_addons_filter_custom_meta_value', 'trx_addons_woocommerce_custom_meta_value', 10, 2 );
	function trx_addons_woocommerce_custom_meta_value($value, $key) {
		if (get_post_type() == 'product' && trx_addons_exists_woocommerce()) {
			global $product;
			if (is_object($product)) {
				if ($key == 'price') {
					$value = $product->get_price_html();
				} else if (in_array($key, array('rating', 'rating_text', 'rating_icons', 'rating_stars')) && get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
					$value = $key == 'rating_text'
								? $product->get_average_rating()
								: wc_get_rating_html( $product->get_average_rating() );
				}
			}
		}
		return $value;
	}
}


// Return layout of the button 'Add to cart' for the custom blog items
if ( !function_exists( 'trx_addons_woocommerce_blog_item_button' ) ) {
	add_filter( 'trx_addons_filter_blog_item_button', 'trx_addons_woocommerce_blog_item_button', 10, 2 );
	function trx_addons_woocommerce_blog_item_button($output, $args) {
		if ( !empty($args['button_link']) && $args['button_link'] == 'cart' && trx_addons_exists_woocommerce() && get_post_type() == 'product' ) {
			$ajax = 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' );
			if ( $ajax ) {
				wp_enqueue_script( 'wc-add-to-cart' );
			}
			ob_start();
			woocommerce_template_loop_add_to_cart(array(
				'class' => 'sc_button button add_to_cart_button' . ($ajax ? ' ajax_add_to_cart' : '')
			));
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}
}

// Return layout of the button 'Add to cart' for the custom blog items
if ( !function_exists( 'trx_addons_woocommerce_blog_item_button_class' ) ) {
	add_filter( 'trx_addons_filter_blog_item_button_class', 'trx_addons_woocommerce_blog_item_button_class', 10, 2 );
	function trx_addons_woocommerce_blog_item_button_class($class, $args) {
		if ( !empty($args['button_link']) && $args['button_link'] == 'cart' && trx_addons_exists_woocommerce() && get_post_type() == 'product' ) {
			$class .= ' woocommerce';
		}
		return $class;
	}
}

// Prevent WooCommerce from redirecting to the "My Account" page.
if ( ! function_exists( 'trx_addons_woocommerce_prevent_admin_access' ) ) {
	add_filter( 'woocommerce_prevent_admin_access', 'trx_addons_woocommerce_prevent_admin_access' );
	function trx_addons_woocommerce_prevent_admin_access( $redirect_to ) {
		$current_user = wp_get_current_user();
		if ( is_array( $current_user->roles ) && apply_filters( 'trx_addons_filter_allow_admin_access', false, $current_user->roles ) ) {
			return false;
		}
		return $redirect_to;
	}
}


// WooCommerce Tools widgets area (before the products loop)
//------------------------------------------------------------------------

// Register custom widgets area for search
if ( ! function_exists('trx_addons_woocommerce_register_sidebar') ) {
	add_action('widgets_init', 'trx_addons_woocommerce_register_sidebar', 20);
	function trx_addons_woocommerce_register_sidebar() {
		global $TRX_ADDONS_STORAGE;
		register_sidebar( apply_filters( 'trx_addons_filter_register_sidebar', array(
										'name'          => __( 'WooCommerce Tools', 'trx_addons' ),
										'description'   => __( 'Widgets before the products loop', 'trx_addons' ),
										'id'            => 'trx_addons_woocommerce_tools',
										'before_widget' => $TRX_ADDONS_STORAGE['widgets_args']['before_widget'],
										'after_widget'  => $TRX_ADDONS_STORAGE['widgets_args']['after_widget'],
										'before_title'  => $TRX_ADDONS_STORAGE['widgets_args']['before_title'],
										'after_title'   => $TRX_ADDONS_STORAGE['widgets_args']['after_title']
										) )
								);
	}
}

// Show custom widgets area
if ( ! function_exists( 'trx_addons_woocommerce_show_sidebar' ) ) {
	add_action( 'woocommerce_before_shop_loop', 'trx_addons_woocommerce_show_sidebar' );
	add_action( 'woocommerce_no_products_found', 'trx_addons_woocommerce_show_sidebar', 1 );
	function trx_addons_woocommerce_show_sidebar() {
		if ( is_active_sidebar( 'trx_addons_woocommerce_tools' ) ) {
			?><div class="trx_addons_woocommerce_tools widget_area"><?php
				do_action( 'trx_addons_action_before_woocommerce_tools' );
				dynamic_sidebar( 'trx_addons_woocommerce_tools' );
				do_action( 'trx_addons_action_after_woocommerce_tools' );
			?></div><?php
		}
	}
}


// Child categories in the header
//------------------------------------------------------------------------

// Show child categories
if ( ! function_exists( 'trx_addons_woocommerce_show_child_categories' ) ) {
	add_action( 'trx_addons_action_after_layouts_title_block', 'trx_addons_woocommerce_show_child_categories' );
	function trx_addons_woocommerce_show_child_categories() {
		// Change false to true in the filter argument below to display child categories in the custom header
		if ( apply_filters( 'trx_addons_filter_woocommerce_show_child_categories', false ) && trx_addons_exists_woocommerce() && ( is_shop() || is_product_category() ) ) {
			$taxonomy = 'product_cat';
			$params = trx_addons_widget_woocommerce_search_query_params( array(
																			array( 'filter' => $taxonomy )
																			),
																			true
																		);
			$terms = trx_addons_get_list_terms( false, $taxonomy, array(
																		'hide_empty' => 1,
																		'parent' => $params[$taxonomy],
																		'return_key' => 'id',
																		'pad_count' => 1
																		)
												);

			if ( count( $terms ) > 0 ) {
				$terms = array_filter( $terms, function( $term ) {
					return substr( $term, 0, 2) !== '- ';
				} );

				if ( count( $terms ) > 0 ) {
					$buttons = array();
					foreach ( $terms as $id => $title ) {
						$image = trx_addons_get_term_image($id, $taxonomy);
						$image = ! empty($image) ? trx_addons_add_thumb_size($image, trx_addons_get_thumb_size( 'medium' ) ) : "";
						$image_small = trx_addons_get_term_image_small($id, $taxonomy);
						if ( empty( $image_small ) ) {
							$icon = trx_addons_get_term_icon($id, $taxonomy);
						}
						$buttons[] = apply_filters( 'trx_addons_filter_categories_list_button_args', array(
							"type" => "default",
							"size" => "normal",
							"text_align" => "none",
							"bg_image" => ! empty($image) ? $image : "",
							"image" => ! empty($image_small) ? $image_small : "",
							"icon" => empty($image_small) && ! empty( $icon ) ? $icon : "",
							"icon_position" => "left",
							"title" => $title,
							"subtitle" => "",
							"link" => get_term_link($id, $taxonomy),	// trx_addons_add_to_url( trx_addons_woocommerce_get_shop_page_link(), array( $taxonomy => $k ) )
							"css" => ""
						) );
					}
					trx_addons_show_layout( trx_addons_sc_button( array( 'buttons' => $buttons ) ), '<div class="trx_addons_woocommerce_child_categories">', '</div>' );
				}
			}

			trx_addons_sc_layouts_showed('child_categories', true);
		}
	}
}


// Load required scripts and styles
//------------------------------------------------------------------------

// Load required styles and scripts for the frontend
if ( ! function_exists( 'trx_addons_woocommerce_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_woocommerce_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_woocommerce_load_scripts_front', 10, 1 );
	function trx_addons_woocommerce_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_woocommerce() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_woocommerce_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'sc_woocommerce',
									'entries' => array(
										//array( 'type' => 'gb',  'sc' => 'wp:trx-addons/events' ),	// This sc is not exists for GB
										// Core WooCommerce shortcodes
										array( 'type' => 'sc',  'sc' => 'product' ),
										array( 'type' => 'sc',  'sc' => 'product_page' ),
										array( 'type' => 'sc',  'sc' => 'product_category' ),
										array( 'type' => 'sc',  'sc' => 'product_categories' ),
										array( 'type' => 'sc',  'sc' => 'product_add_to_cart' ),
										array( 'type' => 'sc',  'sc' => 'product_add_to_cart_url' ),
										array( 'type' => 'sc',  'sc' => 'product_attribute' ),
										array( 'type' => 'sc',  'sc' => 'recent_products' ),
										array( 'type' => 'sc',  'sc' => 'sale_products' ),
										array( 'type' => 'sc',  'sc' => 'best_selling_products' ),
										array( 'type' => 'sc',  'sc' => 'top_rated_products' ),
										array( 'type' => 'sc',  'sc' => 'featured_products' ),
										array( 'type' => 'sc',  'sc' => 'related_products' ),
										array( 'type' => 'sc',  'sc' => 'shop_messages' ),
										array( 'type' => 'sc',  'sc' => 'order_tracking' ),
										array( 'type' => 'sc',  'sc' => 'cart' ),
										array( 'type' => 'sc',  'sc' => 'checkout' ),
										array( 'type' => 'sc',  'sc' => 'my_account' ),
										// Our shortcodes and widgets
										array( 'type' => 'sc',  'sc' => 'trx_sc_extended_products' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_sc_extended_products"' ),
										array( 'type' => 'sc',  'sc' => 'trx_widget_woocommerce_search' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_widget_woocommerce_search"' ),
										array( 'type' => 'sc',  'sc' => 'trx_widget_woocommerce_title' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"trx_widget_woocommerce_title"' ),
										// Shortcodes in Elementor
										array( 'type' => 'elm', 'sc' => '"shortcode":"[product' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[recent_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[sale_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[best_selling_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[top_rated_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[featured_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[related_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[shop_messages' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[order_tracking' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[cart' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[checkout' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[my_account' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_sc_extended_products' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_widget_woocommerce_search' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[trx_widget_woocommerce_title' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			wp_enqueue_style(  'trx_addons-woocommerce', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.css'), array(), null );
			wp_enqueue_script( 'trx_addons-woocommerce', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.js'), array('jquery'), null, true );
			do_action( 'trx_addons_action_load_scripts_front', $force, 'woocommerce' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'woocommerce', 2 );
		}
	}
}

// Enqueue responsive styles for frontend
if ( ! function_exists( 'trx_addons_woocommerce_load_scripts_front_responsive' ) ) {
	add_action( 'wp_enqueue_scripts', 'trx_addons_woocommerce_load_scripts_front_responsive', TRX_ADDONS_ENQUEUE_RESPONSIVE_PRIORITY );
	add_action( 'trx_addons_action_load_scripts_front_woocommerce', 'trx_addons_woocommerce_load_scripts_front_responsive', 10, 1 );
	function trx_addons_woocommerce_load_scripts_front_responsive( $force = false ) {
		static $loaded = false;
		if ( ! $loaded && (
			current_action() == 'wp_enqueue_scripts' && trx_addons_need_frontend_scripts( 'woocommerce' )
			||
			current_action() != 'wp_enqueue_scripts' && $force === true
			)
		) {
			$loaded = true;
			wp_enqueue_style(  'trx_addons-woocommerce-responsive', trx_addons_get_file_url(TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.responsive.css'), array(), null, trx_addons_media_for_load_css_responsive( 'woocommerce', 'sm' ) );
		}
	}
}
	
// Merge specific styles to the single stylesheet
if ( ! function_exists( 'trx_addons_woocommerce_merge_styles' ) ) {
	add_filter( 'trx_addons_filter_merge_styles', 'trx_addons_woocommerce_merge_styles' );
	function trx_addons_woocommerce_merge_styles( $list ) {
		if ( trx_addons_exists_woocommerce() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.css' ] = false;
		}
		return $list;
	}
}

// Merge shortcode's specific styles to the single stylesheet (responsive)
if ( ! function_exists( 'trx_addons_woocommerce_merge_styles_responsive' ) ) {
	add_filter( 'trx_addons_filter_merge_styles_responsive', 'trx_addons_woocommerce_merge_styles_responsive' );
	function trx_addons_woocommerce_merge_styles_responsive( $list ) {
		if ( trx_addons_exists_woocommerce() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.responsive.css' ] = false;
		}
		return $list;
	}
}

// Merge specific scripts into single file
if ( ! function_exists( 'trx_addons_woocommerce_merge_scripts' ) ) {
	add_action( 'trx_addons_filter_merge_scripts', 'trx_addons_woocommerce_merge_scripts', 11 );
	function trx_addons_woocommerce_merge_scripts( $list ) {
		if ( trx_addons_exists_woocommerce() ) {
			$list[ TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce.js' ] = false;
		}
		return $list;
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( ! function_exists( 'trx_addons_woocommerce_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_woocommerce_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_woocommerce_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_woocommerce_check_in_html_output', 10, 1 );
	function trx_addons_woocommerce_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_woocommerce()
			&& ! trx_addons_need_frontend_scripts( 'woocommerce' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'<(div|ul|li)[^>]*class=[\'"][^\'"]*(woocommerce|wc\\-block\\-grid__product)',
//							'class=[\'"][^\'"]*sc_layouts_cart',
							'class=[\'"][^\'"]*type\\-(product|product_variation|shop_coupon|shop_webhook)',
							'class=[\'"][^\'"]*(product_type|product_visibility|product_cat|product_tag|product_shipping_class)\\-',
							),
							'woocommerce'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_woocommerce_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_woocommerce_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_woocommerce_filter_head_output', 10, 1 );
	function trx_addons_woocommerce_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_woocommerce()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'woocommerce' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'woocommerce' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/woocommerce/assets/[^>]*>#', '', $content );
			$content = preg_replace( '#<style[^>]*id=[\'"]woocommerce-[^>]*>[\\s\\S]*</style>#U', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_woocommerce_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_woocommerce_filter_body_output', 10, 1 );
	function trx_addons_woocommerce_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_woocommerce()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'woocommerce' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'woocommerce' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/woocommerce/assets/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/woocommerce/assets/[^>]*>[\\s\\S]*</script>#U', '', $content );
			$content = preg_replace( '#<script[^>]*id=[\'"]woocommerce-[^>]*>[\\s\\S]*</script>#U', '', $content );
			$content = preg_replace( '#<script[^>]*id=[\'"]wc-cart-[^>]*>[\\s\\S]*</script>#U', '', $content );
			$content = preg_replace( '#<script[^>]*id=[\'"]wc-add-to-cart-[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}


// Load WooCommerce Extended Attributes
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce-extended-attributes.php';

// Load WooCommerce Extended Shortcode 'Products'
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce-extended-products.php';

// Load WooCommerce Search Widget
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/widget.woocommerce_search.php';

// Load WooCommerce Title Widget
require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/widget.woocommerce_title.php';

// Add Elementor's support
if ( trx_addons_exists_woocommerce() && trx_addons_exists_elementor() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce-sc-elementor.php';
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_woocommerce() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/woocommerce-demo-ocdi.php';
}
