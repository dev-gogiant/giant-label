<?php
/**
 * Plugin Name: Giant Label
 * Plugin URI:
 * Description: Display up to two customisable fixed labels on the right side of your website, rotated 90°, with square, rounded, or hexagonal design variants.
 * Version:     1.0.0
 * Author:      Marius C.
 * Author URI:
 * Text Domain: giant-label
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GIANT_LABEL_VERSION', '1.0.0' );
define( 'GIANT_LABEL_URL',     plugin_dir_url( __FILE__ ) );

class Giant_Label {

	private static $instance = null;
	const OPTION_KEY = 'giant_label_settings';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',            array( $this, 'register_admin_menu' ) );
		add_action( 'admin_init',            array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_public_assets' ) );
		add_action( 'wp_footer',             array( $this, 'render_frontend' ) );
	}

	/* -------------------------------------------------------------------------
	 * Settings helpers
	 * ---------------------------------------------------------------------- */

	private function defaults() {
		return array(
			'enabled'         => 0,
			'design_variant'  => 'square',
			'buttons_display' => 'two',
			'btn1_text'       => 'Contact Us',
			'btn1_url'        => '',
			'btn1_bg_color'   => '#0073aa',
			'btn1_text_color' => '#ffffff',
			'btn1_radius_tl'  => 0,
			'btn1_radius_tr'  => 0,
			'btn1_radius_br'  => 0,
			'btn1_radius_bl'  => 0,
			'btn2_text'       => 'Get a Quote',
			'btn2_url'        => '',
			'btn2_bg_color'   => '#003d56',
			'btn2_text_color' => '#ffffff',
			'btn2_radius_tl'  => 0,
			'btn2_radius_tr'  => 0,
			'btn2_radius_br'  => 0,
			'btn2_radius_bl'  => 0,
			'position'        => 50,
		);
	}

	public function settings() {
		return wp_parse_args(
			(array) get_option( self::OPTION_KEY, array() ),
			$this->defaults()
		);
	}

	/* -------------------------------------------------------------------------
	 * Admin menu & settings registration
	 * ---------------------------------------------------------------------- */

	public function register_admin_menu() {
		add_menu_page(
			__( 'Giant Label', 'giant-label' ),
			__( 'Giant Label', 'giant-label' ),
			'manage_options',
			'giant-label',
			array( $this, 'render_admin_page' ),
			'dashicons-tag',
			81
		);
	}

	public function register_settings() {
		register_setting(
			'giant_label_group',
			self::OPTION_KEY,
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);
	}

	public function sanitize_settings( $raw ) {
		$raw   = (array) $raw;
		$clean = array();

		$clean['enabled']        = ! empty( $raw['enabled'] ) ? 1 : 0;
		$clean['design_variant'] = in_array( $raw['design_variant'] ?? '', array( 'square', 'rounded', 'hexagonal' ), true )
		                           ? $raw['design_variant'] : 'square';

		$display_opts             = array( 'none', 'one', 'two' );
		$clean['buttons_display'] = in_array( $raw['buttons_display'] ?? '', $display_opts, true )
		                            ? $raw['buttons_display'] : 'two';

		$clean['position'] = min( 95, max( 5, absint( $raw['position'] ?? 50 ) ) );

		foreach ( array( 'btn1', 'btn2' ) as $b ) {
			$clean[ $b . '_text' ]       = sanitize_text_field( $raw[ $b . '_text' ]      ?? '' );
			$clean[ $b . '_url' ]        = esc_url_raw( $raw[ $b . '_url' ]               ?? '' );
			$clean[ $b . '_bg_color' ]   = sanitize_hex_color( $raw[ $b . '_bg_color' ]   ?? '#0073aa' ) ?: '#0073aa';
			$clean[ $b . '_text_color' ] = sanitize_hex_color( $raw[ $b . '_text_color' ] ?? '#ffffff' ) ?: '#ffffff';
			foreach ( array( 'tl', 'tr', 'br', 'bl' ) as $corner ) {
				$key = $b . '_radius_' . $corner;
				$clean[ $key ] = min( 100, max( 0, absint( $raw[ $key ] ?? 0 ) ) );
			}
		}

		return $clean;
	}

	/* -------------------------------------------------------------------------
	 * Asset enqueuing
	 * ---------------------------------------------------------------------- */

	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_giant-label' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style(
			'giant-label-admin',
			GIANT_LABEL_URL . 'giant-label-admin.css',
			array(),
			GIANT_LABEL_VERSION
		);
		wp_enqueue_script(
			'giant-label-admin',
			GIANT_LABEL_URL . 'giant-label-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			GIANT_LABEL_VERSION,
			true
		);
	}

	public function enqueue_public_assets() {
		$s = $this->settings();
		if ( empty( $s['enabled'] ) ) {
			return;
		}
		wp_enqueue_style(
			'giant-label-public',
			GIANT_LABEL_URL . 'giant-label-public.css',
			array(),
			GIANT_LABEL_VERSION
		);
	}

	/* -------------------------------------------------------------------------
	 * Helpers
	 * ---------------------------------------------------------------------- */

	private function radius_style( $s, $btn ) {
		// The label uses writing-mode + rotate(180deg), which swaps left and right
		// corners visually. To match what the user sees/expects in the admin UI,
		// we swap the left/right values here before outputting.
		$tl = intval( $s[ $btn . '_radius_tr' ] ); // visual TL = CSS TR
		$tr = intval( $s[ $btn . '_radius_tl' ] ); // visual TR = CSS TL
		$br = intval( $s[ $btn . '_radius_bl' ] ); // visual BR = CSS BL
		$bl = intval( $s[ $btn . '_radius_br' ] ); // visual BL = CSS BR
		if ( $tl === 0 && $tr === 0 && $br === 0 && $bl === 0 ) {
			return '';
		}
		return sprintf(
			'border-radius:%dpx %dpx %dpx %dpx;',
			$tl, $tr, $br, $bl
		);
	}

	/* -------------------------------------------------------------------------
	 * Frontend render
	 * ---------------------------------------------------------------------- */

	public function render_frontend() {
		$s = $this->settings();
		if ( empty( $s['enabled'] ) ) {
			return;
		}

		$variant   = esc_attr( $s['design_variant'] );
		$show_btn1 = in_array( $s['buttons_display'], array( 'one', 'two' ), true );
		$show_btn2 = ( $s['buttons_display'] === 'two' );

		if ( ! $show_btn1 && ! $show_btn2 ) {
			return;
		}
		?>
<div class="gl-wrapper gl-variant-<?php echo $variant; ?>"
     style="top:<?php echo intval( $s['position'] ); ?>%;transform:translateY(-50%);"
     role="complementary" aria-label="<?php esc_attr_e( 'Quick links', 'giant-label' ); ?>">

	<?php if ( $show_btn1 ) : ?>
	<a class="gl-item gl-shape-<?php echo $variant; ?>"
	   href="<?php echo esc_url( $s['btn1_url'] ); ?>"
	   style="background-color:<?php echo esc_attr( $s['btn1_bg_color'] ); ?>;color:<?php echo esc_attr( $s['btn1_text_color'] ); ?>;<?php echo $this->radius_style( $s, 'btn1' ); ?>">
		<span><?php echo esc_html( $s['btn1_text'] ); ?></span>
	</a>
	<?php endif; ?>

	<?php if ( $show_btn2 ) : ?>
	<a class="gl-item gl-shape-<?php echo $variant; ?>"
	   href="<?php echo esc_url( $s['btn2_url'] ); ?>"
	   style="background-color:<?php echo esc_attr( $s['btn2_bg_color'] ); ?>;color:<?php echo esc_attr( $s['btn2_text_color'] ); ?>;<?php echo $this->radius_style( $s, 'btn2' ); ?>">
		<span><?php echo esc_html( $s['btn2_text'] ); ?></span>
	</a>
	<?php endif; ?>

</div>
		<?php
	}

	/* -------------------------------------------------------------------------
	 * Admin page render
	 * ---------------------------------------------------------------------- */

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = $this->settings();
		?>
