<script setup lang="ts">
/**
 * <TemplatesCarousel />
 *
 * 3-up carousel of the three CV templates (modern / classic / minimalist).
 * We re-implement the vanilla JS carousel as a small reactive composable
 * so it works on both SSR (renders the slides) and client (handles swipe /
 * resize / dots).
 */
const { t } = useI18n();
const { dir } = useDirection();
const isRtl = computed(() => dir.value === 'rtl');

const templateKeys = ['modern', 'classic', 'minimalist'] as const;

const trackEl = ref<HTMLElement | null>(null);
const slidesEls = ref<HTMLElement[]>([]);
const dotsEl = ref<HTMLElement | null>(null);

const perView = ref(1);
const index = ref(0);

const totalPages = computed(() => Math.max(1, templateKeys.length - perView.value + 1));

const updateTransform = () => {
    const track = trackEl.value;
    const first = slidesEls.value[0];
    if (!track || !first) return;
    const slideWidth = first.getBoundingClientRect().width;
    const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '20') || 20;
    const offset = (slideWidth + gap) * index.value;
    track.style.transform = isRtl.value
        ? `translateX(${offset}px)`
        : `translateX(-${offset}px)`;
};

const setPerView = () => {
    if (window.matchMedia('(min-width: 1024px)').matches) perView.value = 3;
    else if (window.matchMedia('(min-width: 640px)').matches) perView.value = 2;
    else perView.value = 1;
    if (index.value >= totalPages.value) index.value = totalPages.value - 1;
};

const prev = () => { if (index.value > 0) { index.value--; updateTransform(); } };
const next = () => { if (index.value < totalPages.value - 1) { index.value++; updateTransform(); } };
const goTo = (i: number) => { index.value = i; updateTransform(); };

// Touch swipe
let startX: number | null = null;
const onTouchStart = (e: TouchEvent) => { startX = e.touches[0].clientX; };
const onTouchEnd = (e: TouchEvent) => {
    if (startX === null) return;
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 40) {
        if (isRtl.value ? dx > 0 : dx < 0) {
            if (index.value < totalPages.value - 1) { index.value++; updateTransform(); }
        } else if (index.value > 0) { index.value--; updateTransform(); }
    }
    startX = null;
};

onMounted(() => {
    setPerView();
    updateTransform();
    window.addEventListener('resize', () => { setPerView(); updateTransform(); });
    // Re-update when direction changes (locale switch).
    watch(dir, () => nextTick(updateTransform));
});

const atStart = computed(() => index.value === 0 || totalPages.value <= 1);
const atEnd = computed(() => index.value >= totalPages.value - 1 || totalPages.value <= 1);
</script>

