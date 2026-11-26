# Migi – bộ plugin WordPress / WooCommerce

Tất cả plugin dùng tiền tố `mg-`, chung menu quản trị **Migi** (file dùng chung `includes/mg-core.php`, giống hệt nhau trong mọi plugin).
Bản cài đặt: thư mục `dist/` (Plugins → Add New → Upload Plugin).

| Plugin | Chức năng | Shortcode |
|---|---|---|
| `mg-bulk-deals` | Deal mua nhiều giảm giá theo số lượng, badge "Phổ biến nhất / Giá tốt nhất", áp giá vào giỏ | – |
| `mg-solar-calc` | Tính hệ thống điện mặt trời theo hoá đơn EVN, lead, email, webhook, CSV | `[mg_solar_calc]` |
| `mg-material-calc` | Tính gạch ốp lát, giấy dán tường, tấm ốp, sàn gỗ, sơn theo nhiều phòng + dự phòng, tự điền số lượng vào giỏ | `[mg_material_calc id="123"]` |

Nâng cấp từ bản cũ `vt-*`: kích hoạt bản `mg-*` sẽ tự tắt bản cũ và chuyển cài đặt, dữ liệu sản phẩm, lead sang tên mới.
Shortcode cũ `[vt_solar_calc]` vẫn hoạt động.

## Kiểm thử

```bash
node mg-material-calc/tests/engine-parity.js   # engine PHP và JS cho kết quả giống hệt nhau (3000 ca ngẫu nhiên)
php  mg-material-calc/tests/sanity.php         # các ví dụ tính tay
```

## Lộ trình module đề xuất cho mg-material-calc

| Module | Mô tả | Ưu tiên |
|---|---|---|
| Báo giá & lead | Nút "Nhận báo giá" lưu bảng tính + thông tin khách (dùng lại cơ chế lead của mg-solar-calc), gửi email/Zalo, xuất PDF có logo | Cao |
| Phối nhiều sản phẩm | Một bảng phòng dùng chung cho nhiều sản phẩm: gạch sàn + gạch ốp tường + gạch viền + keo/ron, thêm tất cả vào giỏ một lần | Cao |
| Vật tư đi kèm | Tự gợi ý keo dán gạch (kg/m²), ron/keo chít mạch, nẹp góc, keo dán giấy, lót sàn... theo diện tích | Cao |
| Phòng hình chữ L / nhiều hình | Thêm hình chữ nhật cộng/trừ, nhập diện tích trực tiếp, bản vẽ mặt bằng đơn giản | Trung bình |
| Gạch viền & len chân tường | Tính theo chu vi (m dài) trừ cửa | Trung bình |
| Lưu & chia sẻ bảng tính | Link chia sẻ / lưu vào tài khoản khách để mua lại khi thiếu (cùng lô hàng) | Trung bình |
| Tồn kho theo lô | Cảnh báo khi số thùng cần > tồn cùng lô (màu dễ lệch giữa các lô) | Trung bình |
| Công thi công | Thêm đơn giá nhân công/m² theo khu vực, tổng chi phí trọn gói | Thấp |
| Shortcode chọn sản phẩm | Trang "Tính vật liệu" cho khách chọn danh mục → sản phẩm → tính | Thấp |
| Flatsome UX Builder element | Kéo thả công cụ trong UX Builder thay vì shortcode | Thấp |
