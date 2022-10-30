<?php
/**
 * Theme storage manipulations
 *
 * @package QWERY
 * @since QWERY 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

// Get theme variable
if ( ! function_exists( 'qwery_storage_get' ) ) {
	function qwery_storage_get( $var_name, $default = '' ) {
		global $QWERY_STORAGE;
		return isset( $QWERY_STORAGE[ $var_name ] ) ? $QWERY_STORAGE[ $var_name ] : $default;
	}
}

// Set theme variable
if ( ! function_exists( 'qwery_storage_set' ) ) {
	function qwery_storage_set( $var_name, $value ) {
		global $QWERY_STORAGE;
		$QWERY_STORAGE[ $var_name ] = $value;
	}
}

// Check if theme variable is empty
if ( ! function_exists( 'qwery_storage_empty' ) ) {
	function qwery_storage_empty( $var_name, $key = '', $key2 = '' ) {
		global $QWERY_STORAGE;
		if ( ! empty( $key ) && ! empty( $key2 ) ) {
			return empty( $QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] );
		} elseif ( ! empty( $key ) ) {
			return empty( $QWERY_STORAGE[ $var_name ][ $key ] );
		} else {
			return empty( $QWERY_STORAGE[ $var_name ] );
		}
	}
}

// Check if theme variable is set
if ( ! function_exists( 'qwery_storage_isset' ) ) {
	function qwery_storage_isset( $var_name, $key = '', $key2 = '' ) {
		global $QWERY_STORAGE;
		if ( ! empty( $key ) && ! empty( $key2 ) ) {
			return isset( $QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] );
		} elseif ( ! empty( $key ) ) {
			return isset( $QWERY_STORAGE[ $var_name ][ $key ] );
		} else {
			return isset( $QWERY_STORAGE[ $var_name ] );
		}
	}
}

// Delete theme variable
if ( ! function_exists( 'qwery_storage_unset' ) ) {
	function qwery_storage_unset( $var_name, $key = '', $key2 = '' ) {
		global $QWERY_STORAGE;
		if ( ! empty( $key ) && ! empty( $key2 ) ) {
			unset( $QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] );
		} elseif ( ! empty( $key ) ) {
			unset( $QWERY_STORAGE[ $var_name ][ $key ] );
		} else {
			unset( $QWERY_STORAGE[ $var_name ] );
		}
	}
}

// Inc/Dec theme variable with specified value
if ( ! function_exists( 'qwery_storage_inc' ) ) {
	function qwery_storage_inc( $var_name, $value = 1 ) {
		global $QWERY_STORAGE;
		if ( empty( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = 0;
		}
		$QWERY_STORAGE[ $var_name ] += $value;
	}
}

// Concatenate theme variable with specified value
if ( ! function_exists( 'qwery_storage_concat' ) ) {
	function qwery_storage_concat( $var_name, $value ) {
		global $QWERY_STORAGE;
		if ( empty( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = '';
		}
		$QWERY_STORAGE[ $var_name ] .= $value;
	}
}

// Get array (one or two dim) element
if ( ! function_exists( 'qwery_storage_get_array' ) ) {
	function qwery_storage_get_array( $var_name, $key, $key2 = '', $default = '' ) {
		global $QWERY_STORAGE;
		if ( empty( $key2 ) ) {
			return ! empty( $var_name ) && ! empty( $key ) && isset( $QWERY_STORAGE[ $var_name ][ $key ] ) ? $QWERY_STORAGE[ $var_name ][ $key ] : $default;
		} else {
			return ! empty( $var_name ) && ! empty( $key ) && isset( $QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] ) ? $QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] : $default;
		}
	}
}

// Set array element
if ( ! function_exists( 'qwery_storage_set_array' ) ) {
	function qwery_storage_set_array( $var_name, $key, $value ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( '' === $key ) {
			$QWERY_STORAGE[ $var_name ][] = $value;
		} else {
			$QWERY_STORAGE[ $var_name ][ $key ] = $value;
		}
	}
}

// Set two-dim array element
if ( ! function_exists( 'qwery_storage_set_array2' ) ) {
	function qwery_storage_set_array2( $var_name, $key, $key2, $value ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( ! isset( $QWERY_STORAGE[ $var_name ][ $key ] ) ) {
			$QWERY_STORAGE[ $var_name ][ $key ] = array();
		}
		if ( '' === $key2 ) {
			$QWERY_STORAGE[ $var_name ][ $key ][] = $value;
		} else {
			$QWERY_STORAGE[ $var_name ][ $key ][ $key2 ] = $value;
		}
	}
}

// Merge array elements
if ( ! function_exists( 'qwery_storage_merge_array' ) ) {
	function qwery_storage_merge_array( $var_name, $key, $value ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( '' === $key ) {
			$QWERY_STORAGE[ $var_name ] = array_merge( $QWERY_STORAGE[ $var_name ], $value );
		} else {
			$QWERY_STORAGE[ $var_name ][ $key ] = array_merge( $QWERY_STORAGE[ $var_name ][ $key ], $value );
		}
	}
}

// Add array element after the key
if ( ! function_exists( 'qwery_storage_set_array_after' ) ) {
	function qwery_storage_set_array_after( $var_name, $after, $key, $value = '' ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( is_array( $key ) ) {
			qwery_array_insert_after( $QWERY_STORAGE[ $var_name ], $after, $key );
		} else {
			qwery_array_insert_after( $QWERY_STORAGE[ $var_name ], $after, array( $key => $value ) );
		}
	}
}

// Add array element before the key
if ( ! function_exists( 'qwery_storage_set_array_before' ) ) {
	function qwery_storage_set_array_before( $var_name, $before, $key, $value = '' ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( is_array( $key ) ) {
			qwery_array_insert_before( $QWERY_STORAGE[ $var_name ], $before, $key );
		} else {
			qwery_array_insert_before( $QWERY_STORAGE[ $var_name ], $before, array( $key => $value ) );
		}
	}
}

// Push element into array
if ( ! function_exists( 'qwery_storage_push_array' ) ) {
	function qwery_storage_push_array( $var_name, $key, $value ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( '' === $key ) {
			array_push( $QWERY_STORAGE[ $var_name ], $value );
		} else {
			if ( ! isset( $QWERY_STORAGE[ $var_name ][ $key ] ) ) {
				$QWERY_STORAGE[ $var_name ][ $key ] = array();
			}
			array_push( $QWERY_STORAGE[ $var_name ][ $key ], $value );
		}
	}
}

// Pop element from array
if ( ! function_exists( 'qwery_storage_pop_array' ) ) {
	function qwery_storage_pop_array( $var_name, $key = '', $defa = '' ) {
		global $QWERY_STORAGE;
		$rez = $defa;
		if ( '' === $key ) {
			if ( isset( $QWERY_STORAGE[ $var_name ] ) && is_array( $QWERY_STORAGE[ $var_name ] ) && count( $QWERY_STORAGE[ $var_name ] ) > 0 ) {
				$rez = array_pop( $QWERY_STORAGE[ $var_name ] );
			}
		} else {
			if ( isset( $QWERY_STORAGE[ $var_name ][ $key ] ) && is_array( $QWERY_STORAGE[ $var_name ][ $key ] ) && count( $QWERY_STORAGE[ $var_name ][ $key ] ) > 0 ) {
				$rez = array_pop( $QWERY_STORAGE[ $var_name ][ $key ] );
			}
		}
		return $rez;
	}
}

// Inc/Dec array element with specified value
if ( ! function_exists( 'qwery_storage_inc_array' ) ) {
	function qwery_storage_inc_array( $var_name, $key, $value = 1 ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( empty( $QWERY_STORAGE[ $var_name ][ $key ] ) ) {
			$QWERY_STORAGE[ $var_name ][ $key ] = 0;
		}
		$QWERY_STORAGE[ $var_name ][ $key ] += $value;
	}
}

// Concatenate array element with specified value
if ( ! function_exists( 'qwery_storage_concat_array' ) ) {
	function qwery_storage_concat_array( $var_name, $key, $value ) {
		global $QWERY_STORAGE;
		if ( ! isset( $QWERY_STORAGE[ $var_name ] ) ) {
			$QWERY_STORAGE[ $var_name ] = array();
		}
		if ( empty( $QWERY_STORAGE[ $var_name ][ $key ] ) ) {
			$QWERY_STORAGE[ $var_name ][ $key ] = '';
		}
		$QWERY_STORAGE[ $var_name ][ $key ] .= $value;
	}
}

// Call object's method
if ( ! function_exists( 'qwery_storage_call_obj_method' ) ) {
	function qwery_storage_call_obj_method( $var_name, $method, $param = null ) {
		global $QWERY_STORAGE;
		if ( null === $param ) {
			return ! empty( $var_name ) && ! empty( $method ) && isset( $QWERY_STORAGE[ $var_name ] ) ? $QWERY_STORAGE[ $var_name ]->$method() : '';
		} else {
			return ! empty( $var_name ) && ! empty( $method ) && isset( $QWERY_STORAGE[ $var_name ] ) ? $QWERY_STORAGE[ $var_name ]->$method( $param ) : '';
		}
	}
}

// Get object's property
if ( ! function_exists( 'qwery_storage_get_obj_property' ) ) {
	function qwery_storage_get_obj_property( $var_name, $prop, $default = '' ) {
		global $QWERY_STORAGE;
		return ! empty( $var_name ) && ! empty( $prop ) && isset( $QWERY_STORAGE[ $var_name ]->$prop ) ? $QWERY_STORAGE[ $var_name ]->$prop : $default;
	}
}
