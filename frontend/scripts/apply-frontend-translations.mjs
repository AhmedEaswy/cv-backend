#!/usr/bin/env node
/**
 * Merge frontend translation patches into resources/lang/*.json and sync to Nuxt.
 *
 *   node frontend/scripts/apply-frontend-translations.mjs
 */
import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(__dirname, '..', '..');
const patchDir = resolve(projectRoot, 'resources', 'lang', 'patches');
const langDir = resolve(projectRoot, 'resources', 'lang');
const LOCALES = ['ar', 'tr', 'es', 'fr', 'de', 'ur'];

const patchFiles = readdirSync(patchDir).filter((f) => f.endsWith('.json') && f !== 'manifest.json');

for (const locale of LOCALES) {
    const patchPaths = [
        resolve(patchDir, `${locale}.json`),
        resolve(patchDir, `round2-${locale}.json`),
        resolve(patchDir, `round3-${locale}.json`),
        resolve(patchDir, `round4-${locale}.json`),
        resolve(patchDir, `round5-${locale}.json`),
    ];

    const langPath = resolve(langDir, `${locale}.json`);
    const lang = JSON.parse(readFileSync(langPath, 'utf8'));
    let count = 0;

    for (const patchPath of patchPaths) {
        if (!patchFiles.includes(patchPath.split(/[/\\]/).pop())) {
            continue;
        }
        const patch = JSON.parse(readFileSync(patchPath, 'utf8'));
        for (const [key, value] of Object.entries(patch)) {
            lang[key] = value;
            count++;
        }
    }

    if (count === 0) {
        console.warn(`  ! skip ${locale}: no patch files`);
        continue;
    }

    writeFileSync(langPath, `${JSON.stringify(lang, null, 4)}\n`, 'utf8');
    console.log(`  ✓ ${locale}.json — ${count} keys patched`);
}

const sync = spawnSync(process.execPath, [resolve(__dirname, 'sync-locales.mjs')], {
    stdio: 'inherit',
    cwd: resolve(__dirname, '..'),
});
process.exit(sync.status ?? 1);
