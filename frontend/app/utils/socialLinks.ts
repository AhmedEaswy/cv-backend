import type { PublicProfileSocialLink, SocialLinkPlatform } from '~/composables/usePortalApi';

export interface SocialLinkCard {
    id: string;
    platform: SocialLinkPlatform;
    url: string;
    label: string;
    enabled: boolean;
}

export const SOCIAL_LINK_PLATFORMS: SocialLinkPlatform[] = [
    'linkedin', 'github', 'x', 'instagram', 'youtube', 'facebook', 'tiktok', 'snapchat',
    'calendly', 'behance', 'dribbble', 'medium', 'whatsapp', 'telegram', 'website', 'custom',
];

let cardSequence = 0;

function nextCardId(): string {
    cardSequence += 1;
    return `social-link-${cardSequence}`;
}

function toPlatform(value: string | undefined): SocialLinkPlatform {
    return SOCIAL_LINK_PLATFORMS.includes(value as SocialLinkPlatform) ? value as SocialLinkPlatform : 'custom';
}

/**
 * Saved links come first (enabled, in saved order); every other platform follows as a disabled card.
 */
export function buildSocialLinkCards(saved: PublicProfileSocialLink[]): SocialLinkCard[] {
    const cards: SocialLinkCard[] = saved.map((link) => ({
        id: nextCardId(),
        platform: toPlatform(link.platform),
        url: link.url || '',
        label: link.label || '',
        enabled: true,
    }));

    const usedPlatforms = new Set(cards.map((card) => card.platform));
    for (const platform of SOCIAL_LINK_PLATFORMS) {
        const isUnusedFixedPlatform = platform !== 'custom' && !usedPlatforms.has(platform);
        if (isUnusedFixedPlatform) {
            cards.push({ id: nextCardId(), platform, url: '', label: '', enabled: false });
        }
    }

    return cards;
}

export function createCustomSocialLinkCard(): SocialLinkCard {
    return { id: nextCardId(), platform: 'custom', url: '', label: '', enabled: true };
}

export function socialLinksFromCards(cards: SocialLinkCard[]): PublicProfileSocialLink[] {
    return cards
        .filter((card) => card.enabled)
        .map((card) => ({
            platform: card.platform,
            url: card.url.trim(),
            ...(card.platform === 'custom' && card.label.trim() ? { label: card.label.trim() } : {}),
        }))
        .filter((link) => link.url);
}
