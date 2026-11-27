# Flatsome Customizer (theme_mod) keys

Flatsome stores every Customizer option as a **theme_mod** of the active theme (Kirki config `option_type => theme_mod`). Read with `get_theme_mod( $key )`, write with `set_theme_mod()` or `wp theme mod set <key> <value>`.
With a child theme active, mods live in `theme_mods_{child-slug}` – set them while the child theme is active (switching themes does not copy them).

Generated from Flatsome 3.15.x option definitions: key · type · default · label. Defaults shown as `Flatsome_Default::…` are constants: COLOR_PRIMARY `#446084`, COLOR_SECONDARY `#d26e4b`, COLOR_SUCCESS `#7a9c59`, COLOR_ALERT `#b20000`.

Most used for theme kits: `color_primary`, `color_secondary`, `color_success`, `color_alert`, `color_links`, `type_headings`, `type_texts`, `type_nav`, `type_alt`, `type_size`, `site_width`, `body_layout`, `logo_width`, `header_height`, `header_bg`, `header_sticky`, `nav_style`, `button_radius`, `html_custom_css`, `html_scripts_header`, `html_scripts_footer`, `footer_block`, `header_shop_bg_color`, `lazy_load_images`.


## colors
- `color_primary` – color, default `Flatsome_Default::COLOR_PRIMARY` – Primary Color
- `color_secondary` – color, default `Flatsome_Default::COLOR_SECONDARY` – Secondary Color
- `color_success` – color, default `Flatsome_Default::COLOR_SUCCESS` – Success Color
- `color_alert` – color, default `Flatsome_Default::COLOR_ALERT` – Alert Color
- `color_texts` – color, default `#777` – Base Color
- `type_headings_color` – color, default `#555` – Headline Color
- `color_divider` – color-alpha – Divider Color
- `color_links` – color, default `#4e657b` – Link Colors
- `color_links_hover` – color, default `#111` – Link Colors :hover
- `color_widget_links` – color – Widget Link Colors
- `color_widget_links_hover` – color – Widget Link Colors :hover
- `color_checkout` – color – Add to cart / Checkout buttons
- `color_sale` – color – Sale bubble
- `color_new_bubble_auto` – color – New bubble (auto)
- `color_new_bubble` – color – Custom bubble
- `color_review` – color – Review Stars
- `color_regular_price` – color – Regular Price
- `color_sale_price` – color – Sale Price

## type
- `disable_fonts` – checkbox, default `0` – Disable Google Fonts. No fonts will be loaded from Google.
- `googlefonts_font_display` – select, default `swap` – Google Fonts font-display type
- `type_headings` – typography
- `type_texts` – typography
- `type_size` – slider, default `100` – Base Font Size
- `type_size_mobile` – slider, default `100` – Mobile Base Font Size
- `type_nav` – typography
- `type_alt` – typography
- `text_transform_breadcrumbs` – radio-buttonset
- `text_transform_buttons` – radio-buttonset
- `text_transform_navigation` – radio-buttonset
- `text_transform_section_titles` – radio-buttonset
- `text_transform_widget_titles` – radio-buttonset

## layout
- `body_layout` – radio-buttonset, default `full-width` – Layout Mode
- `box_shadow` – checkbox – Add Drop Shadow to Content box
- `site_width_boxed` – number, default `1170` – Site width (px)
- `body_bg` – color, default `""` – Background Color
- `body_bg_image` – image, default `""` – Background Image
- `body_bg_type` – radio-buttonset, default `bg-full-size` – Background Repeat
- `site_width` – number, default `1080` – Container width (px)
- `content_color` – radio-image, default `light` – Content Color
- `content_bg` – color, default `""` – Content Background

## global-styles
- `button_radius` – text – Default Button Radius

## title_tagline
- `site_logo_slogan` – checkbox, default `0` – Display below Logo
- `logo_position` – radio-image, default `left` – Logo position
- `site_logo` – image, default `get_template_directory_uri().'/assets…` – Logo image
- `site_logo_dark` – image – Logo image - Light Version
- `logo_width` – slider, default `200` – Logo container width
- `logo_max_width` – text – Logo max width (px)
- `logo_padding` – slider, default `0` – Logo Padding
- `logo_link` – link – Logo link

