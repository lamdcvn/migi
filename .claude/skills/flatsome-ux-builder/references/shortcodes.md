# Flatsome UX Builder shortcode reference

Generated from Flatsome 3.15.x element definitions (parameter names, types, defaults, allowed values).
Use with `scripts/fs_lint.py`, which validates against the same data (`data/flatsome-elements.json`).

Legend: **C** = container (needs a closing tag) · `↕` = has `__md` (tablet) and `__sm` (mobile) variants · values in *italics* are the allowed select / radio values.
Every element also accepts `class` (Advanced → Class) and `visibility` (*hidden, hide-for-small, show-for-small, hide-for-medium, show-for-medium, show-for-medium hide-for-small*).

Saved tag vs builder name: the Text element is saved as `[ux_text]` (only when it has options; otherwise plain HTML). Nested rows/columns are `row_inner` / `col_inner`, then `_1`, `_2` for deeper levels; nested stacks `ux_stack_inner`, `ux_stack_inner_1`…

## Contents

- Layout: `block`, `col`, `col_grid`, `col_inner`, `col_inner_1`, `col_inner_2`, `page_header`, `row`, `row_inner`, `row_inner_1`, `row_inner_2`, `section`, `section_inner`, `ux_banner_grid`, `ux_sidebar`, `ux_slider`, `ux_stack`
- Content: `accordion`, `blog_posts`, `button`, `contact-form-7`, `divider`, `featured_box`, `follow`, `logo`, `map`, `message_box`, `scroll_to`, `search`, `share`, `tabgroup`, `tabgroup_vertical`, `team_member`, `testimonial`, `text`, `text_box`, `title`, `ux_banner`, `ux_countdown`, `ux_gallery`, `ux_gutenberg`, `ux_html`, `ux_image`, `ux_image_box`, `ux_instagram_feed`, `ux_menu`, `ux_menu_link`, `ux_menu_title`, `ux_nav`, `ux_pages`, `ux_portfolio`, `ux_price_table`, `ux_shortcode`, `ux_text`, `ux_video`, `video_button`
- Shop: `best_selling_products`, `featured_products`, `products`, `recent_products`, `sale_products`, `top_rated_products`, `ux_bestseller_products`, `ux_featured_products`, `ux_payment_icons`, `ux_product_categories`, `ux_product_flip`, `ux_products`, `ux_products_list`
- Product Page: `ux_product_add_to_cart`, `ux_product_breadcrumbs`, `ux_product_excerpt`, `ux_product_gallery`, `ux_product_hook`, `ux_product_meta`, `ux_product_next_prev_nav`, `ux_product_price`, `ux_product_rating`, `ux_product_related`, `ux_product_tabs`, `ux_product_title`, `ux_product_upsell`
- Elements: `ux_hotspot`
- Meta: `ux_breadcrumbs`, `ux_excerpt`, `ux_subnav`, `ux_the_title`
- Other: `accordion-item`, `bullet_item`, `gap`, `tab`, `woocommerce_cart`, `woocommerce_checkout`

## Layout

### `block` – Block
- `id` – select (Block)

### `col` – Column · **C** · parent: `row`
- `label` – textfield
- `span` – col-slider, ↕ (Width)
- `force_first` – select – *medium · small* (Force First Position)
- `divider` – checkbox
- `padding` – margins, ↕
- `margin` – margins, ↕
- `align` – radio-buttons – *left · center · right* (Text align)
- `bg_color` – colorpicker
- `bg_radius` – slider, px
- `color` – radio-buttons – *light*
- `sticky` – radio-buttons – *true*
- `text_depth` – slider, default `0`, + (Text Shadow)
- `max_width` – scrubfield
- `animate` – select, default `none` – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `hover` – select – *fade · focus · blur* (Hover effect)
- `tooltip` – textfield
- `parallax` – slider
- `depth` – slider
- `depth_hover` – slider (Hover Depth)
- `border` – margins (Width)
- `border_margin` – margins (Margin)
- `border_style` – radio-buttons – *dashed · dotted* (Style)
- `border_radius` – slider, px (Radius)
- `border_color` – colorpicker, ↕ (Color)
- `border_hover` – select (Hover)

### `col_grid` – Grid Item · **C** · children: `ux_banner`, `ux_slider`, `ux_image`; parent: `row`
- `span` – col-slider, ↕ (Width)
- `height` – radio-buttons, default `1` – *1 · 1-2 · 1-3 · 2-3 · 1-4 · 3-4*
- `animate` – select – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `depth` – slider
- `depth_hover` – slider (Hover Depth)

