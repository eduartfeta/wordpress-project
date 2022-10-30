/**
 * Shortcode Switcher
 *
 * @package ThemeREX Addons
 * @since v2.6.0
 */

/* global jQuery:false */
/* global TRX_ADDONS_STORAGE:false */


jQuery( document ).on( 'action.init_hidden_elements', function() {

	"use strict";

	jQuery( '.sc_switcher:not(.sc_hscroll_inited)' ).each( function(nth) {

		var $self = jQuery( this ).addClass( 'sc_hscroll_inited' ),
			$toggle = $self.find( '.sc_switcher_controls_toggle' ),
			$sections = $self.find( '.sc_switcher_section' );

		// Click on toggle
		$toggle.on( 'click', function() {
			sc_switcher_toggle_state(0);
		} );
		// Click on the left title
		$self.find('.sc_switcher_controls_section1').on( 'click', function() {
			sc_switcher_toggle_state(1);
		} );
		// Click on the right title
		$self.find('.sc_switcher_controls_section2').on( 'click', function() {
			sc_switcher_toggle_state(2);
		} );
		// Toggle state
		function sc_switcher_toggle_state( state ) {
			if ( $toggle.hasClass( 'sc_switcher_controls_toggle_on' ) ) {
				if ( state === 0 || state == 2 ) {
					$toggle.removeClass( 'sc_switcher_controls_toggle_on' );
					$sections.eq(0).removeClass( 'sc_switcher_section_active' );
					$sections.eq(1).addClass( 'sc_switcher_section_active' );
				}
			} else {
				if ( state === 0 || state == 1 ) {
					$toggle.addClass( 'sc_switcher_controls_toggle_on' );
					$sections.eq(0).addClass( 'sc_switcher_section_active' );
					$sections.eq(1).removeClass( 'sc_switcher_section_active' );
				}				
			}
		}

	} );

} );