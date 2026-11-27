# UX Builder element API

Verified against Flatsome 3.15.x core (`inc/builder/core/server/helpers/*.php`, element definitions in `inc/builder/shortcodes/`) and open-source integrations (Site Reviews' Flatsome integration). Counts in brackets = how often Flatsome's own 84 elements use the key/type.

## 1. Registration

```php
add_action( 'ux_builder_setup', 'my_register_elements' );   // fires only when UX Builder loads
function my_register_elements() {
	if ( ! function_exists( 'add_ux_builder_shortcode' ) ) { return; }
	add_ux_builder_shortcode( 'my_tag', array( /* keys below */ ) );
}
```

The front-end output still comes from your own `add_shortcode( 'my_tag', … )`. Register both; the builder entry is UI only.

### Element keys

| Key | Meaning |
|---|---|
| `name` [241] | Label in the "Add element" panel |
| `category` [79] | Panel group, e.g. `'Content'`, `'Layout'`, `'Shop'`; use your brand (`'Migi'`) for custom elements |
| `thumbnail` [142] | Image URL (SVG/PNG ~100×68) shown in the panel; a `data:image/svg+xml,…` URI works |
| `info` [18] | AngularJS text shown on the element in the tree, e.g. `'{{ title }}'` (option keys in camelCase) |
| `type` | `'container'` [24] when the element holds child elements (needs `[/tag]`) |
| `allow` [11] | Array of child tags a container accepts, e.g. `row` → `array( 'col' )` |
| `require` [10] | Array of parent tags the element must live in, e.g. `col` → `array( 'row' )`, `tab` → `array( 'tabgroup' )` |
| `nested` [5] | Allows the same container inside itself (rows/cols use `_inner` aliases instead) |
| `wrap` [42] | `false` = no extra builder wrapper around the rendered output (recommended for plugin widgets that render their own root) |
| `template` [24] | AngularJS preview template string (see §4). Without it the builder renders the real shortcode through PHP |
| `template_shortcode` | Callback that decides how the element is saved (Text uses it to save `[ux_text]` only when options are set) |
| `presets` [39] | Starter variations: `array( array( 'name' => 'Simple', 'thumbnail' => URL, 'content' => '[my_tag …]…[/my_tag]' ) )` |
| `priority` [12] | Order inside the category |
| `position` [17] | Placement hint for the options panel |
| `scripts` [7] / `styles` [3] | Assets the preview iframe needs: `array( 'handle' => URL )` |
| `inline`, `compile`, `overlay`, `resize`, `toolbar`, `hidden`, `message` | Rare; mirror an existing element if you need them |
| `options` | Array of option definitions (next section) |

Modify a built-in element: `ux_builder_edit_element( 'button', $changes )`; remove: `remove_ux_builder_shortcode( 'ux_instagram_feed' )`. Every registration passes through the filters `ux_builder_shortcode_data` (`$data, $tag`) and `ux_builder_shortcode_data_{$tag}` (`$data`) – use them to adjust built-in elements without re-registering.

## 2. Option types

Each option key becomes a shortcode attribute of the same name. Common keys for every type: `type`, `heading`, `default`, `description`, `conditions` (JS expression on other options, e.g. `'style === "outline"'`, `'!post && !link'`), `responsive` ⇒ adds `__md`/`__sm` attributes, `full_width`, `on_change` (live preview hints: `array( 'selector' => '.x', 'style' => 'color: {{ value }}' )` or `'class' => …`, `'recompile' => false`), `save_when_default` (store even when equal to default), `param_name` (attribute name if it must differ from the key).

| Type | Extra keys | Notes |
|---|---|---|
| `textfield` [102] | `placeholder`, `auto_focus` | Free text |
| `textarea` | `placeholder` | Multi-line |
| `text-editor` [2] | `height`, `tinymce` | Rich text, normally the element **content** (`$content`) |
| `select` [154] | `options` (`value => label`), `config` (`multiple`, `sortable`, `placeholder`, `postSelect`, `termSelect`) | `postSelect => array( 'post_type' => array( 'product' ) )` gives a searchable post picker; `termSelect => array( 'taxonomies' => 'product_cat' )` a term picker. Multiple values are saved comma-separated |
| `radio-buttons` [74] | `options` (`value => array( 'title' => 'Left', 'icon' => 'dashicons-…' )`) | Button group; good for 2–5 choices |
| `checkbox` [25] | – | Saves the strings `'true'` / `'false'` (default is a string too) – compare with `'true' === $atts['x']` |
| `slider` [77] | `min`, `max`, `step`, `unit` (`px`, `%`, `rem`…), `vertical` | Numeric range |
| `scrubfield` [26] | `min`, `max`, `step`, `unit`, `placeholder`, `helpers` | Drag-to-change number **with unit** (`30px`, `92vh`) – used for padding/height/gap |
| `margins` [11] | `min`, `max`, `step`, `full_width` | Four values `top right bottom left` (padding/margin) |
| `colorpicker` [25] | `format` (`rgb`/`hex`), `alpha` (true), `position`, `helpers` | Colour with optional alpha |
| `image` [11] | `thumb_size`, `bg_size`, `bg_position` | Media library picker, saves attachment ID |
| `gallery` [2] | – | Several attachment IDs, comma-separated |
| `col-slider` [2] | – | Column span 1–12 (`col`'s `span`) |
| `group` [51] | `heading`, `collapsed`, `options` | Collapsible group of options; only for UI, keys inside are still top-level attributes |

Reuse Flatsome's own option sets when available (paths relative to the parent theme):
`inc/builder/shortcodes/commons/advanced.php` (class + visibility), `…/commons/visibility.php`, `…/values/animate.php`. Guard with `file_exists( get_template_directory() . '/inc/builder/shortcodes/commons/advanced.php' )` and fall back to your own `class` textfield.

### Custom post pickers

A `select` with `config => array( 'postSelect' => 'my_source' )` (string instead of array) makes UX Builder call `wp_ajax_ux_builder_get_posts` / `wp_ajax_ux_builder_search_posts` with `option=my_source`. Hook both at priority 1, check `check_ajax_referer( "ux-builder-{$post_id}", 'security' )`, and answer `wp_send_json_success( array( array( 'id' => …, 'title' => … ) ) )` only when `option` matches your prefix.

## 3. Containers and children

```php
add_ux_builder_shortcode( 'mg_tabs', array( 'type' => 'container', 'allow' => array( 'mg_tab' ), 'name' => 'Tabs', … ) );
add_ux_builder_shortcode( 'mg_tab',  array( 'type' => 'container', 'require' => array( 'mg_tabs' ), 'name' => 'Tab', … ) );
```

Saved content: `[mg_tabs][mg_tab title="A"]…[/mg_tab][/mg_tabs]`. In the shortcode callback render children with `do_shortcode( $content )`.

## 4. AngularJS preview templates (optional)

Only needed for instant previews without a PHP round trip.

```html
<span id="{{:: shortcode.$id }}">               <!-- REQUIRED root, makes the element selectable -->
  <div class="mg-counter" ng-style="{ color: shortcode.options.textColor }">
    {{ shortcode.options.to }}{{ shortcode.options.suffix }}
  </div>
  <style scope="scope">#{{:: shortcode.$id }} .mg-counter:hover { opacity: .8 }</style>
</span>
```

- Option keys are **camelCase** in templates: `bg_color` → `shortcode.options.bgColor`.
- Child elements of a container render where you put `<content></content>`; raw text content of a non-container renders with `ng-bind-html="shortcode.content | autop | html"`.
- Keep business logic in PHP; templates are only for the editor.

## 5. Hooks & helpers

| Hook / function | Use |
|---|---|
| `ux_builder_setup` (action) | Register elements, templates, post types |
| `ux_builder_enqueue_scripts` (action, `$page` = `'editor'` or `'iframe'`) | Add editor/preview CSS/JS, e.g. `wp_add_inline_style( 'ux-builder-core', … )` |
| `add_ux_builder_post_type( 'my_cpt' )` | Enable UX Builder on a custom post type |
| `ux_builder_add_template( $id, $data )` | Add a page to the *Templates* library (see `templates-kits.md`) |
| `ux_builder_is_active()` / `ux_builder_is_editor()` / `ux_builder_is_iframe()` | Detect builder context (skip lazy JS, analytics, redirects) |
| `ux_builder_edit_url( $post_id )` | "Edit with UX Builder" link |
| `add_ux_builder_breakpoint()` / `get_ux_builder_breakpoints()` | Custom preview widths |
| `flatsome_ux_builder_thumbnail( 'name' )` | URL of a built-in thumbnail (theme only) |

Detection for plugins: `'flatsome' === get_template()` (or `wp_get_theme( get_template() )->get( 'TextDomain' )`), and always `function_exists( 'add_ux_builder_shortcode' )` inside `ux_builder_setup`. Gate on a minimum version if you rely on newer options: `version_compare( wp_get_theme( get_template() )->get( 'Version' ), '3.15', '>=' )`.

## 6. Gotchas

- Attributes with empty values are not saved unless `save_when_default` is set; defaults in PHP and the builder must match or previews lie.
- Values are saved as strings; cast in PHP (`absint`, `(float)`, `'true' === $v`).
- Never `echo` from a shortcode – return the HTML.
- Escape everything (`esc_attr`, `esc_url`, `wp_kses_post`); attributes come from editors.
- Nonce-protected forms inside elements break on page cache – use AJAX endpoints with server-side validation + rate limiting instead.
- A container element that renders its own wrapper should still output `do_shortcode( $content )` exactly once.
- `wrap => false` elements must output a single root element, or selection in the tree becomes erratic.
