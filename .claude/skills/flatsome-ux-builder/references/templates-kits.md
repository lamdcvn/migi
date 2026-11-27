# Templates, UX Blocks and theme kits

## UX Builder *Templates* library

Pages appear under UX Builder → Templates (the "Add template" dialog) when registered inside `ux_builder_setup`:

```php
add_action( 'ux_builder_setup', function () {
	if ( ! function_exists( 'ux_builder_add_template' ) ) { return; }
	ux_builder_add_template( 'migi_interior_home', array(
		'post_types' => array( 'page' ),                 // where it can be inserted
		'category'   => 'Migi · Nội thất',               // group in the dialog
		'name'       => 'Nội thất Luxe – Trang chủ',
		'thumbnail'  => 'https://…/thumb.jpg',           // ~ 300×200 screenshot
		'template'   => 'page-blank.php',                // page template applied on insert
		'content'    => $shortcode_string,               // the page content
	) );
} );
```

Flatsome registers its own demos the same way (`flatsome_home_*`). Remove one with `ux_builder_remove_template( $id )`.
Template `content` must already contain real attachment IDs – register a kit's templates only after its media has been imported.

## UX Blocks (post type `blocks`)

Reusable fragments edited with UX Builder (Flatsome → UX Blocks). Insert anywhere with `[block id="footer-luxe"]` (slug or ID). Typical uses:
- footer (Customizer → Footer → `footer_block`), shop/category header, blog header, mega menu content, off-canvas menus, popups (`[lightbox]` + block);
- shared CTA or contact strips across many pages – edit once, update everywhere.

Create programmatically: `wp_insert_post( array( 'post_type' => 'blocks', 'post_status' => 'publish', 'post_name' => 'footer-luxe', 'post_title' => 'Footer Luxe', 'post_content' => $shortcodes ) )`.

## Flatsome page templates

Set with `update_post_meta( $page_id, '_wp_page_template', … )` – check `locate_template( $file )` first. Common files: `page-blank.php` (full width, no title), `page-blank-title-center.php`, `page-transparent-header.php`, `page-transparent-header-light.php` (light text over dark hero), `page-right-sidebar.php`, `page-left-sidebar.php`, `page-blank-landingpage.php` (no header/footer – landing pages), `page-header-on-scroll.php` (header appears after scrolling), `page-single-page-nav.php` / `page-single-page-nav-transparent.php` / `page-single-page-nav-transparent-light.php` (one-page dot navigation between sections – good for landing pages with `label`-ed sections), `page-blank-featured.php`, `page-blank-sub-nav-vertical.php`. (File list verified against Flatsome 3.15.)

## Theme kit format (kho giao diện site.migi.vn)

A kit is a zip with this layout:

```
mg-kit-interior-luxe/
├── kit.json
├── preview.jpg               # 1200×800 screenshot for the store listing
├── pages/home.txt            # UX Builder shortcodes, one file per page
├── pages/about.txt
├── blocks/footer.txt         # UX Blocks
└── media/hero.jpg …          # images referenced as {{media:hero}}
```

`kit.json`:

```json
{
  "id": "mg-kit-interior-luxe",
  "name": "Nội thất Luxe",
  "version": "1.0.0",
  "industry": "interior",
  "description": "Landing + 4 trang cho công ty thiết kế thi công nội thất.",
  "flatsome": ">=3.15",
  "motion_preset": "interior",
  "requires": ["mg-motion", "contact-form-7"],
  "theme_mods": {
    "color_primary": "#8a6a4f",
    "color_secondary": "#2f3a36",
    "site_width": "1200",
    "header_height": "80",
    "button_radius": "99px"
  },
  "pages": [
    { "slug": "home",  "title": "Trang chủ", "file": "pages/home.txt",  "template": "page-transparent-header-light.php", "front": true },
    { "slug": "about", "title": "Giới thiệu", "file": "pages/about.txt", "template": "page-blank.php" }
  ],
  "blocks": [
    { "slug": "footer-luxe", "title": "Footer Luxe", "file": "blocks/footer.txt", "theme_mod": "footer_block" }
  ],
  "media": {
    "hero":  { "file": "media/hero.jpg",  "alt": "Phòng khách hiện đại" },
    "logo":  { "file": "media/logo.png",  "alt": "Logo" }
  }
}
```

Rules:
- Pages/blocks reference images as `{{media:key}}` in `id=`, `bg=`, `img=`, `image=` attributes – never hard-coded attachment IDs (they differ per site). `fs_lint.py` accepts these tokens.
- Block references inside pages use `[block id="footer-luxe"]` (slug, not ID).
- `theme_mods` keys must exist in `references/customizer.md` (`fs_kit.py` checks them). Typography mods are arrays: `"type_headings": {"font-family": "Be Vietnam Pro", "variant": "700"}`.
- No customer data, no tracking codes, no license keys in a kit.
- Keep images ≤ 300 KB each (WebP/JPEG, 1920px max width for heroes, 900px for cards).

Workflow:

```bash
python3 scripts/fs_kit.py new   kits/interior-luxe --industry interior   # skeleton
python3 scripts/fs_kit.py check kits/interior-luxe                       # schema + lint every page/block + media + theme_mods
python3 scripts/fs_kit.py build kits/interior-luxe                       # dist/mg-kit-interior-luxe-1.0.0.zip
```

Import on a site: **Migi → Hiệu ứng → Kho giao diện** (mg-motion) → upload the zip → *Cài kit*. The importer sideloads media, replaces `{{media:…}}` with attachment IDs, creates UX Blocks then pages, sets page templates, applies `theme_mods` (after saving the previous values for rollback), sets the mg-motion preset, optionally makes the home page the front page, and registers every page in the UX Builder Templates library under "Migi · <industry>".

Store listing (site.migi.vn): use `name`, `description`, `industry`, `preview.jpg`, `requires`, `version`; the zip itself is the download.
