#!/usr/bin/env node
/**
 * sync-locales.mjs
 *
 * Single source of truth for translations is Laravel's resources/lang/*.json
 * (because the existing app, mail templates, and admin panel all use them).
 *
 * This script copies each locale file into frontend/i18n/locales/ so the
 * Nuxt i18n module can bundle them. Run it whenever you change a translation:
 *
 *   node scripts/sync-locales.mjs
 *
 * It is also called automatically as a predev / prebuild step.
 */
import { copyFile, mkdir, stat } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(__dirname, '..', '..');
const sourceDir = resolve(projectRoot, 'resources', 'lang');
const targetDir = resolve(__dirname, '..', 'locales');

const LOCALES = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

async function exists(path) {
    try { await stat(path); return true; } catch { return false; }
}

let copied = 0;
let skipped = 0;
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
    await copyFile(src, dst);
    copied++;
    console.log(`  ✓ ${locale}.json`);
}

console.log(`\n${copied} copied, ${skipped} skipped, ${failed} failed.`);
process.exit(failed > 0 ? 1 : 0);
