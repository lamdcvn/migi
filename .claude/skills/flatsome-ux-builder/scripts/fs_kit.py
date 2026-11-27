#!/usr/bin/env python3
"""Create, check and package a Flatsome theme kit (format: references/templates-kits.md).

  python3 fs_kit.py new   <dir> [--industry interior] [--name "Nội thất Luxe"]
  python3 fs_kit.py check <dir>            # schema, files, media tokens, theme_mods, lint of every page/block
  python3 fs_kit.py build <dir> [--out dist]
"""
import json
import os
import re
import sys
import zipfile

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import fs_lint  # noqa: E402

MODS = json.load(open(os.path.join(HERE, '..', 'data', 'flatsome-theme-mods.json'), encoding='utf-8'))['keys']
PRESETS = ('nightlife', 'fnb', 'spa', 'realestate', 'tech', 'edu', 'interior')
TOKEN = re.compile(r'\{\{media:([a-z0-9_\-]+)\}\}')
MAX_IMG = 300 * 1024


def load(d):
    with open(os.path.join(d, 'kit.json'), encoding='utf-8') as f:
        return json.load(f)


def check(d):
    errors, warnings = [], []
    try:
        kit = load(d)
    except (OSError, ValueError) as e:
        return [f'kit.json: {e}'], []
    for k in ('id', 'name', 'version', 'industry', 'pages'):
        if not kit.get(k):
            errors.append(f'kit.json: missing "{k}"')
    if kit.get('id') and not re.match(r'^[a-z0-9][a-z0-9\-]{2,60}$', kit['id']):
        errors.append('kit.json: "id" must be lowercase letters, digits and hyphens')
    if kit.get('version') and not re.match(r'^\d+\.\d+\.\d+$', kit['version']):
        errors.append('kit.json: "version" must look like 1.0.0')
    if kit.get('motion_preset') and kit['motion_preset'] not in PRESETS:
        warnings.append(f'kit.json: motion_preset "{kit["motion_preset"]}" is not one of {", ".join(PRESETS)}')
    if len(kit.get('description', '')) > 160:
        warnings.append('kit.json: description longer than 160 characters')
    if not os.path.exists(os.path.join(d, 'preview.jpg')) and not os.path.exists(os.path.join(d, 'preview.png')):
        warnings.append('preview.jpg missing (1200×800 screenshot for the store)')

    for k, v in (kit.get('theme_mods') or {}).items():
        if k not in MODS:
            warnings.append(f'theme_mods: "{k}" is not a known Flatsome Customizer key')
        elif MODS[k]['type'] == 'typography' and not isinstance(v, dict):
            errors.append(f'theme_mods: "{k}" is a typography setting – use an object like {{"font-family": "Manrope", "variant": "700"}}')

    media = kit.get('media') or {}
    for key, m in media.items():
        p = os.path.join(d, m.get('file', ''))
        if not os.path.isfile(p):
            errors.append(f'media "{key}": file {m.get("file")} not found')
            continue
        if os.path.getsize(p) > MAX_IMG:
            warnings.append(f'media "{key}": {os.path.getsize(p) // 1024} KB – compress to ≤ 300 KB')
        if not m.get('alt'):
            warnings.append(f'media "{key}": add an "alt" text')

    used, block_slugs = set(), {b.get('slug') for b in kit.get('blocks') or []}
    slugs = set()
    items = [('page', p) for p in kit.get('pages') or []] + [('block', b) for b in kit.get('blocks') or []]
    fronts = 0
    for kind, it in items:
        f = it.get('file', '')
        if not it.get('slug') or not it.get('title'):
            errors.append(f'{kind} {f or "?"}: needs "slug" and "title"')
        if it.get('slug') in slugs:
            errors.append(f'{kind} slug "{it.get("slug")}" used twice')
        slugs.add(it.get('slug'))
        fronts += 1 if it.get('front') else 0
        p = os.path.join(d, f)
        if not os.path.isfile(p):
            errors.append(f'{kind} "{it.get("slug")}": file {f} not found')
            continue
        text = open(p, encoding='utf-8').read()
        used |= set(TOKEN.findall(text))
        for ref in re.findall(r'\[block id="([^"]+)"', text):
            if not ref.isdigit() and ref not in block_slugs:
                warnings.append(f'{f}: [block id="{ref}"] is not a block of this kit')
            if ref.isdigit():
                errors.append(f'{f}: [block id="{ref}"] uses a numeric ID – reference kit blocks by slug')
        if re.search(r'\[(ux_image|ux_banner|section|featured_box|testimonial|team_member|ux_image_box|logo|ux_gallery)\b[^\]]*\b(id|bg|img|image|ids)="\d+', text):
            errors.append(f'{f}: hard-coded attachment IDs – use {{{{media:key}}}} tokens')
        e, w, _ = fs_lint.lint(text, list(fs_lint.DEFAULT_ALLOW), f)
        errors += e
        warnings += w
        if it.get('theme_mod') and it['theme_mod'] not in MODS:
            warnings.append(f'block "{it.get("slug")}": theme_mod "{it["theme_mod"]}" is not a known Customizer key')
    if fronts > 1:
        errors.append('more than one page has "front": true')
    for key in sorted(used - set(media)):
        errors.append(f'{{{{media:{key}}}}} used but not declared in kit.json "media"')
    for key in sorted(set(media) - used):
        warnings.append(f'media "{key}" declared but never used')
    return errors, warnings


