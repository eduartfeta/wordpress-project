<?php
/**
 * File system manipulations
 *
 * @package ThemeREX Addons
 * @since v1.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

//define( 'TRX_ADDONS_REMOTE_USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0' );
define( 'TRX_ADDONS_REMOTE_USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36' );

/* Enqueue scripts and styles
------------------------------------------------------------------------------------- */

//  Enqueue slider scripts and styles
if ( !function_exists( 'trx_addons_enqueue_slider' ) ) {
	function trx_addons_enqueue_slider($engine='swiper') {
		if ($engine=='swiper') {
			if ( false ) {	// true - load from CDN, false - load local version
				wp_enqueue_style(  'swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), null );
				wp_enqueue_script( 'swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true );
			} else {
				wp_enqueue_style(  'swiper', trx_addons_get_file_url('js/swiper/swiper.min.css'), array(), null );
				wp_enqueue_script( 'swiper', trx_addons_get_file_url('js/swiper/swiper.min.js'), array(), null, true );
			}
		} else if ($engine=='elastistack') {
			wp_enqueue_script( 'modernizr', trx_addons_get_file_url('js/elastistack/modernizr.custom.js'), array(), null, true );
			wp_enqueue_script( 'draggabilly', trx_addons_get_file_url('js/elastistack/draggabilly.pkgd.min.js'), array(), null, true );
			wp_enqueue_script( 'elastistack', trx_addons_get_file_url('js/elastistack/elastistack.js'), array(), null, true );
		}
	}
}

// Load if current mode is Preview in the PageBuilder
if ( !function_exists( 'trx_addons_enqueue_slider_pagebuilder_preview_scripts' ) ) {
	add_action("trx_addons_action_pagebuilder_preview_scripts", 'trx_addons_enqueue_slider_pagebuilder_preview_scripts', 10, 1);
	function trx_addons_enqueue_slider_pagebuilder_preview_scripts($editor='') {
		trx_addons_enqueue_slider('swiper');
		trx_addons_enqueue_slider('elastistack');
	}
}

// Load if slider present in the shortcode output
if ( ! function_exists( 'trx_addons_enqueue_slider_sc_output' ) ) {
	add_filter( 'trx_addons_sc_output', 'trx_addons_enqueue_slider_sc_output', 10, 4 );
	function trx_addons_enqueue_slider_sc_output( $output, $sc, $atts, $content ) {
		if ( strpos( $output, 'slider_swiper' ) !== false ) {
			trx_addons_enqueue_slider('swiper');
		} else if ( strpos( $output, 'slider_elastistack' ) !== false ) {
			trx_addons_enqueue_slider('elastistack');
		}
		return $output;
	}
}


// Enqueue popup scripts and styles
// Link must have attribute: data-rel="popupEngine" or data-rel="popupEngine[gallery]"
if ( !function_exists( 'trx_addons_enqueue_popup' ) ) {
	function trx_addons_enqueue_popup($engine='magnific') {
		if ($engine=='pretty') {
			wp_enqueue_style(  'prettyphoto',	trx_addons_get_file_url('js/prettyphoto/css/prettyPhoto.css'), array(), null );
			wp_enqueue_script( 'prettyphoto',	trx_addons_get_file_url('js/prettyphoto/jquery.prettyPhoto.min.js'), array('jquery'), 'no-compose', true );
		} else if ($engine=='magnific') {
			wp_enqueue_style(  'magnific-popup',trx_addons_get_file_url('js/magnific/magnific-popup.min.css'), array(), null );
			wp_enqueue_script( 'magnific-popup',trx_addons_get_file_url('js/magnific/jquery.magnific-popup.min.js'), array('jquery'), null, true );
		}
	}
}

//  Enqueue WP colorpicker in front-end
if ( !function_exists( 'trx_addons_enqueue_wp_color_picker' ) ) {
	function trx_addons_enqueue_wp_color_picker() {
	    wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), null, true);
		wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), null, true);
		wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', array(
			'clear' => __( 'Clear', 'trx_addons' ),
			'defaultString' => __( 'Default', 'trx_addons' ),
			'pick' => __( 'Select Color', 'trx_addons' ),
			'current' => __( 'Current Color', 'trx_addons' ),
		)); 
	
	}
}

//  Enqueue Google map script
if ( !function_exists( 'trx_addons_enqueue_googlemap' ) ) {
	function trx_addons_enqueue_googlemap() {
		$api_key = trx_addons_get_option('api_google');
		if ( trx_addons_is_on( trx_addons_get_option( 'api_google_load' ) ) && ! empty( $api_key ) ) {	
			$places_key = function_exists( 'trx_addons_google_places_api_key' ) ? trx_addons_google_places_api_key() : '';
			$params = array();
			if ( ! empty( $api_key ) ) {
				$params['key'] = $api_key;
			}
			if ( ! empty( $places_key ) ) {
				$params['libraries'] = 'places';
			}
			$url = 'https://maps.googleapis.com/maps/api/js';
			if ( count( $params ) > 0 ) {
				$url = trx_addons_add_to_url( $url, $params );
			}
			wp_enqueue_script( 'google-maps', $url, array(), null, true );
		}
	}
}

