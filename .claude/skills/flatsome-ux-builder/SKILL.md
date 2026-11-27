---
name: flatsome-ux-builder
description: Build and customize sites on the Flatsome WordPress theme – UX Builder shortcode pages, landing pages and template kits (kho giao diện site.migi.vn), custom UX Builder elements for plugins (add_ux_builder_shortcode), UX Builder template library entries, Customizer/theme_mod settings, child-theme tweaks and Flatsome performance. Use whenever the task mentions Flatsome, UX Builder, [section]/[row]/[col]/[ux_banner] shortcodes, a Flatsome page/template/kit, or registering a plugin shortcode as a Flatsome element.
---

# Flatsome UX Builder

Flatsome (classic theme, UX Themes) builds pages with **UX Builder**, which saves plain WordPress shortcodes in `post_content`. Anything you write must reopen and stay editable in UX Builder.

> Not this skill: the newer *Flatsome Block Theme + Site Builder* stores Gutenberg blocks, not UX Builder shortcodes. Check `get_template() === 'flatsome'` and the presence of `add_ux_builder_shortcode()` before using anything here.

## Non-negotiable rules

1. **Section-first tree**: `section → row → col → element`. Nested layout inside a column uses `row_inner → col_inner` (deeper: `_1`, `_2`). Never put elements directly in a row, never nest `[row]` in `[col]`.
2. **Native ≥ 90%**: build with real elements (`ux_text`, `ux_image`, `ux_banner` + `text_box`, `button`, `featured_box`, `ux_image_box`, `title`, `gap`, `divider`, `ux_slider`, `tabgroup`, `accordion`, `ux_products`, `blog_posts`…). `ux_html` only for a small embed that has no element – never as the layout engine.
3. **Only real parameters**: every attribute must exist in `references/shortcodes.md`; responsive variants (`__md` tablet, `__sm` mobile) only where marked ↕. Invented attributes are silently dropped and break editing.
4. **Mobile is part of the build**: set `span__sm`, `padding__sm`, `font_size__sm`, `height__sm` where the desktop value would not fit a 375px screen.
5. **Real media**: `ux_image id`, `ux_banner bg`, `section bg` take an attachment ID (or URL for `bg`). Never ship placeholders – import images first or leave the attribute out.
6. **Styling through the builder, not inline CSS**: colours/spacing via element options; reusable look via the element's `class` (Advanced → Class) + one stylesheet; global look via Customizer theme_mods.
7. **Lint before delivering**: `python3 scripts/fs_lint.py page.txt` must report 0 errors.

## Workflow A – page, landing page or template for the theme library

1. **Brief → section plan** (write it down, one line per section): purpose, element stack, mobile behaviour, CTA. Pick patterns from `references/patterns.md`; vary hero/section order between templates (do not clone the previous kit).
2. **Write shortcodes** into `pages/<slug>.txt`, one element per line, containers indented by nesting depth. Labels: `label="Hero"` on every section (shows in the builder tree).
3. **Lint**: `python3 scripts/fs_lint.py pages/*.txt` → fix every ERROR, review every WARN.
4. **Theme settings**: put colours, fonts, header, footer, widths in `kit.json → theme_mods` using keys from `references/customizer.md` – not hard-coded in page content.
5. **Package** as a kit (`references/templates-kits.md`): `python3 scripts/fs_kit.py build <kit-dir>` → validates every page, writes `dist/<kit-id>.zip` (+ a PHP snippet that registers the pages in the UX Builder *Templates* library).
6. **Verify on a Flatsome site**: open each page in UX Builder (tree intact, every element selectable), check desktop / tablet / mobile previews, run PageSpeed on the public URL.

## Workflow B – plugin shortcode as a UX Builder element

Read `references/element-api.md` first. Short version:

