#!/usr/bin/env python3
"""Lint Flatsome UX Builder shortcode content before it goes into a page, block or template.

Usage:
  python3 fs_lint.py page.txt [more.txt ...] [--allow mg_,contact-form-7] [--json] [--strict]

Checks
  errors   : unbalanced / mis-nested tags, row/col structure, tab/accordion parents,
             nested row not using row_inner, placeholder image ids
  warnings : unknown tags or params, invalid select values, __sm/__md on non-responsive
             params, ux_html share above 10%, columns per row over 12
  info     : element counts and the native ratio

Exit code 1 when there are errors (or warnings with --strict).
The element dictionary lives in ../data/flatsome-elements.json (Flatsome 3.15.x parameter names).
"""
import json
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
DATA = json.load(open(os.path.join(HERE, '..', 'data', 'flatsome-elements.json'), encoding='utf-8'))['elements']

# Shortcodes that are fine to use but are not UX Builder elements in the dictionary.
DEFAULT_ALLOW = ('mg_', 'contact-form-7', 'block', 'ux_shortcode', 'woocommerce_', 'products', 'product_page',
                 'add_to_cart', 'gallery', 'caption', 'embed', 'audio', 'video', 'playlist', 'fl_', 'yith_', 'wpforms',
                 'fluentform', 'site_review')
# Builder containers that must be closed even if the dictionary says otherwise.
ALWAYS_CONTAINER = {'section', 'section_inner', 'row', 'col', 'ux_text', 'ux_banner', 'text_box', 'ux_slider', 'tabgroup',
                    'tabgroup_vertical', 'tab', 'accordion', 'accordion-item', 'ux_stack', 'ux_banner_grid', 'col_grid',
                    'featured_box', 'ux_image_box', 'lightbox', 'team_member', 'testimonial', 'message_box',
                    'ux_menu', 'scroll_to', 'ux_price_table'}
GLOBAL_PARAMS = {'class', 'visibility', 'label', 'id'}
# Saved shortcode tag -> builder element name (used in 'allow' lists).
BUILDER_NAME = {'ux_text': 'text'}
ROW_TAGS = re.compile(r'^row(_inner(_\d)?)?$')
COL_TAGS = re.compile(r'^col(_inner(_\d)?)?$')
STACK_TAGS = re.compile(r'^ux_stack(_inner(_\d)?)?$')
PLACEHOLDER_IMG = re.compile(r'^(|0|x+|id|image_?id|img|todo|\{.*\}|<.*>)$', re.I)
KIT_MEDIA = re.compile(r'^\{\{media:[a-z0-9_\-]+\}\}$')  # kit tokens, replaced by attachment IDs on import
TOKEN = re.compile(r'\[(/?)([a-zA-Z0-9_\-]+)((?:\s+[^\]]*?)?)\s*(/?)\]')
ATTR = re.compile(r'([a-zA-Z0-9_\-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))')


def is_container(tag):
    if tag in ALWAYS_CONTAINER or ROW_TAGS.match(tag) or COL_TAGS.match(tag) or STACK_TAGS.match(tag):
        return True
    return bool(DATA.get(tag, {}).get('container'))


def base_of(tag):
    e = DATA.get(tag)
    return e.get('alias_of', tag) if e else tag


def allowed_tag(tag, allow):
    return tag in DATA or any(tag.startswith(a) for a in allow)