## main_bar
- `header_width` – radio-image, default `container` – Header Width
- `header_height` – slider, default `90` – Height
- `header_color` – radio-image, default `light` – Text color
- `header_bg` – color-alpha, default `rgba(255` – Background Color
- `header_bg_img` – image, default `""` – Background Image
- `header_bg_img_repeat` – radio-buttonset, default `repeat` – Background Repeat
- `box_shadow_header` – checkbox, default `0` – Add Shadow
- `header_divider` – checkbox, default `1` – Add Divider
- `html_after_header` – textarea – HTML after header
- `nav_style` – radio-image – Navigation Style
- `nav_size` – radio-buttonset – Nav Size
- `nav_spacing` – radio-buttonset – Nav Spacing
- `nav_uppercase` – checkbox, default `1` – Uppercase
- `nav_body_overlay` – checkbox, default `0` – Add overlay on hover
- `nav_height` – slider, default `16` – Nav Height
- `nav_push` – slider, default `0` – Nav Push
- `type_nav_color` – color – Nav Color
- `type_nav_color_hover` – color – Nav Color :hover
- `header_icons_color` – color-alpha – Icons Color
- `header_icons_color_hover` – color-alpha – Icons Color :hover
- `header_height_transparent` – slider, default `90` – Height - Transparent Header
- `header_bg_transparent` – color-alpha – Transparent Header Background Color
- `header_bg_transparent_shade` – checkbox, default `0` – Add Shade

## header-layout
- `nav_position` – textfield – Nav Position
- `search_pos` – textfield – Search Position
- `flatsome_version` – text – Flatsome Version
- `topbar_elements_left` – select, default `flatsome_topbar_elements_left()` – ← Left Elements
- `topbar_elements_center` – select, default `array()` – Center Elements
- `topbar_elements_right` – select, default `flatsome_topbar_elements_right()` – Right Elements →
- `header_elements_left` – select, default `flatsome_header_elements_left()` – Left Elements
- `header_elements_right` – select, default `flatsome_header_elements_right()` – Right Elements
- `header_elements_bottom_left` – select, default `flatsome_header_elements_bottom_left()` – Left Elements
- `header_elements_bottom_center` – select, default `flatsome_header_elements_bottom_cente…` – Left Elements
- `header_elements_bottom_right` – select, default `flatsome_header_elements_bottom_right()` – Right Elements
- `header_mobile_elements_top` – select, default `flatsome_header_mobile_elements_top()` – Mobile Top
- `header_mobile_elements_left` – select, default `flatsome_header_mobile_elements_left()` – Left Elements
- `header_mobile_elements_right` – select, default `flatsome_header_mobile_elements_right()` – Left Elements
- `header_mobile_elements_bottom` – select, default `array()` – Left Elements

## header_sticky
- `header_height_sticky` – slider, default `70` – Header Height on Sticky
- `sticky_logo_padding` – slider, default `0` – Sticky Logo Padding
- `nav_height_sticky` – slider – Nav Height on Sticky
- `topbar_sticky` – checkbox, default `0` – Top Bar - Sticky on Scroll
- `header_sticky` – checkbox, default `1` – Header Main - Sticky on Scroll
- `bottombar_sticky` – checkbox, default `1` – Header Bottom - Sticky on Scroll
- `sticky_style` – select, default `jump` – Sticky Style
- `sticky_hide_on_scroll` – checkbox, default `0` – Hide sticky when scrolling down
- `site_logo_sticky` – image – Custom Sticky Logo

## header_mobile
- `header_height_mobile` – slider, default `70` – Mobile Header Height
- `logo_position_mobile` – radio-image, default `center` – Logo position
- `menu_icon_style` – radio-image – Menu Icon Style
- `menu_icon_title` – checkbox, default `0` – Show Menu title
- `mobile_overlay` – radio-image, default `left` – Menu Overlay
- `mobile_submenu_parent_behavior` – radio – Menu item behavior
- `mobile_submenu_effect` – radio, default `accordion` – Submenu effect
- `mobile_submenu_levels` – select, default `1` – Submenu levels
- `mobile_sidebar_top_content` – textarea – Top content
- `mobile_sidebar_tabs` – radio-buttonset, default `0` – Tabs
- `mobile_sidebar_tab_text` – text – Tab 1 text
- `mobile_sidebar` – sortable, default `flatsome_header_mobile_sidebar()` – Menu elements
- `mobile_sidebar_tab_2_text` – text – Tab 2 text
- `mobile_sidebar_tab_2` – sortable – Menu elements tab 2
- `mobile_overlay_color` – radio-image – Overlay Color
- `mobile_overlay_bg` – color-alpha – Background Color

