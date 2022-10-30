<?php
/**
 * Debug utilities (for internal use only!)
 *
 * @package ThemeREX Addons
 * @since v1.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Short analogs for debug functions
if ( ! function_exists('dcl') ) {	function dcl( $msg ) {				if ( ! function_exists('is_user_logged_in' ) || is_user_logged_in() ) echo '<br><pre>' . esc_html($msg) . '</pre><br>'; } }						// Console log - output any message on the screen
if ( ! function_exists('dco') ) {	function dco( $var, $lvl = -1 ) {	if ( ! function_exists('is_user_logged_in' ) || is_user_logged_in() ) trx_addons_debug_dump_screen($var, $lvl); } }								// Console obj - output object struct. on the screen
if ( ! function_exists('dcs') ) {	function dcs( $depth = -1, $args = false ) { if ( ! function_exists('is_user_logged_in' ) || is_user_logged_in() ) trx_addons_debug_calls_stack_screen($depth, $args); } }			// Console stack - output calls stack on the screen
if ( ! function_exists('dcw') ) {	function dcw( $q = null ) {			if ( ! function_exists('is_user_logged_in' ) || is_user_logged_in() ) echo '<code>' . nl2br( trx_addons_debug_dump_wp($q) ) . '</code>'; } }	// Console WP - output WP is_... states on the screen
if ( ! function_exists('dfl') ) {	function dfl( $var ) {				trx_addons_debug_trace_message($var); } }									// File log - output any message into file debug.log
if ( ! function_exists('dfo') ) {	function dfo( $var, $lvl = -1 ) {	trx_addons_debug_dump_file($var, $lvl); } }									// File obj - output object structure into file debug.log
if ( ! function_exists('dfs') ) {	function dfs( $depth = -1, $args = false ) { trx_addons_debug_calls_stack_file($depth, $args); } }				// File stack - output calls stack into file debug.log
if ( ! function_exists('dfw') ) {	function dfw( $q = null ) {			trx_addons_debug_trace_message( trx_addons_debug_dump_wp($q) ); } }			// File WP - output WP is_... states to the file debug.log
if ( ! function_exists('ddo') ) {	function ddo( $var, $lvl = 0, $max_lvl = -1 ) {	return trx_addons_debug_dump_var($var, $lvl, $max_lvl); } }	// Return obj - return object structure

// Return call stack
if ( ! function_exists( 'trx_addons_debug_calls_stack' ) ) {
	function trx_addons_debug_calls_stack( $depth = -1, $args = false ) {
		return debug_backtrace( $args
									? DEBUG_BACKTRACE_PROVIDE_OBJECT
									: DEBUG_BACKTRACE_IGNORE_ARGS,
								$depth > 0
									? $depth
									: 0
								);
	}
}

// Output call stack to the current page
if ( ! function_exists( 'trx_addons_debug_calls_stack_screen' ) ) {
	function trx_addons_debug_calls_stack_screen( $depth = -1, $args = false ) {
		$s = trx_addons_debug_calls_stack( $depth, $args );
		trx_addons_debug_dump_screen( $s );
	}
}

// Output call stack to the debug.log
if ( ! function_exists( 'trx_addons_debug_calls_stack_file' ) ) {
	function trx_addons_debug_calls_stack_file( $depth = -1, $args = false ) {
		$s = trx_addons_debug_calls_stack( $depth, $args );
		trx_addons_debug_dump_file( $s );
	}
}

// Output var's dump into the current page
if ( ! function_exists( 'trx_addons_debug_dump_screen' ) ) {
	function trx_addons_debug_dump_screen( $var, $level = -1 ) {
		echo "<pre>\n" . esc_html( trx_addons_debug_dump_var( $var, 0, $level ) ) . "</pre>\n";
	}
}

// Save msg into file debug.log in the stylesheet directory
if ( ! function_exists( 'trx_addons_debug_trace_message' ) ) {
	function trx_addons_debug_trace_message( $msg ) {
		trx_addons_fpc( get_stylesheet_directory() . '/debug.log', date( 'd.m.Y H:i:s' ) . " {$msg}\n", FILE_APPEND );
	}
}

// Output var's dump into the debug.log
if ( ! function_exists( 'trx_addons_debug_dump_file' ) ) {
	function trx_addons_debug_dump_file( $var, $level = -1 ) {
		trx_addons_debug_trace_message( "\n\n" . trx_addons_debug_dump_var( $var, 0, $level ) );
	}
}

// Return var's dump as string
if ( ! function_exists( 'trx_addons_debug_dump_var' ) ) {
	function trx_addons_debug_dump_var( $var, $level = 0, $max_level = -1 )  {
		if ( is_array( $var ) ) {
			$type = 'Array[' . count($var) . ']';
		} else if ( is_object( $var ) ) {
			$type = 'Object';
		} else {
			$type = '';
		}
		if ( $type ) {
			$rez = "{$type}\n";
			if ( $max_level < 0 || $level < $max_level ) {
				$level++;
				foreach ( $var as $k => $v ) {
					if ( is_array( $v ) && $k === "GLOBALS" ) continue;
					for ( $i = 0; $i < $level * 3; $i++ ) {
						$rez .= " ";
					}
					$rez .= $k . ' => ' .  trx_addons_debug_dump_var( $v, $level, $max_level );
				}
			}
		} else if ( is_bool( $var ) ) {
			$rez = ( $var ? 'true' : 'false' ) . "\n";
		} else if ( is_numeric( $var ) ) {
			$rez = $var . "\n";
		} else {
			$rez = '"' . $var . "\"\n";
		}
		return $rez;
	}
}

// Output WP is_...() state into the current page
if ( ! function_exists('trx_addons_debug_dump_wp' ) ) {
	function trx_addons_debug_dump_wp( $query=null ) {
		global $wp_query;
		if ( ! $query && ! empty( $wp_query ) ) {
			$query = $wp_query;
		}
		return 
			  "\naction     = " . current_action()
			. "\nadmin      = " . (int) is_admin()
			. "\najax       = " . (int) wp_doing_ajax()
			. "\nmobile     = " . (int) wp_is_mobile()
			. "\nmain_query = " . (int) is_main_query() . ( $query ? "  query=" . (int) $query->is_main_query() : '' )
			. "\nfront_page = " . (int) is_front_page() . ( $query ? "  query=" . (int) $query->is_front_page() : '' )
			. "\nhome       = " . (int) is_home()       . ( $query ? "  query=" . (int) $query->is_home() . "  query->is_posts_page=" . (int) $query->is_posts_page : '' )
			. "\nsearch     = " . (int) is_search()     . ( $query ? "  query=" . (int) $query->is_search()     : '' )
			. "\ncategory   = " . (int) is_category()   . ( $query ? "  query=" . (int) $query->is_category()   : '' )
			. "\ntag        = " . (int) is_tag()        . ( $query ? "  query=" . (int) $query->is_tag()        : '' )
			. "\ntax        = " . (int) is_tax()        . ( $query ? "  query=" . (int) $query->is_tax()        : '' )
			. "\narchive    = " . (int) is_archive()    . ( $query ? "  query=" . (int) $query->is_archive()    : '' )
			. "\nday        = " . (int) is_day()        . ( $query ? "  query=" . (int) $query->is_day()        : '' )
			. "\nmonth      = " . (int) is_month()      . ( $query ? "  query=" . (int) $query->is_month()      : '' )
			. "\nyear       = " . (int) is_year()       . ( $query ? "  query=" . (int) $query->is_year()       : '' )
			. "\nauthor     = " . (int) is_author()     . ( $query ? "  query=" . (int) $query->is_author()     : '' )
			. "\nsingular   = " . (int) trx_addons_is_singular()   . ( $query ? "  query=" . (int) $query->trx_addons_is_singular()   : '' )
			. "\npage       = " . (int) is_page()       . ( $query ? "  query=" . (int) $query->is_page()       : '' )
			. "\nsingle     = " . (int) trx_addons_is_single()     . ( $query ? "  query=" . (int) $query->trx_addons_is_single()     : '' )
			. "\nattachment = " . (int) is_attachment() . ( $query ? "  query=" . (int) $query->is_attachment() : '' )
			. "\n";
	}
}
