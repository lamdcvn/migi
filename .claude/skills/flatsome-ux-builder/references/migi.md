# Migi conventions (repo lamdcvn/migi, site.migi.vn)

## Naming
- Plugins `mg-<name>`, PHP classes `MG<X>_…`, functions `mg_…`, CSS classes `mg-…`, shortcodes `mg_…`, options `mg<x>_settings`.
- Shared files are **identical** in every plugin – edit in `mg-bulk-deals`, then copy: `includes/mg-core.php`, `assets/css/mg-ui.css`, `assets/css/mg-admin.css`.
- All admin pages live under the top-level **Migi** menu (`mg_core_register_module()`), headers via `mg_core_admin_header()`.

## Migi elements in UX Builder
When Flatsome is active, these shortcodes are registered as UX Builder elements in the **Migi** category (`mg_core_ux_element()` in `mg-core.php`):

| Element | Shortcode | Plugin |
|---|---|---|
| Tính điện mặt trời | `[mg_solar_calc title="" type="ongrid|hybrid|offgrid"]` | mg-solar-calc |
| Tính vật liệu | `[mg_material_calc id="PRODUCT_ID"]` | mg-material-calc |
| Build PC | `[mg_pc_builder title="" presets="yes|no"]` | mg-pc-builder |
| Chạy chữ | `[mg_marquee items="A|B|C" speed="30" reverse="no"]` | mg-motion |
| Đếm số | `[mg_counter to="997" prefix="" suffix="+" label=""]` | mg-motion |

Bulk deals and the material calculator also render automatically on WooCommerce product pages (no element needed).

## Migi Motion classes (Advanced → Class)
`mg-reveal` (+ `mg-reveal-left|right|zoom|blur|flip|wipe`, delays `mg-d1…mg-d5`), `mg-stagger` (on a row/col), `mg-split` (heading letters), `mg-shine`, `mg-underline`, `mg-count` (text containing a number), `mg-marquee`, `mg-kenburns` (section/banner background), `mg-parallax` (section), `mg-zoom-img`, `mg-fx-lasers|aurora|bokeh|grid|grain|lines` (section decoration), `mg-btn-shine`, `mg-glow-btn`, `mg-magnetic`, `mg-tilt`, `mg-spotlight`, `mg-lift`, `mg-float`, `mg-pulse`, `mg-shake`, `mg-no-motion`.
- Prefer these over Flatsome's `animate="…"` when mg-motion is installed (lighter, respects reduced motion) – do not combine both on one element.
- Industry presets (`motion_preset` in a kit): `nightlife`, `fnb`, `spa`, `realestate`, `tech`, `edu`, `interior`.
- Never put `mg-reveal`/`mg-split` on the LCP hero image; text above the fold may use `mg-split`.

## Visual tone (site.migi.vn)
Manrope, accent orange gradient `#f5a000 → #ff8a00`, secondary indigo `#5b5cf0`, ink `#101828`, muted `#667085`, cards radius 22px, buttons radius 13px, soft pastel glows; dark mode via `[data-theme="dark"]`. For a Migi-branded kit set `color_primary` `#ff8a00`, `color_secondary` `#5b5cf0`, `button_radius` `13px`, headings/text font Manrope.

## Kit store checklist (before uploading to site.migi.vn)
1. `fs_kit.py check` passes (0 errors, warnings reviewed).
2. Every page opens in UX Builder; each section has a `label`.
3. Mobile (375px) and tablet (768px) previews checked; no horizontal scroll.
4. Lighthouse mobile ≥ 85 on the demo, CLS < 0.1.
5. Texts are real Vietnamese copy for the industry (no lorem ipsum), images licensed for redistribution.
6. `preview.jpg` 1200×800, `description` ≤ 160 characters, `requires` lists every plugin used.