## top_bar
- `topbar_show` – checkbox, default `1` – Enable Top Bar
- `header_top_height` – slider, default `30` – Height
- `topbar_color` – radio-image, default `dark` – Text Color
- `topbar_bg` – color-alpha – Background Color
- `nav_style_top` – radio-image, default `divided` – Navigation Style
- `nav_top_uppercase` – checkbox, default `0` – Uppercase
- `nav_top_body_overlay` – checkbox, default `0` – Add overlay on hover
- `nav_height_top` – slider, default `16` – Nav Height
- `type_nav_top_color` – color – Nav Color
- `type_nav_top_color_hover` – color – Nav Color :hover

## bottom_bar
- `header_bottom_height` – slider – Height
- `nav_position_bg` – color-alpha, default `"#f1f1f1"` – Background color
- `nav_style_bottom` – radio-image – Nav Style
- `nav_height_bottom` – slider, default `16` – Nav Height
- `nav_size_bottom` – radio-buttonset – Nav Size
- `nav_spacing_bottom` – radio-buttonset – Nav Spacing
- `nav_uppercase_bottom` – checkbox, default `1` – Uppercase
- `nav_bottom_body_overlay` – checkbox, default `0` – Add overlay on hover
- `nav_position_color` – radio-image, default `light` – Nav Base Color
- `type_nav_bottom_color` – color – Nav Color
- `type_nav_bottom_color_hover` – color – Nav Color :hover

## header_buttons
- `header_button_1` – text, default `Button 1` – Text
- `header_button_1_link` – text – Link
- `header_button_1_link_target` – select, default `_self` – Target
- `header_button_1_link_rel` – text – Rel
- `header_button_1_radius` – text, default `99px` – Radius
- `header_button_1_color` – radio-buttonset, default `primary` – Color
- `header_button_1_style` – radio-buttonset – Style
- `header_button_1_size` – radio-buttonset – Size
- `header_button_1_depth` – slider, default `0` – Depth
- `header_button_1_depth_hover` – slider, default `0` – Depth :hover
- `header_button_2` – text, default `Button 2` – Text
- `header_button_2_link` – text – Link
- `header_button_2_link_target` – select, default `_self` – Target
- `header_button_2_link_rel` – text – Rel
- `header_button_2_radius` – text, default `99px` – Radius
- `header_button_2_color` – radio-buttonset, default `secondary` – Color
- `header_button_2_style` – radio-buttonset, default `outline` – Style
- `header_button_2_size` – radio-buttonset – Size
- `header_button_2_depth` – slider, default `0` – Depth
- `header_button_2_depth_hover` – slider, default `0` – Depth :hover

## footer
- `footer_block` – select, default `false` – Custom Footer Block
- `footer_1` – checkbox, default `1` – Enable Footer 1
- `footer_1_columns` – radio-buttonset, default `4` – Columns
- `footer_1_color` – radio-image, default `light` – Text color
- `footer_1_bg_color` – color-alpha, default `#fff` – Background Color
- `footer_1_bg_image` – image – Background Image
- `footer_2` – checkbox, default `1` – Enable Footer 2
- `footer_2_columns` – radio-buttonset, default `4` – Columns
- `footer_2_color` – radio-image, default `dark` – Text color
- `footer_2_bg_color` – color-alpha, default `#777` – Background Color
- `footer_2_bg_image` – image – Background Image
- `footer_bottom_text` – radio-image, default `dark` – Text color
- `footer_bottom_align` – radio-buttonset – Align
- `footer_bottom_color` – color-alpha – Background Color
- `footer_left_text` – textarea, default `Copyright [ux_current_year] &copy; <s…` – Bottom Text - Primary
- `footer_right_text` – textarea – Bottom Text - Secondary
- `back_to_top` – checkbox, default `1` – Enable Back To Top Button
- `back_to_top_shape` – radio-image, default `circle` – Button Shape
- `back_to_top_position` – radio-buttonset, default `right` – Position
- `back_to_top_mobile` – checkbox, default `0` – Show on Mobile
- `html_before_footer` – textarea – HTML before footer
- `html_after_footer` – textarea – HTML after footer

