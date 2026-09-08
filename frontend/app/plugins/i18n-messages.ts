/**
 * vue-i18n config — provides messages directly to vue-i18n, bypassing
 * the @nuxtjs/i18n locale file loader (which has path-resolution issues
 * on Windows when using the default `locales/{lang}.json` layout).
 *
 * This file is loaded both on the server and the client. The same JSON
 * files (synced from Laravel's resources/lang/ by scripts/sync-locales.mjs)
 * are imported as ES modules via Vite's built-in JSON loader.
 *
 * We use relative paths (not the `~/` alias) because Vite's SSR module
 * loader doesn't always resolve tsconfig paths.
 *
 * Laravel uses `:param` placeholders; vue-i18n needs `{param}`. Sync converts
 * them on disk, and we convert again here so a stale/unconverted locale never
 * shows literal ":name" in the UI.
 *
 * Locale files store flat dotted keys ("auth.login.title"). We unflatten them
 * into nested objects before mergeLocaleMessage so lookups work even when
 * `flatJson` is not active yet during plugin init.
 */
import en from '../../locales/en.json';
import ar from '../../locales/ar.json';
import tr from '../../locales/tr.json';
import es from '../../locales/es.json';
import fr from '../../locales/fr.json';
import de from '../../locales/de.json';
import ur from '../../locales/ur.json';

/** Convert Laravel `:param` placeholders to vue-i18n `{param}`. */
function toVueI18nPlaceholders(value: unknown): unknown {
    if (typeof value === 'string') {
        return value.replace(/(^|[^:{]):([A-Za-z_][A-Za-z0-9_]*)\b/g, (_m, prefix: string, name: string) => `${prefix}{${name}}`);
    }
    if (Array.isArray(value)) {
        return value.map(toVueI18nPlaceholders);
    }
    if (value && typeof value === 'object') {
        const out: Record<string, unknown> = {};
        for (const [key, val] of Object.entries(value as Record<string, unknown>)) {
            out[key] = toVueI18nPlaceholders(val);
        }
        return out;
    }
    return value;
}

/**
 * Turn flat {"a.b.c": "x"} into nested {a:{b:{c:"x"}}}.
 * If a prefix key is also a leaf string, nested children win.
 */
function unflattenMessages(flat: Record<string, unknown>): Record<string, unknown> {
    const out: Record<string, unknown> = {};
    const keys = Object.keys(flat).sort((a, b) => a.localeCompare(b));

    for (const key of keys) {
        const value = flat[key];
        const parts = key.split('.');
        let cur = out;

        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            const isLeaf = i === parts.length - 1;

            if (isLeaf) {
                if (cur[part] && typeof cur[part] === 'object' && cur[part] !== null) {
                    // Prefer keeping nested children over a conflicting leaf string.
                    continue;
                }
                cur[part] = value;
                continue;
            }

            const next = cur[part];
            if (!next || typeof next !== 'object' || next === null || Array.isArray(next)) {
                cur[part] = {};
            }
            cur = cur[part] as Record<string, unknown>;
        }
    }

    return out;
}

const locales = {
    en,
    ar,
    tr,
    es,
    fr,
    de,
    ur,
} as const;

export default defineNuxtPlugin((nuxtApp) => {
    const i18n = nuxtApp.$i18n as any;
    if (!i18n?.mergeLocaleMessage) return;

    if (i18n.flatJson !== true) {
        i18n.flatJson = true;
    }

    for (const [code, messages] of Object.entries(locales)) {
        const prepared = toVueI18nPlaceholders(messages) as Record<string, unknown>;
        i18n.mergeLocaleMessage(code, unflattenMessages(prepared) as any);
    }
});