### `col_inner` – alias of `col` (same parameters)

### `col_inner_1` – alias of `col` (same parameters)

### `col_inner_2` – alias of `col` (same parameters)

### `page_header` – Page Header
- `height` – scrubfield, ↕
- `margin` – scrubfield, ↕
- `style` – select, default `featured` – *featured · normal · simple · divided* (Content Style)
- `type` – select, default `breadcrumbs` – *breadcrumbs · subnav · onpage · share* (Content Type)
- `text_color` – radio-buttons, default `light` – *light · dark*
- `align` – radio-buttons, default `left` – *left · center · right*
- `v_align` – radio-buttons, default `center` – *top · center · bottom* (Vertical Align)
- `depth` – slider
- `parallax_text` – slider, + (Content Parallax)
- `show_title` – checkbox, default `true`
- `title` – textfield
- `title_size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge* (Size)
- `title_case` – radio-buttons, default `normal` – *normal · uppercase* (Letter Case)
- `sub_title` – textfield
- `nav_style` – select, default `line` – *simple · line · divided · line-grow · line-bottom · outline · tabs · bold · pills* (Style)
- `nav_size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge* (Size)
- `nav_case` – radio-buttons, default `uppercase` – *uppercase · lowercase* (Letter Case)
- `bg` – image (Image)
- `bg_size` – select, default `large` – *original · large · medium · thumbnail* (Size)
- `bg_color` – colorpicker (Color)
- `bg_overlay` – colorpicker, ↕ (Overlay)
- `bg_pos` – textfield (Position)
- `parallax` – slider, +

### `row` – Row · **C** · children: `col`
- `label` – textfield
- `style` – radio-buttons – *small · large · collapse* (Column Spacing)
- `col_style` – radio-buttons – *divided · dashed · solid* (Column Style)
- `col_bg` – colorpicker (Column Background)
- `col_bg_radius` – slider, px (Column Radius)
- `width` – radio-buttons – *full-width · custom*
- `custom_width` – scrubfield
- `v_align` – radio-buttons – *equal · middle · bottom* (Align Vertical)
- `h_align` – radio-buttons – *center · right* (Align Horizontal)
- `padding` – margins, ↕ (Column Padding)
- `depth` – slider (Column Depth)
- `depth_hover` – slider (Column Depth Hover)

### `row_inner` – alias of `row` (same parameters)

### `row_inner_1` – alias of `row` (same parameters)

### `row_inner_2` – alias of `row` (same parameters)

### `section` – Section · **C**
- `label` – textfield (Admin label)
- `bg` – image (Image)
- `bg_size` – select, default `large` – *original · large · medium · thumbnail* (Size)
- `bg_color` – colorpicker (Color)
- `bg_overlay` – colorpicker, ↕ (Overlay)
- `bg_pos` – textfield (Position)
- `hover` – select
- `hover_alt` – select
- `parallax` – slider, +
- `effect` – select – *snow · confetti · sliding-glass · sparkle · rain* (Effects)
- `dark` – radio-buttons, default `false` – *true · false* (Color)
- `sticky` – radio-buttons – *true*
- `mask` – select – *circle · arrow · angled · angled-right · arrow-large · angled-large · angled-right-large*
- `padding` – scrubfield, default `30px`, ↕
- `height` – scrubfield, ↕ (Min Height)
- `margin` – scrubfield
- `scroll_for_more` – radio-buttons – *true*
- `loading` – radio-buttons – *true* (Loading Spinner)
- `divider_top` – select
- `divider_top_height` – scrubfield, default `150px`, ↕ (Height)
- `divider_top_width` – slider, default `100`, %, ↕ (Width)
- `divider_top_fill` – colorpicker (Fill)
- `divider_top_flip` – checkbox, default `false` (Flip Y)
- `divider_top_to_front` – checkbox, default `false` (To front)
- `divider` – select (Divider bottom)
- `divider_height` – scrubfield, default `150px`, ↕ (Height)
- `divider_width` – slider, default `100`, %, ↕ (Width)
- `divider_fill` – colorpicker (Fill)
- `divider_flip` – checkbox, default `false` (Flip Y)
- `divider_to_front` – checkbox, default `false` (To front)
- `border` – margins (Width)
- `border_margin` – margins (Margin)
- `border_style` – radio-buttons – *dashed · dotted* (Style)
- `border_radius` – slider, px (Radius)
- `border_color` – colorpicker, ↕ (Color)
- `border_hover` – select (Hover)
- `youtube` – textfield
- `video_mp4` – textfield
- `video_ogg` – textfield (Video OGG )
- `video_webm` – textfield
- `video_sound` – select, default `false` – *true · false* (Sound)
- `video_loop` – select, default `true` – *true · false* (Loop)
- `video_visibility` – select, default `hide-for-medium` – *visible · hidden · hide-for-medium · show-for-small · show-for-medium hide-for-small · show-for-medium · hide-for-small* (Visibility)