## custom-css
- `html_custom_css` – code – Custom CSS
- `html_custom_css_tablet` – code – Custom Tablet CSS
- `html_custom_css_mobile` – code – Custom Mobile CSS

## woocommerce_product_catalog
- `html_shop_page` – textarea – Shop Page Header
- `html_shop_page_content` – textarea – Shop Page Content
- `category_sidebar` – radio-image, default `left-sidebar` – Layout
- `category_sticky_sidebar` – checkbox, default `0` – Sticky sidebar
- `category_grid_style` – radio-image, default `grid` – List Style
- `category_force_image_height` – checkbox, default `false` – EQUAL IMAGE HEIGHTS
- `category_image_height` – slider, default `100` – Equal Image height
- `products_pr_page` – text, default `12` – Products per Page
- `category_row_count` – slider, default `3` – Products per row - Desktop
- `category_row_count_tablet` – slider, default `3` – Products per row - Tablet
- `category_row_count_mobile` – slider, default `2` – Products per row - Mobile
- `category_title_style` – radio-image – Title Style
- `category_show_title` – checkbox, default `0` – Show title
- `breadcrumb_home` – checkbox, default `1` – Show home link in breadcrumb
- `category_header_transparent` – checkbox, default `0` – Transparent Header
- `header_shop_bg_featured` – checkbox, default `1` – Featured Image as Background
- `header_shop_bg_image` – image – Shop Title Background
- `header_shop_bg_color` – color-alpha, default `rgba(0` – Title Background Color
- `category_filter_text` – text – Custom Filter Text
- `breadcrumb_size` – select, default `large` – Breadcrumb Size
- `breadcrumb_case` – radio-buttonset, default `uppercase`
- `cat_style` – radio-image, default `badge` – Category Box Style
- `category_show_count` – checkbox, default `1` – Show product count
- `grid_style` – radio-image, default `grid1` – Grid Style
- `product_hover` – select, default `fade_in_back` – Product Image Hover style
- `category_shadow` – slider, default `0` – Drop Shadow
- `category_shadow_hover` – slider, default `0` – Drop Shadow :hover
- `add_to_cart_icon` – radio-image, default `disable` – Add To Cart Button
- `add_to_cart_style` – select, default `outline` – Button Style
- `product_box_category` – checkbox, default `1` – Show Category
- `product_box_rating` – checkbox, default `1` – Show Ratings
- `short_description_in_grid` – checkbox, default `0` – Show Short Description
- `disable_quick_view` – checkbox, default `0` – Disable Quick View
- `equalize_product_box` – checkbox, default `0`
- `bubble_style` – radio-image, default `style1` – Bubble Style
- `sale_bubble_text` – text – Custom Sale Bubble Text
- `sale_bubble_percentage` – checkbox, default `0` – Enable % instead of "Sale!"
- `sale_bubble_percentage_formatting` – text, default `-{value}%` – Sale Bubble % Formatting
- `new_bubble_auto` – number, default `0` – Auto "New" bubble
- `swatches_box_attribute` – select – Product attribute
- `swatches_box_layout` – radio-buttonset – Layout
- `swatches_box_limit` – slider, default `5` – Limit
- `swatches_box_size` – radio-buttonset – Size
- `swatches_box_shape` – radio-buttonset – Shape
- `swatches_box_select_event` – radio-buttonset – Select event - Desktop
- `swatches_box_color_selected` – color, default `Flatsome_Default::COLOR_SECONDARY` – Color :selected
- `swatches_box_behavior_selected` – select – Behavior :selected
- `swatches_box_update_urls` – checkbox, default `1` – Update links to selected variation
- `swatches_box_reset` – checkbox, default `0` – Auto reset selection
- `swatches_box_reset_extent` – radio-buttonset – Reset extent
- `swatches_box_reset_time` – slider, default `300` – Reset time

