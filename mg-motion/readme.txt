=== Migi Motion – Hiệu ứng landing theo lĩnh vực ===
Stable tag: 1.1.0

Bộ hiệu ứng siêu nhẹ cho Flatsome (và mọi theme): JS ~8KB không jQuery, CSS ~14KB, tải "defer".
Học từ landing kiểu A PLUS CLUB: hero chữ bay, Ken Burns, laser, chữ gradient, đếm số, chạy chữ, nút gọi nổi.

CÁCH DÙNG TRONG FLATSOME UX BUILDER: chọn phần tử (Section, Row, Col, Text, Button, Image…) → Advanced → Class → gõ:
  mg-reveal (+ mg-reveal-left | -right | -zoom | -blur | -flip | -wipe), mg-d1…mg-d5 (trễ), mg-stagger (con lần lượt)
  mg-split (tiêu đề bay từng chữ), mg-shine (chữ gradient), mg-underline, mg-count (đếm số "99.845+"), mg-marquee
  mg-kenburns, mg-parallax, mg-zoom-img, mg-fx-lasers | aurora | bokeh | grid | grain | lines (lớp trang trí cho Section)
  mg-btn-shine, mg-glow-btn, mg-magnetic, mg-tilt, mg-spotlight, mg-lift, mg-float, mg-pulse, mg-shake
Chế độ Tự động: tự thêm hiệu ứng xuất hiện cho cột/box/icon box (không cần gõ class); mg-no-motion để bỏ qua.
Shortcode: [mg_marquee items="A|B|C" speed="30"]  [mg_counter to="997" suffix="+" label="Đêm show"]

7 bộ lĩnh vực (màu + tốc độ + kiểu xuất hiện + lớp trang trí gợi ý): Bar/Club/Karaoke, Nhà hàng/Cafe, Spa/Thẩm mỹ,
BĐS/Khách sạn, Công nghệ/SaaS/PC, Giáo dục, Nội thất/Xây dựng.
Toàn trang: thanh tiến trình đọc, quầng sáng theo chuột, header kính mờ khi cuộn (Flatsome sticky), nút Gọi/Zalo/Messenger.
Trang demo: Migi → Hiệu ứng → "Tạo trang demo /flatsome" (dựng bằng shortcode Flatsome, sửa tiếp trong UX Builder;
theme khác: khối HTML).

WordPress tối giản (bật từng mục): tắt emoji, oEmbed, thẻ thừa <head>, XML-RPC, Dashicons cho khách, jQuery Migrate,
CSS Gutenberg ở trang UX Builder, Heartbeat, cart fragments ngoài trang shop, giới hạn revision.

An toàn: tôn trọng "giảm chuyển động" của hệ điều hành; nội dung chỉ ẩn khi JS chạy được, tự hiện lại sau 3s nếu
JS lỗi; hiệu ứng chuột chỉ bật trên máy có chuột; không gây tràn ngang trên điện thoại.

1.1 – Kho giao diện (Migi → Kho giao diện): tải kit .zip từ site.migi.vn, cài kit (nhập ảnh, tạo UX Block, tạo trang,
áp màu/font/footer qua Customizer, đổi bộ hiệu ứng, đặt trang chủ), khôi phục Customizer, trang của kit hiện trong
UX Builder → Templates ("Migi · <lĩnh vực>"). Kèm kit mẫu "Nội thất Luxe".
1.1 – 7 landing theo lĩnh vực có trong UX Builder → Templates ("Migi · Landing"); [mg_marquee], [mg_counter] là phần
tử kéo thả trong nhóm "Migi" của UX Builder.