<div class="wrap gl-admin-wrap">

	<div class="gl-admin-header">
		<span class="dashicons dashicons-tag gl-header-icon"></span>
		<div>
			<h1>Giant Label</h1>
			<p class="gl-admin-subtitle">Configure your fixed right-side labels. Each label links to a URL and is displayed rotated 90°.</p>
		</div>
	</div>

	<?php settings_errors( 'giant_label_group' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'giant_label_group' ); ?>

		<div class="gl-grid">

			<!-- ── General ───────────────────────────────────────── -->
			<div class="gl-card">
				<div class="gl-card-head"><span class="dashicons dashicons-admin-settings"></span> General</div>
				<div class="gl-card-body">

					<div class="gl-row">
						<label class="gl-lbl">Enable</label>
						<div class="gl-ctrl">
							<label class="gl-switch">
								<input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[enabled]" value="1" <?php checked( $s['enabled'], 1 ); ?>>
								<span class="gl-track"><span class="gl-thumb"></span></span>
							</label>
						</div>
					</div>

					<div class="gl-row">
						<label for="gl_buttons_display" class="gl-lbl">Show Labels</label>
						<div class="gl-ctrl">
							<select id="gl_buttons_display" name="<?php echo self::OPTION_KEY; ?>[buttons_display]">
								<option value="none" <?php selected( $s['buttons_display'], 'none' ); ?>>None</option>
								<option value="one"  <?php selected( $s['buttons_display'], 'one' );  ?>>Label 1 only</option>
								<option value="two"  <?php selected( $s['buttons_display'], 'two' );  ?>>Both Labels</option>
							</select>
						</div>
					</div>

					<div class="gl-row gl-row--top">
						<label class="gl-lbl">Design Variant</label>
						<div class="gl-ctrl">
							<div class="gl-variants">
								<?php
								$variants = array(
									'square'    => 'Square',
									'rounded'   => 'Rounded',
									'hexagonal' => 'Hexagonal',
								);
								foreach ( $variants as $key => $label ) :
								?>
								<label class="gl-vcard<?php echo $s['design_variant'] === $key ? ' gl-vcard--on' : ''; ?>">
									<input type="radio" name="<?php echo self::OPTION_KEY; ?>[design_variant]"
									       value="<?php echo esc_attr( $key ); ?>"
									       <?php checked( $s['design_variant'], $key ); ?>>
									<span class="gl-vthumb gl-vthumb--<?php echo esc_attr( $key ); ?>"></span>
									<span class="gl-vname"><?php echo esc_html( $label ); ?></span>
								</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

				</div>
			</div>

					<div class="gl-row gl-row--top">
						<label for="gl_position" class="gl-lbl">Vertical Position</label>
						<div class="gl-ctrl">
							<div class="gl-pos-control">

								<div class="gl-pos-slider-row">
									<span class="gl-pos-edge-lbl">Top</span>
									<input type="range"
									       id="gl_position"
									       name="giant_label_settings[position]"
									       min="5" max="95" step="1"
									       value="<?php echo esc_attr( $s['position'] ); ?>"
									       class="gl-range">
									<span class="gl-pos-edge-lbl">Bottom</span>
									<span class="gl-pos-readout"><strong id="gl-pos-value"><?php echo esc_html( $s['position'] ); ?></strong>%</span>
								</div>

								<div class="gl-viewport-preview">
									<div class="gl-vp-chrome">
										<span class="gl-vp-dot"></span>
										<span class="gl-vp-dot"></span>
										<span class="gl-vp-dot"></span>
									</div>
									<div class="gl-vp-body">
										<div class="gl-vp-content">
											<div class="gl-vp-line" style="width:78%"></div>
											<div class="gl-vp-line" style="width:55%"></div>
											<div class="gl-vp-line" style="width:70%"></div>
											<div class="gl-vp-line" style="width:45%"></div>
											<div class="gl-vp-line" style="width:65%"></div>
											<div class="gl-vp-line" style="width:80%"></div>
										</div>
										<div class="gl-vp-rail">
											<div class="gl-vp-markers" id="gl-vp-markers"
											     style="top:<?php echo esc_attr( $s['position'] ); ?>%">
												<div class="gl-vp-marker" style="background:<?php echo esc_attr( $s['btn1_bg_color'] ); ?>"></div>
												<div class="gl-vp-marker" style="background:<?php echo esc_attr( $s['btn2_bg_color'] ); ?>"></div>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>

			<!-- ── Labels ─────────────────────────────────────────── -->
			<div class="gl-card gl-card--full">
				<div class="gl-card-head"><span class="dashicons dashicons-tag"></span> Labels</div>
				<div class="gl-card-body">
					<div class="gl-btn-cols">
						<?php foreach ( array( 1, 2 ) as $n ) :
							$b = 'btn' . $n;
						?>
						<div class="gl-btn-box">
							<h3 class="gl-btn-heading">Label <?php echo $n; ?></h3>

							<div class="gl-row">
								<label class="gl-lbl">Text</label>
								<div class="gl-ctrl">
									<input type="text"
									       name="<?php echo self::OPTION_KEY; ?>[<?php echo $b; ?>_text]"
									       value="<?php echo esc_attr( $s[ $b . '_text' ] ); ?>"
									       class="regular-text">
								</div>
							</div>

							<div class="gl-row">
								<label class="gl-lbl">URL</label>
								<div class="gl-ctrl">
									<input type="url"
									       name="<?php echo self::OPTION_KEY; ?>[<?php echo $b; ?>_url]"
									       value="<?php echo esc_attr( $s[ $b . '_url' ] ); ?>"
									       class="regular-text" placeholder="https://">
								</div>
							</div>

							<div class="gl-row">
								<label class="gl-lbl">Background</label>
								<div class="gl-ctrl">
									<input type="text"
									       name="<?php echo self::OPTION_KEY; ?>[<?php echo $b; ?>_bg_color]"
									       value="<?php echo esc_attr( $s[ $b . '_bg_color' ] ); ?>"
									       class="gl-color-picker">
								</div>
							</div>

							<div class="gl-row">
								<label class="gl-lbl">Text Colour</label>
								<div class="gl-ctrl">
									<input type="text"
									       name="<?php echo self::OPTION_KEY; ?>[<?php echo $b; ?>_text_color]"
									       value="<?php echo esc_attr( $s[ $b . '_text_color' ] ); ?>"
									       class="gl-color-picker">
								</div>
							</div>

							<div class="gl-row gl-row--top">
								<label class="gl-lbl">Border Radius</label>
								<div class="gl-ctrl">
									<div class="gl-radius-picker" data-btn="<?php echo esc_attr( $b ); ?>">
										<div class="gl-radius-grid">
											<?php
											$corners = array(
												'tl' => array( 'label' => 'Top left',     'pos' => 'top-left' ),
												'tr' => array( 'label' => 'Top right',    'pos' => 'top-right' ),
												'bl' => array( 'label' => 'Bottom left',  'pos' => 'bottom-left' ),
												'br' => array( 'label' => 'Bottom right', 'pos' => 'bottom-right' ),
											);
											foreach ( $corners as $corner => $info ) :
												$val = intval( $s[ $b . '_radius_' . $corner ] );
											?>
											<div class="gl-radius-corner gl-radius-corner--<?php echo esc_attr( $corner ); ?>">
												<label class="gl-radius-corner__label"><?php echo esc_html( $info['label'] ); ?></label>
												<div class="gl-radius-corner__input">
													<input type="number"
													       name="<?php echo self::OPTION_KEY; ?>[<?php echo $b; ?>_radius_<?php echo $corner; ?>]"
													       value="<?php echo esc_attr( $val ); ?>"
													       min="0" max="100" step="1"
													       class="gl-radius-input"
													       data-corner="<?php echo esc_attr( $corner ); ?>"
													       aria-label="<?php echo esc_attr( $info['label'] ); ?> radius">
													<span class="gl-radius-unit">px</span>
												</div>
											</div>
											<?php endforeach; ?>
										</div>
										<div class="gl-radius-preview-wrap">
											<div class="gl-radius-preview" id="gl-radius-preview-<?php echo esc_attr( $b ); ?>"
											     style="border-radius:<?php
											     echo esc_attr( intval( $s[ $b . '_radius_tl' ] ) ) . 'px ' .
											          esc_attr( intval( $s[ $b . '_radius_tr' ] ) ) . 'px ' .
											          esc_attr( intval( $s[ $b . '_radius_br' ] ) ) . 'px ' .
											          esc_attr( intval( $s[ $b . '_radius_bl' ] ) ) . 'px';
											     ?>;background:<?php echo esc_attr( $s[ $b . '_bg_color' ] ); ?>;">
											</div>
										</div>
									</div>
									<p class="gl-field-desc">Set a radius (0–100 px) per corner. Only applies when the design variant is <strong>Square</strong>.</p>
								</div>
							</div>

						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div><!-- /.gl-grid -->

		<div class="gl-save-row">
			<?php submit_button( 'Save Settings', 'primary large', 'submit', false ); ?>
		</div>

	</form>
</div>
		<?php
	}
}

Giant_Label::get_instance();