def lint(text, allow, name='input'):
    errors, warnings, info = [], [], []
    stack = []          # (tag, line, attrs, children_spans)
    counts = {}
    line_of = lambda pos: text.count('\n', 0, pos) + 1
    closers = {m.group(2) for m in TOKEN.finditer(text) if m.group(1)}

    for m in TOKEN.finditer(text):
        closing, tag, raw, selfclose = m.group(1) == '/', m.group(2), m.group(3) or '', m.group(4) == '/'
        ln = line_of(m.start())
        if not allowed_tag(tag, allow):
            # Only flag things that look like shortcodes (lowercase, has underscore or known style).
            if re.match(r'^[a-z][a-z0-9_\-]*$', tag):
                warnings.append(f'{name}:{ln}: unknown shortcode [{tag}] – typo, or add its prefix with --allow')
            continue
        if closing:
            if not stack:
                errors.append(f'{name}:{ln}: closing [/{tag}] without an opening tag')
                continue
            # Pop until we find the matching open tag; anything popped on the way was left open.
            idx = next((i for i in range(len(stack) - 1, -1, -1) if stack[i][0] == tag), None)
            if idx is None:
                errors.append(f'{name}:{ln}: closing [/{tag}] does not match open [{stack[-1][0]}] (line {stack[-1][1]})')
                continue
            for t, l, *_ in stack[idx + 1:]:
                if is_container(t):
                    errors.append(f'{name}:{l}: [{t}] is never closed before [/{tag}] on line {ln}')
            open_tag, open_ln, attrs, spans = stack[idx]
            del stack[idx:]
            if ROW_TAGS.match(tag):
                for bp, total in spans.items():
                    if total > 12 and bp != 'mobile':
                        info.append(f'{name}:{open_ln}: [{tag}] columns add up to {total}/12 on {bp} – they wrap onto a new line (normal for grids)')
            continue

        counts[tag] = counts.get(tag, 0) + 1
        attrs = {a.group(1): next(v for v in a.groups()[1:] if v is not None) for a in ATTR.finditer(raw)}
        parent = stack[-1][0] if stack else None

        # ---- structure
        if COL_TAGS.match(tag):
            want = 'row' if tag == 'col' else tag.replace('col', 'row', 1)
            if parent != want:
                errors.append(f'{name}:{ln}: [{tag}] must sit directly inside [{want}] (found {"[" + parent + "]" if parent else "top level"})')
            elif stack:
                spans = stack[-1][3]
                for bp, key in (('desktop', 'span'), ('tablet', 'span__md'), ('mobile', 'span__sm')):
                    v = attrs.get(key) or (attrs.get('span') if bp == 'tablet' else None) or ('12' if bp == 'mobile' else attrs.get('span', '12'))
                    try:
                        spans[bp] = spans.get(bp, 0) + int(float(v))
                    except ValueError:
                        warnings.append(f'{name}:{ln}: [{tag}] {key}="{v}" is not a number')
        if parent and ROW_TAGS.match(parent) and not COL_TAGS.match(tag):
            errors.append(f'{name}:{ln}: [{tag}] placed directly in [{parent}] – wrap it in a column ([{parent.replace("row", "col", 1)}])')
        if ROW_TAGS.match(tag):
            col_parents = [s[0] for s in stack if COL_TAGS.match(s[0])]
            if tag == 'row' and col_parents:
                errors.append(f'{name}:{ln}: [row] nested inside a column – use [row_inner] (and [col_inner]); deeper levels use _1 / _2')
            if tag.startswith('row_inner') and not col_parents:
                warnings.append(f'{name}:{ln}: [{tag}] outside a column – use [row]')
        if tag == 'section' and any(s[0] == 'section' for s in stack):
            errors.append(f'{name}:{ln}: [section] inside another [section] – use [section_inner] or close the first one')
        for child, parents in (('tab', ('tabgroup', 'tabgroup_vertical')), ('accordion-item', ('accordion',)), ('ux_menu_link', ('ux_menu',)), ('ux_menu_title', ('ux_menu',))):
            if tag == child and parent not in parents:
                errors.append(f'{name}:{ln}: [{tag}] must be inside [{"] or [".join(parents)}]')
        if tag == 'text_box' and parent not in ('ux_banner',) and not any(s[0] == 'ux_banner' for s in stack):
            warnings.append(f'{name}:{ln}: [text_box] is meant to sit inside [ux_banner]')
        allowed_children = DATA.get(base_of(parent) if parent else '', {}).get('allow') or []
        if parent and allowed_children and base_of(tag) not in allowed_children and BUILDER_NAME.get(tag, tag) not in allowed_children and not COL_TAGS.match(tag) and not any(tag.startswith(a) for a in allow):
            info.append(f'{name}:{ln}: [{tag}] is not in the allowed children of [{parent}] ({", ".join(allowed_children)}) – UX Builder may refuse to edit it')

        # ---- params
        el = DATA.get(base_of(tag))
        if el:
            params = el['params']
            for k, v in attrs.items():
                key = re.sub(r'__(sm|md)$', '', k)
                if k in GLOBAL_PARAMS:
                    continue
                if key not in params:
                    warnings.append(f'{name}:{ln}: [{tag}] unknown param "{k}"')
                    continue
                p = params[key]
                if k != key and not p['responsive']:
                    warnings.append(f'{name}:{ln}: [{tag}] "{key}" has no mobile/tablet variant – "{k}" is ignored')
                if p['type'] in ('select', 'radio-buttons') and p['values'] and v not in p['values'] and v != '' and len(p['values']) > 1:
                    if not (key in ('animate',) and v == 'none'):
                        warnings.append(f'{name}:{ln}: [{tag}] {k}="{v}" not in known values ({", ".join(x or "''" for x in p["values"][:12])})')
        if tag == 'ux_image' and PLACEHOLDER_IMG.match(attrs.get('id', '')) and not KIT_MEDIA.match(attrs.get('id', '')):
            errors.append(f'{name}:{ln}: [ux_image] id="{attrs.get("id", "")}" – set a real attachment ID (or remove the element)')
        for k in ('bg', 'img'):
            if k in attrs and PLACEHOLDER_IMG.match(attrs[k]) and attrs[k] != '' and not KIT_MEDIA.match(attrs[k]):
                errors.append(f'{name}:{ln}: [{tag}] {k}="{attrs[k]}" looks like a placeholder – use an attachment ID or image URL')

        if is_container(tag) and not selfclose and tag in closers:
            stack.append((tag, ln, attrs, {}))
        elif is_container(tag) and tag not in ('ux_text',) and tag not in closers and not selfclose:
            errors.append(f'{name}:{ln}: [{tag}] is a container but [/{tag}] never appears')

    for t, l, *_ in stack:
        errors.append(f'{name}:{l}: [{t}] is never closed')

    total = sum(counts.values()) or 1
    html_share = counts.get('ux_html', 0) / total
    if html_share > 0.10:
        warnings.append(f'{name}: ux_html is {html_share:.0%} of elements – keep layout native (target ≥90% native elements)')
    first = next((m.group(2) for m in TOKEN.finditer(text) if not m.group(1)), None)
    if counts and not counts.get('section') and first not in ('ux_banner', 'ux_slider', 'block', 'ux_banner_grid'):
        warnings.append(f'{name}: no [section] – pages should be section-first (section → row → col → element)')
    info.append(f'{name}: {total if counts else 0} elements, {len(counts)} kinds, native ratio {1 - html_share:.0%}; top: ' +
                ', '.join(f'{k}×{v}' for k, v in sorted(counts.items(), key=lambda x: -x[1])[:8]))
    return errors, warnings, info


def main(argv):
    files = [a for a in argv if not a.startswith('--')]
    allow = list(DEFAULT_ALLOW)
    if '--allow' in argv:
        allow += [x for x in argv[argv.index('--allow') + 1].split(',') if x]
        files = [f for f in files if f != argv[argv.index('--allow') + 1]]
    if not files:
        print(__doc__)
        return 2
    out, bad = {}, False
    for f in files:
        text = sys.stdin.read() if f == '-' else open(f, encoding='utf-8').read()
        e, w, i = lint(text, allow, os.path.basename(f))
        out[f] = {'errors': e, 'warnings': w, 'info': i}
        bad = bad or bool(e) or ('--strict' in argv and bool(w))
    if '--json' in argv:
        print(json.dumps(out, ensure_ascii=False, indent=1))
    else:
        for f, r in out.items():
            for k, sym in (('errors', 'ERROR'), ('warnings', 'WARN'), ('info', 'INFO')):
                for line in r[k]:
                    print(f'{sym:5} {line}')
        n_e = sum(len(r['errors']) for r in out.values())
        n_w = sum(len(r['warnings']) for r in out.values())
        print(f'\n{n_e} error(s), {n_w} warning(s)')
    return 1 if bad else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv[1:]))
