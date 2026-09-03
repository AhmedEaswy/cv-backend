import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const cssDir = path.join(__dirname, '..', 'app', 'assets', 'css');
const mainPath = path.join(cssDir, 'main.css');
const main = fs.readFileSync(mainPath, 'utf8');

// Split main.css at section comment headers (after @theme block)
const themeEnd = main.indexOf('/* =========================================================================\n   Base');
const preamble = main.slice(0, themeEnd);

const sections = main.slice(themeEnd).split(/(?=\/\* =+\n   [^\n]+)/);

const map = {
    'Layout helpers': 'layout.css',
    'Buttons': 'buttons.css',
    'Card surfaces': 'cards.css',
    'Form controls': 'forms-controls.css',
    'Tag (the small pill': 'tags.css',
    'Eyebrow / section label': 'typography.css',
    'Display headings': 'typography.css',
    'Hero': 'sections.css',
    'Decorative "scribble"': 'sections.css',
    'Marquee (used in': 'components.css',
    'Pricing card': 'cards.css',
    'Card grid bento': 'cards.css',
    'Footer': 'footer.css',
    'Reveal-on-scroll': 'layout.css',
    'Focus state': 'base.css',
    'Lang switcher': 'components.css',
    'Templates carousel': 'components.css',
    'Auth split layout': 'auth.css',
    'Portal — top bar': 'portal.css',
    'Stat card': 'cards.css',
    'List item card': 'cards.css',
    'Empty state': 'cards.css',
    'Modal': 'components.css',
    'Alert': 'components.css',
    'ATS score ring': 'components.css',
    'Tabs': 'components.css',
    'Print-only helpers': 'base.css',
};

const buckets = {};
for (const chunk of sections) {
    if (!chunk.trim()) continue;
    const titleMatch = chunk.match(/\/\* =+\n   ([^\n]+)/);
    const title = titleMatch?.[1]?.trim() ?? 'misc';
    let target = 'misc.css';
    for (const [key, file] of Object.entries(map)) {
        if (title.startsWith(key) || title.includes(key)) {
            target = file;
            break;
        }
    }
    buckets[target] = (buckets[target] ?? '') + chunk;
}

// Base chunk (html, body, font-display, selection)
const baseChunk = sections.find((s) => s.includes('Base\n   =====')) ?? '';
buckets['base.css'] = baseChunk + (buckets['base.css'] ?? '');

fs.writeFileSync(path.join(cssDir, 'base.css'), preamble + '\n' + (buckets['base.css'] ?? ''));
delete buckets['base.css'];

for (const [file, content] of Object.entries(buckets)) {
    if (!content.trim()) continue;
    const fp = path.join(cssDir, file);
    const existing = fs.existsSync(fp) ? fs.readFileSync(fp, 'utf8') : '';
    fs.writeFileSync(fp, existing ? `${existing.trim()}\n\n${content.trim()}\n` : `${content.trim()}\n`);
}

console.log('Split main.css into:', Object.keys(buckets).join(', '));
