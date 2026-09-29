#!/usr/bin/env node
/**
 * i18n-untranslated.mjs
 *
 * Finds every key in resources/lang/en.json that is missing from another
 * locale, or still holds the English value, and fills it from a translation map.
 *
 *   node scripts/i18n-untranslated.mjs check     # report counts, exit 1 if anything is untranslated
 *   node scripts/i18n-untranslated.mjs extract   # write resources/lang/patches/pending/source.json
 *   node scripts/i18n-untranslated.mjs apply     # read pending/<locale>.json, patch locales, sync to Nuxt
 *
 * pending/source.json is an object of { id: "English text" } holding each unique
 * untranslated English string once. pending/<locale>.json maps the same ids to the
 * translated text. Laravel placeholders (:name), HTML tags and URLs must be kept.
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const __dirname = dirname(fileURLToPath(import.meta.url));
const langDir = resolve(__dirname, '..', '..', 'resources', 'lang');
const pendingDir = resolve(langDir, 'patches', 'pending');
const LOCALES = ['ar', 'tr', 'es', 'fr', 'de', 'ur'];

const SYMBOLS_ONLY = /^[\s\d\p{P}\p{S}]*$/u;

/**
 * { locale: ["English value", ...] } — values a translator deliberately kept
 * identical (brand names, template names, acronyms). `apply` appends to it.
 */
const identicalAllowlistPath = resolve(langDir, 'patches', 'identical-allowlist.json');

const readJson = (path) => JSON.parse(readFileSync(path, 'utf8'));
const writeJson = (path, data) => writeFileSync(path, `${JSON.stringify(data, null, 4)}\n`, 'utf8');

const english = readJson(resolve(langDir, 'en.json'));
const identicalAllowlist = existsSync(identicalAllowlistPath) ? readJson(identicalAllowlistPath) : {};

function untranslatedKeys(locale, localeMessages) {
    const allowedIdentical = new Set(identicalAllowlist[locale] ?? []);
    return Object.keys(english).filter((key) => {
        const englishValue = english[key];
        if (typeof englishValue !== 'string' || SYMBOLS_ONLY.test(englishValue)) return false;
        const isMissing = !(key in localeMessages);
        const isStillEnglish = localeMessages[key] === englishValue && !allowedIdentical.has(englishValue);
        return isMissing || isStillEnglish;
    });
}

const mode = process.argv[2] ?? 'check';

if (mode === 'check') {
    let total = 0;
    for (const locale of LOCALES) {
        const keys = untranslatedKeys(locale, readJson(resolve(langDir, `${locale}.json`)));
        total += keys.length;
        console.log(`  ${keys.length === 0 ? '✓' : '!'} ${locale}: ${keys.length} untranslated`);
        if (keys.length) console.log(`      ${keys.slice(0, 10).join(', ')}${keys.length > 10 ? ', …' : ''}`);
    }
    process.exit(total > 0 ? 1 : 0);
}

if (mode === 'extract') {
    const uniqueEnglish = new Set();
    for (const locale of LOCALES) {
        const localeMessages = readJson(resolve(langDir, `${locale}.json`));
        for (const key of untranslatedKeys(locale, localeMessages)) uniqueEnglish.add(english[key]);
    }
    const source = {};
    [...uniqueEnglish].sort().forEach((text, index) => { source[`s${index + 1}`] = text; });
    mkdirSync(pendingDir, { recursive: true });
    writeJson(resolve(pendingDir, 'source.json'), source);
    console.log(`${Object.keys(source).length} unique strings written to ${resolve(pendingDir, 'source.json')}`);
    process.exit(0);
}

if (mode === 'apply') {
    const source = readJson(resolve(pendingDir, 'source.json'));
    const idByEnglish = new Map(Object.entries(source).map(([id, text]) => [text, id]));

    for (const locale of LOCALES) {
        const translationPath = resolve(pendingDir, `${locale}.json`);
        if (!existsSync(translationPath)) {
            console.warn(`  ! ${locale}: no ${translationPath}`);
            continue;
        }
        const translations = readJson(translationPath);
        const localePath = resolve(langDir, `${locale}.json`);
        const localeMessages = readJson(localePath);
        const allowedIdentical = new Set(identicalAllowlist[locale] ?? []);
        let patched = 0;
        const unresolved = [];
        for (const key of untranslatedKeys(locale, localeMessages)) {
            const translated = translations[idByEnglish.get(english[key])];
            if (typeof translated === 'string' && translated.trim()) {
                localeMessages[key] = translated;
                if (translated === english[key]) allowedIdentical.add(translated);
                patched++;
            } else {
                unresolved.push(key);
            }
        }
        identicalAllowlist[locale] = [...allowedIdentical].sort();
        const sorted = Object.fromEntries(Object.keys(localeMessages).sort().map((key) => [key, localeMessages[key]]));
        writeJson(localePath, sorted);
        console.log(`  ✓ ${locale}: ${patched} patched${unresolved.length ? `, ${unresolved.length} unresolved` : ''}`);
    }
    writeJson(identicalAllowlistPath, identicalAllowlist);

    const sync = spawnSync(process.execPath, [resolve(__dirname, 'sync-locales.mjs')], { stdio: 'inherit' });
    process.exit(sync.status ?? 1);
}

console.error(`Unknown mode "${mode}". Use check, extract or apply.`);
process.exit(1);
