import { readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import { join, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const appDir = resolve(__dirname, '..', 'app');
const localeDir = resolve(__dirname, '..', '..', 'resources', 'lang');
const langs = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

function walk(dir) {
    const keys = new Set();
    for (const entry of readdirSync(dir)) {
        const p = join(dir, entry);
        if (statSync(p).isDirectory()) {
            for (const k of walk(p)) keys.add(k);
        } else if (/\.(vue|ts)$/.test(entry)) {
            const content = readFileSync(p, 'utf8');
            for (const m of content.matchAll(/t\(\s*['"`]([^'"`]+)['"`]/g)) keys.add(m[1]);
        }
    }
    return keys;
}

function flatten(obj, prefix = '') {
    const out = {};
    for (const [k, v] of Object.entries(obj)) {
        const key = prefix ? `${prefix}.${k}` : k;
        if (v && typeof v === 'object' && !Array.isArray(v)) Object.assign(out, flatten(v, key));
        else out[key] = v;
    }
    return out;
}

const frontendKeys = [...walk(appDir)]
    .filter((k) => !k.includes('${') && !k.startsWith('/') && !k.includes('.png') && !k.includes('.svg') && k !== 'update:open' && k.trim())
    .sort();
const en = JSON.parse(readFileSync(join(localeDir, 'en.json'), 'utf8'));

console.log('Frontend t() keys:', frontendKeys.length);
console.log('Missing in en.json:', frontendKeys.filter((k) => !(k in en)).join(', ') || '(none)');

const report = {};
for (const lang of langs.filter((l) => l !== 'en')) {
    const data = JSON.parse(readFileSync(join(localeDir, `${lang}.json`), 'utf8'));
    const missing = frontendKeys.filter((k) => !(k in data));
    const untrans = frontendKeys.filter((k) => k in data && k in en && data[k] === en[k]);
    report[lang] = { missing, untrans };
    console.log(`\n${lang}: missing=${missing.length}, untranslated=${untrans.length}`);
    if (missing.length) console.log('  missing:', missing.join(', '));
    if (untrans.length) console.log('  untranslated:', untrans.join(', '));
}

const allUntrans = [...new Set(Object.values(report).flatMap((r) => r.untrans))].sort();
writeFileSync(join(__dirname, 'remaining-untranslated.json'), JSON.stringify(allUntrans, null, 2));
console.log(`\nUnique keys still English in at least one locale: ${allUntrans.length}`);

// Extra: landing.platforms_* and other dynamic-prefix keys used in frontend
const prefixKeys = frontendKeys.filter((k) =>
    k.startsWith('landing.platforms_')
    || k.startsWith('landing.final_')
    || k.startsWith('landing.section_mockup_')
    || k.startsWith('landing.template_')
    || k.startsWith('landing.feature_')
    || k.startsWith('landing.cover_feat_')
    || k.startsWith('landing.profile_feat_')
    || k.startsWith('landing.ai_connect_chip_')
    || k.startsWith('agent.')
);
console.log(`\nOther frontend keys to spot-check: ${prefixKeys.length}`);
for (const lang of langs.filter((l) => l !== 'en')) {
    const data = JSON.parse(readFileSync(join(localeDir, `${lang}.json`), 'utf8'));
    const untrans = prefixKeys.filter((k) => k in data && k in en && data[k] === en[k]);
    if (untrans.length) console.log(`  ${lang}: ${untrans.length} still English — ${untrans.slice(0, 8).join(', ')}${untrans.length > 8 ? '…' : ''}`);
}