## product-page
- `product_layout` – radio-image, default `flatsome_product_layout()` – Product Layout
- `product_custom_layout` – select, default `false` – Custom product layout
- `product_offcanvas_sidebar` – checkbox, default `0` – Off-canvas Sidebar for Mobile
- `product_header` – radio-image – Product Header
- `product_header_transparent` – checkbox, default `0` – Transparent Header
- `product_next_prev_nav` – checkbox, default `1` – Next / Prev Navigation
- `product_image_width` – select, default `6` – Product Image Width
- `product_image_style` – radio-image, default `normal` – Product Image Style
- `product_lightbox` – select, default `default` – Product Image Lightbox
- `product_zoom` – checkbox, default `0` – Product Image Hover Zoom
- `product_info_align` – radio-buttonset, default `left` – Text Align
- `product_info_form` – radio-buttonset – Form Style
- `product_title_divider` – checkbox, default `1` – Product Title divider
- `product_sticky_cart` – checkbox, default `0` – Sticky add to cart
- `product_info_review_count` – checkbox, default `false` – Show Review Count
- `product_info_review_count_style` – select, default `inline` – Review Count Style
- `product_info_meta` – checkbox, default `1` – Show Meta / Categories
- `product_info_share` – checkbox, default `1` – Show Share Icons
- `swatches_layout` – radio-image – Layout
- `swatches_tooltip` – checkbox, default `1` – Tooltip
- `swatches_color_selected` – color, default `Flatsome_Default::COLOR_SECONDARY` – Color :selected
- `product_display` – select, default `tabs` – Product Tabs Style
- `product_tabs_align` – radio-buttonset, default `left` – Product Tabs Align
- `tab_title` – text – Global custom tab title
- `tab_content` – textarea – Global custom tab content
- `product_upsell` – select, default `sidebar` – Product Upsell
- `related_products` – select, default `slider` – Related Products Style
- `related_products_pr_row` – slider, default `4` – Products per row - Desktop
- `related_products_pr_row_tablet` – slider, default `3` – Products per row - Tablet
- `related_products_pr_row_mobile` – slider, default `2` – Products per row - Mobile
- `max_related_products` – text, default `12` – Max number of Related Products
- `html_before_add_to_cart` – textarea – HTML before Add To Cart button
- `html_after_add_to_cart` – textarea – HTML after Add To Cart button

## blog-layout
- `blog_header` – textarea – Blog Homepage Header
- `blog_archive_transparent` – checkbox, default `0` – Transparent Header
- `blog_layout` – radio-image, default `right-sidebar` – Blog Sidebar
- `blog_layout_divider` – checkbox, default `1` – Enable Sidebar Divider
- `blog_sticky_sidebar` – checkbox, default `0` – Sticky sidebar
- `blog_style` – radio-image, default `normal` – Posts Layout
- `blog_style_type` – radio-buttonset, default `masonry` – Posts Layout Type
- `blog_show_excerpt` – checkbox, default `1` – Show Excerpts Only
- `blog_bg_color` – color-alpha – Blog Background Color
- `blog_posts_depth` – slider, default `0` – Depth
- `blog_posts_depth_hover` – slider, default `0` – Depth :hover
- `blog_posts_header_style` – radio-image, default `normal` – Posts Title Style
- `blog_posts_title_align` – radio-image, default `center` – Posts Title Align

## blog-single
- `blog_post_layout` – radio-image, default `right-sidebar` – Blog Post Single Layout
- `blog_post_style` – radio-image, default `default` – Title Layout
- `blog_single_transparent` – checkbox, default `0` – Transparent Header
- `blog_single_header_meta` – checkbox, default `1` – Enable Header Meta
- `blog_single_featured_image` – checkbox, default `1` – Enable Featured Image
- `blog_share` – checkbox, default `1` – Enable Share Icons
- `blog_single_footer_meta` – checkbox, default `1` – Enable Footer Meta
- `blog_author_box` – checkbox, default `1` – Enable Blog Author Box
- `blog_single_next_prev_nav` – checkbox, default `1` – Enable Next/Prev Navigation
- `blog_after_post` – textarea – HTML after blog posts

