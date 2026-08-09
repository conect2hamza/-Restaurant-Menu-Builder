#!/usr/bin/env python3
"""Rebuild the plugin POT from the PHP and JS sources."""
import os, re, sys
from collections import OrderedDict

ROOT = sys.argv[1]
DOMAIN = 'restaurant-menu-builder'

FUNCS = r"(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_x|esc_html_x|esc_attr_x)"
STR = r"""(?:'((?:[^'\\]|\\.)*)'|"((?:[^"\\]|\\.)*)")"""
# fn( 'text' [, 'context'] , 'domain' )
SINGLE = re.compile(FUNCS + r"\(\s*" + STR + r"\s*(?:,\s*" + STR + r"\s*)?,\s*['\"]" + re.escape(DOMAIN) + r"['\"]\s*\)")
PLURAL = re.compile(r"(?:_n|_nx)\(\s*" + STR + r"\s*,\s*" + STR + r"\s*,[^,]+,\s*(?:" + STR + r"\s*,\s*)?['\"]" + re.escape(DOMAIN) + r"['\"]\s*\)")
COMMENT = re.compile(r"/\*\s*translators:(.*?)\*/", re.S)

def unescape(raw, quote):
    if raw is None:
        return None
    if quote == "'":
        return raw.replace("\\'", "'").replace("\\\\", "\\")
    return (raw.replace('\\"', '"').replace('\\n', '\n').replace('\\t', '\t')
               .replace('\\\\', '\\'))

def pick(m, a, b):
    if m.group(a) is not None:
        return unescape(m.group(a), "'")
    return unescape(m.group(b), '"')

def escape_po(value):
    return (value.replace('\\', '\\\\').replace('"', '\\"')
                 .replace('\n', '\\n').replace('\t', '\\t'))

entries = OrderedDict()

def add(key, data, ref, comment):
    entry = entries.setdefault(key, dict(data, refs=[], comments=[]))
    if ref not in entry['refs']:
        entry['refs'].append(ref)
    if comment and comment not in entry['comments']:
        entry['comments'].append(comment)

def translator_comment(source, start):
    """Nearest /* translators: */ comment above the call."""
    head = source[:start]
    matches = list(COMMENT.finditer(head))
    if not matches:
        return None
    last = matches[-1]
    between = head[last.end():]
    # Only attach when nothing but whitespace, a line break or code indentation
    # separates the comment from the string, mirroring how gettext pairs them.
    if between.count('\n') > 2:
        return None
    return ' '.join(last.group(1).split())

files = []
for base, dirs, names in os.walk(ROOT):
    dirs[:] = [d for d in dirs if d not in ('node_modules', '.git', 'languages')]
    for name in sorted(names):
        if name.endswith(('.php', '.js')):
            files.append(os.path.join(base, name))

for path in sorted(files):
    rel = os.path.relpath(path, ROOT)
    source = open(path, encoding='utf-8').read()
    lines = source.split('\n')

    def line_of(index):
        return source.count('\n', 0, index) + 1

    for m in SINGLE.finditer(source):
        text = pick(m, 1, 2)
        ctx = pick(m, 3, 4)
        if text is None:
            continue
        key = (ctx or '') + '\x04' + text
        add(key, {'msgid': text, 'ctx': ctx}, '%s:%d' % (rel, line_of(m.start())),
            translator_comment(source, m.start()))

    for m in PLURAL.finditer(source):
        single = pick(m, 1, 2)
        plural = pick(m, 3, 4)
        ctx = pick(m, 5, 6)
        if single is None:
            continue
        key = (ctx or '') + '\x04' + single + '\x05' + (plural or '')
        add(key, {'msgid': single, 'plural': plural, 'ctx': ctx},
            '%s:%d' % (rel, line_of(m.start())), translator_comment(source, m.start()))

version = re.search(r"^\s*\*\s*Version:\s*(\S+)", open(os.path.join(ROOT, 'restaurant-menu-builder.php'), encoding='utf-8').read(), re.M).group(1)

out = ['# Copyright (C) 2025 Hamza Dezinr',
       '# This file is distributed under the GPL-2.0-or-later license.',
       'msgid ""',
       'msgstr ""',
       '"Project-Id-Version: Restaurant Menu Builder %s\\n"' % version,
       '"Report-Msgid-Bugs-To: https://hamzadezinr.com/support\\n"',
       '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"',
       '"Language-Team: LANGUAGE <LL@li.org>\\n"',
       '"MIME-Version: 1.0\\n"',
       '"Content-Type: text/plain; charset=UTF-8\\n"',
       '"Content-Transfer-Encoding: 8bit\\n"',
       '"POT-Creation-Date: 2025-01-01T00:00:00+00:00\\n"',
       '"X-Generator: bin/makepot.py\\n"',
       '"X-Domain: %s\\n"' % DOMAIN,
       '']

for entry in entries.values():
    for comment in entry['comments']:
        out.append('#. translators: %s' % comment)
    out.append('#: ' + ' '.join(entry['refs']))
    if entry.get('ctx'):
        out.append('msgctxt "%s"' % escape_po(entry['ctx']))
    out.append('msgid "%s"' % escape_po(entry['msgid']))
    if entry.get('plural'):
        out.append('msgid_plural "%s"' % escape_po(entry['plural']))
        out.append('msgstr[0] ""')
        out.append('msgstr[1] ""')
    else:
        out.append('msgstr ""')
    out.append('')

open(os.path.join(ROOT, 'languages', DOMAIN + '.pot'), 'w', encoding='utf-8').write('\n'.join(out))
print('%d strings from %d files' % (len(entries), len(files)))