//  Enqueue OpenStreen map script and style
if ( !function_exists( 'trx_addons_enqueue_osmap' ) ) {
	function trx_addons_enqueue_osmap() {
		if ( trx_addons_is_on( trx_addons_get_option('api_openstreet_load') ) ) {
			// LeaFlet OSM lib 
			wp_enqueue_style(  'openstreet-maps', 'https://unpkg.com/leaflet@1.4.0/dist/leaflet.css', array(), null );
			wp_enqueue_script( 'openstreet-maps', 'https://unpkg.com/leaflet@1.4.0/dist/leaflet.js', array(), null, true );
			// Geocoder Control
			wp_enqueue_style(  'openstreet-maps-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css', array(), null );
			wp_enqueue_script( 'openstreet-maps-geocoder', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array(), null, true );
			// Clustering
			wp_enqueue_style(  'openstreet-maps-cluster', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css', array(), null );
			wp_enqueue_style(  'openstreet-maps-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css', array(), null );
			wp_enqueue_script( 'openstreet-maps-cluster', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js', array(), null, true );
			// Mapbox GL
			if ( trx_addons_get_option('api_openstreet_tiler') == 'vector' ) {
				wp_enqueue_style(  'openstreet-maps-mapbox-gl', 'https://cdn.maptiler.com/mapbox-gl-js/v0.53.0/mapbox-gl.css', array(), null );
				wp_enqueue_script( 'openstreet-maps-mapbox-gl', 'https://cdn.maptiler.com/mapbox-gl-js/v0.53.0/mapbox-gl.js', array(), null, true );
				wp_enqueue_script( 'openstreet-maps-mapbox-gl-leaflet', 'https://cdn.maptiler.com/mapbox-gl-leaflet/latest/leaflet-mapbox-gl.js', array(), null, true );
			}
		}
	}
}

//  Enqueue Select2 scripts and styles
if ( !function_exists( 'trx_addons_enqueue_select2' ) ) {
	function trx_addons_enqueue_select2() {
		wp_enqueue_style(  'select2', trx_addons_get_file_url('js/select2/select2.min.css'), array(), null );
		wp_enqueue_script( 'select2', trx_addons_get_file_url('js/select2/select2.min.js'), array('jquery'), null, true );
	}
}

//  Enqueue masonry scripts and styles
if ( !function_exists( 'trx_addons_enqueue_masonry' ) ) {
	function trx_addons_enqueue_masonry() {
		static $once = true;
		if ( $once ) {
			$once = false;
			wp_enqueue_script( 'imagesloaded' );
			wp_enqueue_script( 'masonry' );
			trx_addons_lazy_load_off();
		}
	}
}

//  Enqueue TweenMax script
if ( !function_exists( 'trx_addons_enqueue_tweenmax' ) ) {
	function trx_addons_enqueue_tweenmax() {
		wp_enqueue_script( 'tweenmax', trx_addons_get_file_url('js/tweenmax/tweenmax.min.js'), array(), null, true );
	}
}

//  Enqueue ScrollMagic script
if ( !function_exists( 'trx_addons_enqueue_scroll_magic' ) ) {
	function trx_addons_enqueue_scroll_magic() {
		wp_enqueue_script( 'scroll-magic', trx_addons_get_file_url( 'js/tweenmax/ScrollMagic.js' ), array(), null, true );
		wp_enqueue_script( 'animation-gsap', trx_addons_get_file_url( 'js/tweenmax/animation.gsap.js' ), array(), null, true );
	}
}

//  Enqueue Parallax script
if ( !function_exists( 'trx_addons_enqueue_parallax' ) ) {
	function trx_addons_enqueue_parallax() {
		trx_addons_enqueue_tweenmax();	// Must be first!
		trx_addons_enqueue_scroll_magic();
	}
}


/* Merge scripts and styles
------------------------------------------------------------------------------------- */

// Merge all separate styles and scripts to the single file to increase page upload speed
if ( !function_exists( 'trx_addons_merge_js' ) ) {
	function trx_addons_merge_js($to, $list) {
		$s = '';
		foreach ($list as $f) {
			$s .= trx_addons_fgc(trx_addons_get_file_dir($f));
		}
		if ( $s != '') {
			$file_dir = trx_addons_get_file_dir( $to );
			if ( empty( $file_dir ) && strpos( $to, '-full.js' ) !== false ) {
				$file_dir = trx_addons_get_file_dir( str_replace( '-full.js', '.js', $to ) );
				if ( ! empty( $file_dir ) ) {
					$file_dir = str_replace( '.js', '-full.js', $file_dir );
				}
			}
			trx_addons_fpc( $file_dir,
				'/* ' 
				. strip_tags( __("ATTENTION! This file was generated automatically! Don't change it!!!", 'trx_addons') ) 
				. "\n----------------------------------------------------------------------- */\n"
				. apply_filters( 'trx_addons_filter_js_output', trx_addons_minify_js( $s ), $to )
			);
		}
	}
}


// Merge styles to the CSS file
if ( ! function_exists( 'trx_addons_merge_css' ) ) {
	function trx_addons_merge_css( $to, $list, $need_responsive = false ) {
		global $TRX_ADDONS_STORAGE;
		$responsive = $TRX_ADDONS_STORAGE['responsive'];
		if ($need_responsive) $responsive = apply_filters('trx_addons_filter_responsive_sizes', $responsive);
		$sizes  = array();
		$output = '';
		foreach ( $list as $f ) {
			$fdir = trx_addons_get_file_dir( $f );
			if ( '' != $fdir ) {
				$css = trx_addons_fgc( $fdir );
				if ( $need_responsive ) {
					$pos = 0;
					while( false !== $pos ) {
						$pos = strpos($css, '@media' );
						if ( false !== $pos ) {
							$pos += 7;
							$pos_lbrace = strpos( $css, '{', $pos );
							$cnt = 0;
							$in_comment = false;
							for ( $pos_rbrace = $pos_lbrace + 1; $pos_rbrace < strlen( $css ); $pos_rbrace++ ) {
								if ( $in_comment ) {
									if ( substr( $css, $pos_rbrace, 2 ) == '*/' ) {
										$pos_rbrace++;
										$in_comment = false;
									}
								} else if ( substr( $css, $pos_rbrace, 2 ) == '/*' ) {
									$pos_rbrace++;
									$in_comment = true;
								} else if ( substr( $css, $pos_rbrace, 1 ) == '{' ) {
									$cnt++;
								} elseif ( substr( $css, $pos_rbrace, 1 ) == '}' ) {
									if ( $cnt > 0 ) {
										$cnt--;
									} else {
										break;
									}
								}
							}
							$media = trim( substr( $css, $pos, $pos_lbrace - $pos ) );
							if ( empty( $sizes[ $media ] ) ) {
								$sizes[ $media ] = '';
							}
							$sizes[ $media ] .= "\n\n" . apply_filters( 'trx_addons_filter_merge_css', substr( $css, $pos_lbrace + 1, $pos_rbrace - $pos_lbrace - 1 ) );
							$css = substr( $css, $pos_rbrace + 1);
						}
					}
				} else {
					$output .= "\n\n" . apply_filters( 'trx_addons_filter_merge_css', $css );
				}
			}
		}
		if ( $need_responsive ) {
			foreach ( $responsive as $k => $v ) {
				$media = ( ! empty( $v['min'] ) ? "(min-width: {$v['min']}px)" : '' )
						. ( ! empty( $v['min'] ) && ! empty( $v['max'] ) ? ' and ' : '' )
						. ( ! empty( $v['max'] ) ? "(max-width: {$v['max']}px)" : '' );
				if ( ! empty( $sizes[ $media ] ) ) {
					$output .= "\n\n"
							// Translators: Add responsive size's name to the comment
							. strip_tags( sprintf( __( '/* SASS Suffix: --%s */', 'trx_addons' ), $k ) )
							. "\n"
							. "@media {$media} {\n"
								. $sizes[ $media ]
							. "\n}\n";
					unset( $sizes[ $media ] );
				}
			}
			if ( count( $sizes ) > 0 ) {
				$output .= "\n\n"
						. strip_tags( __( '/* Unknown Suffixes: */', 'trx_addons' ) );
				foreach ( $sizes as $k => $v ) {
					$output .= "\n\n"
							. "@media {$k} {\n"
								. $v
							. "\n}\n";
				}
			}
		}
		if ( $output != '') {
			$file_dir = trx_addons_get_file_dir( $to );
			if ( empty( $file_dir ) && strpos( $to, '-full.css' ) !== false ) {
				$file_dir = trx_addons_get_file_dir( str_replace( '-full.css', '.css', $to ) );
				if ( ! empty( $file_dir ) ) {
					$file_dir = str_replace( '.css', '-full.css', $file_dir );
				}
			}
			trx_addons_fpc( $file_dir,
				'/* ' 
				. strip_tags( __("ATTENTION! This file was generated automatically! Don't change it!!!", 'trx_addons') ) 
				. "\n----------------------------------------------------------------------- */\n"
				. apply_filters( 'trx_addons_filter_css_output', trx_addons_minify_css( $output ), $to )
			);
		}
	}
}


// Merge styles to the SASS file
if ( !function_exists( 'trx_addons_merge_sass' ) ) {
	function trx_addons_merge_sass($to, $list, $need_responsive=false, $root='../') {
		global $TRX_ADDONS_STORAGE;
		$responsive = $TRX_ADDONS_STORAGE['responsive'];
		if ($need_responsive) $responsive = apply_filters('trx_addons_filter_responsive_sizes', $responsive);
		$sass = array(
			'import' => '',
			'sizes'  => array()
			);
		$save = false;
		foreach ($list as $f) {
			$add = false;
			if (($fdir = trx_addons_get_file_dir($f)) != '') {
				if ($need_responsive) {
					$css = trx_addons_fgc($fdir);
					if (strpos($css, '@required')!==false) $add = true;
					foreach ($responsive as $k=>$v) {
						if (preg_match("/([\d\w\-_]+\-\-{$k})\(/", $css, $matches)) {
							$sass['sizes'][$k] = (!empty($sass['sizes'][$k]) ? $sass['sizes'][$k] : '') . "\t@include {$matches[1]}();\n";
							$add = true;
						}
					}
				} else
					$add = true;
			}
			if ($add) {
				$sass['import'] .= apply_filters('trx_addons_filter_sass_import', "@import \"{$root}{$f}\";\n", $f);
				$save = true;
			}
		}
		if ($save) {
			$output = '/* ' 
					. strip_tags( __("ATTENTION! This file was generated automatically! Don't change it!!!", 'trx_addons') ) 
					. "\n----------------------------------------------------------------------- */\n"
					. $sass['import'];
			if ($need_responsive) {
				foreach ($responsive as $k => $v) {
					if (!empty($sass['sizes'][$k])) {
						$output .= "\n\n"
								. strip_tags( sprintf( __("/* SASS Suffix: --%s */", 'trx_addons'), $k) )
								. "\n"
								. "@media " . (!empty($v['min']) ? "(min-width: {$v['min']}px)" : '')
											. (!empty($v['min']) && !empty($v['max']) ? ' and ' : '')
											. (!empty($v['max']) ? "(max-width: {$v['max']}px)" : '')
											. " {\n"
												. $sass['sizes'][$k]
											. "}\n";
					}
				}
			}
			trx_addons_fpc(
				trx_addons_get_file_dir($to),
				apply_filters( 'trx_addons_filter_sass_output', $output, $to )
			);
		}
	}
}


/* Process loading scripts and styles
------------------------------------------------------------------------------------- */
if (!function_exists('trx_addons_process_styles')) {
	add_filter('style_loader_tag', 'trx_addons_process_styles', 10, 4);
	function trx_addons_process_styles($tag, $handle='', $href='', $media='') {
		return apply_filters( 'trx_addons_filter_process_styles', $tag, $handle, $href, $media);
	}
}

if (!function_exists('trx_addons_process_scripts')) {
	add_filter('script_loader_tag', 'trx_addons_process_scripts', 10, 3);
	function trx_addons_process_scripts($tag, $handle='', $href='') {
		return apply_filters( 'trx_addons_filter_process_scripts', $tag, $handle, $href);
	}
}

/* Check if file/folder present in the child theme and return path (url) to it. 
   Else - path (url) to file in the main theme dir
------------------------------------------------------------------------------------- */
if (!function_exists('trx_addons_get_file_dir')) {	
	function trx_addons_get_file_dir($file, $return_url=false) {
		if ($file[0]=='/') $file = substr($file, 1);
		$theme_dir = get_template_directory().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$theme_url = get_template_directory_uri().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$child_dir = get_stylesheet_directory().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$child_url = get_stylesheet_directory_uri().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$dir = '';
		if (($filtered_dir = apply_filters('trx_addons_filter_get_theme_file_dir', '', TRX_ADDONS_PLUGIN_BASE.'/'.($file), $return_url)) != '')
			$dir = $filtered_dir;
		else if ($theme_dir != $child_dir && file_exists(($child_dir).($file)))
			$dir = ($return_url ? $child_url : $child_dir) . trx_addons_check_min_file($file, $child_dir);
		else if (file_exists(($theme_dir).($file)))
			$dir = ($return_url ? $theme_url : $theme_dir) . trx_addons_check_min_file($file, $theme_dir);
		else if (file_exists(TRX_ADDONS_PLUGIN_DIR . ($file)))
			$dir = ($return_url ? TRX_ADDONS_PLUGIN_URL : TRX_ADDONS_PLUGIN_DIR) . trx_addons_check_min_file($file, TRX_ADDONS_PLUGIN_DIR);
		return apply_filters( 'trx_addons_filter_get_file_dir', $dir, $file, $return_url );
	}
}

if (!function_exists('trx_addons_get_file_url')) {	
	function trx_addons_get_file_url($file) {
		return trx_addons_get_file_dir($file, true);
	}
}

// Return file extension from full name/path
if (!function_exists('trx_addons_get_file_ext')) {	
	function trx_addons_get_file_ext($file) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		return empty($ext) ? '' : $ext;
	}
}

// Return file name from full name/path
if (!function_exists('trx_addons_get_file_name')) {	
	function trx_addons_get_file_name($file, $without_ext=true) {
		$parts = pathinfo($file);
		return !empty($parts['filename']) && $without_ext ? $parts['filename'] : $parts['basename'];
	}
}

// Detect folder location (in the child theme or in the main theme)
if (!function_exists('trx_addons_get_folder_dir')) {	
	function trx_addons_get_folder_dir($folder, $return_url=false) {
		if ($folder[0]=='/') $folder = substr($folder, 1);
		$theme_dir = get_template_directory().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$theme_url = get_template_directory_uri().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$child_dir = get_stylesheet_directory().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$child_url = get_stylesheet_directory_uri().'/'.TRX_ADDONS_PLUGIN_BASE.'/';
		$dir = '';
		if (($filtered_dir = apply_filters('trx_addons_filter_get_theme_folder_dir', '', TRX_ADDONS_PLUGIN_BASE.'/'.($folder), $return_url)) != '')
			$dir = $filtered_dir;
		else if ($theme_dir != $child_dir && is_dir(($child_dir).($folder)))
			$dir = ($return_url ? $child_url : $child_dir).($folder);
		else if (is_dir(($theme_dir).($folder)))
			$dir = ($return_url ? $theme_url : $theme_dir).($folder);
		else if (is_dir((TRX_ADDONS_PLUGIN_DIR).($folder)))
			$dir = ($return_url ? TRX_ADDONS_PLUGIN_URL : TRX_ADDONS_PLUGIN_DIR).($folder);
		return apply_filters( 'trx_addons_filter_get_folder_dir', $dir, $folder, $return_url );
	}
}

if (!function_exists('trx_addons_get_folder_url')) {	
	function trx_addons_get_folder_url($folder) {
		return trx_addons_get_folder_dir($folder, true);
	}
}

// Get domain part from URL
if (!function_exists('trx_addons_get_domain_from_url')) {
	function trx_addons_get_domain_from_url($url) {
		if (($pos=strpos($url, '//'))!==false) $url = substr($url, $pos+2);
		if (($pos=strpos($url, '/'))!==false) $url = substr($url, 0, $pos);
		return $url;
	}
}


// Return .min version (if exists and filetime .min > filetime original) instead original
if (!function_exists('trx_addons_check_min_file')) {	
	function trx_addons_check_min_file($file, $dir = '') {
		if ( empty( $dir ) ) {
			$dir = dirname( $file );
		}
		if (substr($file, -3)=='.js') {
			if (substr($file, -7)!='.min.js' && trx_addons_is_off(trx_addons_get_option('debug_mode', false, false))) {
				$dir = trailingslashit($dir);
				$file_min = substr($file, 0, strlen($file)-3).'.min.js';
				if (file_exists($dir . $file_min) && filemtime($dir . $file) <= filemtime($dir . $file_min)) $file = $file_min;
			}
		} else if (substr($file, -4)=='.css') {
			if (substr($file, -8)!='.min.css'  && trx_addons_is_off(trx_addons_get_option('debug_mode', false, false))) {
				$dir = trailingslashit($dir);
				$file_min = substr($file, 0, strlen($file)-4).'.min.css';
				if (file_exists($dir . $file_min) && filemtime($dir . $file) <= filemtime($dir . $file_min)) $file = $file_min;
			}
		}
		return $file;
	}
}



/* Init WP Filesystem before the plugins and theme init
   Attention! WordPress is not recommended to use this class for regular file operations.
   Below is a message from WordPress "Theme Check" plugin:
     WP_Filesystem sould only be used for theme upgrade operations, not for all file operations.
     Consider using file_get_contents(), scandir() or glob()
------------------------------------------------------------------- */
if ( ! function_exists( 'trx_addons_init_filesystem' ) ) {
	add_action( 'after_setup_theme', 'trx_addons_init_filesystem', 0 );
	function trx_addons_init_filesystem( $force = false ) {
		if ( TRX_ADDONS_USE_WP_FILESYSTEM || $force ) {
			if ( ! function_exists('WP_Filesystem') ) {
				require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php';
			}
			if ( is_admin() ) {
				$url = admin_url();
				$creds = false;
				// First attempt to get credentials.
				if ( function_exists( 'request_filesystem_credentials' ) && false === ( $creds = request_filesystem_credentials( $url, '', false, false, array() ) ) ) {
					// If we comes here - we don't have credentials
					// so the request for them is displaying no need for further processing
					return false;
				}
		
				// Now we got some credentials - try to use them.
				if ( ! WP_Filesystem( $creds ) ) {
					// Incorrect connection data - ask for credentials again, now with error message.
					if ( function_exists( 'request_filesystem_credentials' ) ) {
						request_filesystem_credentials( $url, '', true, false );
					}
					return false;
				}
				
				return true; // Filesystem object successfully initiated.
			} else {
				WP_Filesystem();
			}
		}
		return true;
	}
}


// Put data into specified file
if ( ! function_exists( 'trx_addons_fpc' ) ) {	
	function trx_addons_fpc( $file, $data, $flag = 0 ) {
		if ( TRX_ADDONS_USE_WP_FILESYSTEM ) {
			global $wp_filesystem;
			if ( ! empty( $file ) ) {
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
					// Attention! WP_Filesystem can't append the content to the file!
					if ( $flag == FILE_APPEND && $wp_filesystem->exists( $file ) && ! trx_addons_is_url( $file ) ) {
						// If it is a existing local file and we need to append data -
						// use native PHP function to prevent large consumption of memory
						return file_put_contents( $file, $data, $flag );
					} else {
						// In other case (not a local file or not need to append data or file not exists)
						// That's why we have to read the contents of the file into a string,
						// add new content to this string and re-write it to the file if parameter $flag == FILE_APPEND!
						return $wp_filesystem->put_contents( $file,
															( $flag == FILE_APPEND && $wp_filesystem->exists($file)
																? $wp_filesystem->get_contents( $file )
																: ''
																)
															. $data,
															false );
					}
				} else {
					if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode', false, false ) ) ) {
						throw new Exception( sprintf( esc_html__( 'WP Filesystem is not initialized! Put contents to the file "%s" failed', 'trx_addons' ), $file ) );
					}
				}
			}
		} else {
			if ( ! empty( $file ) ) {
				$file = trx_addons_prepare_path( $file );
				return file_put_contents( $file, $data, $flag );
			}
		}
		return false;
	}
}

