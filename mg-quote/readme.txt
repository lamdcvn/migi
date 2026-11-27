=== Migi Quote – Báo giá & PDF ===
Requires Plugins: woocommerce
Stable tag: 1.0.0

Hai hình thức báo giá:
1. Báo giá chuẩn (giá web) – khách tự bấm "Tải báo giá PDF" trong Build PC / Tính vật liệu (hoặc admin tạo).
   Giá luôn lấy lại từ giá bán hiện tại trên server, khách không sửa được.
2. Báo giá cạnh tranh (giá tuỳ chỉnh) – CHỈ quản lý cửa hàng (quyền manage_woocommerce) mới tạo/sửa giá:
   Migi → Báo giá → Tạo báo giá, hoặc nút "Tạo báo giá cạnh tranh" trên yêu cầu báo giá vật liệu,
   yêu cầu tư vấn Build PC, khách điện mặt trời (tự chép khách + sản phẩm).
   Hiện giá niêm yết gạch ngang + giá ưu đãi + "Quý khách tiết kiệm …".

Mẫu PDF A4: logo, tên công ty, địa chỉ, hotline, email, website, MST; số báo giá (BG-2611-0001), ngày lập,
hiệu lực; thông tin khách; bảng STT/Sản phẩm/ĐVT/SL/Đơn giá/Thành tiền; chiết khấu, vận chuyển, VAT
(đã gồm hoặc cộng thêm), tổng cộng, số tiền bằng chữ; điều khoản; tài khoản ngân hàng; chữ ký hai bên.
PDF tạo bằng dompdf đóng gói sẵn (font DejaVu hỗ trợ tiếng Việt, không cần Composer). Nếu hosting không tạo
được PDF, link tự chuyển sang bản in A4 và mở hộp thoại "Lưu PDF" của trình duyệt.

Gửi khách: link xem riêng (?mg-quote=mã bí mật, không index) có nút Tải PDF / In / Đồng ý báo giá;
"Lưu & gửi email kèm PDF"; trạng thái Nháp → Đã gửi → Khách đã xem → Khách đồng ý → Đã tạo đơn;
"Lưu & tạo đơn hàng từ báo giá" (đơn WooCommerce chờ thanh toán với đúng giá đã báo); lịch sử thao tác.

Cài đặt: Migi → Báo giá – Cài đặt (logo, thông tin công ty, ngân hàng, người ký, tiền tố số, hiệu lực, VAT,
điều khoản, mẫu email).
Kiểm thử: php tests/totals.php
