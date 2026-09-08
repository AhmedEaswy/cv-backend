import type { I18nOptions } from 'vue-i18n';

/**
 * Locale JSON files use flat dotted keys (e.g. "auth.login.title").
 * vue-i18n must treat them as flat, not nested paths — otherwise SSR
 * throws CompileError 10 on keys like auth.login.aside.bullet_1.
 */
export default {
    flatJson: true,
    fallbackLocale: 'en',
    missingWarn: false,
    fallbackWarn: false,
} satisfies I18nOptions;
