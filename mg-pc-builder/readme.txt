=== Migi PC Builder – Build cấu hình máy tính ===
Requires Plugins: woocommerce
Stable tag: 1.0.0

Shortcode: [mg_pc_builder]   (tuỳ chọn: title="..." presets="no")

Khách tự build cấu hình PC theo từng nhóm linh kiện (tham khảo luồng Build PC của các shop lớn, giao diện Migi):
- Cấu hình gợi ý theo nhu cầu (Gaming / Đồ hoạ / Văn phòng / AI...): Migi → Build PC – Cấu hình gợi ý.
- 15 nhóm mặc định (CPU, Mainboard, VGA, RAM, SSD, HDD, Case, Nguồn, Tản nhiệt, Quạt, Màn hình, Chuột, Bàn phím,
  Tai nghe, Phần mềm) – sửa / thêm / kéo sắp xếp, gán danh mục sản phẩm, bắt buộc, cho chọn số lượng.
- Ngăn kéo chọn linh kiện: tìm kiếm (không dấu), bộ lọc tự sinh từ thương hiệu & thuộc tính, khoảng giá,
  sắp xếp, "chỉ hiện linh kiện tương thích", tải thêm, thông số nổi bật, giá giảm, hết hàng.
- Kiểm tra tương thích: socket CPU–mainboard–tản nhiệt, loại RAM, số khe RAM, chuẩn mainboard–case,
  độ dài VGA–case, công suất nguồn (ước tính điện năng + dự phòng), CPU không có iGPU.
- Tạm tính dính bên phải (thanh dưới trên điện thoại), ưu đãi Build PC (% khi đủ số nhóm, áp vào giỏ dạng phí âm).
- Chọn lại, Xem & in, Chia sẻ link (?build=...), Tải file CSV, lưu tự động trên trình duyệt.
- Thêm cả cấu hình vào giỏ / Mua ngay (mỗi dòng giỏ có mã cấu hình PCxxxxxx).
- Nhận tư vấn cấu hình: lưu ở Migi → Build PC – Yêu cầu tư vấn, email, webhook.

Thông số tương thích: tab "Build PC" ở trang sửa sản phẩm, hoặc tự đọc từ thuộc tính WooCommerce
(cấu hình slug ở Migi → Build PC – Cài đặt → Tương thích). Tự chuẩn hoá: "LGA 1700" = "LGA1700", "Micro-ATX" = "mATX".

Kiểm thử: php tests/compat.php
