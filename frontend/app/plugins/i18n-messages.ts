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

    // Ensure dotted keys stay flat even if vueI18n config is skipped in a build.
    if (i18n.flatJson !== true) {
        i18n.flatJson = true;
    }

    for (const [code, messages] of Object.entries(locales)) {
        i18n.mergeLocaleMessage(code, toVueI18nPlaceholders(messages) as any);
    }
});
