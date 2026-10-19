"""Публичный снимок для Pages: python3 .github/export-pages.py --base /cofesti/.

Запускать после npm run build при работающем локальном Laravel (гостевой сеанс).
Экспорт ограничен публичными GET-страницами; база и приватное хранилище не читаются.
"""

import argparse
import hashlib
import html
from html.parser import HTMLParser
import json
from pathlib import Path
import re
import shutil
from urllib.parse import parse_qs, urlencode, urljoin, urlsplit, unquote
from urllib.request import urlopen

ROOT = Path(__file__).resolve().parents[1]
PUBLIC = ROOT / 'public'
OUTPUT = PUBLIC / 'preview'
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--origin', default='http://127.0.0.1:8000')
parser.add_argument('--base', default='/cofesti/')
args = parser.parse_args()
ORIGIN = args.origin.rstrip('/')
BASE = '/' + args.base.strip('/') + '/'
if not re.fullmatch(r'/[a-zA-Z0-9_-]+/', BASE):
    raise ValueError('Укажите один безопасный сегмент пути репозитория')


class PageLinks(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = []
        self.assets = []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'a' and attrs.get('href'):
            self.links.append(attrs['href'])
        if tag in ('img', 'script') and attrs.get('src'):
            self.assets.append(attrs['src'])


def local_url(value):
    parts = urlsplit(urljoin(ORIGIN + '/', html.unescape(value)))
    if parts.netloc not in (urlsplit(ORIGIN).netloc, 'localhost:8000'):
        return None
    return parts


def canonical(value):
    parts = local_url(value)
    if parts is None:
        return None
    path = parts.path.rstrip('/') or '/'
    if path not in ('/', '/catalog', '/coffeeshop', '/cart') and not path.startswith('/catalog/'):
        return None
    query = parse_qs(parts.query)
    allowed = {k: v[0] for k, v in query.items() if k in ('page', 'category') and v[0]}
    return path + ('?' + urlencode(sorted(allowed.items())) if allowed else '')


def preview_url(value):
    if value.startswith('#'):
        return value
    parts = local_url(value)
    if parts is None:
        return value
    path = parts.path
    if path.startswith(('/login', '/admin', '/checkout', '/inquiries', '/logout')):
        return '#preview-info'
    if path == '/catalog' or path.startswith('/catalog/') or path in ('/coffeeshop', '/cart'):
        path = path.rstrip('/') + '/'
    return BASE + path.lstrip('/') + ('?' + parts.query if parts.query else '') + ('#' + parts.fragment if parts.fragment else '')


def copy_asset(value):
    parts = local_url(value)
    if parts is None:
        return
    relative = Path(unquote(parts.path).lstrip('/'))
    if not relative.parts or relative.parts[0] not in ('images', 'storage', 'build', 'fonts'):
        return
    source = (PUBLIC / relative).resolve()
    permitted = [PUBLIC.resolve(), (ROOT / 'storage/app/public').resolve()]
    if not any(source.is_relative_to(parent) for parent in permitted):
        raise ValueError('Ресурс вне публичного хранилища')
    if source.suffix.lower() not in ('.webp', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.css', '.js'):
        raise ValueError('Неподдерживаемый публичный ресурс')
    destination = OUTPUT / relative
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copyfile(source, destination)


def rewrite(markup):
    markup = re.sub(r'<script\b[^>]*\bid="browser-logger-active"[^>]*>.*?</script>', '', markup, flags=re.S)
    markup = re.sub(r'<input\b[^>]*name="_token"[^>]*>', '', markup)
    markup = re.sub(r'<form\b([^>]*\bmethod="POST"[^>]*)>', '<div data-preview-form \\1>', markup, flags=re.I)
    # У формы заявки нет серверного action и возможности отправить введённые данные.
    markup = re.sub(r'<div data-preview-form([^>]*)\baction="[^"]*"([^>]*)>', r'<div data-preview-form\1\2>', markup)
    markup = re.sub(r'<div data-preview-form([^>]*)\bmethod="POST"([^>]*)>', r'<div data-preview-form\1\2>', markup, flags=re.I)
    # POST-формы заменяются последовательно; GET-форма каталога сохраняется.
    opens = iter(re.findall(r'<(?:form\b|div data-preview-form\b)[^>]*>', markup))
    for opening in opens:
        if opening.startswith('<div data-preview-form'):
            start = markup.index(opening) + len(opening)
            end = markup.index('</form>', start)
            markup = markup[:end] + '</div>' + markup[end + 7:]
    markup = re.sub(r'\b(href|src|action)="([^"]*)"', lambda m: m[1] + '="' + html.escape(preview_url(m[2]), quote=True) + '"', markup)
    markup = markup.replace('</head>', '<meta name="robots" content="noindex, nofollow"><link rel="stylesheet" href="' + BASE + 'preview.css"></head>')
    markup = markup.replace('</body>', '<dialog id="preview-info" aria-labelledby="preview-title"><h2 id="preview-title">Версия для просмотра дизайна</h2><p>GitHub Pages показывает страницы сайта. Корзина, отправка заявок, оформление заказов и вход для команды будут доступны на основном сайте с сервером.</p><button type="button" data-close-preview>Понятно</button></dialog><script defer src="' + BASE + 'preview.js"></script></body>')
    return re.sub(r'[ \t]+(?=\n)', '', markup)


if OUTPUT.exists():
    if OUTPUT.is_symlink():
        raise ValueError('Папка снимка не должна быть ссылкой')
    # Старый снимок сохраняется вне Git вместо удаления файлов пользователя.
    backup = Path('/tmp') / ('cofesti-pages-' + hashlib.sha256(str(OUTPUT.stat().st_mtime_ns).encode()).hexdigest()[:12])
    shutil.move(OUTPUT, backup)
OUTPUT.mkdir(parents=True)
shutil.copytree(PUBLIC / 'build', OUTPUT / 'build', ignore=shutil.ignore_patterns('manifest.json'))
for stylesheet in (OUTPUT / 'build').rglob('*.css'):
    stylesheet.write_text(stylesheet.read_text().replace('/build/', BASE + 'build/'), encoding='utf-8')
queue = ['/', '/catalog', '/coffeeshop', '/cart']
pages = {}
cards = {}
memberships = {}

while queue:
    path = queue.pop(0)
    if path in pages:
        continue
    with urlopen(ORIGIN + path, timeout=30) as response:
        if response.status != 200:
            raise RuntimeError('Не удалось получить публичную страницу: ' + path)
        markup = response.read().decode('utf-8')
    pages[path] = markup
    page = PageLinks()
    page.feed(markup)
    if path == '/catalog':
        options = re.search(r'<select[^>]*name="category".*?</select>', markup, re.S)[0]
        for slug in re.findall(r'<option value="([^"]+)"', options):
            queue.append('/catalog?' + urlencode({'category': html.unescape(slug)}))
    for value in page.assets:
        copy_asset(value)
    for value in page.links:
        target = canonical(value)
        if target and target not in pages and target not in queue:
            queue.append(target)
    if path.startswith('/catalog') and not urlsplit(path).path.startswith('/catalog/'):
        category = parse_qs(urlsplit(path).query).get('category', [''])[0]
        for card in re.findall(r'<article class="product-card"[^>]*>.*?</article>', markup, flags=re.S):
            product = re.search(r'href="([^"]+)"', card)[1]
            key = urlsplit(product).path
            if not category:
                cards[key] = card
            if category:
                memberships.setdefault(key, set()).add(category)
    if len(pages) % 100 == 0:
        print(f'Получено страниц: {len(pages)}', flush=True)
    if len(pages) > 2000:
        raise RuntimeError('Превышен ожидаемый размер публичного каталога')

catalog = pages['/catalog']
options = re.search(r'<select[^>]*name="category".*?</select>', catalog, re.S)[0]
for slug in re.findall(r'<option value="([^"]+)"', options):
    # Корневые категории должны быть доступны и без ссылки с главной.
    if '/catalog?category=' + slug not in pages:
        raise RuntimeError('Не экспортирована категория: ' + slug)

records = []
for key, card in cards.items():
    name = html.unescape(re.sub('<[^>]+>', '', re.search(r'<h3>(.*?)</h3>', card, re.S)[1])).strip()
    price = re.search(r'<div class="product-bottom">\s*<span>(.*?)</span>', card, re.S)[1]
    digits = re.sub(r'\D', '', html.unescape(price))
    records.append({'name': name, 'price': int(digits) if digits else None, 'categories': sorted(memberships.get(key, [])), 'html': rewrite(card).replace(' data-reveal', '')})
if not records:
    raise RuntimeError('Публичный каталог пуст')
(OUTPUT / 'catalog-data.json').write_text(json.dumps(records, ensure_ascii=False), encoding='utf-8')

for path, markup in pages.items():
    if '?' in path:
        continue
    if path == '/catalog':
        markup = re.sub(r'<div class="mt-12">\s*<nav.*?</nav>\s*</div>', '<div data-preview-pagination class="mt-12"></div>', markup, flags=re.S)
    target = OUTPUT / path.lstrip('/') / 'index.html'
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(rewrite(markup), encoding='utf-8')

shutil.copyfile(ROOT / '.github/pages-preview.js', OUTPUT / 'preview.js')
shutil.copyfile(ROOT / '.github/pages-preview.css', OUTPUT / 'preview.css')
(OUTPUT / '.nojekyll').touch()
(OUTPUT / 'robots.txt').write_text('User-agent: *\nDisallow: /\n', encoding='utf-8')
print(f'Готово: {len(pages)} публичных страниц получено, {len(records)} товаров, {len(list(OUTPUT.rglob("index.html")))} HTML-страниц сохранено.')