// Get text from specified file
if ( ! function_exists( 'trx_addons_fgc' ) ) {
	function trx_addons_fgc( $file, $unpack = false ) {
		$tmp_cont = '';
		if ( ! empty( $file ) ) {
			if ( TRX_ADDONS_USE_WP_FILESYSTEM ) {
				global $wp_filesystem;
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$file = str_replace( ABSPATH, $wp_filesystem->abspath(), $file );
					$tmp_cont = trx_addons_is_url( $file ) //&& ! $allow_url_fopen 
									? trx_addons_remote_get( $file ) 
									: $wp_filesystem->get_contents( $file );
				} else {
					if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode', false, false ) ) ) {
						throw new Exception( sprintf( esc_html__( 'WP Filesystem is not initialized! Get contents from the file "%s" failed', 'trx_addons' ), $file ) );
					}
				}
			} else {
				if ( trx_addons_is_url( $file ) ) { //&& ! $allow_url_fopen 
					$tmp_cont = trx_addons_remote_get( $file );
				} else {
					$file = trx_addons_prepare_path( $file );
					if ( file_exists( $file ) ) {
						$tmp_cont = file_get_contents( $file );
					}
				}
			}
		}
		if ( ! empty( $tmp_cont ) && $unpack && trx_addons_get_file_ext( $file ) == 'zip' ) {
			$tmp_name = 'tmp-'.rand().'.zip';
			$tmp = wp_upload_bits( $tmp_name, null, $tmp_cont );
			if ( $tmp['error'] ) {
				$tmp_cont = '';
			} else {
				trx_addons_unzip_file( $tmp['file'], dirname( $tmp['file'] ) );
				$file_name = trailingslashit( dirname( $tmp['file'] ) ) . basename( $file, '.zip' ) . '.txt';
				$tmp_cont = trx_addons_fgc( $file_name );
				unlink( $tmp['file'] );
				unlink( $file_name );
			}
		}
		return $tmp_cont;
	}
}