## blog-archive
- `blog_archive_title` – checkbox, default `1` – Enable Blog Archive Title
- `blog_style_archive` – radio-image – Posts Layout

## blog-featured
- `blog_featured` – radio-image – Featured Posts
- `blog_hide_sticky` – checkbox, default `0` – Hide Featured Posts from Default Blog feed.
- `blog_featured_height` – slider, default `500` – Featured Posts Height
- `blog_featured_image_size` – radio-buttonset, default `medium`

## blog-global
- `blog_badge` – checkbox, default `1` – Show Date Box
- `blog_badge_style` – radio-image, default `outline` – Date Box Style
- `blog_excerpt_suffix` – text, default ` [...]` – Blog Excerpt Suffix

## cart-checkout
- `cart_layout` – radio-buttonset – Cart layout
- `cart_sticky_sidebar` – checkbox, default `0` – Sticky sidebar
- `cart_auto_refresh` – checkbox, default `0` – Auto update on quantity change
- `cart_boxed_shipping_labels` – checkbox, default `0` – Boxed Shipping labels
- `cart_estimate_text` – checkbox, default `1` – Show shipping estimate destination
- `cart_steps_size` – radio-buttonset, default `h2` – Steps size
- `cart_steps_case` – radio-buttonset, default `uppercase`
- `cart_steps_numbers` – checkbox, default `0` – Step numbers
- `html_cart_sidebar` – textarea – Cart Sidebar content
- `html_cart_footer` – textarea – After Cart content

## catalog-mode
- `catalog_mode` – checkbox, default `0` – Enable Catalogue Mode.
- `catalog_mode_prices` – checkbox, default `0` – Disable Prices
- `catalog_mode_header` – textarea – Header Cart replacement
- `catalog_mode_product` – textarea, default `Add any HTML or Shortcode here...` – Product page Add to cart replacement.
- `catalog_mode_lightbox` – textarea – Add to cart replacement - Product Quick View

## fl-my-account
- `my_account_title_bg_color` – color-alpha – Title Background Color
- `facebook_login_bg` – image – Title Background Image
- `my_account_title_align` – radio-image, default `left` – Title Align
- `my_account_title_text_color` – radio-image, default `dark` – Text color
- `facebook_login_text` – text – Login Text
- `social_login_pos` – select, default `top` – Social Button
- `wc_account_links` – checkbox, default `1` – Enable default WooCommerce Account links in Dropdown and Account Sidebar. You can create a custom my account menu instead if you want.

## fl-portfolio
- `featured_items_page` – select, default `false` – Custom Portfolio Page
- `portfolio_layout` – radio-image – Single Portfolio Layout
- `portfolio_title_transparent` – checkbox, default `0` – Transparent Header
- `portfolio_title` – radio-image – Single Portfolio Title
- `portfolio_share` – checkbox, default `1` – Show share icons
- `portfolio_related` – checkbox, default `1` – Show related items
- `portfolio_next_prev` – checkbox, default `1` – Show Next/Prev navigation
- `portfolio_archive_orderby` – select, default `menu_order` – Portfolio Items Orderby
- `portfolio_archive_order` – select, default `desc` – Portfolio Items Order
- `portfolio_style` – radio-image – Portfolio Style
- `portfolio_height` – select, default `0` – Image Height
- `portfolio_archive_image_radius` – slider, default `0` – Image Radius (%)
- `portfolio_archive_image_size` – select, default `medium` – Image Size
- `portfolio_archive_depth` – slider, default `0` – Item Depth
- `portfolio_archive_depth_hover` – slider, default `0` – Item Depth :hover
- `portfolio_archive_spacing` – radio-buttonset, default `small` – Column Spacing
- `portfolio_archive_columns` – slider, default `4` – Items per row - Desktop
- `portfolio_archive_columns_tablet` – slider, default `3` – Items per row - Tablet
- `portfolio_archive_columns_mobile` – slider, default `2` – Items per row - Mobile
- `portfolio_archive_title` – radio-image – Archive Portfolio Title
- `portfolio_archive_title_transparent` – checkbox, default `0` – Transparent Header
- `portfolio_archive_bg` – image, default `""` – Portfolio Header Background
- `portfolio_archive_filter` – radio-buttonset, default `left` – Filter Navigation
- `portfolio_archive_filter_style` – radio-image, default `line-grow` – Filter Nav style

