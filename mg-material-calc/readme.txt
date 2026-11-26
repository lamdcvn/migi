=== Migi Material Calculator – Tính vật liệu ốp lát, dán tường ===
Requires Plugins: woocommerce
Stable tag: 1.1.0

Khách nhập dài × rộng (× cao) cho nhiều phòng khác nhau, plugin tính:
diện tích thi công → vật liệu cần dùng → dự phòng → số thùng/cuộn/tấm/hộp/lon nên mua → thành tiền, giá/m².
Số lượng tự điền vào ô "Số lượng", chi tiết từng phòng được lưu vào giỏ hàng và đơn hàng.

Loại vật liệu (chọn cho từng sản phẩm, tab "Tính vật liệu"):
- Gạch ốp lát: kích thước viên (cm), ron (mm), số viên/thùng; đếm theo viên thực tế (tính viên cắt) hoặc theo diện tích + hao hụt; lát thẳng / chéo 45° / xương cá.
- Giấy dán tường: khổ & chiều dài cuộn, bước lặp hoa văn; tính số dải theo chiều cao tường, số dải/cuộn.
- Tấm ốp (nano, PVC, lam sóng, gỗ nhựa...): rộng × dài tấm, số tấm/đơn vị bán.
- Vật liệu bán theo m² (sàn gỗ, vinyl, thảm, decal...): m²/hộp.
- Sơn: độ phủ m²/lít/lớp, số lớp, dung tích thùng.
- Vật tư theo m² (keo dán gạch, keo chít mạch, keo dán giấy, lót sàn...): mức tiêu hao/m², quy cách bao/thùng.
- Vật tư theo mét dài (len chân tường, nẹp, phào trần): chiều dài thanh, tính theo chu vi.
Bề mặt: sàn, tường (trừ diện tích cửa), trần.

1.1 – Phối kèm: chọn "Sản phẩm phối kèm & vật tư" ở sản phẩm chính (VD gạch sàn → gạch ốp tường WC, keo dán gạch,
len chân tường). Khách tick bề mặt từng phòng, mỗi sản phẩm tự tính theo bề mặt của nó; tick chọn và thêm tất cả vào giỏ
cùng lúc khi bấm "Thêm vào giỏ" (server tính lại số lượng).
1.1 – Báo giá: nút "Nhận báo giá" (họ tên, SĐT, email, địa chỉ công trình, ghi chú) → lưu ở Migi → Báo giá vật liệu,
email cho shop + khách, webhook JSON, trạng thái chăm sóc, ghi chú nội bộ, xuất CSV. Nút "In bảng tính".
1.1 – Giao diện theo tone Migi, phòng mẫu nhập nhanh, chọn dự phòng dạng chip, hỗ trợ chế độ tối.

Tích hợp Migi Bulk Deals: nếu sản phẩm có deal mua nhiều, thành tiền hiển thị giá sau deal.
Shortcode: [mg_material_calc id="123"] – đặt công cụ + nút mua ở bài viết/landing page.
Cài đặt chung: Migi → Tính vật liệu.

Kiểm thử: node tests/engine-parity.js (so khớp engine PHP và JS), php tests/sanity.php (các ví dụ tính tay).
