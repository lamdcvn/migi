# Section patterns (native UX Builder)

Copy a pattern, replace texts/IDs, then lint (`scripts/fs_lint.py`). Every block below passes the linter.
Image IDs (`bg="101"`, `id="102"`…) are examples: replace them with real attachment IDs from the target site.
`class="mg-…"` values are optional Migi Motion effects (see `migi.md`); remove them on sites without mg-motion.

Design rules that keep templates distinct and usable:
- One H1 per page (hero). Section titles H2, card titles H3.
- Vertical rhythm: section `padding` 80–110px desktop, `padding__sm` 50–60px.
- Max ~65 characters per line of body text: use `col span="8"` or `max_width` on wide copy.
- Every section gets `label="…"` so editors find it in the tree.
- Vary the hero family and section order between kits; never reuse a kit's exact order.

## 1. Hero – full-width banner with text box

```
[section label="Hero" padding="0px"]
[ux_banner height="640px" height__sm="520px" bg="101" bg_overlay="rgba(0,0,0,.45)" bg_pos="50% 40%" class="mg-kenburns"]
[text_box width="62" width__sm="92" position_x="10" position_x__sm="50" position_y="50" text_align="left"]
[ux_text font_size="0.9" text_color="rgba(255,255,255,.8)"]<p class="uppercase"><strong>Showroom Hà Nội · Từ 2012</strong></p>[/ux_text]
[ux_text font_size="3" font_size__sm="1.9" line_height="1.1" class="mg-split"]<h1><strong>Không gian sống mang dấu ấn riêng</strong></h1>[/ux_text]
[ux_text font_size="1.15" font_size__sm="1"]<p>Thiết kế, thi công trọn gói. Báo giá minh bạch từng hạng mục.</p>[/ux_text]
[button text="Nhận báo giá" color="secondary" radius="99" size="large" class="mg-btn-shine" link="#lien-he"]
[button text="Xem công trình" style="outline" color="white" radius="99" size="large" link="#cong-trinh"]
[/text_box]
[/ux_banner]
[/section]
```

## 2. Hero – split (copy left, image right)

```
[section label="Hero split" padding="90px" padding__sm="50px"]
[row v_align="middle" h_align="center"]
[col span="6" span__sm="12" padding__sm="0px 0px 30px 0px"]
[ux_text font_size="2.6" font_size__sm="1.8" line_height="1.1"]<h1><strong>Ra mắt website trong 7 ngày</strong></h1>[/ux_text]
[ux_text font_size="1.1"]<p>Chuẩn SEO, tốc độ cao, tích hợp bán hàng và báo giá tự động.</p>[/ux_text]
[button text="Bắt đầu" color="primary" radius="99"]
[button text="Xem demo" style="link" color="primary"]
[/col]
[col span="6" span__sm="12" force_first="small"]
[ux_image id="102" image_size="large" depth="3" class="mg-reveal mg-reveal-zoom"]
[/col]
[/row]
[/section]
```

## 3. Trust strip – logos or running text

```
[section label="Logos" bg_color="#f7f7fa" padding="30px"]
[row style="small" h_align="center" v_align="middle"]
[col span="2" span__sm="4"][ux_image id="111" width="70"][/col]
[col span="2" span__sm="4"][ux_image id="112" width="70"][/col]
[col span="2" span__sm="4"][ux_image id="113" width="70"][/col]
[col span="2" span__sm="4"][ux_image id="114" width="70"][/col]
[col span="2" span__sm="4"][ux_image id="115" width="70"][/col]
[col span="2" span__sm="4"][ux_image id="116" width="70"][/col]
[/row]
[/section]
```

Running text alternative (Migi Motion): `[mg_marquee items="Bảo hành 10 năm|Miễn phí khảo sát|Trả góp 0%" speed="28"]` inside a full-width column.

## 4. Features – icon boxes

