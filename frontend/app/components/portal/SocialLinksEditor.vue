<script setup lang="ts">
import { HugeiconsIcon } from '@hugeicons/vue';
import {
    BehanceIcon,
    Calendar03Icon,
    DribbbleIcon,
    Facebook01Icon,
    Github01Icon,
    Globe02Icon,
    InstagramIcon,
    Link01Icon,
    Linkedin01Icon,
    MediumIcon,
    NewTwitterIcon,
    SnapchatIcon,
    TelegramIcon,
    TiktokIcon,
    WhatsappIcon,
    YoutubeIcon,
} from '@hugeicons/core-free-icons';
import type { SocialLinkPlatform } from '~/composables/usePortalApi';

const cards = defineModel<SocialLinkCard[]>({ required: true });
const { t } = useI18n();
const { overIndex, dragIndex, onDragStart, onDragOver, onDrop, onDragEnd } = useSortableList(cards);

interface PlatformStyle {
    icon: typeof Link01Icon;
    /** Tile background; gradients allowed. */
    tile: string;
    /** Icon stroke colour on top of the tile. */
    ink: string;
    placeholder: string;
}

const PLATFORM_STYLES: Record<SocialLinkPlatform, PlatformStyle> = {
    linkedin: { icon: Linkedin01Icon, tile: '#0A66C2', ink: '#fff', placeholder: 'https://www.linkedin.com/in/username' },
    github: { icon: Github01Icon, tile: '#181717', ink: '#fff', placeholder: 'https://github.com/username' },
    x: { icon: NewTwitterIcon, tile: '#000000', ink: '#fff', placeholder: 'https://x.com/username' },
    instagram: {
        icon: InstagramIcon,
        tile: 'radial-gradient(circle at 30% 107%, #fdf497 0%, #fd5949 45%, #d6249f 60%, #285AEB 90%)',
        ink: '#fff',
        placeholder: 'https://www.instagram.com/username',
    },
    youtube: { icon: YoutubeIcon, tile: '#FF0000', ink: '#fff', placeholder: 'https://www.youtube.com/@channel' },
    facebook: { icon: Facebook01Icon, tile: '#1877F2', ink: '#fff', placeholder: 'https://www.facebook.com/username' },
    tiktok: { icon: TiktokIcon, tile: 'linear-gradient(135deg, #25F4EE 0%, #000 45%, #000 55%, #FE2C55 100%)', ink: '#fff', placeholder: 'https://www.tiktok.com/@username' },
    snapchat: { icon: SnapchatIcon, tile: '#FFFC00', ink: '#111', placeholder: 'https://www.snapchat.com/add/username' },
    calendly: { icon: Calendar03Icon, tile: '#006BFF', ink: '#fff', placeholder: 'https://calendly.com/username' },
    behance: { icon: BehanceIcon, tile: '#1769FF', ink: '#fff', placeholder: 'https://www.behance.net/username' },
    dribbble: { icon: DribbbleIcon, tile: '#EA4C89', ink: '#fff', placeholder: 'https://dribbble.com/username' },
    medium: { icon: MediumIcon, tile: '#000000', ink: '#fff', placeholder: 'https://medium.com/@username' },
    whatsapp: { icon: WhatsappIcon, tile: '#25D366', ink: '#fff', placeholder: 'https://wa.me/15551234567' },
    telegram: { icon: TelegramIcon, tile: '#26A5E4', ink: '#fff', placeholder: 'https://t.me/username' },
    website: { icon: Globe02Icon, tile: 'linear-gradient(135deg, #0EA5E9, #6366F1)', ink: '#fff', placeholder: 'https://example.com' },
    custom: { icon: Link01Icon, tile: 'linear-gradient(135deg, #F97316, #EC4899)', ink: '#fff', placeholder: 'https://example.com' },
};

function styleFor(platform: SocialLinkPlatform): PlatformStyle {
    return PLATFORM_STYLES[platform] ?? PLATFORM_STYLES.custom;
}

