<script setup lang="ts">
/**
 * <LandingFooter /> — final footer with brand, nav columns, social row.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const year = new Date().getFullYear();
</script>

<template>
    <footer class="footer">
        <div class="container-narrow">
            <div class="footer__grid">
                <div class="footer__brand">
                    <NuxtLink to="/" class="footer__brand-mark">
                        <span class="footer__brand-dot" aria-hidden="true" />
                        <span class="footer__brand-name">{{ appName }}</span>
                    </NuxtLink>
                    <p class="footer__brand-text">{{ t('landing.footer_about') }}</p>
                </div>

                <div class="footer__col">
                    <h4>{{ t('landing.footer_product') }}</h4>
                    <ul>
                        <li><a href="#features">{{ t('landing.footer_features') }}</a></li>
                        <li><a href="#download">{{ t('landing.section_download_title') }}</a></li>
                        <li><a href="#pricing">{{ t('landing.footer_pricing') }}</a></li>
                        <li><a href="#templates">{{ t('landing.nav.templates') }}</a></li>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4>{{ t('landing.footer_company') }}</h4>
                    <ul>
                        <li><a href="#">{{ t('landing.footer_about_link') }}</a></li>
                        <li><a href="#">{{ t('landing.footer_careers') }}</a></li>
                        <li><a href="#">{{ t('landing.footer_contact') }}</a></li>
                        <li><a href="#">{{ t('landing.footer_press') }}</a></li>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4>{{ t('landing.footer_resources') }}</h4>
                    <ul>
                        <li><a href="#">{{ t('landing.footer_help') }}</a></li>
                        <li><a href="#">{{ t('landing.footer_updates') }}</a></li>
                        <li><a href="#">{{ t('landing.footer_guides') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer__rule" />

            <div class="footer__bottom">
                <div class="footer__copy">© {{ year }} {{ appName }}. {{ t('landing.footer_rights') }}</div>
                <div class="footer__social">
                    <a href="#" aria-label="LinkedIn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM8 18H5V9h3v9zM6.5 7.7a1.7 1.7 0 1 1 0-3.5 1.7 1.7 0 0 1 0 3.5zM18 18h-3v-4.5c0-1.1-.4-1.8-1.4-1.8-.8 0-1.3.5-1.5 1.1-.1.2-.1.5-.1.8V18h-3V9h3v1.3c.4-.6 1.1-1.5 2.7-1.5 2 0 3.4 1.3 3.4 4.1V18z"/></svg>
                    </a>
                    <a href="#" aria-label="X">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h3.3l-7.2 8.2 8.5 11.8h-6.6L10.6 14 5 22H1.6l7.7-8.8L1.5 2h6.8l4.7 6.5L18 2zM16.7 20h1.8L7.3 4H5.4L16.7 20z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.footer__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
    margin-bottom: 3rem;
}
@media (min-width: 640px)  { .footer__grid { grid-template-columns: 1.5fr 1fr 1fr 1fr; } }

.footer__brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    text-decoration: none;
    color: var(--color-ink);
    font-weight: 600;
    margin-bottom: 1rem;
}
.footer__brand-dot {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--color-ink);
    display: inline-block;
}
.footer__brand-name { font-size: 1rem; letter-spacing: -0.01em; }
.footer__brand-text { font-size: 0.9rem; color: var(--color-ink-soft); line-height: 1.5; max-width: 26rem; margin: 0; }

.footer__col ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem; }
.footer__col a { font-size: 0.9rem; }

.footer__rule { height: 1px; background: var(--color-line); margin: 0 0 1.5rem; }
.footer__bottom {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.footer__copy { font-size: 0.8rem; color: var(--color-muted); }
.footer__social { display: inline-flex; gap: 0.5rem; }
.footer__social a {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--color-ink-soft);
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.footer__social a:hover { background: var(--color-ink); color: var(--color-paper); border-color: var(--color-ink); }
</style>
