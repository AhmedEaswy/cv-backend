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
 */
import en from '../../locales/en.json';
import ar from '../../locales/ar.json';
import tr from '../../locales/tr.json';
import es from '../../locales/es.json';
import fr from '../../locales/fr.json';
import de from '../../locales/de.json';
import ur from '../../locales/ur.json';

export default defineNuxtPlugin((nuxtApp) => {
    const i18n = nuxtApp.$i18n as any;
    if (!i18n?.mergeLocaleMessage) return;
    i18n.mergeLocaleMessage('en', en as any);
    i18n.mergeLocaleMessage('ar', ar as any);
    i18n.mergeLocaleMessage('tr', tr as any);
    i18n.mergeLocaleMessage('es', es as any);
    i18n.mergeLocaleMessage('fr', fr as any);
    i18n.mergeLocaleMessage('de', de as any);
    i18n.mergeLocaleMessage('ur', ur as any);
});
