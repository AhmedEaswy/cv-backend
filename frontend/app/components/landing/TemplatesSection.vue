<script setup lang="ts">
/**
 * <LandingTemplates /> — the templates section. Bordered bento of mock
 * template previews with category labels. Each tile links to a public
 * template preview if available.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

interface Tile {
    title: string;
    subtitle: string;
    href: string;
    preview: string; // css preview builder key
}

const tiles: Tile[] = [
    { title: 'Ink Editorial',  subtitle: 'Editorial · A4',         href: `${laravel}/test/cv/ink-editorial`,         preview: 'editorial' },
    { title: 'Modern Pro',     subtitle: 'Modern · A4',            href: `${laravel}/test/cv/modern-professional`,   preview: 'modern' },
    { title: 'Sidebar Slate',  subtitle: 'Two-column · A4',        href: `${laravel}/test/cv/sidebar-slate`,         preview: 'slate' },
    { title: 'Forest Folio',   subtitle: 'Two-column · A4',        href: `${laravel}/test/cv/forest-folio`,          preview: 'forest' },
    { title: 'Midnight Banner', subtitle: 'Banner · A4',           href: `${laravel}/test/cv/midnight-banner`,       preview: 'midnight' },
    { title: 'Portrait',       subtitle: 'Portrait · A4',          href: `${laravel}/test/cv/portrait-modern`,       preview: 'portrait' },
];
</script>

<template>
    <section id="templates" class="section">
        <div class="container-narrow">
            <div class="tpl-head">
                <span class="eyebrow">{{ t('landing.templates_eyebrow') }}</span>
                <h2 class="display-2 tpl-head__title">{{ t('landing.section_templates_title') }}</h2>
                <p class="lede tpl-head__sub">{{ t('landing.section_templates_subtitle') }}</p>
            </div>

            <div class="tpl-grid">
                <a v-for="t_ in tiles" :key="t_.title" :href="t_.href" target="_blank" rel="noopener" class="tpl-tile">
                    <div :class="['tpl-preview', `tpl-preview--${t_.preview}`]">
                        <div class="tpl-mock">
                            <div class="tpl-mock__header">
                                <div class="tpl-mock__avatar" />
                                <div class="tpl-mock__title">
                                    <span /><span class="short" />
                                </div>
                            </div>
                            <div class="tpl-mock__line" v-for="i in 5" :key="i" :style="{ width: [95, 80, 90, 70, 60][i - 1] + '%' }" />
                            <div class="tpl-mock__section" />
                            <div class="tpl-mock__line" v-for="i in 4" :key="'b' + i" :style="{ width: [90, 85, 92, 75][i - 1] + '%' }" />
                        </div>
                    </div>
                    <div class="tpl-meta">
                        <h3>{{ t_.title }}</h3>
                        <p>{{ t_.subtitle }}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>

<style scoped>
.tpl-head { text-align: center; margin-bottom: 3rem; }
.tpl-head__title { margin: 0.75rem 0 1rem; }
.tpl-head__sub { margin: 0 auto; }

.tpl-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 640px)  { .tpl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .tpl-grid { grid-template-columns: repeat(3, 1fr); } }

.tpl-tile {
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease;
}
.tpl-tile:hover { transform: translateY(-3px); }
.tpl-tile:hover .tpl-preview { border-color: var(--color-ink); box-shadow: var(--shadow-3); }

.tpl-preview {
    aspect-ratio: 3 / 4;
    background: var(--color-paper-2);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}
.tpl-preview--editorial { background: linear-gradient(180deg, #fafaf9 0%, #f4f4f2 100%); }
.tpl-preview--modern    { background: var(--color-white); border-color: var(--color-ink); }
.tpl-preview--slate     { background: #1a1a1a; }
.tpl-preview--slate .tpl-mock__line { background: rgba(255,255,255,0.18) !important; }
.tpl-preview--slate .tpl-mock__section { background: rgba(255,255,255,0.12) !important; }
.tpl-preview--slate .tpl-mock__avatar { background: rgba(255,255,255,0.4) !important; }
.tpl-preview--slate .tpl-mock__title > span { background: rgba(255,255,255,0.5) !important; }
.tpl-preview--forest    { background: linear-gradient(135deg, #f0ede5 0%, #e3ddc9 100%); }
.tpl-preview--midnight  { background: linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%); }
.tpl-preview--midnight .tpl-mock__line { background: rgba(255,255,255,0.18) !important; }
.tpl-preview--midnight .tpl-mock__section { background: rgba(255,255,255,0.12) !important; }
.tpl-preview--midnight .tpl-mock__avatar { background: rgba(255,255,255,0.4) !important; }
.tpl-preview--midnight .tpl-mock__title > span { background: rgba(255,255,255,0.5) !important; }
.tpl-preview--portrait  { background: var(--color-white); }

.tpl-mock {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 6px;
    padding: 0.85rem;
    height: 100%;
    box-shadow: 0 8px 20px -10px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.tpl-preview--slate .tpl-mock,
.tpl-preview--midnight .tpl-mock { background: rgba(255, 255, 255, 0.05); }
.tpl-mock__header { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; }
.tpl-mock__avatar { width: 24px; height: 24px; border-radius: 50%; background: var(--color-ink); flex-shrink: 0; }
.tpl-mock__title { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.tpl-mock__title > span { display: block; height: 5px; background: var(--color-ink); border-radius: 2px; opacity: 0.85; width: 60%; }
.tpl-mock__title > span.short { width: 40%; opacity: 0.5; height: 4px; }
.tpl-mock__line { height: 4px; background: var(--color-line-2); border-radius: 2px; }
.tpl-mock__section { height: 1px; background: var(--color-ink); opacity: 0.2; margin-block: 0.4rem; width: 30%; }

.tpl-meta h3 {
    font-family: var(--font-display);
    font-size: 1.15rem;
    font-weight: 400;
    letter-spacing: -0.015em;
    color: var(--color-ink);
    margin: 0 0 0.25rem;
}
.tpl-meta p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--color-ink-soft);
}
</style>
