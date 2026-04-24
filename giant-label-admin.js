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

	var $demo  = $( '#gl-hover-demo' );
	var demoEl = $demo[0];

	// Inject keyframes needed for animation-based effects
	var demoStyles = document.createElement( 'style' );
	demoStyles.textContent =
		'@keyframes gl-demo-pulse{0%{transform:rotate(180deg) scale(1)}40%{transform:rotate(180deg) scale(1.08)}70%{transform:rotate(180deg) scale(0.97)}100%{transform:rotate(180deg) scale(1)}}' +
		'@keyframes gl-demo-shake{0%{transform:rotate(180deg) translateY(0)}20%{transform:rotate(180deg) translateY(-4px)}40%{transform:rotate(180deg) translateY(4px)}60%{transform:rotate(180deg) translateY(-3px)}80%{transform:rotate(180deg) translateY(3px)}100%{transform:rotate(180deg) translateY(0)}}';
	document.head.appendChild( demoStyles );

	function resetDemo() {
		// Kill any running animation first, force a reflow, then clear all overrides
		demoEl.style.animation  = 'none';
		void demoEl.offsetWidth; // reflow — forces animation to restart cleanly next time
		demoEl.style.animation     = '';
		demoEl.style.transform     = 'rotate(180deg)';
		demoEl.style.filter        = '';
		demoEl.style.boxShadow     = '';
		demoEl.style.transformOrigin = '';
	}

	function applyDemoEffect( key ) {
		// Always reset + reflow so animations fire fresh on every mouseenter
		demoEl.style.animation = 'none';
		void demoEl.offsetWidth;
		demoEl.style.animation = '';

		switch ( key ) {
			case 'glow':
				demoEl.style.transform = 'rotate(180deg)';
				demoEl.style.filter    = 'brightness(1.12)';
				demoEl.style.boxShadow = '4px 0 18px 4px rgba(0,0,0,.35)';
				break;
			case 'brighten':
				demoEl.style.transform = 'rotate(180deg) scaleX(1.06)';
				demoEl.style.filter    = 'brightness(1.22) saturate(1.1)';
				break;
			case 'grow':
				demoEl.style.transformOrigin = 'right center';
				demoEl.style.transform       = 'rotate(180deg) scale(1.1)';
				break;
			case 'shrink':
				demoEl.style.transform = 'rotate(180deg) scale(0.92)';
				demoEl.style.filter    = 'brightness(0.9)';
				break;
			case 'pulse':
				demoEl.style.animation = 'gl-demo-pulse 0.55s ease forwards';
				break;
			case 'shake':
				demoEl.style.animation = 'gl-demo-shake 0.45s ease forwards';
				break;
			case 'flip':
				demoEl.style.transform = 'rotate(180deg) rotateY(180deg)';
				demoEl.style.filter    = 'brightness(1.1)';
				break;
			case 'none':
			default:
				break;
		}
	}

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