```
[section label="Lợi ích" padding="90px" padding__sm="55px"]
[row h_align="center"]
[col span="8" span__sm="12" align="center"]
[title style="center" text="Vì sao chọn chúng tôi" tag_name="h2"]
[/col]
[/row]
[row class="mg-stagger"]
[col span="4" span__sm="12"]
[featured_box img="121" img_width="56" pos="left" title="Thiết kế 3D miễn phí"]
<p>Xem trước không gian trước khi thi công.</p>
[/featured_box]
[/col]
[col span="4" span__sm="12"]
[featured_box img="122" img_width="56" pos="left" title="Xưởng sản xuất riêng"]
<p>Chủ động vật liệu, đúng tiến độ.</p>
[/featured_box]
[/col]
[col span="4" span__sm="12"]
[featured_box img="123" img_width="56" pos="left" title="Báo giá minh bạch"]
<p>Chi tiết từng hạng mục, không phát sinh.</p>
[/featured_box]
[/col]
[/row]
[/section]
```

## 5. Stats

```
[section label="Số liệu" bg_color="#101828" dark="true" padding="70px"]
[row h_align="center"]
[col span="3" span__sm="6" align="center"]
[ux_text font_size="2.6" font_size__sm="2" class="mg-count"]<h2><strong>650+</strong></h2>[/ux_text]
<p>Công trình</p>
[/col]
[col span="3" span__sm="6" align="center"]
[ux_text font_size="2.6" font_size__sm="2" class="mg-count"]<h2><strong>10</strong></h2>[/ux_text]
<p>Năm bảo hành</p>
[/col]
[col span="3" span__sm="6" align="center"]
[ux_text font_size="2.6" font_size__sm="2" class="mg-count"]<h2><strong>4,9</strong></h2>[/ux_text]
<p>Điểm đánh giá</p>
[/col]
[col span="3" span__sm="6" align="center"]
[ux_text font_size="2.6" font_size__sm="2" class="mg-count"]<h2><strong>30</strong></h2>[/ux_text]
<p>Ngày thi công</p>
[/col]
[/row]
[/section]
```

## 6. Image + text alternating (zig-zag)

Use `force_first="small"` on the image column of the *second* row so images stay above text on mobile.

```
[section label="Quy trình" padding="90px" padding__sm="50px"]
[row v_align="middle"]
[col span="6" span__sm="12"][ux_image id="131" image_size="large"][/col]
[col span="6" span__sm="12"]
[ux_text]<h3>1. Khảo sát &amp; tư vấn</h3><p>Kiến trúc sư đo đạc tận nơi, lắng nghe nhu cầu.</p>[/ux_text]
[/col]
[/row]
[row v_align="middle"]
[col span="6" span__sm="12"]
[ux_text]<h3>2. Thiết kế 3D</h3><p>Duyệt phương án, vật liệu và chi phí trước khi làm.</p>[/ux_text]
[/col]
[col span="6" span__sm="12" force_first="small"][ux_image id="132" image_size="large"][/col]
[/row]
[/section]
```

## 7. Products by category (tabs)

```
[section label="Sản phẩm" padding="80px" padding__sm="50px"]
[row]
[col span="12"]
[title style="center" text="Sản phẩm nổi bật" tag_name="h2"]
[tabgroup style="line-bottom" align="center" nav_size="large"]
[tab title="Gạch lát nền"][ux_products style="normal" columns="4" columns__md="3" columns__sm="2" products="8" cat="gach-lat-nen"][/tab]
[tab title="Gạch ốp tường"][ux_products style="normal" columns="4" columns__md="3" columns__sm="2" products="8" cat="gach-op-tuong"][/tab]
[/tabgroup]
[/col]
[/row]
[/section]
```

## 8. Testimonials

```
[section label="Khách hàng nói gì" bg_color="#f7f7fa" padding="90px" padding__sm="50px"]
[row]
[col span="4" span__sm="12"]
[testimonial image="141" image_width="64" pos="left" name="Anh Nam" company="Vinhomes Riverside" stars="5"]
<p>Thi công đúng tiến độ, báo giá rõ ràng, không phát sinh.</p>
[/testimonial]
[/col]
[col span="4" span__sm="12"]
[testimonial image="142" image_width="64" pos="left" name="Chị Lan" company="Cầu Giấy" stars="5"]
<p>Kiến trúc sư tư vấn rất kỹ, nhà đẹp hơn mong đợi.</p>
[/testimonial]
[/col]
[col span="4" span__sm="12"]
[testimonial image="143" image_width="64" pos="left" name="Anh Hùng" company="Long Biên" stars="5"]
<p>Bảo hành nhanh, đội ngũ nhiệt tình.</p>
[/testimonial]
[/col]
[/row]
[/section]
```