// Get array with rows from specified file
if ( ! function_exists( 'trx_addons_fga' ) ) {
	function trx_addons_fga( $file ) {
		if ( ! empty( $file ) ) {
			if ( TRX_ADDONS_USE_WP_FILESYSTEM ) {
				global $wp_filesystem;
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$file = str_replace( ABSPATH, $wp_filesystem->abspath(), $file );
					return $wp_filesystem->get_contents_array( $file );
				} else {
					if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode', false, false ) ) ) {
						throw new Exception( sprintf( esc_html__( 'WP Filesystem is not initialized! Get rows from the file "%s" failed', 'trx_addons' ), $file ) );
					}
				}
			} else {
				$file = trx_addons_prepare_path( $file );
				if ( file_exists( $file ) ) {
					return file( $file );
				}
			}
		}
		return array();
	}
}

// Create a directory
if ( ! function_exists( 'trx_addons_mkdir' ) ) {
	function trx_addons_mkdir( $path ) {
		if ( ! empty( $path ) ) {
			if ( TRX_ADDONS_USE_WP_FILESYSTEM ) {
				global $wp_filesystem;
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$path = str_replace( ABSPATH, $wp_filesystem->abspath(), $path );
					if ( ! $wp_filesystem->is_dir( $path ) ) {
						if ( ! $wp_filesystem->mkdir( $path, FS_CHMOD_DIR ) ) {
							if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) ) ) {
								// Translators: Add the file name to the message
								throw new Exception( sprintf( esc_html__( 'Create a folder "%s" failed', 'trx_addons' ), $path ) );
							}
						} else {
							return true;
						}
					} else {
						return true;
					}
				} else {
					if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) ) ) {
						// Translators: Add the file name to the message
						throw new Exception( sprintf( esc_html__( 'WP Filesystem is not initialized! Create a folder "%s" failed', 'trx_addons' ), $path ) );
					}
				}
			} else {
				$path = trx_addons_prepare_path( $path );
				if ( ! is_dir( $path ) ) {
					if ( ! wp_mkdir_p( $path ) ) {
						if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) ) ) {
							// Translators: Add the file name to the message
							throw new Exception( sprintf( esc_html__( 'Create a folder "%s" failed', 'trx_addons' ), $path ) );
						}
					} else {
						return true;
					}
				} else {
					return true;
				}
			}
		}
		return false;
	}
}