```php
add_action( 'ux_builder_setup', function () {
	if ( ! function_exists( 'add_ux_builder_shortcode' ) ) { return; }
	add_ux_builder_shortcode( 'mg_counter', array(
		'name'      => 'Đếm số',
		'category'  => 'Migi',
		'thumbnail' => plugins_url( 'assets/ux/counter.svg', __FILE__ ),
		'info'      => '{{ label }}',          // text shown on the element in the tree
		'wrap'      => false,                  // render the plugin markup as-is
		'options'   => array(
			'to'     => array( 'type' => 'textfield', 'heading' => 'Số', 'default' => '1000' ),
			'suffix' => array( 'type' => 'textfield', 'heading' => 'Hậu tố', 'default' => '+' ),
			'label'  => array( 'type' => 'textfield', 'heading' => 'Nhãn', 'default' => '' ),
			'advanced_options' => array( 'type' => 'group', 'heading' => 'Advanced', 'options' => array(
				'class' => array( 'type' => 'textfield', 'heading' => 'Class', 'default' => '' ),
			) ),
		),
	) );
} );
```

- The shortcode itself is still registered with `add_shortcode()` so it renders everywhere; the builder entry only adds the UI.
- Options become shortcode attributes with the same keys; keep defaults identical to the shortcode's `shortcode_atts`.
- Scaffold quickly: `python3 scripts/fs_scaffold_element.py spec.json > element.php`.
- JS-driven widgets: the builder re-renders an element by replacing its HTML inside the preview iframe, so make init idempotent and re-run it when new markup appears (a `MutationObserver` on `document.body`, enabled only when `ux_builder_is_iframe()` is true). If hover/click handlers make the element hard to select, add `.uxb-draggable > .your-root { pointer-events: none; }` through `ux_builder_enqueue_scripts` (page `iframe`).

## Workflow C – Customizer / theme options

- All Flatsome options are **theme_mods** (`references/customizer.md`). Apply with `set_theme_mod()` in a kit importer or `wp theme mod set color_primary '#ff8a00'`.
- Child theme for code changes (template overrides, `functions.php`); never edit the parent theme.
- Custom CSS belongs in the child theme stylesheet or `html_custom_css` (kit-scoped); target Flatsome classes (`.header-main`, `.nav > li > a`, `.button.primary`, `.box-text`, `.product-small`), not generated IDs.
- Reusable headers/footers/shop headers: create a **UX Block** (post type `blocks`) and insert with `[block id="slug-or-id"]`; select it in Customizer (e.g. `footer_block`).

## Workflow D – performance

Follow `references/performance.md`: measure first (3 runs, median), fix the LCP image (one `<img>`, real width/height, `fetchpriority="high"`, no lazy-load / animate on the first banner), keep sliders off the first screen on mobile, limit fonts, defer third-party scripts, then re-measure. Do not remove consent or conversion tracking silently.

## Migi conventions

When working in this repository (mg-* plugins, site.migi.vn) also read `references/migi.md`: prefixes, the shared `mg-ui` tokens, Migi elements already available in UX Builder, mg-motion effect classes for the **Class** field, and the kit format used by the theme library.

## Files

| File | Use it for |
|---|---|
| `references/shortcodes.md` | Every element: parameters, types, defaults, allowed values, ↕ responsive, parent/children |
| `references/patterns.md` | Section recipes (hero, features, stats, products, testimonials, FAQ, pricing, contact, CTA, footer block) |
| `references/element-api.md` | `add_ux_builder_shortcode` keys, all option types, containers/children, presets, AngularJS templates, hooks, helpers |
| `references/customizer.md` | 465 theme_mod keys by Customizer section |
| `references/templates-kits.md` | UX Builder template library, UX Blocks, page templates, kit format + import/export |
| `references/performance.md` | Flatsome speed checklist |
| `references/migi.md` | Migi-specific conventions |
| `scripts/fs_lint.py` | Validate shortcode files (structure, params, values, native ratio) |
| `scripts/fs_scaffold_element.py` | Generate a UX Builder element registration from a JSON spec |
| `scripts/fs_kit.py` | Validate and package a template kit |
| `data/flatsome-elements.json` | Machine-readable element dictionary used by the scripts |