### `section_inner` – alias of `section` (same parameters)

### `ux_banner_grid` – Grid · **C** · children: `col_grid`
- `label` – textfield (Admin label)
- `spacing` – radio-buttons, default `small` – *xsmall · small · normal · large · collapse*
- `height` – slider, ↕
- `width` – select – *full-width*
- `depth` – slider (Col Depth)
- `depth_hover` – slider (Col Hover Depth)

### `ux_sidebar` – Widget Area
- `id` – select, default `sidebar-main` – *description* (Select)
- `style` – select – *framed · boxed* (Widgets style)

### `ux_slider` – Slider · **C** · children: `ux_banner`, `ux_image`, `section`, `row`, `ux_banner_grid`, `logo`
- `label` – textfield (Admin label)
- `type` – select, default `slide` – *slide · fade*
- `style` – select, default `normal` – *normal · container · focus · shadow*
- `slide_width` – scrubfield
- `slide_align` – select, default `center` – *center · left · right*
- `bg_color` – colorpicker
- `margin` – scrubfield, default `0px`, ↕
- `infinitive` – radio-buttons, default `true` – *false · true*
- `freescroll` – radio-buttons, default `false` – *false · true* (Free Scroll)
- `draggable` – radio-buttons, default `true` – *false · true*
- `parallax` – slider, +
- `mobile` – radio-buttons, default `true` – *false · true* (Show for Mobile)
- `hide_nav` – radio-buttons – *true* (Always Visible)
- `nav_pos` – select – *outside* (Position)
- `nav_size` – select, default `large` – *large · normal* (Size)
- `arrows` – radio-buttons, default `true` – *false · true*
- `nav_style` – select, default `circle` – *circle · simple · reveal* (Arrow Style)
- `nav_color` – radio-buttons, default `light` – *dark · light* (Arrow Color)
- `bullets` – radio-buttons, default `true` – *false · true*
- `bullet_style` – select, default `circle` – *circle · dashes · dashes-spaced · simple · square*
- `auto_slide` – radio-buttons, default `true` – *false · true*
- `timer` – textfield (Timer (ms))
- `pause_hover` – radio-buttons, default `true` – *false · true* (Pause on Hover)

### `ux_stack` – Stack · **C**
- `direction` – select, default `row`, ↕ – *row · col*
- `distribute` – select, default `start`, ↕ – *start · center · end · between · around*
- `align` – select, default `stretch`, ↕ – *stretch · start · center · end · baseline*
- `gap` – slider, default `0`, rem, ↕

## Content

### `accordion` – Accordion · **C** · children: `accordion-item`
- `title` – textfield
- `auto_open` – radio-buttons – *true*

### `blog_posts` – Blog posts
- `style` – select – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `ids` – select (Custom Posts)
- `cat` – select (Category)
- `posts` – textfield, default `8` (Total Posts)
- `offset` – textfield
- `orderby` – select, default `date` – *ID · name · date · modified · rand · comment_count · menu_order* (Order by)
- `order` – select, default `DESC` – *ASC · DESC*
- `title_size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `title_style` – radio-buttons – *uppercase*
- `readmore` – textfield (Text)
- `readmore_color` – select – *primary · secondary · alert · success · white* (Color)
- `readmore_style` – select, default `outline` – *outline · link · underline · shade · bevel · gloss* (Style)
- `readmore_size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge* (Size)
- `show_date` – select, default `badge` – *badge · text · false* (Date)
- `badge_style` – select – *outline · square · circle · circle-inside*
- `excerpt` – select, default `visible` – *visible · fade · slide · reveal · false*
- `excerpt_length` – slider
- `show_category` – select, default `false` – *label · text · false* (Category)
- `comments` – select, default `visible` – *visible · false*
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `button` – Button
- `text` – textfield
- `letter_case` – radio-buttons – *lowercase*
- `color` – select, default `primary` – *primary · secondary · alert · success · white*
- `style` – select – *outline · link · underline · shade · bevel · gloss*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `animate` – select, default `none` – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `padding` – margins
- `radius` – slider, default `0`
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)
- `expand` – checkbox
- `icon` – select
- `icon_pos` – select – *left* (Position)
- `icon_reveal` – select – *true* (Visibility)
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield

