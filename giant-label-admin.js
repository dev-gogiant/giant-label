/* Giant Label — Admin JS */
jQuery( document ).ready( function ( $ ) {

	/* ── Colour pickers ────────────────────────────────── */
	$( '.gl-color-picker' ).wpColorPicker();

	/* ── Variant card highlight on radio change ─────────── */
	$( 'input[name$="[design_variant]"]' ).on( 'change', function () {
		$( '.gl-vcard' ).removeClass( 'gl-vcard--on' );
		$( this ).closest( '.gl-vcard' ).addClass( 'gl-vcard--on' );
	} );

	/* ── Position slider ─────────────────────────────────── */
	var $slider  = $( '#gl_position' );
	var $readout = $( '#gl-pos-value' );
	var $markers = $( '#gl-vp-markers' );

	function syncPosition() {
		var v = $slider.val();
		$readout.text( v );
		$markers.css( 'top', v + '%' );
	}

	$slider.on( 'input', syncPosition );

	// Initialise to saved value on page load
	syncPosition();

} );