## follow
- `follow_style` – radio-image, default `small` – Icons Style
- `follow_facebook` – text – Facebook
- `follow_twitter` – text – Twitter
- `follow_pinterest` – text – Pinterest
- `follow_instagram` – text – Instagram
- `follow_tiktok` – text – TikTok
- `follow_linkedin` – text – LinkedIn
- `follow_youtube` – text – YouTube
- `follow_vk` – text – VKontakte
- `follow_flickr` – text – Flickr
- `follow_email` – text – E-mail
- `follow_phone` – text – Phone
- `follow_rss` – text – RSS
- `follow_500px` – text – 500px
- `follow_telegram` – text – Telegram
- `follow_twitch` – text – Twitch
- `follow_discord` – text – Discord
- `follow_snapchat` – image – SnapChat

## header-presets
- `preset_demo` – preset – Preset
- `select_preset` – custom

## header_account
- `account_icon_style` – radio-image – Icon Style
- `header_account_title` – checkbox, default `1` – Show label
- `header_account_username` – checkbox, default `0` – Use username as label
- `account_login_style` – select, default `lightbox` – Login Style
- `header_account_register` – checkbox – Show "Register" label
- `account_login_lightbox_layout` – radio-image – Lightbox layout (register)
- `account_login_lightbox_side_panel_block` – select, default `false` – Side panel block
- `account_login_lightbox_side_panel_bg_color` – color, default `Flatsome_Default::COLOR_PRIMARY` – Side panel background color
- `account_login_lightbox_side_panel_bg_image` – image – Side panel background image
- `account_login_lightbox_side_panel_bg_overlay` – color-alpha – Side panel background overlay

## header_cart
- `header_cart_style` – select, default `dropdown` – Cart Style
- `cart_dropdown_show` – checkbox, default `1` – Auto reveal mini cart
- `cart_icon_style` – radio-image – Cart Icon Style
- `cart_icon` – radio-image, default `basket` – Cart Icon
- `custom_cart_icon` – image – Custom Cart Icon
- `header_cart_total` – checkbox, default `1` – Show cart totals
- `header_cart_title` – checkbox, default `1` – Show cart title
- `html_cart_header` – textarea – Custom Content after Cart

## header_contact
- `contact_style` – radio-buttonset, default `left` – Icon Style
- `contact_icon_size` – text, default `16px` – Icon Size
- `contact_phone` – text, default `+47 900 99 000` – Phone
- `contact_email` – text, default `youremail@gmail.com` – E-mail
- `contact_email_label` – text – E-mail label
- `contact_location` – text – Location
- `contact_location_label` – text – Location label
- `contact_hours` – text, default `08:00 - 17:00` – Open Hours
- `contact_hours_details` – textarea – Open Hours - Details

## header_content
- `header-block-1` – select – Header Block 1
- `header-block-2` – select – Header Block 2
- `topbar_left` – textarea, default `<strong class="uppercase">Add anythin…` – HTML 1
- `topbar_right` – textarea – HTML 2
- `top_right_text` – textarea – HTML 3
- `nav_position_text_top` – textarea – HMTL 4
- `nav_position_text` – textarea – HTML 5

## header_dropdown
- `dropdown_bg` – color-alpha – Background color
- `dropdown_border_enabled` – checkbox, default `1` – Add border
- `dropdown_border` – color-alpha – Border Color
- `dropdown_shadow` – checkbox, default `1` – Add shadow
- `dropdown_nav_size` – slider, default `100` – Dropdown Content Size (%)
- `dropdown_radius` – radio-buttonset, default `0` – Dropdown radius
- `dropdown_arrow` – checkbox, default `1` – Add arrow
- `dropdown_style` – radio-image, default `default` – Link Style
- `dropdown_text` – radio-buttonset, default `light` – Text Color
- `dropdown_text_style` – radio-buttonset, default `simple` – Text Style