// Remove a file or a directory
// Parameters $recursive and $type are deprecated in the plugin version v.2.3.0
if ( ! function_exists( 'trx_addons_unlink' ) ) {
	function trx_addons_unlink( $path, $recursive = true, $type = 'd' ) {
		if ( ! empty( $path ) ) {
			if ( TRX_ADDONS_USE_WP_FILESYSTEM ) {
				global $wp_filesystem;
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$path = str_replace( ABSPATH, $wp_filesystem->abspath(), $path );
					return $wp_filesystem->delete( $path, true, $wp_filesystem->is_file( $path ) ? 'f' : 'd' );
				} else {
					if ( trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) ) ) {
						// Translators: Add the file name to the message
						throw new Exception( sprintf( esc_html__( 'WP Filesystem is not initialized! Delete a file/folder "%s" failed', 'trx_addons' ), $path ) );
					}
				}
			} else {
				$path = trx_addons_prepare_path( $path );
				if ( is_dir( $path ) ) {
					$files = scandir( $path,  SCANDIR_SORT_NONE );
					foreach ( $files as $file ) {
						if ( $file != "." && $file != ".." ) {
							trx_addons_unlink( "$path/$file" );
						}
					}
					rmdir( $path );
					return true;
				} else if ( file_exists( $path ) ) {
					unlink( $path );
					return true;
				}
			}
		}
		return false;
	}
}

