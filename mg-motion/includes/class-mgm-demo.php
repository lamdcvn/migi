<?php
defined( 'ABSPATH' ) || exit;

/** Creates the /flatsome demo landing for the selected industry preset. */
class MGM_Demo {

	public static function init() {
		add_action( 'admin_post_mgm_demo', array( __CLASS__, 'create' ) );
	}

	public static function is_flatsome() {
		return 'flatsome' === get_template();
	}

	/** Flatsome UX Builder markup (shortcodes) – editable in UX Builder. */
	public static function flatsome_content( $key, $p ) {
		$c    = $p['copy'];
		$dark = $p['dark'];
		$bg   = $p['colors'][0];
		$alt  = $dark ? 'rgb(255,255,255,0.03)' : '#ffffff';
		$d    = $dark ? ' dark="true"' : '';
		$out  = '[section bg_color="' . $bg . '"' . $d . ' padding="140px" padding__sm="100px" height="92vh" class="mg-kenburns ' . esc_attr( $p['fx'] ) . '"]' . "\n";
		$out .= '[row h_align="center"]' . "\n" . '[col span="10" span__sm="12" align="center"]' . "\n";
		$out .= '[ux_text text_align="center" class="mg-reveal"]<p style="letter-spacing:.3em;text-transform:uppercase;font-size:13px;font-weight:700;">' . esc_html( $c['eyebrow'] ) . '</p>[/ux_text]' . "\n";
		$out .= '[ux_text font_size="2.6" font_size__sm="1.6" line_height="1.05" text_align="center" class="mg-split"]<h1><strong>' . esc_html( $c['title'] ) . '</strong></h1>[/ux_text]' . "\n";
		$out .= '[ux_text font_size="1.15" text_align="center" class="mg-reveal mg-d3"]<p>' . esc_html( $c['sub'] ) . '</p>[/ux_text]' . "\n";
		$out .= '[gap height="20px"]' . "\n";
		$out .= '[button text="' . esc_attr( $c['cta'] ) . '" color="primary" radius="99" size="larger" class="mg-btn-shine mg-glow-btn mg-magnetic mg-reveal mg-d4" link="#lien-he"]' . "\n";
		$out .= '[button text="' . esc_attr( $c['cta2'] ) . '" style="outline" radius="99" size="larger" class="mg-reveal mg-d5" link="#dich-vu"]' . "\n";
		$out .= '[/col]' . "\n" . '[/row]' . "\n" . '[/section]' . "\n";
		$out .= '[section bg_color="' . ( $dark ? '#0f0d15' : '#ffffff' ) . '"' . $d . ' padding="24px"]' . "\n" . '[mg_marquee items="' . esc_attr( $c['marquee'] ) . '" speed="28"]' . "\n" . '[/section]' . "\n";
		$out .= '[section bg_color="' . $bg . '"' . $d . ' padding="90px"]' . "\n" . '[row class="mg-stagger" h_align="center"]' . "\n";
		foreach ( $c['stats'] as $s ) {
			$out .= '[col span="4" span__sm="12" align="center"]' . "\n" . '[ux_text font_size="2.6" text_align="center" class="mg-count mg-shine"]<h2><strong>' . esc_html( $s[0] ) . '</strong></h2>[/ux_text]' . "\n" . '[ux_text text_align="center"]<p>' . esc_html( $s[1] ) . '</p>[/ux_text]' . "\n" . '[/col]' . "\n";
		}
		$out .= '[/row]' . "\n" . '[/section]' . "\n";
		$out .= '[section label="Dịch vụ" bg_color="' . ( $dark ? '#0f0d15' : '#f7f7fb' ) . '"' . $d . ' padding="100px" class="' . ( 'tech' === $key ? 'mg-fx-grid' : '' ) . '"]' . "\n";
		$out .= '[row h_align="center"]' . "\n" . '[col span="8" span__sm="12" align="center"]' . "\n" . '[ux_text text_align="center" class="mg-reveal"]<h2 id="dich-vu">Vì sao chọn chúng tôi</h2>[/ux_text]' . "\n" . '[/col]' . "\n" . '[/row]' . "\n";
		$out .= '[row class="mg-stagger" col_bg="' . $alt . '" col_bg_radius="22" padding="34px 28px 28px 28px"]' . "\n";
		foreach ( $c['features'] as $i => $f ) {
			$out .= '[col span="4" span__sm="12" class="mg-tilt mg-spotlight mg-lift"]' . "\n" . '[ux_text]<p style="font-size:40px;line-height:1;margin-bottom:12px;">' . esc_html( array( '✦', '◆', '●' )[ $i % 3 ] ) . '</p><h3>' . esc_html( $f[0] ) . '</h3><p>' . esc_html( $f[1] ) . '</p>[/ux_text]' . "\n" . '[/col]' . "\n";
		}
		$out .= '[/row]' . "\n" . '[/section]' . "\n";
		$out .= '[section bg_color="' . $bg . '"' . $d . ' padding="110px" class="mg-parallax ' . esc_attr( 'nightlife' === $key ? 'mg-fx-lasers' : 'mg-fx-aurora' ) . '"]' . "\n" . '[row h_align="center"]' . "\n" . '[col span="8" span__sm="12" align="center"]' . "\n";
		$out .= '[ux_text font_size="2" text_align="center" class="mg-reveal mg-reveal-zoom"]<h2 id="lien-he"><span class="mg-shine">' . esc_html( $c['final'][0] ) . '</span></h2>[/ux_text]' . "\n";
		$out .= '[ux_text text_align="center" class="mg-reveal mg-d2"]<p>' . esc_html( $c['final'][1] ) . '</p>[/ux_text]' . "\n";
		$out .= '[button text="' . esc_attr( $c['cta'] ) . '" color="primary" radius="99" size="larger" class="mg-btn-shine mg-glow-btn mg-pulse"]' . "\n";
		$out .= '[/col]' . "\n" . '[/row]' . "\n" . '[/section]';
		return $out;
	}