function titleFor(card: SocialLinkCard): string {
    const isNamedCustomLink = card.platform === 'custom' && card.label.trim();
    return isNamedCustomLink ? card.label.trim() : t(`portal.public_profile.social.platform.${card.platform}`);
}

function addCustomLink() {
    cards.value = [...cards.value, createCustomSocialLinkCard()];
}

function removeCard(index: number) {
    cards.value = cards.value.filter((_, cardIndex) => cardIndex !== index);
}
</script>

<template>
    <div class="social-editor">
        <ul class="social-editor__grid">
            <li
                v-for="(card, index) in cards"
                :key="card.id"
                class="social-card"
                :class="{
                    'is-enabled': card.enabled,
                    'is-dragover': overIndex === index && dragIndex !== index,
                    'is-dragging': dragIndex === index,
                }"
                @dragover="onDragOver(index, $event)"
                @drop="onDrop(index, $event)"
            >
                <div class="social-card__head">
                    <span
                        class="social-card__drag"
                        role="button"
                        tabindex="0"
                        draggable="true"
                        :aria-label="t('portal.cvs.drag')"
                        @dragstart="onDragStart(index, $event)"
                        @dragend="onDragEnd"
                    >
                        <Icon name="grip" :size="14" />
                    </span>
                    <span class="social-card__tile" :style="{ background: styleFor(card.platform).tile }">
                        <HugeiconsIcon :icon="styleFor(card.platform).icon" :size="20" :color="styleFor(card.platform).ink" :stroke-width="1.8" />
                    </span>
                    <label class="social-card__title" :for="card.id">{{ titleFor(card) }}</label>
                    <button
                        v-if="card.platform === 'custom'"
                        type="button"
                        class="btn btn--ghost btn--sm btn--icon social-card__remove"
                        :aria-label="t('portal.public_profile.social.remove')"
                        @click="removeCard(index)"
                    >
                        <Icon name="trash" :size="14" />
                    </button>
                    <Switch :id="card.id" v-model="card.enabled" size="sm" />
                </div>

                <div v-if="card.enabled" class="social-card__body">
                    <input
                        v-model="card.url"
                        type="url"
                        class="input"
                        dir="ltr"
                        :aria-label="t('portal.public_profile.social.url')"
                        :placeholder="styleFor(card.platform).placeholder"
                    />
                    <input
                        v-if="card.platform === 'custom'"
                        v-model="card.label"
                        class="input"
                        :aria-label="t('portal.public_profile.social.label')"
                        :placeholder="t('portal.public_profile.social.label_placeholder')"
                    />
                </div>
            </li>
        </ul>

        <button type="button" class="btn btn--secondary btn--sm" @click="addCustomLink">
            <Icon name="plus" :size="14" /> {{ t('portal.public_profile.social.add_custom') }}
        </button>
    </div>
</template>

<style scoped>
.social-editor { display: grid; gap: 1rem; }
.social-editor__grid {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    align-items: start;
}
.social-card {
    background: var(--color-paper-2);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-lg);
    padding: 0.7rem 0.8rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, opacity 0.15s ease;
}
.social-card.is-enabled {
    background: var(--color-white);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.social-card.is-dragover {
    border-color: var(--color-ink);
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
}
.social-card.is-dragging { opacity: 0.45; }
.social-card__head {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.social-card__drag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 28px;
    margin-inline-start: -0.25rem;
    color: var(--color-muted);
    border-radius: var(--radius-sm);
    cursor: grab;
    user-select: none;
}
.social-card__drag:hover { color: var(--color-ink); background: var(--color-paper-2); }
.social-card__drag:active { cursor: grabbing; }
.social-card__tile {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
    transition: filter 0.2s ease, opacity 0.2s ease;
}
.social-card:not(.is-enabled) .social-card__tile {
    filter: grayscale(1);
    opacity: 0.5;
}
.social-card__title {
    flex: 1;
    min-width: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}
.social-card:not(.is-enabled) .social-card__title { color: var(--color-muted); }
.social-card__remove { color: var(--color-muted); }
.social-card__body {
    display: grid;
    gap: 0.5rem;
    margin-top: 0.7rem;
}
</style>
