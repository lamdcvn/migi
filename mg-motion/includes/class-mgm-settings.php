<?php
defined( 'ABSPATH' ) || exit;

class MGM_Settings {

	const OPTION = 'mgm_settings';

	/**
	 * Industry presets: colours + motion character + suggested decorations + demo copy.
	 */
	public static function presets() {
		return array(
			'nightlife'  => array(
				'label'   => 'Bar · Club · Karaoke · Sự kiện',
				'colors'  => array( '#07060a', '#ff2d87', '#7b2dff', '#1ee3ff' ),
				'dark'    => true,
				'variant' => 'mg-reveal-zoom',
				'fx'      => 'mg-fx-lasers mg-fx-grain',
				'motion'  => 'Nhanh, bùng nổ: chữ bay từng ký tự, laser quét, chữ neon gradient, nút quét sáng.',
				'copy'    => array(
					'eyebrow' => 'Super Club · Hà Nội', 'title' => 'Đêm nay quẩy hết mình', 'sub' => 'Âm thanh chuẩn quốc tế, visual LED và những set nhạc khiến cả dance floor bùng cháy.',
					'cta' => 'Đặt bàn ngay', 'cta2' => 'Xem sự kiện', 'marquee' => 'EDM|Vinahouse|Ladies Night|Bottle Service|Live DJ',
					'stats' => array( array( '99.845+', 'Lượt khách mỗi năm' ), array( '997+', 'Đêm show đã tổ chức' ), array( '98+', 'DJ & nghệ sĩ' ) ),
					'features' => array( array( 'Âm thanh đỉnh cao', 'Hệ thống loa line-array, bass sâu, rõ nét ở mọi vị trí.' ), array( 'Visual LED sống động', 'Màn LED, laser và ánh sáng đồng bộ theo từng drop nhạc.' ), array( 'Bàn VIP riêng tư', 'Bottle service, champagne show và đội ngũ phục vụ riêng.' ) ),
					'final' => array( 'Hẹn bạn tối nay', 'Mở cửa 21:30 – 03:00 mỗi ngày. Giữ bàn trước để có vị trí đẹp nhất.' ),
				),
			),
			'fnb'        => array(
				'label'   => 'Nhà hàng · Cafe · Bánh',
				'colors'  => array( '#fbf6ef', '#c8553d', '#f2a65a', '#588b8b' ),
				'dark'    => false,
				'variant' => '',
				'fx'      => 'mg-fx-grain',
				'motion'  => 'Ấm, mềm: ảnh món ăn zoom chậm (Ken Burns), hiện dần nhẹ, gạch chân chữ.',
				'copy'    => array(
					'eyebrow' => 'Bếp nhà làm · Từ 2015', 'title' => 'Vị ngon kể chuyện', 'sub' => 'Nguyên liệu tươi mỗi sáng, công thức gia truyền và một góc ấm cúng cho cả gia đình.',
					'cta' => 'Đặt bàn', 'cta2' => 'Xem thực đơn', 'marquee' => 'Phở bò|Bún chả|Cà phê muối|Bánh mì|Trà sen',
					'stats' => array( array( '12', 'Năm phục vụ' ), array( '45+', 'Món đặc sản' ), array( '4,9', 'Điểm đánh giá' ) ),
					'features' => array( array( 'Nguyên liệu tươi', 'Nhập mỗi sáng từ chợ đầu mối và trang trại đối tác.' ), array( 'Không gian ấm cúng', 'Phòng riêng cho gia đình, sân vườn cho buổi chiều.' ), array( 'Giao tận nơi', 'Đóng gói giữ nhiệt, giao trong 30 phút nội thành.' ) ),
					'final' => array( 'Ghé quán hôm nay', 'Mở cửa 7:00 – 22:00. Đặt bàn trước để được giữ chỗ đẹp.' ),
				),
			),
			'spa'        => array(
				'label'   => 'Spa · Thẩm mỹ · Nha khoa',
				'colors'  => array( '#fff8f6', '#d4a5a5', '#b89fd6', '#f6d7c3' ),
				'dark'    => false,
				'variant' => 'mg-reveal-blur',
				'fx'      => 'mg-fx-bokeh',
				'motion'  => 'Chậm, thư giãn: hiện dần mờ → rõ, bokeh trôi nhẹ, bo tròn mềm mại.',
				'copy'    => array(
					'eyebrow' => 'Clinic & Spa chuẩn y khoa', 'title' => 'Đẹp tự nhiên, rạng rỡ mỗi ngày', 'sub' => 'Liệu trình cá nhân hoá bởi bác sĩ da liễu, công nghệ Hàn Quốc, không gian thư giãn riêng tư.',
					'cta' => 'Đặt lịch tư vấn', 'cta2' => 'Bảng giá', 'marquee' => 'Chăm sóc da|Trị mụn|Trẻ hoá|Massage body|Gội đầu dưỡng sinh',
					'stats' => array( array( '15.000+', 'Khách hàng hài lòng' ), array( '8', 'Bác sĩ chuyên khoa' ), array( '100%', 'Mỹ phẩm chính hãng' ) ),
					'features' => array( array( 'Bác sĩ thăm khám', 'Soi da, phân tích và lên phác đồ riêng cho từng khách.' ), array( 'Công nghệ hiện đại', 'Máy móc nhập khẩu, được Bộ Y tế cấp phép.' ), array( 'Không gian riêng tư', 'Phòng VIP, trà thảo mộc và nhạc thư giãn.' ) ),
					'final' => array( 'Dành một giờ cho chính mình', 'Ưu đãi 30% cho lần trải nghiệm đầu tiên trong tháng này.' ),
				),
			),
			'realestate' => array(
				'label'   => 'Bất động sản · Khách sạn · Resort',
				'colors'  => array( '#0e1626', '#c9a24b', '#1d2a44', '#e8d9b0' ),
				'dark'    => true,
				'variant' => 'mg-reveal-left',
				'fx'      => 'mg-fx-lines',
				'motion'  => 'Sang trọng, chậm rãi: parallax ảnh lớn, chữ vàng gradient, trượt ngang tinh tế.',
				'copy'    => array(
					'eyebrow' => 'Biệt thự ven sông', 'title' => 'Sống giữa thiên nhiên, cách trung tâm 15 phút', 'sub' => 'Quần thể 120 căn biệt thự với bến du thuyền, sân golf và tiện ích 5 sao.',
					'cta' => 'Nhận bảng giá', 'cta2' => 'Xem phối cảnh', 'marquee' => 'Pháp lý sổ hồng|Bàn giao 2027|Hỗ trợ vay 70%|Chiết khấu 12%',
					'stats' => array( array( '120', 'Căn biệt thự' ), array( '35 ha', 'Quy mô dự án' ), array( '70%', 'Diện tích cây xanh' ) ),
					'features' => array( array( 'Vị trí độc bản', 'Ba mặt giáp sông, kết nối cao tốc và trung tâm.' ), array( 'Tiện ích 5 sao', 'Clubhouse, bến du thuyền, hồ bơi vô cực.' ), array( 'Chủ đầu tư uy tín', 'Hơn 20 năm phát triển các dự án cao cấp.' ) ),
					'final' => array( 'Đặt lịch tham quan nhà mẫu', 'Xe đưa đón tận nơi, tư vấn 1-1 cùng chuyên viên.' ),
				),
			),
			'tech'       => array(
				'label'   => 'Công nghệ · SaaS · Cửa hàng PC',
				'colors'  => array( '#f7f8ff', '#ff8a00', '#5b5cf0', '#14b8a6' ),
				'dark'    => false,
				'variant' => '',
				'fx'      => 'mg-fx-aurora mg-fx-grid',
				'motion'  => 'Hiện đại: nền aurora, lưới phát sáng, thẻ nghiêng theo chuột, đếm số liệu.',
				'copy'    => array(
					'eyebrow' => 'Nền tảng website cho doanh nghiệp', 'title' => 'Ra mắt website trong 7 ngày', 'sub' => 'Thiết kế chuẩn SEO, tối ưu tốc độ, tích hợp bán hàng và báo giá tự động.',
					'cta' => 'Bắt đầu miễn phí', 'cta2' => 'Xem demo', 'marquee' => 'WordPress|WooCommerce|Flatsome|Tốc độ 95+|Bảo mật SSL',
					'stats' => array( array( '1.200+', 'Website đã triển khai' ), array( '95', 'Điểm PageSpeed trung bình' ), array( '24/7', 'Hỗ trợ kỹ thuật' ) ),
					'features' => array( array( 'Tối ưu tốc độ', 'Mã nguồn tinh gọn, ảnh WebP, cache nhiều tầng.' ), array( 'Bán hàng & báo giá', 'Giỏ hàng, build cấu hình, báo giá PDF tự động.' ), array( 'Dễ quản trị', 'Kéo thả UX Builder, đào tạo 1-1 khi bàn giao.' ) ),
					'final' => array( 'Sẵn sàng tăng trưởng?', 'Nhận tư vấn và bản demo miễn phí trong 24 giờ.' ),
				),
			),
			'edu'        => array(
				'label'   => 'Giáo dục · Trung tâm · Khoá học',
				'colors'  => array( '#f5f9ff', '#2f80ed', '#f2c94c', '#27ae60' ),
				'dark'    => false,
				'variant' => 'mg-reveal-zoom',
				'fx'      => 'mg-fx-bokeh',
				'motion'  => 'Vui tươi: bật nảy nhẹ, phóng to khi xuất hiện, số liệu thành tích chạy.',
				'copy'    => array(
					'eyebrow' => 'Tiếng Anh cho trẻ 4–16 tuổi', 'title' => 'Tự tin giao tiếp sau 6 tháng', 'sub' => 'Lớp tối đa 12 học viên, giáo viên bản ngữ, lộ trình theo chuẩn Cambridge.',
					'cta' => 'Học thử miễn phí', 'cta2' => 'Lộ trình học', 'marquee' => 'Starters|Movers|Flyers|IELTS Junior|Public Speaking',
					'stats' => array( array( '8.500+', 'Học viên' ), array( '96%', 'Đạt chứng chỉ' ), array( '12', 'Cơ sở' ) ),
					'features' => array( array( 'Giáo viên bản ngữ', 'Có chứng chỉ TESOL/CELTA, kinh nghiệm dạy trẻ.' ), array( 'Lớp học nhỏ', 'Tối đa 12 bạn, theo sát từng học viên.' ), array( 'Báo cáo hằng tuần', 'Phụ huynh nhận nhận xét và video tiết học.' ) ),
					'final' => array( 'Đăng ký buổi học thử', 'Kiểm tra trình độ miễn phí, tặng bộ học liệu khi nhập học.' ),
				),
			),
			'interior'   => array(
				'label'   => 'Nội thất · Xây dựng · Vật liệu',
				'colors'  => array( '#f6f2ec', '#8a6a4f', '#2f3a36', '#d9cbb6' ),
				'dark'    => false,
				'variant' => 'mg-reveal-wipe',
				'fx'      => '',
				'motion'  => 'Tinh tế, kiến trúc: ảnh hiện bằng hiệu ứng vén màn, đường kẻ, số liệu công trình.',
				'copy'    => array(
					'eyebrow' => 'Thiết kế & thi công trọn gói', 'title' => 'Không gian sống mang dấu ấn riêng', 'sub' => 'Từ bản vẽ 3D đến bàn giao chìa khoá, minh bạch chi phí với báo giá chi tiết từng hạng mục.',
					'cta' => 'Nhận báo giá', 'cta2' => 'Công trình đã làm', 'marquee' => 'Phòng khách|Bếp|Phòng ngủ|Văn phòng|Showroom',
					'stats' => array( array( '650+', 'Công trình bàn giao' ), array( '10', 'Năm bảo hành' ), array( '30', 'Ngày thi công' ) ),
					'features' => array( array( 'Thiết kế 3D miễn phí', 'Xem trước không gian trước khi thi công.' ), array( 'Xưởng sản xuất riêng', 'Chủ động vật liệu, giá tốt, đúng tiến độ.' ), array( 'Báo giá minh bạch', 'Chi tiết từng hạng mục, không phát sinh.' ) ),
					'final' => array( 'Khảo sát tận nơi miễn phí', 'Kiến trúc sư đến đo đạc và tư vấn phong cách phù hợp.' ),
				),
			),
		);
	}

