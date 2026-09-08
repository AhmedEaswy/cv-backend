import type { TemplateKind } from '~/composables/useTemplatePrompt';
import { templateDisplayName } from '~/composables/useTemplatePrompt';

export type TemplateCatalogKind = TemplateKind | 'public-profile';

/**
 * Resolve localized template label / description by stable slug (`name`).
 * Falls back to title-cased slug or API description when a key is missing.
 */
export function useLocalizedTemplate() {
    const { t } = useI18n();

    function translate(key: string): string | null {
        const value = String(t(key));
        // Flat JSON keys: missing lookups often echo the key path.
        if (!value || value === key) return null;
        return value;
    }

    function label(
        kind: TemplateCatalogKind,
        slug: string,
        fallback?: string | null,
    ): string {
        if (!slug) return fallback?.trim() || '';
        const translated = translate(`templates.${kind}.${slug}.name`);
        if (translated) return translated;
        const human = fallback?.trim();
        if (human && human !== slug) return human;
        return templateDisplayName(slug);
    }

    function description(
        kind: TemplateCatalogKind,
        slug: string,
        fallback?: string | null,
    ): string {
        if (!slug) return fallback?.trim() || '';
        const translated = translate(`templates.${kind}.${slug}.description`);
        if (translated) return translated;
        return fallback?.trim() || '';
    }

    return { label, description };
}
