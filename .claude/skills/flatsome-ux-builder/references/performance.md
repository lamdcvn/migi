# Flatsome performance checklist

Adapted from the MIT-licensed *flatsome-performance-optimization* skill (thanhtungtav4/Optimize-Website-Skills) plus Flatsome option keys.

## Measure first
- Record URL, device (mobile 412×823, DPR 1.75 is Lighthouse's default), Lighthouse version.
- 3 valid runs, use the median of Score / FCP / LCP / TBT / CLS. One change per experiment, re-measure.
- Identify the LCP element (DevTools → Performance → LCP) before touching anything.

## Theme settings (Customizer → Advanced → Performance / Lazy loading; keys in `customizer.md`)
- Lazy load images on, but **not** for the first hero/banner image.
- Disable features not used: Instagram, portfolio, wishlist, quick view, blog features, unused social icons.
- Fonts: one family (two weights) for text + optional display family; use "font-display: swap"; avoid Google Fonts for > 2 families (self-host instead).
- Turn off UX Builder animations (`animate="…"`) on above-the-fold elements; they delay LCP and can shift layout.

## Content rules for templates
- Hero: one image, real `width`/`height`, `fetchpriority="high"`, not inside `ux_slider` on mobile (hide the slider with `visibility="hide-for-small"` and show a single banner with `show-for-small`).
- Image sizes: hero 1920px max, cards 600–900px, WebP/AVIF, ≤ 300 KB.
- `ux_products` / `blog_posts`: limit items on the home page (8 / 3); avoid several product sliders on one page.
- Avoid `ux_html` iframes (maps, videos) above the fold; use `video_button` (lightbox) or a static image linking out.
- Each extra `[section]` with a video background costs a lot – one per page at most.

## Assets
- Child theme: enqueue only what a template needs; no jQuery plugins for effects CSS can do.
- Defer/delay third-party scripts (chat widgets, pixels, heatmaps) until interaction – keep consent and conversion tracking working, and state the trade-off.
- WooCommerce: cart fragments only on shop pages if the mini cart is not in the header.
- Critical CSS / async CSS only with a CLS check on header, hero, product gallery and sticky elements – roll back on any shift.

## Verify
- Re-run 3 times, compare medians, check CLS < 0.1, LCP < 2.5s (mobile field data if available).
- Check UX Builder still opens the page and the mobile menu, sticky header and cart still work.