## 9. Pricing

```
[section label="Bảng giá" padding="90px" padding__sm="50px"]
[row h_align="center"]
[col span="4" span__sm="12"]
[ux_price_table title="Cơ bản" price="990.000đ" description="/ tháng"]
[bullet_item text="Website 5 trang"]
[bullet_item text="Tên miền .vn"]
[button text="Chọn gói" style="outline" expand="true"]
[/ux_price_table]
[/col]
[col span="4" span__sm="12"]
[ux_price_table title="Doanh nghiệp" price="2.490.000đ" description="/ tháng" featured="true"]
[bullet_item text="Bán hàng + báo giá PDF"]
[bullet_item text="Hỗ trợ 24/7"]
[button text="Chọn gói" expand="true"]
[/ux_price_table]
[/col]
[/row]
[/section]
```

## 10. FAQ

```
[section label="Câu hỏi thường gặp" padding="80px" padding__sm="50px"]
[row h_align="center"]
[col span="8" span__sm="12"]
[title style="center" text="Câu hỏi thường gặp" tag_name="h2"]
[accordion auto_open="true"]
[accordion-item title="Bao lâu thì nhận được báo giá?"]<p>Trong 24 giờ sau khi khảo sát.</p>[/accordion-item]
[accordion-item title="Có hỗ trợ trả góp không?"]<p>Có, trả góp 0% qua thẻ tín dụng.</p>[/accordion-item]
[/accordion]
[/col]
[/row]
[/section]
```

## 11. Blog

```
[section label="Tin tức" padding="80px" padding__sm="50px"]
[row]
[col span="12"]
[title style="bold-center" text="Cẩm nang" tag_name="h2" link_text="Xem tất cả" link="/blog/"]
[blog_posts style="normal" columns="3" columns__md="2" columns__sm="1" posts="3" image_height="56.25%"]
[/col]
[/row]
[/section]
```

## 12. Contact + map

```
[section label="Liên hệ" padding="0px"]
[row style="collapse" width="full-width" v_align="equal"]
[col span="6" span__sm="12" padding="70px 50px 70px 50px" padding__sm="40px 20px 40px 20px"]
[ux_text]<h2 id="lien-he">Đặt lịch khảo sát</h2><p>Hotline 0901 234 567 · 8:00–20:00</p>[/ux_text]
[contact-form-7 id="534"]
[/col]
[col span="6" span__sm="12"]
[map height="520px" height__sm="320px" lat="21.0285" long="105.8542" zoom="15" content_enable="false"]
[/map]
[/col]
[/row]
[/section]
```

## 13. CTA band

```
[section label="CTA" bg_color="#ff8a00" dark="true" padding="70px" padding__sm="50px" class="mg-fx-aurora"]
[row v_align="middle" h_align="center"]
[col span="8" span__sm="12"]
[ux_text font_size="1.6" font_size__sm="1.3"]<h2><strong>Khảo sát tận nơi miễn phí</strong></h2><p>Kiến trúc sư đến đo đạc và tư vấn phong cách phù hợp.</p>[/ux_text]
[/col]
[col span="4" span__sm="12" align="right"]
[button text="Đặt lịch ngay" color="white" radius="99" size="large" class="mg-btn-shine"]
[/col]
[/row]
[/section]
```

## 14. Footer as a UX Block

Build the footer in **UX Blocks** (post type `blocks`, slug `footer-migi`), then select it in Customizer → Footer (`footer_block`). Content example:

```
[section label="Footer" bg_color="#0e1322" dark="true" padding="60px" padding__sm="40px"]
[row]
[col span="4" span__sm="12"][ux_image id="151" width="40"]<p>Công ty TNHH ABC · MST 0101234567</p>[/col]
[col span="4" span__sm="6"][ux_text]<h4>Liên hệ</h4><p>0901 234 567<br>hello@abc.vn</p>[/ux_text][/col]
[col span="4" span__sm="6"][follow style="small" facebook="https://facebook.com/abc" youtube="https://youtube.com/@abc" phone="0901234567"][/col]
[/row]
[/section]
```
