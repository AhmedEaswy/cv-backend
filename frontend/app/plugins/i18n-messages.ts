/**
 * vue-i18n config — provides messages directly to vue-i18n, bypassing
 * the @nuxtjs/i18n locale file loader (which has path-resolution issues
 * on Windows when using the default `locales/{lang}.json` layout).
 *
 * Locale JSON uses flat dotted keys ("auth.login.title"). Those must stay
 * flat and pair with `flatJson: true` in i18n.config.ts — do not unflatten
 * them here or vue-i18n will look up the wrong shape and SSR can throw
 * CompileError 10 on keys like auth.login.aside.bullet_1.
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
        return value
            // Laravel ":name" → vue-i18n "{name}"
            .replace(/(^|[^:{]):([A-Za-z_][A-Za-z0-9_]*)\b/g, (_m, prefix: string, name: string) => `${prefix}{${name}}`)
            // Literal "@" (emails) — idempotent so sync + plugin can both run.
            .replace(/\{'@'\}/g, '\u0000')
            .replace(/@(?!\.?[A-Za-z_][\w-]*:)/g, "{'@'}")
            .replace(/\u0000/g, "{'@'}");
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

    for (const [code, messages] of Object.entries(locales)) {
        i18n.mergeLocaleMessage(code, toVueI18nPlaceholders(messages) as any);
    }
});