def build(d, out):
    errors, warnings = check(d)
    for w in warnings:
        print('WARN ', w)
    if errors:
        for e in errors:
            print('ERROR', e)
        print(f'\nNot built: {len(errors)} error(s).')
        return 1
    kit = load(d)
    os.makedirs(out, exist_ok=True)
    target = os.path.join(out, f'{kit["id"]}-{kit["version"]}.zip')
    files = ['kit.json'] + [x for x in ('preview.jpg', 'preview.png') if os.path.exists(os.path.join(d, x))]
    files += [it['file'] for it in (kit.get('pages') or []) + (kit.get('blocks') or [])]
    files += [m['file'] for m in (kit.get('media') or {}).values()]
    with zipfile.ZipFile(target, 'w', zipfile.ZIP_DEFLATED) as z:
        for f in files:
            z.write(os.path.join(d, f), os.path.join(kit['id'], f))
    print(f'Built {target} ({os.path.getsize(target) // 1024} KB, {len(files)} files, {len(warnings)} warning(s)).')
    return 0


def new(d, industry, name):
    if os.path.exists(os.path.join(d, 'kit.json')):
        print(f'{d}/kit.json already exists')
        return 1
    for sub in ('pages', 'blocks', 'media'):
        os.makedirs(os.path.join(d, sub), exist_ok=True)
    slug = os.path.basename(os.path.normpath(d))
    kit = {
        'id': 'mg-kit-' + re.sub(r'[^a-z0-9]+', '-', slug.lower()).strip('-'),
        'name': name or slug.replace('-', ' ').title(),
        'version': '1.0.0',
        'industry': industry,
        'description': '',
        'flatsome': '>=3.15',
        'motion_preset': industry if industry in PRESETS else 'tech',
        'requires': ['mg-motion'],
        'theme_mods': {'color_primary': '#ff8a00', 'color_secondary': '#5b5cf0', 'button_radius': '13px'},
        'pages': [{'slug': 'home', 'title': 'Trang chủ', 'file': 'pages/home.txt', 'template': 'page-blank.php', 'front': True}],
        'blocks': [{'slug': f'{slug}-footer', 'title': 'Footer', 'file': 'blocks/footer.txt', 'theme_mod': 'footer_block'}],
        'media': {},
    }
    json.dump(kit, open(os.path.join(d, 'kit.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=2)
    open(os.path.join(d, 'pages', 'home.txt'), 'w', encoding='utf-8').write(
        '[section label="Hero" padding="110px" padding__sm="60px"]\n[row h_align="center"]\n[col span="8" span__sm="12" align="center"]\n'
        '[ux_text font_size="2.6" font_size__sm="1.8" class="mg-split"]<h1><strong>Tiêu đề chính</strong></h1>[/ux_text]\n'
        '[ux_text]<p>Một câu mô tả lợi ích chính.</p>[/ux_text]\n[button text="Liên hệ" color="primary" radius="99"]\n[/col]\n[/row]\n[/section]\n')
    open(os.path.join(d, 'blocks', 'footer.txt'), 'w', encoding='utf-8').write(
        '[section label="Footer" bg_color="#0e1322" dark="true" padding="50px"]\n[row]\n[col span="12" align="center"]\n'
        '[ux_text]<p>© Tên công ty · Hotline</p>[/ux_text]\n[/col]\n[/row]\n[/section]\n')
    print(f'Created kit skeleton in {d}')
    return 0


def main(argv):
    if len(argv) < 2 or argv[0] not in ('new', 'check', 'build'):
        print(__doc__)
        return 2
    cmd, d = argv[0], argv[1]
    opt = lambda name, default: argv[argv.index(name) + 1] if name in argv else default
    if cmd == 'new':
        return new(d, opt('--industry', 'tech'), opt('--name', ''))
    if cmd == 'build':
        return build(d, opt('--out', 'dist'))
    errors, warnings = check(d)
    for e in errors:
        print('ERROR', e)
    for w in warnings:
        print('WARN ', w)
    print(f'\n{len(errors)} error(s), {len(warnings)} warning(s)')
    return 1 if errors else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv[1:]))