// Copy a folder tree (with subfolders)
if ( ! function_exists( 'trx_addons_copy' ) ) {
	function trx_addons_copy( $src, $dst ) {
		if ( ! empty( $src ) && ! empty( $dst ) ) {
			if ( TRX_ADDONS_USE_WP_FILESYSTEM && function_exists( 'copy_dir' ) ) {
				$src = trx_addons_prepare_path( $src );
				$dst = trx_addons_prepare_path( $dst );
				return copy_dir( $src, $dst );
			} else {
				trx_addons_unlink( $dst );
				if ( is_dir( $src ) ) {
					if ( ! is_dir( $dst ) ) {
						trx_addons_mkdir( $dst );
					}
					$files = scandir( $src, SCANDIR_SORT_NONE );
					foreach ( $files as $file ) {
						if ( $file != "." && $file != ".." ) {
							trx_addons_copy( "$src/$file", "$dst/$file" );
						}
					}
					return true;
				} else if ( file_exists( $src ) ) {
					return copy( $src, $dst );
				}
			}
		}
		return false;
	}
}

// Return a list of files in the folder
if ( ! function_exists( 'trx_addons_list_files' ) ) {
	function trx_addons_list_files( $path, $recursive_levels = 1 ) {
		$list = array();
		if ( ! empty( $path ) ) {
			if ( function_exists( 'list_files' ) ) {
				$path = trx_addons_prepare_path( $path );
				return list_files( $path, max( 1, $recursive_levels ) );
			} else {
				if ( is_dir( $path ) ) {
					$files = scandir( $path );	//, SCANDIR_SORT_NONE
					foreach ( $files as $file ) {
						if ( $file != "." && $file != ".." ) {
							if ( is_dir( "$path/$file" ) ) {
								if ( $recursive_levels > 1 ) {
									$list = array_merge( $list, trx_addons_list_files( "$path/$file", $recursive_levels - 1 ) );
								}
							} else {
								$list[] = "$path/$file";
							}
						}
					}
				} else if ( file_exists( $path ) ) {
					$list[] = $path;
				}
			}
		}
		return $list;
	}
}