### `contact-form-7` – Form (CF7)
- `id` – select (Select Form)

### `divider` – Divider
- `align` – radio-buttons – *left · center · right*
- `width` – scrubfield, default `30px`
- `height` – scrubfield, default `3px`
- `margin` – scrubfield, default `1.0em`
- `color` – colorpicker

### `featured_box` – Icon Box · **C**
- `img` – image (Icon)
- `inline_svg` – checkbox, default `true`
- `img_width` – slider, px (Icon Width)
- `pos` – select, default `top` – *top · center · left · right* (Icon Position)
- `title` – textfield
- `title_small` – textfield
- `tooltip` – textfield
- `font_size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge* (Text Size)
- `margin` – margins
- `icon_border` – slider, px
- `icon_color` – colorpicker
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield

### `follow` – Follow Icons
- `title` – textfield
- `style` – radio-buttons, default `outline` – *outline · fill · small*
- `align` – radio-buttons – *left · center · right*
- `scale` – slider, default `100`, %
- `facebook` – textfield
- `instagram` – textfield
- `tiktok` – textfield
- `snapchat` – image
- `twitter` – textfield
- `linkedin` – textfield
- `email` – textfield
- `phone` – textfield
- `pinterest` – textfield
- `rss` – textfield
- `youtube` – textfield
- `flickr` – textfield
- `vkontakte` – textfield
- `px500` – textfield (500px)
- `telegram` – textfield
- `discord` – textfield
- `twitch` – textfield

### `logo` – Logo
- `img` – image (Image)
- `image_size` – select, default `original` – *thumbnail · medium · large · original*
- `title` – textfield
- `hover` – select – *zoom · fade-in · glow · color · grayscale*
- `height` – scrubfield, default `50px`
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield

### `map` – Map · **C**
- `height` – scrubfield, default `400px`, ↕
- `lat` – scrubfield, default `40.79028` (Latitude)
- `long` – scrubfield, default `-73.95972` (Longitude)
- `zoom` – slider
- `pan` – checkbox, default `true`
- `content_enable` – checkbox, default `true` (Show Content)
- `content_bg` – colorpicker, default `#fff` (Background)
- `content_width` – slider, ↕ (Width)
- `position_x` – slider, ↕ (X Position)
- `position_y` – slider, ↕ (Y Position)
- `controls` – checkbox (Show controls)
- `zoom_control` – checkbox, default `true` (Zoom)
- `street_view_control` – checkbox, default `true` (Street view)
- `map_type_control` – checkbox, default `true` (Map type)
- `color` – colorpicker
- `saturation` – slider

### `message_box` – Message Box · **C**
- `bg` – image (BG Image)
- `bg_color` – colorpicker
- `text_color` – radio-buttons, default `dark` – *light · dark* (Color)
- `padding` – slider

### `scroll_to` – Scroll To
- `title` – textfield, default `Change this`
- `link` – textfield
- `bullet` – radio-buttons, default `true` – *false · true*

### `search` – Search Box
- `style` – select – *flat*
- `size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge*

### `share` – Share Icons
- `title` – textfield
- `style` – radio-buttons – *outline · fill · small*
- `align` – radio-buttons – *left · center · right*
- `scale` – slider, default `100`, %

### `tabgroup` – Tabs · **C** · children: `tab`
- `title` – textfield
- `style` – select, default `line` – *simple · line · divided · line-grow · line-bottom · outline · tabs · bold · pills*
- `type` – select, default `horizontal` – *horizontal · vertical*
- `nav_style` – radio-buttons, default `uppercase` – *normal · uppercase*
- `nav_size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge* (Size)
- `align` – radio-buttons, default `left` – *left · center · right* (Tabs Align)
- `event` – radio-buttons – *hover* (Activate)

### `tabgroup_vertical` – alias of `tabgroup` (same parameters)