	/** Theme-independent HTML version (Gutenberg "Custom HTML" block). */
	public static function html_content( $key, $p ) {
		$c   = $p['copy'];
		$ink = $p['dark'] ? '#f4f1f8' : '#101828';
		$mut = $p['dark'] ? '#a39db0' : '#667085';
		$alt = $p['dark'] ? '#0f0d15' : '#f7f7fb';
		$card = $p['dark'] ? 'rgba(255,255,255,.04)' : '#ffffff';
		ob_start();
		?>
<style>.mgm-demo{--ink:<?php echo esc_attr( $ink ); ?>;--mut:<?php echo esc_attr( $mut ); ?>;color:var(--ink);font-family:inherit}.mgm-demo section{padding:90px 20px}.mgm-demo .wrap{max-width:1100px;margin:0 auto}.mgm-demo h1{font-size:clamp(40px,7vw,86px);line-height:1.02;margin:.2em 0;color:var(--ink)}.mgm-demo h2{font-size:clamp(28px,4vw,46px);color:var(--ink)}.mgm-demo h3{color:var(--ink)}.mgm-demo p{color:var(--mut);font-size:17px}.mgm-demo .center{text-align:center}.mgm-demo .eyebrow{letter-spacing:.3em;text-transform:uppercase;font-size:13px;font-weight:700;color:var(--mgm-accent)}.mgm-demo .btn{display:inline-block;margin:6px;padding:16px 30px;border-radius:99px;font-weight:700;text-decoration:none;background:linear-gradient(135deg,var(--mgm-accent),var(--mgm-accent-2));color:#fff}.mgm-demo .btn.ghost{background:transparent;border:1px solid currentColor;color:var(--ink)}.mgm-demo .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px}.mgm-demo .card{padding:34px 28px;border-radius:22px;background:<?php echo esc_attr( $card ); ?>;border:1px solid rgba(127,127,127,.15)}.mgm-demo .stat h2{margin:0;font-size:clamp(40px,5vw,60px)}.mgm-demo .hero{min-height:88vh;display:grid;place-items:center;background:<?php echo esc_attr( $p['colors'][0] ); ?>;overflow:hidden}.mgm-demo .alt{background:<?php echo esc_attr( $alt ); ?>}.mgm-demo .base{background:<?php echo esc_attr( $p['colors'][0] ); ?>}.mgm-demo .marq{padding:22px 0}</style>
<div class="mgm-demo">
<section class="hero <?php echo esc_attr( $p['fx'] ); ?>"><div class="wrap center">
<p class="eyebrow mg-reveal"><?php echo esc_html( $c['eyebrow'] ); ?></p>
<h1 class="mg-split"><?php echo esc_html( $c['title'] ); ?></h1>
<p class="mg-reveal mg-d3" style="max-width:640px;margin:0 auto 26px"><?php echo esc_html( $c['sub'] ); ?></p>
<a class="btn mg-btn-shine mg-glow-btn mg-magnetic mg-reveal mg-d4" href="#lien-he"><?php echo esc_html( $c['cta'] ); ?></a><a class="btn ghost mg-reveal mg-d5" href="#dich-vu"><?php echo esc_html( $c['cta2'] ); ?></a>
</div></section>
<div class="alt marq"><div class="mg-marquee" data-items="<?php echo esc_attr( $c['marquee'] ); ?>" data-speed="28"></div></div>
<section class="base"><div class="wrap grid mg-stagger">
<?php foreach ( $c['stats'] as $s ) : ?><div class="center stat"><h2 class="mg-count mg-shine"><?php echo esc_html( $s[0] ); ?></h2><p><?php echo esc_html( $s[1] ); ?></p></div><?php endforeach; ?>
</div></section>
<section class="alt <?php echo 'tech' === $key ? 'mg-fx-grid' : ''; ?>"><div class="wrap"><h2 id="dich-vu" class="center mg-reveal">Vì sao chọn chúng tôi</h2><div class="grid mg-stagger">
<?php foreach ( $c['features'] as $i => $f ) : ?><div class="card mg-tilt mg-spotlight mg-lift"><p style="font-size:40px;line-height:1;margin:0 0 12px;color:var(--mgm-accent)"><?php echo esc_html( array( '✦', '◆', '●' )[ $i % 3 ] ); ?></p><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></div><?php endforeach; ?>
</div></div></section>
<section class="base mg-parallax <?php echo 'nightlife' === $key ? 'mg-fx-lasers' : 'mg-fx-aurora'; ?>"><div class="wrap center" style="max-width:760px">
<h2 id="lien-he" class="mg-reveal mg-reveal-zoom"><span class="mg-shine"><?php echo esc_html( $c['final'][0] ); ?></span></h2>
<p class="mg-reveal mg-d2"><?php echo esc_html( $c['final'][1] ); ?></p>
<a class="btn mg-btn-shine mg-glow-btn mg-pulse" href="#"><?php echo esc_html( $c['cta'] ); ?></a>
</div></section>
</div>
		<?php
		return ob_get_clean();
	}

	public static function create() {
		check_admin_referer( 'mgm_demo' );
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$s       = MGM_Settings::get();
		$presets = MGM_Settings::presets();
		$p       = $presets[ $s['preset'] ];
		$content = self::is_flatsome() ? self::flatsome_content( $s['preset'], $p ) : "<!-- wp:html -->\n" . self::html_content( $s['preset'], $p ) . "\n<!-- /wp:html -->";
		$page    = get_page_by_path( 'flatsome' );
		$args    = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Landing ' . $p['label'], 'post_name' => 'flatsome', 'post_content' => $content );
		if ( $page ) {
			if ( empty( $_POST['overwrite'] ) ) {
				wp_safe_redirect( get_permalink( $page ) );
				exit;
			}
			$args['ID'] = $page->ID;
			$id         = wp_update_post( wp_slash( $args ) );
		} else {
			$id = wp_insert_post( wp_slash( $args ) );
		}
		if ( $id && ! is_wp_error( $id ) && self::is_flatsome() ) {
			foreach ( $p['dark'] ? array( 'page-transparent-header-light.php', 'page-blank.php' ) : array( 'page-transparent-header.php', 'page-blank.php' ) as $tpl ) {
				if ( locate_template( $tpl ) ) {
					update_post_meta( $id, '_wp_page_template', $tpl );
					break;
				}
			}
		}
		wp_safe_redirect( $id && ! is_wp_error( $id ) ? get_permalink( $id ) : admin_url( 'admin.php?page=mg-motion' ) );
		exit;
	}
}