// Get text from specified file via HTTP GET
if ( ! function_exists( 'trx_addons_remote_get' ) ) {	
	function trx_addons_remote_get( $file, $args = array() ) {
		$args = array_merge(
					array(
						'method'     => 'GET',
						'timeout'    => -1,
						'user-agent' => TRX_ADDONS_REMOTE_USER_AGENT
					),
					is_array( $args ) ? $args : array( 'timeout' => $args )
				);
		// Set timeout as half of the PHP execution time
		if ( $args['timeout'] < 1 ) {
			$args['timeout'] = round( 0.5 * max( 30, function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : 30 ) );
		}
		// Add current protocol (if not specified)
		$file = trx_addons_add_protocol( $file );
		// Do request and get a response
		$response = wp_remote_get( $file, $args );
		// Save last error to the globals
		global $TRX_ADDONS_STORAGE;
		$TRX_ADDONS_STORAGE['last_remote_error'] = is_wp_error( $response ) ? $response->get_error_message() : '';
		// Check the response code and return response body if OK
		return ! is_wp_error( $response ) && isset( $response['response']['code'] ) && $response['response']['code'] == 200
					? $response['body']
					: '';
	}
}

// Get text from specified file via HTTP POST
if ( ! function_exists( 'trx_addons_remote_post' ) ) {
	function trx_addons_remote_post( $file, $args = array(), $vars = array() ) {
		$args = array_merge(
					array(
						'method'     => 'POST',
						'timeout'    => -1,
						'user-agent' => TRX_ADDONS_REMOTE_USER_AGENT
					),
					is_array( $args ) ? $args : array( 'timeout' => $args )
				);
		// Add variables to the request body
		if ( is_array( $vars ) && count( $vars ) > 0 ) {
			$args['body'] = $vars;
		}
		// Set timeout as half of the PHP execution time
		if ( $args['timeout'] < 1 ) {
			$args['timeout'] = round( 0.5 * max( 30, ini_get( 'max_execution_time' ) ) );
		}
		// Add current protocol (if not specified)
		$file = trx_addons_add_protocol( $file );
		// Do request and get a response
		$response = wp_remote_post( $file, $args );
		// Check the response code and return response body if OK
		return ! is_wp_error( $response ) && isset( $response['response']['code'] ) && $response['response']['code'] == 200
					? $response['body']
					: '';
	}
}

