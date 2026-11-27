<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Kho giao diện" – Flatsome theme kits (format: kit.json + pages/*.txt + blocks/*.txt + media/*).
 * Upload a kit zip, install it (media → UX Blocks → pages → theme_mods → motion preset), roll back theme_mods,
 * and expose installed kit pages in the UX Builder Templates library.
 */
class MGM_Kits {

	const STATE = 'mgm_kit_state';
	const EXT   = array( 'json', 'txt', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg' );

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 21 );
		add_action( 'admin_post_mgm_kit_upload', array( __CLASS__, 'upload' ) );
		add_action( 'admin_post_mgm_kit_install', array( __CLASS__, 'install' ) );
		add_action( 'admin_post_mgm_kit_rollback', array( __CLASS__, 'rollback' ) );
		add_action( 'ux_builder_setup', array( __CLASS__, 'register_templates' ) );
	}

	public static function menu() {
		add_submenu_page( 'migi', 'Kho giao diện', 'Kho giao diện', 'manage_options', 'mg-kits', array( __CLASS__, 'page' ) );
	}

	/* ---------- Kit discovery ---------- */

	private static function upload_dir() {
		$u = wp_upload_dir();
		return array( 'dir' => trailingslashit( $u['basedir'] ) . 'mg-kits', 'url' => trailingslashit( $u['baseurl'] ) . 'mg-kits' );
	}

	/** @return array id => [ 'kit' => array, 'dir' => path, 'url' => url, 'builtin' => bool ] */
	public static function all() {
		$out = array();
		$up  = self::upload_dir();
		foreach ( array( array( MGM_DIR . 'kits', MGM_URL . 'kits', true ), array( $up['dir'], $up['url'], false ) ) as $src ) {
			foreach ( (array) glob( $src[0] . '/*/kit.json' ) as $json ) {
				$kit = json_decode( (string) file_get_contents( $json ), true ); // phpcs:ignore -- local file.
				if ( ! is_array( $kit ) || empty( $kit['id'] ) || ! preg_match( '/^[a-z0-9][a-z0-9\-]{2,60}$/', $kit['id'] ) ) {
					continue;
				}
				$dir                = dirname( $json );
				$out[ $kit['id'] ] = array( 'kit' => $kit, 'dir' => $dir, 'url' => $src[1] . '/' . basename( $dir ), 'builtin' => $src[2] );
			}
		}
		return $out;
	}

	public static function state() {
		return (array) get_option( self::STATE, array() );
	}

	private static function file( $entry, $rel ) {
		$path = realpath( $entry['dir'] . '/' . $rel );
		return $path && 0 === strpos( $path, realpath( $entry['dir'] ) ) && is_file( $path ) ? $path : '';
	}

	/** Shortcode content of a kit file with {{media:key}} replaced by attachment IDs (or removed). */
	private static function content( $entry, $rel, $media ) {
		$path = self::file( $entry, $rel );
		if ( ! $path ) {
			return '';
		}
		return preg_replace_callback( '/\{\{media:([a-z0-9_\-]+)\}\}/', function ( $m ) use ( $media ) {
			return isset( $media[ $m[1] ] ) ? (string) (int) $media[ $m[1] ] : '';
		}, (string) file_get_contents( $path ) ); // phpcs:ignore -- local file.
	}

	/* ---------- Upload ---------- */

	public static function upload() {
		check_admin_referer( 'mgm_kit_upload' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$f = $_FILES['kit'] ?? null; // phpcs:ignore -- validated below.
		if ( ! $f || UPLOAD_ERR_OK !== (int) $f['error'] || ! class_exists( 'ZipArchive' ) || 'zip' !== strtolower( pathinfo( (string) $f['name'], PATHINFO_EXTENSION ) ) ) {
			self::back( 'Hãy chọn file .zip của kit (máy chủ cần PHP ZipArchive).', false );
		}
		$zip = new ZipArchive();
		if ( true !== $zip->open( $f['tmp_name'] ) ) {
			self::back( 'Không đọc được file zip.', false );
		}
		// Locate kit.json at the root or one folder deep.
		$root = null;
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$name = $zip->getNameIndex( $i );
			if ( preg_match( '#^([^/]+/)?kit\.json$#', $name, $m ) ) {
				$root = isset( $m[1] ) ? $m[1] : '';
				break;
			}
		}
		$kit = null !== $root ? json_decode( (string) $zip->getFromName( $root . 'kit.json' ), true ) : null;
		if ( ! is_array( $kit ) || empty( $kit['id'] ) || ! preg_match( '/^[a-z0-9][a-z0-9\-]{2,60}$/', $kit['id'] ) || empty( $kit['pages'] ) ) {
			$zip->close();
			self::back( 'File zip không chứa kit.json hợp lệ.', false );
		}
		$dest = self::upload_dir()['dir'] . '/' . $kit['id'];
		wp_mkdir_p( $dest );
		$count = 0;
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$name = $zip->getNameIndex( $i );
			if ( '' !== $root && 0 !== strpos( $name, $root ) ) {
				continue;
			}
			$rel = substr( $name, strlen( $root ) );
			if ( '' === $rel || '/' === substr( $rel, -1 ) ) {
				continue;
			}
			// Only kit file types, no traversal, max depth 2.
			if ( false !== strpos( $rel, '..' ) || substr_count( $rel, '/' ) > 2 || ! in_array( strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) ), self::EXT, true ) ) {
				continue;
			}
			$target = $dest . '/' . $rel;
			wp_mkdir_p( dirname( $target ) );
			file_put_contents( $target, $zip->getFromIndex( $i ) ); // phpcs:ignore -- extracting an uploaded kit.
			$count++;
		}
		$zip->close();
		file_put_contents( $dest . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore
		self::back( sprintf( 'Đã tải lên kit "%s" (%d file). Bấm "Cài kit" để áp dụng.', $kit['name'] ?? $kit['id'], $count ), true );
	}

	/* ---------- Install / rollback ---------- */

	public static function install() {
		check_admin_referer( 'mgm_kit_install' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$id    = sanitize_key( $_POST['kit'] ?? '' );
		$all   = self::all();
		$entry = $all[ $id ] ?? null;
		if ( ! $entry ) {
			self::back( 'Không tìm thấy kit.', false );
		}
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$kit   = $entry['kit'];
		$state = self::state();
		$st    = $state[ $id ] ?? array( 'media' => array(), 'pages' => array(), 'blocks' => array(), 'prev_mods' => array() );
		$notes = array();

		// 1. Media (reuse attachments from a previous install).
		foreach ( (array) ( $kit['media'] ?? array() ) as $key => $m ) {
			if ( ! empty( $st['media'][ $key ] ) && get_post( $st['media'][ $key ] ) ) {
				continue;
			}
			$path = self::file( $entry, $m['file'] ?? '' );
			if ( ! $path ) {
				$notes[] = 'Thiếu ảnh ' . $key;
				continue;
			}
			$tmp = wp_tempnam( basename( $path ) );
			copy( $path, $tmp );
			$att = media_handle_sideload( array( 'name' => sanitize_file_name( $id . '-' . basename( $path ) ), 'tmp_name' => $tmp ), 0, sanitize_text_field( $m['alt'] ?? '' ) );
			if ( is_wp_error( $att ) ) {
				wp_delete_file( $tmp );
				$notes[] = 'Ảnh ' . $key . ': ' . $att->get_error_message();
				continue;
			}
			update_post_meta( $att, '_wp_attachment_image_alt', sanitize_text_field( $m['alt'] ?? '' ) );
			update_post_meta( $att, '_mgm_kit', $id );
			$st['media'][ $key ] = $att;
		}

		// 2. UX Blocks (Flatsome only).
		foreach ( (array) ( $kit['blocks'] ?? array() ) as $b ) {
			if ( ! post_type_exists( 'blocks' ) ) {
				$notes[] = 'Theme hiện tại không có UX Blocks (Flatsome) – bỏ qua block ' . $b['slug'];
				continue;
			}
			$post = array( 'post_type' => 'blocks', 'post_status' => 'publish', 'post_name' => sanitize_title( $b['slug'] ), 'post_title' => sanitize_text_field( $b['title'] ), 'post_content' => self::content( $entry, $b['file'], $st['media'] ) );
			if ( ! empty( $st['blocks'][ $b['slug'] ] ) && get_post( $st['blocks'][ $b['slug'] ] ) ) {
				$post['ID'] = $st['blocks'][ $b['slug'] ];
			}
			$bid = wp_insert_post( wp_slash( $post ) );
			if ( $bid && ! is_wp_error( $bid ) ) {
				$st['blocks'][ $b['slug'] ] = $bid;
				if ( ! empty( $b['theme_mod'] ) && ! empty( $_POST['mods'] ) ) {
					$st['prev_mods'] += array( $b['theme_mod'] => get_theme_mod( $b['theme_mod'], null ) );
					set_theme_mod( sanitize_key( $b['theme_mod'] ), $bid );
				}
			}
		}

		// 3. Pages.
		if ( ! empty( $_POST['pages'] ) ) {
			foreach ( (array) $kit['pages'] as $p ) {
				$post = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => sanitize_title( $p['slug'] ), 'post_title' => sanitize_text_field( $p['title'] ), 'post_content' => self::content( $entry, $p['file'], $st['media'] ) );
				if ( ! empty( $st['pages'][ $p['slug'] ] ) && get_post( $st['pages'][ $p['slug'] ] ) ) {
					$post['ID'] = $st['pages'][ $p['slug'] ];
				}
				$pid = wp_insert_post( wp_slash( $post ) );
				if ( ! $pid || is_wp_error( $pid ) ) {
					continue;
				}
				$st['pages'][ $p['slug'] ] = $pid;
				if ( ! empty( $p['template'] ) && locate_template( basename( $p['template'] ) ) ) {
					update_post_meta( $pid, '_wp_page_template', basename( $p['template'] ) );
				}
				if ( ! empty( $p['front'] ) && ! empty( $_POST['front'] ) ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $pid );
				}
			}
		}

		// 4. Customizer (theme_mods) – previous values kept for rollback.
		if ( ! empty( $_POST['mods'] ) ) {
			foreach ( (array) ( $kit['theme_mods'] ?? array() ) as $k => $v ) {
				$k = sanitize_key( $k );
				if ( ! array_key_exists( $k, $st['prev_mods'] ) ) {
					$st['prev_mods'][ $k ] = get_theme_mod( $k, null );
				}
				set_theme_mod( $k, is_array( $v ) ? array_map( 'sanitize_text_field', $v ) : sanitize_text_field( (string) $v ) );
			}
		}

		// 5. Motion preset.
		if ( ! empty( $_POST['preset'] ) && ! empty( $kit['motion_preset'] ) && isset( MGM_Settings::presets()[ $kit['motion_preset'] ] ) ) {
			$s           = MGM_Settings::get();
			$s['preset'] = $kit['motion_preset'];
			update_option( MGM_Settings::OPTION, $s );
		}

		$st['version']      = $kit['version'] ?? '';
		$st['installed_at'] = current_time( 'mysql' );
		$state[ $id ]       = $st;
		update_option( self::STATE, $state, false );
		self::back( 'Đã cài kit "' . ( $kit['name'] ?? $id ) . '": ' . count( $st['pages'] ) . ' trang, ' . count( $st['blocks'] ) . ' block, ' . count( $st['media'] ) . ' ảnh.' . ( $notes ? ' Lưu ý: ' . implode( '; ', $notes ) : '' ), true );
	}

	public static function rollback() {
		check_admin_referer( 'mgm_kit_rollback' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$id    = sanitize_key( $_POST['kit'] ?? '' );
		$state = self::state();
		if ( empty( $state[ $id ]['prev_mods'] ) ) {
			self::back( 'Kit này chưa thay đổi cài đặt Customizer nào.', false );
		}
		foreach ( $state[ $id ]['prev_mods'] as $k => $v ) {
			if ( null === $v ) {
				remove_theme_mod( $k );
			} else {
				set_theme_mod( $k, $v );
			}
		}
		$state[ $id ]['prev_mods'] = array();
		update_option( self::STATE, $state, false );
		self::back( 'Đã khôi phục cài đặt Customizer trước khi cài kit. Trang và ảnh của kit vẫn được giữ lại.', true );
	}

	private static function back( $msg, $ok ) {
		set_transient( 'mgm_kit_notice_' . get_current_user_id(), array( 'm' => $msg, 'ok' => $ok ), 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=mg-kits' ) );
		exit;
	}

	/* ---------- UX Builder templates ---------- */

	public static function register_templates() {
		if ( ! function_exists( 'ux_builder_add_template' ) ) {
			return;
		}
		$state = self::state();
		foreach ( self::all() as $id => $entry ) {
			if ( empty( $state[ $id ] ) ) {
				continue; // Templates need real attachment IDs, so only installed kits.
			}
			$kit   = $entry['kit'];
			$thumb = is_file( $entry['dir'] . '/preview.jpg' ) ? $entry['url'] . '/preview.jpg' : '';
			foreach ( (array) $kit['pages'] as $p ) {
				ux_builder_add_template( 'mgkit_' . $id . '_' . sanitize_key( $p['slug'] ), array(
					'post_types' => array( 'page' ),
					'category'   => 'Migi · ' . ( $kit['industry'] ?? 'Kit' ),
					'name'       => ( $kit['name'] ?? $id ) . ' – ' . $p['title'],
					'thumbnail'  => $thumb,
					'template'   => ! empty( $p['template'] ) ? basename( $p['template'] ) : 'page-blank.php',
					'content'    => self::content( $entry, $p['file'], $state[ $id ]['media'] ?? array() ),
				) );
			}
		}
	}

	/* ---------- Admin page ---------- */

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$n = get_transient( 'mgm_kit_notice_' . get_current_user_id() );
		if ( $n ) {
			delete_transient( 'mgm_kit_notice_' . get_current_user_id() );
			echo '<div class="notice notice-' . ( $n['ok'] ? 'success' : 'error' ) . ' is-dismissible"><p>' . esc_html( $n['m'] ) . '</p></div>';
		}
		$state     = self::state();
		$flatsome  = function_exists( 'mg_core_is_flatsome' ) && mg_core_is_flatsome();
		$presets   = MGM_Settings::presets();
		echo '<div class="wrap mg-admin">';
		mg_core_admin_header( 'Kho giao diện', 'Kit giao diện Flatsome từ <strong>site.migi.vn</strong>: trang dựng sẵn bằng UX Builder, UX Block, ảnh, màu &amp; font Customizer và bộ hiệu ứng theo lĩnh vực.' . ( $flatsome ? '' : ' <strong>Theme hiện tại không phải Flatsome</strong> – trang vẫn tạo được nhưng shortcode UX Builder sẽ không hiển thị đúng.' ), 'dashicons-layout' );
		?>
		<form class="mg-admin-card" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:18px">
			<input type="hidden" name="action" value="mgm_kit_upload"><?php wp_nonce_field( 'mgm_kit_upload' ); ?>
			<h2>Tải kit lên</h2>
			<p>Chọn file <code>.zip</code> tải từ kho giao diện (đóng gói bằng <code>fs_kit.py build</code>).</p>
			<p><input type="file" name="kit" accept=".zip" required> <button class="mg-admin-btn" type="submit">Tải lên</button></p>
		</form>
		<div class="mg-admin-grid">
		<?php
		foreach ( self::all() as $id => $e ) {
			$kit = $e['kit'];
			$st  = $state[ $id ] ?? null;
			$img = is_file( $e['dir'] . '/preview.jpg' ) ? $e['url'] . '/preview.jpg' : '';
			?>
			<div class="mg-admin-card mgm-kit">
				<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" style="width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:14px;margin-bottom:12px"><?php endif; ?>
				<h2><?php echo esc_html( $kit['name'] ?? $id ); ?> <span class="mg-admin-chip">v<?php echo esc_html( $kit['version'] ?? '?' ); ?></span><?php echo $e['builtin'] ? ' <span class="mg-admin-chip">Mẫu</span>' : ''; ?></h2>
				<p><?php echo esc_html( $kit['description'] ?? '' ); ?></p>
				<p class="description"><?php echo esc_html( count( (array) $kit['pages'] ) . ' trang · ' . count( (array) ( $kit['blocks'] ?? array() ) ) . ' block · ' . count( (array) ( $kit['media'] ?? array() ) ) . ' ảnh' . ( ! empty( $kit['motion_preset'] ) && isset( $presets[ $kit['motion_preset'] ] ) ? ' · hiệu ứng: ' . $presets[ $kit['motion_preset'] ]['label'] : '' ) ); ?></p>
				<?php if ( ! empty( $kit['requires'] ) ) : ?><p class="description">Cần: <?php echo esc_html( implode( ', ', (array) $kit['requires'] ) ); ?></p><?php endif; ?>
				<?php if ( $st ) : ?><p><span class="mg-status mg-status--won">Đã cài <?php echo esc_html( mysql2date( 'd/m/Y H:i', $st['installed_at'] ) ); ?></span>
					<?php foreach ( $st['pages'] as $pid ) : ?> <a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" target="_blank">Xem</a> · <a href="<?php echo esc_url( function_exists( 'ux_builder_edit_url' ) ? ux_builder_edit_url( $pid ) : get_edit_post_link( $pid ) ); ?>">Sửa</a><?php endforeach; ?></p><?php endif; ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="mgm_kit_install"><input type="hidden" name="kit" value="<?php echo esc_attr( $id ); ?>"><?php wp_nonce_field( 'mgm_kit_install' ); ?>
					<label style="display:block"><input type="checkbox" name="pages" value="1" checked> Tạo / cập nhật trang</label>
					<label style="display:block"><input type="checkbox" name="mods" value="1" checked> Áp màu, font &amp; footer (Customizer)</label>
					<label style="display:block"><input type="checkbox" name="preset" value="1" checked> Đổi bộ hiệu ứng theo lĩnh vực</label>
					<label style="display:block"><input type="checkbox" name="front" value="1"> Đặt làm trang chủ</label>
					<p><button class="mg-admin-btn" type="submit"><?php echo $st ? 'Cài lại' : 'Cài kit'; ?></button></p>
				</form>
				<?php if ( $st && ! empty( $st['prev_mods'] ) ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="mgm_kit_rollback"><input type="hidden" name="kit" value="<?php echo esc_attr( $id ); ?>"><?php wp_nonce_field( 'mgm_kit_rollback' ); ?>
						<button class="button" type="submit">Khôi phục Customizer như trước khi cài</button>
					</form>
				<?php endif; ?>
			</div>
			<?php
		}
		echo '</div><p class="description">Trang của kit đã cài xuất hiện trong UX Builder → Templates, nhóm "Migi · &lt;lĩnh vực&gt;".</p></div>';
	}
}
