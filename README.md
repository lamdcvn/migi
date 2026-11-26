# Migi – bộ plugin WordPress / WooCommerce

Tất cả plugin dùng tiền tố `mg-`, chung menu quản trị **Migi** và chung bộ giao diện theo tone migi.vn (Manrope, cam gradient, thẻ kính mờ, nền pastel, hỗ trợ `[data-theme="dark"]`).

File dùng chung – giống hệt nhau trong mọi plugin, sửa ở `mg-bulk-deals` rồi copy sang các plugin khác:

- `includes/mg-core.php` – menu Migi, trang tổng quan, cài đặt giao diện chung (màu nhấn, font, hiệu ứng kính)
- `assets/css/mg-ui.css` – token & component phía website (`.mg-ui`, `.mg-card`, `.mg-btn`, `.mg-seg`, `.mg-input`, `.mg-stat`...)
- `assets/css/mg-admin.css` – giao diện trang quản trị Migi
Bản cài đặt: thư mục `dist/` (Plugins → Add New → Upload Plugin).

| Plugin | Chức năng | Shortcode |
|---|---|---|
| `mg-bulk-deals` | Deal mua nhiều giảm giá theo số lượng, badge "Phổ biến nhất / Giá tốt nhất", áp giá vào giỏ | – |
| `mg-solar-calc` | Tính hệ thống điện mặt trời theo hoá đơn EVN, lead, email, webhook, CSV | `[mg_solar_calc]` |
| `mg-material-calc` | Tính gạch, giấy dán tường, tấm ốp, sàn gỗ, sơn, vật tư (keo, len chân tường...) theo nhiều phòng + dự phòng; phối nhiều sản phẩm vào giỏ cùng lúc; báo giá & lead | `[mg_material_calc id="123"]` |
| `mg-pc-builder` | Build cấu hình PC: chọn linh kiện theo nhóm, bộ lọc, kiểm tra tương thích (socket, RAM, mainboard–case, VGA, nguồn), cấu hình gợi ý, chia sẻ / in / CSV, thêm cả bộ vào giỏ, ưu đãi Build PC, nhận tư vấn | `[mg_pc_builder]` |

Nâng cấp từ bản cũ `vt-*`: kích hoạt bản `mg-*` sẽ tự tắt bản cũ và chuyển cài đặt, dữ liệu sản phẩm, lead sang tên mới.
Shortcode cũ `[vt_solar_calc]` vẫn hoạt động.

## Kiểm thử

```bash
node mg-material-calc/tests/engine-parity.js   # engine PHP và JS cho kết quả giống hệt nhau (3000 ca ngẫu nhiên)
php  mg-material-calc/tests/sanity.php         # các ví dụ tính tay
php  mg-pc-builder/tests/compat.php            # kiểm tra tương thích linh kiện PC
```

## Lộ trình module đề xuất cho mg-material-calc

| Module | Mô tả | Ưu tiên |
|---|---|---|
| ✅ Báo giá & lead (1.1) | Nút "Nhận báo giá": lưu bảng tính + thông tin khách, email shop & khách, webhook, quản lý trạng thái, CSV | Xong |
| ✅ Phối nhiều sản phẩm (1.1) | Một bảng phòng dùng chung cho sản phẩm chính + sản phẩm phối kèm theo bề mặt, thêm tất cả vào giỏ một lần | Xong |
| ✅ Vật tư đi kèm (1.1) | Loại "vật tư theo m²" (keo dán gạch, keo chít mạch, keo dán giấy...) và "theo mét dài" (len chân tường, nẹp, phào) | Xong |
| Xuất PDF có logo | Báo giá PDF tải về / đính kèm email (hiện dùng In / Lưu PDF của trình duyệt) | Cao |
| Phòng hình chữ L / nhiều hình | Thêm hình chữ nhật cộng/trừ, nhập diện tích trực tiếp, bản vẽ mặt bằng đơn giản | Trung bình |
| Gạch viền & len chân tường | Tính theo chu vi (m dài) trừ cửa | Trung bình |
| Lưu & chia sẻ bảng tính | Link chia sẻ / lưu vào tài khoản khách để mua lại khi thiếu (cùng lô hàng) | Trung bình |
| Tồn kho theo lô | Cảnh báo khi số thùng cần > tồn cùng lô (màu dễ lệch giữa các lô) | Trung bình |
| Công thi công | Thêm đơn giá nhân công/m² theo khu vực, tổng chi phí trọn gói | Thấp |
| Shortcode chọn sản phẩm | Trang "Tính vật liệu" cho khách chọn danh mục → sản phẩm → tính | Thấp |
| Flatsome UX Builder element | Kéo thả công cụ trong UX Builder thay vì shortcode | Thấp |