	public static function get() {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), array(
			'preset'       => 'tech',
			'auto'         => 'yes',
			'auto_sel'     => '.section .col-inner > *:not(.mg-no-motion), .section .box, .section .icon-box, .section .featured-box',
			'progress'     => 'no',
			'cursor'       => 'no',
			'header_glass' => 'yes',
			'fab'          => 'yes',
			'fab_side'     => 'right',
			'phone'        => '',
			'zalo'         => '',
			'messenger'    => '',
			'load_where'   => 'all',
			'lite'         => array( 'emoji', 'embeds', 'meta', 'xmlrpc', 'dashicons' ),
		) );
	}

	public static function lite_options() {
		return array(
			'emoji'      => array( 'Tắt emoji script/CSS của WordPress', 'An toàn. Trình duyệt hiện emoji gốc.' ),
			'embeds'     => array( 'Tắt oEmbed (wp-embed.js)', 'An toàn nếu không nhúng bài WordPress khác.' ),
			'meta'       => array( 'Gỡ thẻ thừa trong <head> (RSD, WLW, shortlink, generator, REST link)', 'An toàn.' ),
			'xmlrpc'     => array( 'Tắt XML-RPC', 'An toàn, trừ khi dùng app WordPress di động / Jetpack.' ),
			'dashicons'  => array( 'Không tải Dashicons cho khách chưa đăng nhập', 'An toàn với Flatsome.' ),
			'jqmigrate'  => array( 'Bỏ jQuery Migrate', 'Kiểm tra plugin cũ trước khi bật.' ),
			'blocks'     => array( 'Bỏ CSS Gutenberg ở trang không dùng block', 'Hợp với trang dựng bằng UX Builder.' ),
			'heartbeat'  => array( 'Tắt Heartbeat ngoài trang quản trị', 'Giảm request nền.' ),
			'fragments'  => array( 'Tắt cập nhật giỏ hàng AJAX ở trang không phải cửa hàng', 'Giảm 1 request/trang. Mini-cart cập nhật khi vào trang shop.' ),
			'revisions'  => array( 'Giới hạn 5 bản sửa đổi mỗi bài', 'Giảm dung lượng database.' ),
		);
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
	}

	public static function menu() {
		add_submenu_page( 'migi', 'Hiệu ứng', 'Hiệu ứng (Motion)', 'manage_options', 'mg-motion', array( __CLASS__, 'page' ) );
	}

	/** Class cheat sheet shown in the admin. */
	public static function classes() {
		return array(
			'Xuất hiện khi cuộn' => array( 'mg-reveal' => 'Hiện dần từ dưới lên', 'mg-reveal mg-reveal-left' => 'Trượt từ trái', 'mg-reveal mg-reveal-right' => 'Trượt từ phải', 'mg-reveal mg-reveal-zoom' => 'Phóng to', 'mg-reveal mg-reveal-blur' => 'Mờ → rõ', 'mg-reveal mg-reveal-flip' => 'Lật 3D', 'mg-reveal mg-reveal-wipe' => 'Vén màn (ảnh)', 'mg-stagger' => 'Con xuất hiện lần lượt (đặt cho Row / Col)', 'mg-d1 … mg-d5' => 'Trễ 0,1s → 0,6s', 'mg-no-motion' => 'Bỏ qua hiệu ứng tự động' ),
			'Chữ'                => array( 'mg-split' => 'Tiêu đề bay từng chữ', 'mg-shine' => 'Chữ gradient lấp lánh', 'mg-underline' => 'Gạch chân chạy khi hiện', 'mg-count' => 'Đếm số (99.845+ chạy từ 0)', 'mg-marquee' => 'Chạy chữ ngang (mỗi dòng/đoạn là 1 mục)' ),
			'Ảnh & nền'          => array( 'mg-kenburns' => 'Ảnh nền zoom chậm (Section / Ảnh)', 'mg-parallax' => 'Nền trôi khi cuộn (Section)', 'mg-zoom-img' => 'Phóng ảnh khi rê chuột', 'mg-fx-lasers' => 'Tia laser quét', 'mg-fx-aurora' => 'Nền cực quang', 'mg-fx-bokeh' => 'Đốm sáng trôi', 'mg-fx-grid' => 'Lưới phát sáng', 'mg-fx-grain' => 'Hạt phim', 'mg-fx-lines' => 'Đường kẻ dọc' ),
			'Nút & thẻ'          => array( 'mg-btn-shine' => 'Nút quét sáng', 'mg-glow-btn' => 'Nút phát sáng', 'mg-magnetic' => 'Nút hút theo chuột', 'mg-tilt' => 'Thẻ nghiêng 3D theo chuột', 'mg-spotlight' => 'Đèn rọi theo chuột', 'mg-lift' => 'Nổi lên khi rê chuột', 'mg-float' => 'Lơ lửng', 'mg-pulse' => 'Nhịp đập', 'mg-shake' => 'Rung (icon gọi)' ),
		);
	}

	private static function save( $p ) {
		$s           = self::get();
		$presets     = self::presets();
		$s['preset'] = isset( $presets[ $p['preset'] ?? '' ] ) ? $p['preset'] : 'tech';
		foreach ( array( 'auto', 'progress', 'cursor', 'header_glass', 'fab' ) as $k ) {
			$s[ $k ] = empty( $p[ $k ] ) ? 'no' : 'yes';
		}
		$s['auto_sel']   = sanitize_text_field( $p['auto_sel'] ?? '' );
		$s['fab_side']   = 'left' === ( $p['fab_side'] ?? '' ) ? 'left' : 'right';
		$s['phone']      = preg_replace( '/[^0-9+]/', '', (string) ( $p['phone'] ?? '' ) );
		$s['zalo']       = sanitize_text_field( $p['zalo'] ?? '' );
		$s['messenger']  = sanitize_text_field( $p['messenger'] ?? '' );
		$s['load_where'] = in_array( $p['load_where'] ?? '', array( 'all', 'pages', 'front' ), true ) ? $p['load_where'] : 'all';
		$s['lite']       = array_values( array_intersect( (array) ( $p['lite'] ?? array() ), array_keys( self::lite_options() ) ) );
		update_option( self::OPTION, $s );
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_POST['mgm_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgm_nonce'] ), 'mgm_save' ) ) {
			self::save( wp_unslash( $_POST ) ); // phpcs:ignore -- sanitized in save().
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu.</p></div>';
		}
		$s     = self::get();
		$sw    = function ( $k, $label ) use ( $s ) {
			printf( '<label class="mg-admin-switch" style="display:flex;margin:6px 0"><input type="checkbox" name="%1$s" value="1" %2$s><span></span> %3$s</label>', esc_attr( $k ), checked( 'yes', $s[ $k ], false ), esc_html( $label ) );
		};
		$demo  = get_page_by_path( 'flatsome' );
		?>
		<div class="wrap mg-admin mgm-admin">
			<?php mg_core_admin_header( 'Hiệu ứng theo lĩnh vực', 'Chọn lĩnh vực, sau đó trong <strong>Flatsome UX Builder</strong> chọn phần tử → <em>Advanced → Class</em> và gõ tên class bên dưới (VD: <code>mg-reveal</code>, <code>mg-split</code>).', 'dashicons-art' ); ?>
			<form method="post">
				<?php wp_nonce_field( 'mgm_save', 'mgm_nonce' ); ?>
				<h2>1. Lĩnh vực</h2>
				<div class="mgm-presets">
					<?php foreach ( self::presets() as $k => $p ) : ?>
						<label class="mgm-preset<?php echo $p['dark'] ? ' is-dark' : ''; ?>" style="--bg:<?php echo esc_attr( $p['colors'][0] ); ?>;--a:<?php echo esc_attr( $p['colors'][1] ); ?>;--b:<?php echo esc_attr( $p['colors'][2] ); ?>;--c:<?php echo esc_attr( $p['colors'][3] ); ?>">
							<input type="radio" name="preset" value="<?php echo esc_attr( $k ); ?>" <?php checked( $s['preset'], $k ); ?>>
							<span class="mgm-preset__demo"><b>Aa</b><i></i><i></i><i></i></span>
							<strong><?php echo esc_html( $p['label'] ); ?></strong>
							<small><?php echo esc_html( $p['motion'] ); ?></small>
							<?php if ( $p['fx'] ) : ?><code><?php echo esc_html( $p['fx'] ); ?></code><?php endif; ?>
						</label>
					<?php endforeach; ?>
				</div>

				<h2>2. Tuỳ chọn</h2>
				<table class="form-table">
					<tr><th>Tự động</th><td>
						<?php $sw( 'auto', 'Tự thêm hiệu ứng xuất hiện cho cột, box, icon box của Flatsome (không cần gõ class)' ); ?>
						<input type="text" class="large-text code" name="auto_sel" value="<?php echo esc_attr( $s['auto_sel'] ); ?>">
						<p class="description">Bộ chọn CSS áp dụng tự động. Thêm class <code>mg-no-motion</code> cho phần tử muốn bỏ qua.</p>
					</td></tr>
					<tr><th>Hiệu ứng toàn trang</th><td>
						<?php
						$sw( 'progress', 'Thanh tiến trình đọc ở đầu trang' );
						$sw( 'cursor', 'Quầng sáng theo chuột (đẹp với nền tối)' );
						$sw( 'header_glass', 'Header dính dạng kính mờ khi cuộn (Flatsome sticky header)' );
						?>
					</td></tr>
					<tr><th>Nút liên hệ nổi</th><td>
						<?php $sw( 'fab', 'Hiện nút Gọi / Zalo / Messenger có vòng sóng' ); ?>
						<p><input type="text" name="phone" value="<?php echo esc_attr( $s['phone'] ); ?>" placeholder="Số điện thoại"> <input type="text" name="zalo" value="<?php echo esc_attr( $s['zalo'] ); ?>" placeholder="Số Zalo hoặc link"> <input type="text" name="messenger" value="<?php echo esc_attr( $s['messenger'] ); ?>" placeholder="Username / link m.me">
						<select name="fab_side"><option value="right" <?php selected( $s['fab_side'], 'right' ); ?>>Bên phải</option><option value="left" <?php selected( $s['fab_side'], 'left' ); ?>>Bên trái</option></select></p>
					</td></tr>
					<tr><th>Tải hiệu ứng ở</th><td><select name="load_where">
						<option value="all" <?php selected( $s['load_where'], 'all' ); ?>>Mọi trang</option>
						<option value="pages" <?php selected( $s['load_where'], 'pages' ); ?>>Chỉ Trang (Page) – không tải ở bài viết, sản phẩm</option>
						<option value="front" <?php selected( $s['load_where'], 'front' ); ?>>Chỉ trang chủ &amp; trang landing</option>
					</select></td></tr>
				</table>

				<h2>3. WordPress tối giản (nhẹ hơn)</h2>
				<div class="mg-admin-card mgm-lite">
					<?php foreach ( self::lite_options() as $k => $o ) : ?>
						<label class="mg-admin-switch" style="display:flex;margin:8px 0"><input type="checkbox" name="lite[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $s['lite'], true ) ); ?>><span></span> <span><strong><?php echo esc_html( $o[0] ); ?></strong><br><small class="description"><?php echo esc_html( $o[1] ); ?></small></span></label>
					<?php endforeach; ?>
					<p class="description">Flatsome: nên bật thêm ở <em>Theme Options → Advanced → Performance</em> (lazy load, tắt font không dùng). Mỗi tuỳ chọn ở đây có thể tắt lại bất cứ lúc nào.</p>
				</div>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>

			<div class="mg-admin-card">
				<h2>Trang demo /flatsome</h2>
				<p>Tạo trang landing mẫu theo lĩnh vực đang chọn (hero chữ bay, chạy chữ, đếm số, thẻ nghiêng, CTA) – dựng bằng shortcode Flatsome nên sửa tiếp được trong UX Builder.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="mgm_demo"><?php wp_nonce_field( 'mgm_demo' ); ?>
					<?php if ( $demo ) : ?>
						<p><a class="button" target="_blank" href="<?php echo esc_url( get_permalink( $demo ) ); ?>">Xem /flatsome</a> <a class="button" href="<?php echo esc_url( get_edit_post_link( $demo ) ); ?>">Sửa</a></p>
						<p><label><input type="checkbox" name="overwrite" value="1"> Ghi đè nội dung trang hiện có</label></p>
					<?php endif; ?>
					<button class="mg-admin-btn" type="submit"><?php echo $demo ? 'Tạo lại trang demo' : 'Tạo trang demo /flatsome'; ?></button>
				</form>
			</div>

			<h2>Bảng class</h2>
			<div class="mgm-classes">
				<?php foreach ( self::classes() as $group => $list ) : ?>
					<div class="mg-admin-card"><h3><?php echo esc_html( $group ); ?></h3><table class="widefat striped"><tbody>
						<?php foreach ( $list as $cls => $desc ) : ?>
							<tr><td><code class="mgm-copy" title="Bấm để sao chép"><?php echo esc_html( $cls ); ?></code></td><td><?php echo esc_html( $desc ); ?></td></tr>
						<?php endforeach; ?>
					</tbody></table></div>
				<?php endforeach; ?>
			</div>
			<p class="description">Đếm số: gõ con số vào Text (VD <code>99.845+</code>) rồi thêm class <code>mg-count</code>. Shortcode: <code>[mg_marquee items="EDM|Vinahouse|Live DJ" speed="30"]</code>, <code>[mg_counter to="997" suffix="+" label="Đêm show"]</code>.</p>
		</div>
		<style>
			.mgm-presets{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin:10px 0 24px}
			.mgm-preset{position:relative;display:flex;flex-direction:column;gap:6px;padding:16px;border:2px solid #e4e7ec;border-radius:18px;background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s}
			.mgm-preset:has(input:checked){border-color:#ff8a00;box-shadow:0 0 0 4px rgba(255,138,0,.18)}
			.mgm-preset input{position:absolute;opacity:0}
			.mgm-preset__demo{display:flex;align-items:center;gap:6px;height:64px;padding:0 14px;border-radius:12px;background:var(--bg);margin-bottom:4px}
			.mgm-preset__demo b{font-size:26px;background:linear-gradient(90deg,var(--a),var(--b),var(--c));-webkit-background-clip:text;background-clip:text;color:transparent;margin-right:auto}
			.mgm-preset__demo i{width:16px;height:16px;border-radius:50%;background:var(--a)}.mgm-preset__demo i+i{background:var(--b)}.mgm-preset__demo i+i+i{background:var(--c)}
			.mgm-preset.is-dark .mgm-preset__demo{box-shadow:inset 0 0 0 1px rgba(255,255,255,.08)}
			.mgm-preset small{color:#667085}.mgm-preset code{font-size:11px;align-self:flex-start}
			.mgm-classes{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:14px}
			.mgm-copy{cursor:copy}.mgm-copy.is-copied{background:#e6f6f4;color:#0b7a70}
		</style>
		<script>
			document.addEventListener('click', function (e) {
				var c = e.target.closest('.mgm-copy'); if (!c || !navigator.clipboard) { return; }
				navigator.clipboard.writeText(c.textContent.replace(' … ', ' ')); c.classList.add('is-copied'); setTimeout(function () { c.classList.remove('is-copied'); }, 900);
			});
		</script>
		<?php
	}
}