### `team_member` – Team Member · **C**
- `img` – image (Image)
- `style` – select, default `normal` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `name` – textfield
- `title` – textfield
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)
- `icon_style` – radio-buttons, default `outline` – *outline · fill · small* (Style)
- `facebook` – textfield
- `instagram` – textfield
- `tiktok` – textfield
- `twitter` – textfield
- `youtube` – textfield
- `email` – textfield
- `phone` – textfield
- `pinterest` – textfield
- `linkedin` – textfield
- `telegram` – textfield
- `twitch` – textfield
- `discord` – textfield
- `snapchat` – image
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `testimonial` – Testimonial · **C**
- `image` – image
- `image_width` – slider, px
- `pos` – select, default `left` – *top · center · left · right* (Image Position)
- `name` – textfield
- `company` – textfield
- `font_size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge* (Text Size)
- `stars` – slider

### `text` – Text · **C**
- `font_size` – slider, rem, ↕
- `line_height` – slider, ↕
- `text_align` – radio-buttons, ↕ – *left · center · right*
- `text_color` – colorpicker

### `text_box` – Text Box · **C** · children: `ux_breadcrumbs`, `ux_the_title`, `ux_image`, `ux_stack`, `ux_html`, `text`, `divider`, `button`, `title`, `video_button`, `row`, `follow`, `share`
- `style` – radio-buttons – *square · circle*
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `hover` – select – *fade-out · slide · zoom · zoom-in · blur · bounce · invert*
- `width` – slider, default `60`, %, ↕
- `scale` – slider, default `100`, %, ↕
- `margin` – margins, px, ↕
- `padding` – margins, ↕
- `rotate` – slider
- `animate` – select – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `parallax` – slider, +
- `position_x` – slider, ↕ (Horizontal)
- `position_y` – slider, ↕ (Vertical)
- `text_align` – radio-buttons, default `center` – *left · center · right* (Align)
- `text_depth` – slider, default `0`, + (Shadow)
- `bg` – colorpicker (BG Color)
- `radius` – slider, px
- `depth` – slider, default `0`, +
- `depth_hover` – slider, default `0`, + (Depth :hover)
- `border` – margins (Width)
- `border_margin` – margins (Margin)
- `border_style` – radio-buttons – *dashed · dotted* (Style)
- `border_radius` – slider, px (Radius)
- `border_color` – colorpicker, ↕ (Color)
- `border_hover` – select (Hover)

### `title` – Title
- `style` – select, default `normal` – *normal · center · bold · bold-center*
- `text` – textfield, default `Lorem ipsum dolor sit amet...` (Title)
- `tag_name` – select, default `h3` – *h1 · h2 · h3 · h4* (Tag)
- `color` – colorpicker
- `icon` – select
- `width` – scrubfield
- `margin_top` – scrubfield
- `margin_bottom` – scrubfield
- `size` – slider, %
- `link_text` – textfield
- `link` – textfield

### `ux_banner` – Banner · **C** · children: `text_box`, `ux_image`
- `label` – textfield (Admin label)
- `height` – scrubfield, ↕
- `slide_effect` – select – *fade-in · zoom-in · zoom-out · fade-in-fast · zoom-in-fast · zoom-out-fast*
- `bg` – image (Image)
- `bg_size` – select, default `large` – *original · large · medium · thumbnail* (Size)
- `bg_color` – colorpicker (Color)
- `bg_overlay` – colorpicker, ↕ (Overlay)
- `bg_pos` – textfield (Position)
- `hover` – select
- `hover_alt` – select
- `parallax` – slider, +
- `effect` – select – *snow · confetti · sliding-glass · sparkle · rain* (Effects)
- `divider_top` – select
- `divider_top_height` – scrubfield, default `150px`, ↕ (Height)
- `divider_top_width` – slider, default `100`, %, ↕ (Width)
- `divider_top_fill` – colorpicker (Fill)
- `divider_top_flip` – checkbox, default `false` (Flip Y)
- `divider_top_to_front` – checkbox, default `false` (To front)
- `divider` – select (Divider bottom)
- `divider_height` – scrubfield, default `150px`, ↕ (Height)
- `divider_width` – slider, default `100`, %, ↕ (Width)
- `divider_fill` – colorpicker (Fill)
- `divider_flip` – checkbox, default `false` (Flip Y)
- `divider_to_front` – checkbox, default `false` (To front)
- `border` – margins (Width)
- `border_margin` – margins (Margin)
- `border_style` – radio-buttons – *dashed · dotted* (Style)
- `border_radius` – slider, px (Radius)
- `border_color` – colorpicker, ↕ (Color)
- `border_hover` – select (Hover)
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield
- `youtube` – textfield
- `video_mp4` – textfield
- `video_ogg` – textfield (Video OGG )
- `video_webm` – textfield
- `video_sound` – select, default `false` – *true · false* (Sound)
- `video_loop` – select, default `true` – *true · false* (Loop)
- `video_visibility` – select, default `hide-for-medium` – *visible · hidden · hide-for-medium · show-for-small · show-for-medium hide-for-small · show-for-medium · hide-for-small* (Visibility)
- `sticky` – radio-buttons – *true*

### `ux_countdown` – Countdown
- `style` – select, default `clock` – *clock · text*
- `size` – slider, default `300`, %, ↕
- `color` – radio-buttons, default `dark` – *dark · light*
- `bg_color` – colorpicker, ↕ (Background)
- `year` – textfield, default `2021`
- `month` – textfield, default `12`
- `day` – textfield, default `31`
- `time` – textfield, default `18:00`
- `t_week` – textfield, default `week` (Week)
- `t_day` – textfield, default `day` (Day)
- `t_hour` – textfield, default `hour` (Hour)
- `t_min` – textfield, default `min` (Min)
- `t_sec` – textfield, default `sec` (Sec)
- `t_plural` – textfield (Plural default)
- `t_week_p` – textfield (Week Plural)
- `t_day_p` – textfield (Day Plural)
- `t_hour_p` – textfield (Hour Plural)
- `t_min_p` – textfield (Min Plural)
- `t_sec_p` – textfield (Sec Plural)

### `ux_gallery` – Gallery
- `ids` – gallery (Images)
- `style` – select, default `overlay` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `lightbox` – radio-buttons, default `true` – *false · true* (Open in Lightbox)
- `lightbox_image_size` – select – *large · medium · thumbnail · original*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_gutenberg` – WP Content Blocks · **C** · parent: `_root`
- (no options)

