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

	/* ── Hover effect picker ─────────────────────────────── */

	var $demo = $( '#gl-hover-demo' );

	// CSS applied to the demo element per effect key
	var hoverEffects = {
		glow:     function( el ) {
			$( el ).css({ 'filter': 'brightness(1.12)', 'box-shadow': '4px 0 18px 4px rgba(0,0,0,.35)', 'transform': 'rotate(180deg)' });
		},
		brighten: function( el ) {
			$( el ).css({ 'filter': 'brightness(1.22) saturate(1.1)', 'transform': 'rotate(180deg) scaleX(1.06)' });
		},
		grow:     function( el ) {
			$( el ).css({ 'transform': 'rotate(180deg) scale(1.1)', 'transform-origin': 'right center' });
		},
		shrink:   function( el ) {
			$( el ).css({ 'filter': 'brightness(0.9)', 'transform': 'rotate(180deg) scale(0.92)' });
		},
		pulse:    function( el ) {
			$( el ).css({ 'animation': 'gl-demo-pulse 0.55s ease forwards' });
		},
		shake:    function( el ) {
			$( el ).css({ 'animation': 'gl-demo-shake 0.45s ease forwards' });
		},
		flip:     function( el ) {
			$( el ).css({ 'transform': 'rotate(180deg) rotateY(180deg)', 'filter': 'brightness(1.1)' });
		},
		none:     function() {}
	};

	function resetDemo() {
		$demo.css({ filter: '', transform: 'rotate(180deg)', 'box-shadow': '', animation: '' });
	}

	function applyDemoEffect( key ) {
		resetDemo();
		if ( hoverEffects[ key ] ) {
			hoverEffects[ key ]( $demo[0] );
		}
	}

	// Inject keyframes for demo animations
	var demoStyles = document.createElement( 'style' );
	demoStyles.textContent =
		'@keyframes gl-demo-pulse{0%{transform:rotate(180deg) scale(1)}40%{transform:rotate(180deg) scale(1.08)}70%{transform:rotate(180deg) scale(0.97)}100%{transform:rotate(180deg) scale(1)}}' +
		'@keyframes gl-demo-shake{0%{transform:rotate(180deg) translateY(0)}20%{transform:rotate(180deg) translateY(-4px)}40%{transform:rotate(180deg) translateY(4px)}60%{transform:rotate(180deg) translateY(-3px)}80%{transform:rotate(180deg) translateY(3px)}100%{transform:rotate(180deg) translateY(0)}}';
	document.head.appendChild( demoStyles );

	$demo.on( 'mouseenter', function () {
		var active = $( 'input[name$="[hover_effect]"]:checked' ).val() || 'none';
		applyDemoEffect( active );
	} ).on( 'mouseleave', function () {
		resetDemo();
	} );

	$( '.gl-hcard' ).on( 'click', function () {
		$( '.gl-hcard' ).removeClass( 'gl-hcard--on' );
		$( this ).addClass( 'gl-hcard--on' );
	} );

	// Initialise demo bg colour from btn1 colour picker value
	var initBg = $( 'input[name$="[btn1_bg_color]"]' ).val();
	if ( initBg ) { $demo.css( 'background', initBg ); }

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