## header_nav_vertical
- `header_nav_vertical_icon_style` – radio-image, default `plain` – Icon
- `header_nav_vertical_height` – slider, default `50` – Height
- `header_nav_vertical_width` – slider, default `250` – Width
- `header_nav_vertical_tagline` – text – Tag line
- `header_nav_vertical_text` – text – Text
- `header_nav_vertical_text_color` – radio-image, default `dark` – Text color
- `header_nav_vertical_bg_color` – color-alpha – Background color
- `header_nav_vertical_fly_out_frontpage` – checkbox, default `1` – Keep open on front page
- `header_nav_vertical_fly_out_shadow` – checkbox, default `1` – Add shadow
- `header_nav_vertical_fly_out_text_color` – radio-image, default `light` – Text color
- `header_nav_vertical_fly_out_bg_color` – color-alpha – Background color

## header_newsletter
- `newsletter_icon_style` – radio-image, default `plain` – Icon Style
- `header_newsletter_label` – text, default `Newsletter` – Label
- `header_newsletter_block` – select, default `false` – Newsletter Block
- `header_newsletter_title` – text, default `Sign up for Newsletter` – Title
- `header_newsletter_sub_title` – text, default `Signup for our newsletter to get noti…` – Sub Title
- `header_newsletter_shortcode` – text – Form Shortcode
- `header_newsletter_bg` – image – Background Image
- `header_newsletter_height` – text, default `500px` – Height
- `header_newsletter_auto_open` – checkbox, default `false` – Auto Open
- `header_newsletter_auto_timer` – slider, default `3000` – Auto Timer
- `header_newsletter_auto_show` – select, default `always` – Auto Show
- `header_newsletter_version` – text, default `1` – Version

## header_search
- `search_icon_style` – radio-image – Search Icon Style
- `header_search_style` – radio-buttonset, default `dropdown` – Search Icon Type
- `header_search_form_style` – radio-buttonset – Search Form Style
- `search_placeholder` – text – Placeholder text
- `header_search_categories` – checkbox, default `0` – Search Categories
- `header_search_width` – slider, default `60` – Header Search form width

## header_wishlist
- `wishlist_icon` – select, default `heart` – Wishlist Icon
- `wishlist_icon_style` – radio-image – Wishlist Icon Style
- `wishlist_title` – checkbox, default `1` – Show Wishlist Title
- `header_wishlist_label` – text – Custom Title

## lightbox
- `flatsome_lightbox` – checkbox, default `1` – Enable Flatsome Lightbox
- `flatsome_lightbox_bg` – color – Lightbox background color
- `flatsome_lightbox_multi_gallery` – checkbox, default `0` – Use multiple galleries on a page

## notifications
- `cookie_notice` – checkbox, default `false`
- `cookie_notice_text` – textarea
- `privacy_policy_page` – select, default `false`
- `cookie_notice_button_style` – select
- `cookie_notice_text_color` – radio-image, default `light`
- `cookie_notice_bg_color` – color-alpha
- `cookie_notice_version` – text, default `1`

## pages
- `pages_template` – select, default `default` – Default - Page Template
- `default_title` – checkbox, default `0` – Show H1 Page title on the container (default), left sidebar and right sidebar templates.
- `page_top_excerpt` – checkbox, default `1` – Add excerpt content to top of pages.

## payment-icons
- `payment_icons` – sortable, default `array( 'visa` – Payment Icons
- `payment_icons_custom` – image – Custom Icons (Replace)
- `payment_icons_placement` – multicheck – Placement

## share
- `social_icons_style` – radio-image, default `outline` – Share Icons Style
- `social_icons` – multicheck – Share Icons
- `custom_share_icons` – textarea – Share Replace

## woocommerce_checkout
- `checkout_layout` – radio-buttonset – Checkout layout
- `facebook_login_checkout` – checkbox, default `0` – Social Login Buttons
- `checkout_terms_and_conditions` – radio-buttonset – Terms and conditions link style
- `terms_and_conditions_lightbox_buttons` – checkbox, default `1` – Terms and conditions "Agree" button
- `checkout_floating_labels` – checkbox, default `0` – Floating field labels
- `checkout_fields_email_first` – checkbox, default `0` – Move E-mail field to first position
- `checkout_sticky_sidebar` – checkbox, default `0` – Sticky sidebar
- `html_checkout_sidebar` – textarea – Checkout Sidebar content

## woocommerce_store_notice
- `woocommerce_store_notice_top` – checkbox, default `0` – Move store notice to the top