### `ux_html` – HTML · **C**
- `label` – textfield

### `ux_image` – Image
- `position_x` – slider, ↕ (Horizontal)
- `position_y` – slider, ↕ (Vertical)
- `id` – image (Image)
- `image_size` – select – *large · medium · thumbnail · original*
- `width` – slider, default `100`, %, ↕
- `height` – scrubfield
- `margin` – margins
- `lightbox` – radio-buttons – *true*
- `lightbox_image_size` – select – *large · medium · thumbnail · original*
- `caption` – radio-buttons – *true*
- `lightbox_caption` – radio-buttons – *true* (Caption on Lightbox)
- `image_overlay` – colorpicker
- `image_hover` – select – *zoom · zoom-long · zoom-fade · blur · fade-in · fade-out · glow · color · grayscale · overlay-add · overlay-remove · overlay-add-50 · overlay-remove-50*
- `image_hover_alt` – select – *zoom · zoom-long · zoom-fade · blur · fade-in · fade-out · glow · color · grayscale · overlay-add · overlay-remove · overlay-add-50 · overlay-remove-50*
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)
- `parallax` – slider, default `0`
- `animate` – select, default `none` – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield

### `ux_image_box` – Image Box · **C**
- `style` – select, default `normal` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `img` – image (Image)
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_instagram_feed` – Instagram Feed
- `username` – textfield, default `wonderful_places`
- `link` – textfield, default `Follow us on instagram` (Link title)
- `hashtag` – textfield
- `hashtag_media` – select, default `top` – *top · recent* (Hashtag media type)
- `photos` – slider (Images)
- `caption` – radio-buttons, default `true` – *true* (Captions)
- `image_overlay` – colorpicker
- `image_hover` – select – *zoom · zoom-long · zoom-fade · blur · fade-in · fade-out · glow · color · grayscale · overlay-add · overlay-remove · overlay-add-50 · overlay-remove-50* (Hover)
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*

### `ux_menu` – Menu · **C** · children: `ux_menu_link`, `ux_menu_title`
- `divider` – radio-buttons, ↕ – *solid*

### `ux_menu_link` – Menu link · parent: `ux_menu`
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield
- `post` – select
- `term` – select
- `text` – textfield
- `icon` – select
- `label` – select

### `ux_menu_title` – Menu title · parent: `ux_menu`
- `text` – textfield

### `ux_nav` – Navigation
- `parent` – select
- `type` – select, default `vertical` – *horizontal · vertical* (Direction)
- `style` – select, default `line` – *simple · line · divided · line-grow · line-bottom · outline · tabs · bold · pills*
- `align` – radio-buttons, default `left` – *left · center · right* (Text align)
- `size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge*