// Get text from specified file via cURL
if ( ! function_exists( 'trx_addons_remote_curl' ) ) {	
	function trx_addons_remote_curl( $file, $vars = array(), $args = array(), $curl_options = array() ) {
		$response = '';
		if ( function_exists( 'curl_init' ) ) {
			// Init connection
			$ch = curl_init();
			// If inited - prepare request
			if ( $ch > 0 ) {
				$file = trx_addons_add_protocol( $file );
				// Default options
				$defa = array(
							CURLOPT_URL            => $file,
							CURLOPT_USERAGENT      => TRX_ADDONS_REMOTE_USER_AGENT,
							CURLOPT_RETURNTRANSFER => 1,
							CURLOPT_FOLLOWLOCATION => 1,
							CURLOPT_MAXREDIRS      => 5,
							CURLOPT_AUTOREFERER    => 1,
							CURLOPT_SSL_VERIFYPEER => 0,
							CURLOPT_SSL_VERIFYHOST => 0,
							);
				// Enable SSL if need
//				if ( strpos( $file, 'https://' ) === 0 ) {
//					$defa[ CURLOPT_SSLVERSION ] = 3;
//				}
				// Add timeout
				$timeout = ! empty( $args['timeout'] ) ? $args['timeout'] : -1;
				if ( $timeout < 1 ) {
					$timeout = round( 0.5 * max( 30, ini_get( 'max_execution_time' ) ) );
				}
				$defa[CURLOPT_TIMEOUT] = $timeout;
				$defa[CURLOPT_CONNECTTIMEOUT] = max( 10, min( 30, round( $timeout / 2 ) ) );
				// Add method
				$method = ! empty( $args['method'] ) ? $args['method'] : '';
				if ( $method == 'put' ) {
					$defa[CURLOPT_CUSTOMREQUEST] = 'PUT';
					$defa[CURLOPT_PUT] = 1;
				} else if ( $method == 'post' || ( empty( $method ) && ! empty( $vars ) ) ) {
					$defa[CURLOPT_CUSTOMREQUEST] = 'POST';
					$defa[CURLOPT_POST] = 1;
				}
				// Add proxy
				if ( ! empty( $args['proxy'] ) ) {
					$defa[CURLOPT_PROXY] = $args['proxy'];
//					$defa[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
//					$defa[CURLOPT_HTTPPROXYTUNNEL] = 0;
//					$defa[CURLOPT_HEADER] = 0;
//					$defa[CURLOPT_ENCODING] = '';
					// Add user and password (if specified) as 'user:pwd'
					if ( ! empty( $args['proxy_user_pwd'] ) ) {
						$defa[CURLOPT_PROXYUSERPWD] = $args['proxy_user_pwd'];
					}
				}
				// Add headers
				if ( ! empty( $args['headers'] ) && is_array( $args['headers'] ) && count( $args['headers'] ) > 0 ) {
					$defa[CURLOPT_HTTPHEADER] = $args['headers'];
				}
				// Add data fields (query arguments)
				if ( is_array( $vars ) && count( $vars ) > 0 ) {
					$defa[CURLOPT_POSTFIELDS] = $method == 'put'
													? http_build_query( $vars )
													: $vars;
				} else if ( ! is_array( $vars ) && ! empty( $vars ) ) {
					$defa[CURLOPT_POSTFIELDS] = $vars;
				}
				// Add native cURL options
				foreach ( $curl_options as $k => $v ) {
					$defa[$k] = $v;
				}
				// Set options
				curl_setopt_array( $ch, $defa );
				// Do request and get a response
				$response = curl_exec( $ch );
				// Check the response code
				$response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				// If failure - set global variable with error code and message
				if ( $response_code < 200 || $response_code > 299 ) {
					$GLOBALS['trx_addons_last_curl_error'] = curl_errno( $ch ) > 0
																? curl_errno( $ch ) . ' (' . curl_error( $ch ) . ')'
																: $response_code;
					$response = '';
				}
				// Close connection
				curl_close($ch);
			}
		}
		return $response;
	}
}

// Init a $wp_filesystem (if need) and unzip file
if ( ! function_exists( 'trx_addons_unzip_file' ) ) {
	function trx_addons_unzip_file( $zip, $dest ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) || ! is_object( $wp_filesystem ) ) {
			trx_addons_init_filesystem( true );
		}
		return unzip_file( $zip, $dest );
	}
}

// Get JSON from specified url via HTTP (cURL) and return object or null
if ( ! function_exists( 'trx_addons_retrieve_json' ) ) {	
	add_filter( 'trx_addons_filter_retrieve_json', 'trx_addons_retrieve_json' );
	function trx_addons_retrieve_json( $url ) {
		$data = '';
		$resp = trim( trx_addons_remote_get( $url ) );
		if ( in_array( substr( $resp, 0, 1 ), array( '{', '[' ) ) ) {
			$data = json_decode( $resp, true );
		}
		return $data;
	}
}

// Remove unsafe characters from file/folder path
if ( ! function_exists( 'trx_addons_esc' ) ) {	
	function trx_addons_esc( $name ) {
		return str_replace(
					array('\\', '~', '$', ':', ';', '+', '>', '<', '|', '"', "'", '`', "\xFF", "\x0A", "\x0D", '*', '?', '^'),
					defined( 'DIRECTORY_SEPARATOR' ) ? DIRECTORY_SEPARATOR : '/',
					trim( $name )
				);
	}
}

// Replace '\' with '/' in the file/folder path
if ( ! function_exists('trx_addons_prepare_path')) {	
	function trx_addons_prepare_path( $name ) {
		return str_replace( '\\', defined( 'DIRECTORY_SEPARATOR' ) ? DIRECTORY_SEPARATOR : '/', trim( $name ) );
	}
}

// Convert URL to the local path
if ( ! function_exists( 'trx_addons_url_to_local_path' ) ) {	
	function trx_addons_url_to_local_path( $url ) {
		$path = '';
		// Remove scheme from url
		$url = trx_addons_remove_protocol( $url );
		// Get upload path & dir
		$upload_info = wp_upload_dir();
		// Where check file
		$locations = array(
			'uploads' => array(
				'dir' => $upload_info['basedir'],
				'url' => trx_addons_remove_protocol($upload_info['baseurl'])
				),
			'child' => array(
				'dir' => get_stylesheet_directory(),
				'url' => trx_addons_remove_protocol(get_stylesheet_directory_uri())
				),
			'theme' => array(
				'dir' => get_template_directory(),
				'url' => trx_addons_remove_protocol(get_template_directory_uri())
				)
			);
		// Find a file in locations
		foreach( $locations as $key => $loc ) {
			// Check if $url is in location
			if ( false === strpos( $url, $loc['url'] ) ) continue;
			// Get a path from the URL
			$path = str_replace( $loc['url'], $loc['dir'], $url );
			// Check if a file exists
			if ( file_exists( $path ) ) {
				break;
			}
			$path = '';
		}
		return $path;
	}
}
