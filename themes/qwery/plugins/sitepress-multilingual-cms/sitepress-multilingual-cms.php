<?php
/* WPML support functions
------------------------------------------------------------------------------- */

// Remove translated options from theme_mods or only duplicate it value
// (to keep access to this option's value from another (not translated) language)
if ( ! defined( 'QWERY_WPML_REMOVE_TRANSLATED_OPTIONS' ) ) {
	define( 'QWERY_WPML_REMOVE_TRANSLATED_OPTIONS', false );
}

// Theme init priorities:
// 1 - register filters to add/remove lists items in the Theme Options
if ( ! function_exists( 'qwery_wpml_theme_setup1' ) ) {
	add_action( 'after_setup_theme', 'qwery_wpml_theme_setup1', 1 );
	function qwery_wpml_theme_setup1() {
		add_action( 'qwery_action_just_save_options', 'qwery_wpml_duplicate_theme_options', 10, 1 );
		add_filter( 'qwery_filter_options_save', 'qwery_wpml_duplicate_trx_addons_options', 10, 2 );
	}
}

// Theme init priorities:
// 3 - add/remove Theme Options elements
if ( ! function_exists( 'qwery_wpml_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'qwery_wpml_theme_setup3', 3 );
	function qwery_wpml_theme_setup3() {
		static $loaded = false;
		if ( $loaded || ! qwery_exists_wpml() ) {
			return;
		}
		$loaded = true;
		// Register hooks on 'get_theme_mod' with translated options
		global $QWERY_STORAGE;
		foreach ( $QWERY_STORAGE['options'] as $k => $v ) {
			if ( isset( $v['std'] ) && ! empty( $v['translate'] ) ) {
				add_filter( "theme_mod_{$k}", 'qwery_wpml_get_theme_mod' );
			}
		}
		// Add hidden option with current language
		qwery_storage_set_array_before(
			'options', 'last_option', 'wpml_current_language', array(
				'title' => '',
				'desc'  => '',
				'std'   => qwery_wpml_get_current_language(),
				'type'  => 'hidden',
			)
		);
	}
}

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'qwery_wpml_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'qwery_wpml_theme_setup9', 9 );
	function qwery_wpml_theme_setup9() {
		if ( qwery_exists_wpml() ) {
			add_action( 'wp_enqueue_scripts', 'qwery_wpml_frontend_scripts', 1100 );
			add_filter( 'qwery_filter_merge_styles', 'qwery_wpml_merge_styles' );
		}
		if ( is_admin() ) {
			add_filter( 'qwery_filter_customizer_vars', 'qwery_wpml_customizer_vars' );
			add_filter( 'qwery_filter_tgmpa_required_plugins', 'qwery_wpml_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'qwery_wpml_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('qwery_filter_tgmpa_required_plugins', 'qwery_wpml_tgmpa_required_plugins');
	function qwery_wpml_tgmpa_required_plugins( $list = array() ) {
		if ( qwery_storage_isset( 'required_plugins', 'sitepress-multilingual-cms' ) && qwery_storage_get_array( 'required_plugins', 'sitepress-multilingual-cms', 'install' ) !== false && qwery_is_theme_activated() ) {
			$path = qwery_get_plugin_source_path( 'plugins/sitepress-multilingual-cms/sitepress-multilingual-cms.zip' );
			if ( ! empty( $path ) || qwery_get_theme_setting( 'tgmpa_upload' ) ) {
				$list[] = array(
					'name'     => qwery_storage_get_array( 'required_plugins', 'sitepress-multilingual-cms', 'title' ),
					'slug'     => 'sitepress-multilingual-cms',
					'source'   => ! empty( $path ) ? $path : 'upload://sitepress-multilingual-cms.zip',
					'version'  => '4.0.1',
					'required' => false,
				);
			}
		}
		return $list;
	}
}


/* Plugin's support utilities
------------------------------------------------------------------------------- */

// Check if plugin installed and activated
if ( ! function_exists( 'qwery_exists_wpml' ) ) {
	function qwery_exists_wpml() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || class_exists( 'sitepress' );
	}
}

// Return default language
if ( ! function_exists( 'qwery_wpml_get_default_language' ) ) {
	function qwery_wpml_get_default_language() {
		return qwery_exists_wpml() ? apply_filters( 'wpml_default_language', null ) : '';
	}
}

// Return current language
if ( ! function_exists( 'qwery_wpml_get_current_language' ) ) {
	function qwery_wpml_get_current_language() {
		return qwery_exists_wpml() ? apply_filters( 'wpml_current_language', null ) : '';
	}
}


// Duplicate translatable theme options for each language
if ( ! function_exists( 'qwery_wpml_duplicate_theme_options' ) ) {
	//Handler of the add_action('qwery_action_just_save_options', 'qwery_wpml_duplicate_theme_options', 10, 1);
	function qwery_wpml_duplicate_theme_options( $values ) {
		if ( ! qwery_exists_wpml() ) {
			return;
		}

		// Load just saved theme_mods
		$options_name = sprintf( 'theme_mods_%s', get_stylesheet() );
		$values       = get_option( $options_name );
		$changed      = false;

		// Detect current language
		if ( isset( $values['wpml_current_language'] ) ) {
			$tmp  = explode( '!', $values['wpml_current_language'] );
			$lang = $tmp[0];
			unset( $values['wpml_current_language'] );
			$changed = true;
		} else {
			$lang = qwery_wpml_get_current_language();
		}

		// Duplicate options to the language-specific options and remove original
		if ( is_array( $values ) ) {
			global $QWERY_STORAGE;
			foreach ( $QWERY_STORAGE['options'] as $k => $v ) {
				if ( ! empty( $v['translate'] ) ) {
					if ( isset( $values[ $k ] ) ) {   // If key not present - value was not changed
						$param_name            = sprintf( '%1$s_lang_%2$s', $k, $lang );
						$values[ $param_name ] = $values[ $k ];
						if ( QWERY_WPML_REMOVE_TRANSLATED_OPTIONS ) {
							unset( $values[ $k ] );
						}
						$changed = true;
					}
				}
			}
			if ( $changed ) {
				update_option( $options_name, $values );
			}
		}
	}
}


// Duplicate translatable ThemeREX Addons options for each language
if ( ! function_exists( 'qwery_wpml_duplicate_trx_addons_options' ) ) {
	//Handler of the add_filter('qwery_filter_options_save', 'qwery_wpml_duplicate_trx_addons_options', 10, 2);
	function qwery_wpml_duplicate_trx_addons_options( $values, $opt_name ) {
		if ( qwery_exists_wpml() && 'trx_addons_options' == $opt_name ) {
			// Load just saved theme_mods
			$mods = get_theme_mods();

			// Detect current language
			if ( isset( $mods['wpml_current_language'] ) ) {
				$tmp  = explode( '!', $mods['wpml_current_language'] );
				$lang = $tmp[0];
			} else {
				$lang = qwery_wpml_get_current_language();
			}

			// Add current language to the plugin's options
			$values['wpml_current_language'] = $lang;

			// Call plugin's filter
			$values = apply_filters( 'trx_addons_filter_options_save', $values );
		}
		return $values;
	}
}

// Return translated theme option's value
if ( ! function_exists( 'qwery_wpml_get_theme_mod' ) ) {
	//Handler of the add_filter('theme_mod_xxx', 'qwery_wpml_get_theme_mod');
	function qwery_wpml_get_theme_mod( $value ) {
		$opt_name = str_replace( 'theme_mod_', '', current_filter() );
		if ( ! empty( $opt_name ) ) {
			$lang          = qwery_wpml_get_current_language();
			$param_name    = sprintf( '%1$s_lang_%2$s', $opt_name, $lang );
			$default_value = -987654321;
			$tmp           = get_theme_mod( $param_name, $default_value );
			$value         = $tmp != $default_value
						? $tmp
						: qwery_storage_get_array( 'options', $opt_name, 'std', $value );
		}
		return $value;
	}
}

// Add current language code and name to the Customizer vars
if ( ! function_exists( 'qwery_wpml_customizer_vars' ) ) {
	//Handler of the add_filter( 'qwery_filter_customizer_vars', 'qwery_wpml_customizer_vars');
	function qwery_wpml_customizer_vars( $vars ) {
		if ( qwery_exists_wpml() && function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0' );
			if ( empty( $languages ) || ! is_array( $languages ) ) {
				return $vars;
			}
			foreach ( $languages as $lang ) {
				if ( $lang['active'] ) {
					$vars['theme_name_suffix'] = ( ! empty( $vars['theme_name_suffix'] ) ? $vars['theme_name_suffix'] : '' )
													. sprintf( ' / <img src="%1$s" alt="%2$s"> %2$s', $lang['country_flag_url'], $lang['translated_name'] );
				}
			}
		}
		return $vars;
	}
}

// Binds JS listener to Customizer controls.
if ( ! function_exists( 'qwery_wpml_customizer_control_js' ) ) {
	add_action( 'customize_controls_enqueue_scripts', 'qwery_wpml_customizer_control_js' );
	function qwery_wpml_customizer_control_js() {
		wp_enqueue_script(
			'qwery-sitepress-multilingual-cms-customizer',
			qwery_get_file_url( 'plugins/sitepress-multilingual-cms/sitepress-multilingual-cms-customizer.js' ),
			array( 'jquery' ), null, true
		);
	}
}

// Load required scripts for admin mode (Theme Options)
if ( ! function_exists( 'qwery_wpml_options_add_scripts' ) ) {
	add_action( 'admin_enqueue_scripts', 'qwery_wpml_options_add_scripts' );
	function qwery_wpml_options_add_scripts() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( is_object( $screen ) && false !== strpos($screen->id, '_page_theme_options') ) {
			wp_enqueue_script(
				'qwery-sitepress-multilingual-cms-options',
				qwery_get_file_url( 'plugins/sitepress-multilingual-cms/sitepress-multilingual-cms-options.js' ),
				array( 'jquery' ), null, true
			);
		}
	}
}

// Switch language in the preview url if we are come from the backend
//              or int the admin (backend) if we are come from the frontend
if ( ! function_exists( 'qwery_wpml_customizer_switch_language' ) ) {
	add_action( 'after_setup_theme', 'qwery_wpml_customizer_switch_language', 1 );
	add_action( 'customize_controls_init', 'qwery_wpml_customizer_switch_language' );
	function qwery_wpml_customizer_switch_language() {
		global $wp_customize;
		if ( qwery_exists_wpml() && is_customize_preview() && is_admin() ) {
			if ( current_action() == 'customize_controls_init' ) {
				$preview_url = $wp_customize->get_preview_url();
				$return_url  = $wp_customize->get_return_url();
			} else {
				$preview_url = qwery_get_current_url();
				$return_url  = wp_get_referer();
			}
			$from_admin = strpos( $return_url, str_replace( home_url( '' ), '', admin_url() ) ) !== false;
			$lang       = '';
			$pos        = strpos( $return_url, '?' );
			if ( false !== $pos ) {
				wp_parse_str( wp_unslash( substr( $return_url, $pos + 1 ) ), $params );
				if ( ! empty( $params['lang'] ) ) {
					$lang = $params['lang'];
				}
			}
			if ( current_action() == 'customize_controls_init' ) {
				if ( $from_admin ) {
					if ( empty( $lang ) ) {
						$lang = qwery_wpml_get_current_language();
					}
					// Set current language for the preview area
					if ( ! empty( $lang ) ) {
						$wp_customize->set_preview_url( add_query_arg( 'lang', $lang, $preview_url ) );
					}
				}
			} elseif ( ! $from_admin ) {
				if ( empty( $lang ) ) {
					$lang = qwery_wpml_get_default_language();
				}
				// Set current language for the admin
				if ( ! empty( $lang ) && qwery_wpml_get_current_language() != $lang ) {
					global $sitepress;
					if ( is_object( $sitepress ) && method_exists( $sitepress, 'switch_lang' ) ) {
						$sitepress->set_admin_language_cookie( $lang );
					}
				}
			}
		}
	}
}

// Enqueue styles for frontend
if ( ! function_exists( 'qwery_wpml_frontend_scripts' ) ) {
	//Handler of the add_action( 'wp_enqueue_scripts', 'qwery_wpml_frontend_scripts', 1100 );
	function qwery_wpml_frontend_scripts() {
		if ( qwery_is_on( qwery_get_theme_option( 'debug_mode' ) ) ) {
			$qwery_url = qwery_get_file_url( 'plugins/sitepress-multilingual-cms/sitepress-multilingual-cms.css' );
			if ( '' != $qwery_url ) {
				wp_enqueue_style( 'qwery-sitepress-multilingual-cms', $qwery_url, array(), null );
			}
		}
	}
}

// Merge custom styles
if ( ! function_exists( 'qwery_wpml_merge_styles' ) ) {
	//Handler of the add_filter( 'qwery_filter_merge_styles', 'qwery_wpml_merge_styles');
	function qwery_wpml_merge_styles( $list ) {
		$list[ 'plugins/sitepress-multilingual-cms/sitepress-multilingual-cms.css' ] = true;
		return $list;
	}
}