### `ux_pages` – Pages
- `style` – select, default `normal` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `parent` – select
- `orderby` – select, default `menu_order` – *post_title · post_date · menu_order · post_modified* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_portfolio` – Portfolio
- `style` – select, default `bounce` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `filter` – radio-buttons – *true*
- `filter_nav` – select, default `line-grow` – *simple · line · divided · line-grow · line-bottom · outline · tabs · bold · pills* (Filter Style)
- `filter_align` – radio-buttons, default `center` – *left · center · right*
- `lightbox` – radio-buttons – *true*
- `lightbox_image_size` – select – *large · medium · thumbnail · original*
- `ids` – select
- `cat` – select (Category)
- `number` – textfield (Total)
- `offset` – textfield
- `orderby` – select, default `menu_order` – *name · date · menu_order* (Order By)
- `order` – select, default `desc` – *desc · asc*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_price_table` – Price Table · **C** · children: `text`, `bullet_item`, `button`
- `title` – textfield, default `Title`
- `price` – textfield, default `$99.99`
- `description` – textfield, default `Description` (Desc.)
- `featured` – radio-buttons, default `false` – *false · true · false · true*
- `color` – radio-buttons – *dark* (Text Color)
- `bg_color` – colorpicker
- `radius` – slider, default `0`, px
- `depth` – slider, default `0`
- `depth_hover` – slider, default `3`
- `text` – textfield, default `Add any text here`
- `tooltip` – textfield
- `enabled` – radio-buttons, default `true` – *true · false*

### `ux_shortcode` – Shortcode
- (no options)

### `ux_text` – Text · **C**
- `font_size` – slider, rem, ↕
- `line_height` – slider, ↕
- `text_align` – radio-buttons, ↕ – *left · center · right*
- `text_color` – colorpicker

### `ux_video` – Video
- `url` – textfield, default `https://www.youtube.com/watch?v=AoPiLg8DZ3A` (Video url)
- `height` – scrubfield, default `56.25%`
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)

### `video_button` – Video Button
- `video` – textfield
- `size` – slider, default `100`, %

## Shop

### `best_selling_products` – Products - Best Selling
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `featured_products` – Products - Featured
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `products` – Products - Custom
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `recent_products` – Products - Recent
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `sale_products` – Products - On Sale
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `top_rated_products` – Products - Top Rated
- `per_page` – slider, default `12` (Number)
- `columns` – slider, default `4`
- `orderby` – select, default `title` – *menu_order · date · rand · id* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `ids` – select (Products)

### `ux_bestseller_products` – Bestseller products
- (no options)

### `ux_featured_products` – Featured Products
- (no options)

### `ux_payment_icons` – Payment Icons
- `icons` – select
- `custom` – image (Custom Icons)
- `link` – textfield
- `target` – select – *_blank*
- `rel` – textfield

### `ux_product_categories` – Product Categories
- `style` – select, default `badge` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `ids` – select (Categories)
- `number` – textfield (Total)
- `offset` – textfield
- `orderby` – select, default `menu_order` – *name · date · menu_order* (Order By)
- `order` – select, default `asc` – *asc · desc*
- `show_count` – checkbox, default `true`
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_product_flip` – Flip Book
- `ids` – select (Custom Posts)
- `cat` – select (Category)
- `products` – textfield, default `8` (Total Posts)
- `offset` – textfield
- `orderby` – select, default `date` – *ID · name · date · modified · rand · comment_count · menu_order* (Order by)
- `order` – select, default `DESC` – *ASC · DESC*
- `show` – select – *featured · onsale* (Order)

### `ux_products` – Products
- `style` – select, default `default` – *none · normal · overlay · shade · vertical · label · push · badge · bounce*
- `type` – select
- `grid` – select, default `1` (Grid Layout)
- `grid_height` – textfield, default `600px`, ↕
- `width` – select – *full-width*
- `col_spacing` – select (Column Spacing)
- `columns` – slider, ↕
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0`
- `animate` – select, default `none`
- `slider_nav_style` – select, default `reveal` (Nav Style)
- `slider_nav_color` – select – *light* (Nav Color)
- `slider_nav_position` – select, default `inside` – *inside · outside* (Nav Position)
- `slider_bullets` – select – *true* (Bullets)
- `auto_slide` – select – *2000 · 3000 · 4000 · 5000 · 6000 · 7000*
- `infinitive` – select – *false*
- `show_cat` – checkbox, default `true` (Category)
- `show_title` – checkbox, default `true` (Title)
- `show_rating` – checkbox, default `true` (Rating)
- `show_price` – checkbox, default `true` (Price)
- `show_add_to_cart` – checkbox, default `true` (Add To Cart)
- `show_quick_view` – checkbox, default `true` (Quick View)
- `equalize_box` – checkbox, default `false` (Equalize Items)
- `ids` – select (Custom Posts)
- `cat` – select (Category)
- `products` – textfield, default `8` (Total Posts)
- `offset` – textfield
- `orderby` – select, default `date` – *ID · name · date · modified · rand · comment_count · menu_order* (Order by)
- `order` – select, default `DESC` – *ASC · DESC*
- `show` – select – *featured · onsale*
- `out_of_stock` – select – *exclude*
- `image_height` – scrubfield (Height)
- `image_width` – slider, % (Width)
- `image_radius` – slider, % (Radius)
- `image_size` – select – *large · medium · thumbnail · original* (Size)
- `image_overlay` – colorpicker (Overlay)
- `image_hover` – select (Hover)
- `image_hover_alt` – select (Hover Alt)
- `text_pos` – select, default `bottom` (Position)
- `text_align` – radio-buttons (Align)
- `text_size` – radio-buttons, default `medium` (Size)
- `text_hover` – select (Hover)
- `text_bg` – colorpicker (Bg Color)
- `text_color` – radio-buttons, default `light` – *light · dark* (Color)
- `text_padding` – margins (Padding)

