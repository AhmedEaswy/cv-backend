#!/usr/bin/env node
/**
 * sync-locales.mjs
 *
 * Single source of truth for translations is Laravel's resources/lang/*.json
 * (because the existing app, mail templates, and admin panel all use them).
 *
 * Copies each locale into frontend/locales/ and converts Laravel placeholders
 * (`:name`) to vue-i18n placeholders (`{name}`) so Nuxt interpolations work.
 *
 *   node scripts/sync-locales.mjs
 *
 * Also runs as a predev / prebuild step.
 */
import { mkdir, readFile, stat, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(__dirname, '..', '..');
const sourceDir = resolve(projectRoot, 'resources', 'lang');
const targetDir = resolve(__dirname, '..', 'locales');

const LOCALES = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

/** Convert Laravel `:param` placeholders to vue-i18n `{param}`. */
function toVueI18nPlaceholders(value) {
    if (typeof value !== 'string') return value;
    // Use a function replacer — string `$1{$2}` is fragile across shells/engines.
    // Skip protocol-like sequences (http:) and already-braced tokens.
    return value.replace(/(^|[^:{]):([A-Za-z_][A-Za-z0-9_]*)\b/g, (_m, prefix, name) => `${prefix}{${name}}`);
}

function transformMessages(input) {
    if (Array.isArray(input)) return input.map(transformMessages);
    if (input && typeof input === 'object') {
        const out = {};
        for (const [key, val] of Object.entries(input)) {
            out[key] = transformMessages(val);
        }
        return out;
    }
    return toVueI18nPlaceholders(input);
}

async function exists(path) {
    try { await stat(path); return true; } catch { return false; }
}

let copied = 0;
let failed = 0;

for (const locale of LOCALES) {
    const src = resolve(sourceDir, `${locale}.json`);
    const dst = resolve(targetDir, `${locale}.json`);

    if (!await exists(src)) {
        console.warn(`  ! missing: ${src}`);
        failed++;
        continue;
    }

    await mkdir(targetDir, { recursive: true });
    const raw = JSON.parse(await readFile(src, 'utf8'));
    const converted = transformMessages(raw);
    await writeFile(dst, `${JSON.stringify(converted, null, 4)}\n`, 'utf8');
    copied++;
    console.log(`  ✓ ${locale}.json`);
}

console.log(`\n${copied} copied (vue-i18n placeholders), ${failed} failed.`);
process.exit(failed > 0 ? 1 : 0);
