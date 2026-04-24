/* Giant Label — Admin JS */
jQuery( document ).ready( function ( $ ) {

	/* ── Colour pickers ────────────────────────────────── */
	$( '.gl-color-picker' ).wpColorPicker( {
		change: function ( event, ui ) {
			// Keep the radius preview background in sync with the bg colour picker
			var $input  = $( this );
			var name    = $input.attr( 'name' ) || '';
			var isBg    = name.indexOf( '_bg_color' ) !== -1;
			if ( ! isBg ) return;
			var btnKey  = name.indexOf( 'btn1' ) !== -1 ? 'btn1' : 'btn2';
			$( '#gl-radius-preview-' + btnKey ).css( 'background', ui.color.toString() );
		}
	} );

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
	syncPosition();

	/* ── Border radius live preview ──────────────────────── */
	$( '.gl-radius-picker' ).each( function () {
		var $picker  = $( this );
		var btn      = $picker.data( 'btn' );
		var $preview = $( '#gl-radius-preview-' + btn );

		function syncRadius() {
			var vals = {};
			$picker.find( '.gl-radius-input' ).each( function () {
				vals[ $( this ).data( 'corner' ) ] = parseInt( $( this ).val(), 10 ) || 0;
			} );
			$preview.css(
				'border-radius',
				vals.tl + 'px ' + vals.tr + 'px ' + vals.br + 'px ' + vals.bl + 'px'
			);
		}

		$picker.find( '.gl-radius-input' ).on( 'input', syncRadius );
		syncRadius();
	} );

} );