### `ux_products_list` – Products List
- `ids` – select (Custom Posts)
- `cat` – select (Category)
- `posts` – textfield, default `8` (Total Posts)
- `offset` – textfield
- `orderby` – select, default `date` – *ID · name · date · modified · rand · comment_count · menu_order* (Order by)
- `order` – select, default `DESC` – *ASC · DESC*
- `show` – select – *featured · onsale*
- `out_of_stock` – select – *exclude*

## Product Page

### `ux_product_add_to_cart` – Product Add To Cart
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_breadcrumbs` – Product Breadcrumbs
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_excerpt` – Product Short Description
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_gallery` – Product Gallery
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_hook` – Product Hooks
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_meta` – Product Meta
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_next_prev_nav` – Product Next/Prev
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_price` – Product Price
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_rating` – Product Rating
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_related` – Product Related
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_tabs` – Product Tabs
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_title` – Product Title
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

### `ux_product_upsell` – Product Up-sells
- `style` – select, default `normal` – *normal · vertical · full-width · stacked*
- `size` – select – *xxsmall · xsmall · smaller · small · large · larger · xlarge · xxlarge*
- `divider` – checkbox, default `true`
- `uppercase` – checkbox, default `false`
- `count` – checkbox, default `false` (Review Count)
- `align` – select, default `left` – *left · center · right*
- `hook` – select, default `woocommerce_single_product_summary`

## Elements

### `ux_hotspot` – Badge
- `position_x` – slider, ↕ (Horizontal)
- `position_y` – slider, ↕ (Vertical)
- `type` – radio-buttons, default `text` – *text · product*
- `prod_id` – select (Product)
- `text` – textfield, default `Enter any text...`
- `link` – textfield
- `icon` – radio-buttons, default `plus` – *plus · search · play*
- `size` – radio-buttons, default `medium` – *xsmall · small · medium · large · xlarge*
- `bg_color` – colorpicker
- `animate` – select, default `none` – *none · fadeInLeft · fadeInRight · fadeInUp · fadeInDown · bounceIn · bounceInUp · bounceInDown · bounceInLeft · bounceInRight · blurIn · flipInX · flipInY*
- `depth` – slider, default `0`
- `depth_hover` – slider, default `0` (Depth :hover)

## Meta

### `ux_breadcrumbs` – Breadcrumbs
- `video` – textfield

### `ux_excerpt` – Excerpt
- `video` – textfield

### `ux_subnav` – Sub Navs
- `video` – textfield

### `ux_the_title` – Page Title
- `video` – textfield

## Other

### `accordion-item` – Accordion Panel · **C** · parent: `accordion`
- `title` – textfield

### `bullet_item` – Bullet Item · parent: `ux_price_table`
- `title` – textfield, default `Title`
- `price` – textfield, default `$99.99`
- `description` – textfield, default `Description` (Desc.)
- `featured` – radio-buttons, default `false` – *false · true · false · true*
- `color` – radio-buttons – *dark* (Text Color)
- `bg_color` – colorpicker
- `radius` – slider, default `0`, px
- `depth` – slider, default `0`
- `depth_hover` – slider, default `3`
- `text` – textfield, default `Add any text here`
- `tooltip` – textfield
- `enabled` – radio-buttons, default `true` – *true · false*

### `gap` – Gap
- `height` – scrubfield, default `30px`, ↕

### `tab` – Tab Panel · **C** · parent: `tabgroup`
- `title` – textfield

### `woocommerce_cart` – WC Cart
- (no options)

### `woocommerce_checkout` – WC Checkout
- (no options)
