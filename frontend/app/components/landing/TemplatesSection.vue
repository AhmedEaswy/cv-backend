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