<template>
    <section id="templates" class="templates-section">
        <div class="feature-section__container templates-section__inner">
            <div class="templates-section__head">
                <div class="feature-section__eyebrow">
                    {{ t('landing.templates_eyebrow') }}
                </div>
                <h2 class="feature-section__title">
                    {{ t('landing.templates_title') }}
                </h2>
                <p class="feature-section__subtitle">
                    {{ t('landing.templates_subtitle') }}
                </p>
            </div>

            <div class="tp-carousel" data-tp-carousel>
                <div class="tp-viewport">
                    <div
                        ref="trackEl"
                        class="tp-track"
                        data-tp-track
                        @touchstart.passive="onTouchStart"
                        @touchend="onTouchEnd"
                    >
                        <article
                            v-for="key in templateKeys"
                            :key="key"
                            :ref="(el) => { if (el) slidesEls[key === 'modern' ? 0 : key === 'classic' ? 1 : 2] = el as HTMLElement; }"
                            class="tp-slide"
                        >
                            <div class="tp-card">
                                <div
                                    class="tp-preview"
                                    :class="key === 'classic' ? 'tp-preview--classic' : 'tp-preview--modern'"
                                >
                                    <!-- Modern preview -->
                                    <div
                                        v-if="key === 'modern'"
                                        class="w-[72%] h-[86%] rounded-sm bg-white shadow-md flex overflow-hidden"
                                    >
                                        <div class="w-1/3 h-full" style="background: var(--color-brand-primary);"></div>
                                        <div class="flex-1 p-2.5 space-y-1.5">
                                            <div class="w-3/4 h-1.5 rounded" style="background: var(--color-brand-midnight);"></div>
                                            <div class="w-1/2 h-1 rounded" style="background: #d9d4c7;"></div>
                                            <div class="w-full h-px my-1.5" style="background: #ece8de;"></div>
                                            <div class="w-full h-0.5 rounded" style="background: #ece8de;"></div>
                                            <div class="w-5/6 h-0.5 rounded" style="background: #ece8de;"></div>
                                            <div class="w-4/6 h-0.5 rounded" style="background: #ece8de;"></div>
                                            <div class="w-full h-px my-1.5" style="background: #ece8de;"></div>
                                            <div class="w-full h-0.5 rounded" style="background: #ece8de;"></div>
                                            <div class="w-3/4 h-0.5 rounded" style="background: #ece8de;"></div>
                                        </div>
                                    </div>

                                    <!-- Classic preview -->
                                    <div
                                        v-else-if="key === 'classic'"
                                        class="w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col"
                                    >
                                        <div class="text-center mb-2">
                                            <div class="w-2/3 h-2 rounded mx-auto mb-1" style="background: var(--color-brand-midnight);"></div>
                                            <div class="w-1/2 h-1 rounded mx-auto" style="background: #b8b1a3;"></div>
                                        </div>
                                        <div class="w-full h-px my-1" style="background: var(--color-brand-midnight);"></div>
                                        <div class="w-full h-1 mt-1.5 mb-1" style="background: var(--color-brand-midnight);"></div>
                                        <div class="w-full h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                        <div class="w-11/12 h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                        <div class="w-4/6 h-0.5 mb-2" style="background: #d9d4c7;"></div>
                                        <div class="w-full h-1 mb-1" style="background: var(--color-brand-midnight);"></div>
                                        <div class="w-full h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                        <div class="w-5/6 h-0.5 mb-0.5" style="background: #d9d4c7;"></div>
                                        <div class="w-3/4 h-0.5" style="background: #d9d4c7;"></div>
                                    </div>

                                    <!-- Minimalist preview -->
                                    <div
                                        v-else
                                        class="w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col"
                                    >
                                        <div class="w-1/2 h-2 rounded mb-1" style="background: var(--color-brand-midnight);"></div>
                                        <div class="w-1/3 h-0.5 rounded mb-3" style="background: var(--color-brand-primary);"></div>
                                        <div class="w-full h-0.5 mb-0.5" style="background: #ece8de;"></div>
                                        <div class="w-11/12 h-0.5 mb-3" style="background: #ece8de;"></div>
                                        <div class="w-1/4 h-1 mb-1.5" style="background: var(--color-brand-midnight);"></div>
                                        <div class="w-full h-0.5 mb-0.5" style="background: #ece8de;"></div>
                                        <div class="w-3/4 h-0.5" style="background: #ece8de;"></div>
                                    </div>
                                </div>
                                <div class="tp-meta">
                                    <h3 class="font-display">{{ t(`landing.template_${key}_name`) }}</h3>
                                    <span class="chip chip-violet">{{ t(`landing.template_${key}_tag`) }}</span>
                                    <p>{{ t(`landing.template_${key}_desc`) }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="tp-nav">
                    <div ref="dotsEl" class="tp-dots" role="tablist" data-tp-dots>
                        <button
                            v-for="(key, i) in templateKeys"
                            :key="`dot-${i}`"
                            type="button"
                            class="tp-dot"
                            role="tab"
                            :aria-label="t('landing.slider_goto', { n: i + 1 })"
                            :aria-current="i === index ? 'true' : 'false'"
                            @click="goTo(i)"
                        ></button>
                    </div>
                    <div class="tp-nav-btns">
                        <button type="button" class="tp-btn" :aria-label="t('landing.slider_prev')" :disabled="atStart" @click="prev">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rtl:rotate-180': true }" aria-hidden="true">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>
                        <button type="button" class="tp-btn" :aria-label="t('landing.slider_next')" :disabled="atEnd" @click="next">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rtl:rotate-180': true }" aria-hidden="true">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
