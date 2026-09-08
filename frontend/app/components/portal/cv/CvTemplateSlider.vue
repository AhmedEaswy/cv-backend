<script setup lang="ts">
/**
 * Template picker — Swiper carousel of preview cards.
 * Used on CV edit, cover letter edit, and public profile.
 */
import { Navigation } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import type { Swiper as SwiperInstance } from 'swiper';
import 'swiper/css';
import { resolvePublicFileUrl } from '~/composables/usePortalApi';
import type { TemplateCatalogKind } from '~/composables/useLocalizedTemplate';
import { useLocalizedTemplate } from '~/composables/useLocalizedTemplate';

export type TemplateOption = {
    id: number;
    name: string;
    preview?: string | null;
    description?: string | null;
    is_default?: boolean;
    supports_image?: boolean;
};

/** @deprecated Prefer TemplateOption */
export type CvTemplateOption = TemplateOption;

const model = defineModel<string | number | null>({ required: true });
const props = withDefaults(defineProps<{
    templates: TemplateOption[];
    kind?: TemplateCatalogKind;
}>(), {
    kind: 'cv',
});

const { t } = useI18n();
const { dir } = useDirection();
const { label, description } = useLocalizedTemplate();
const modules = [Navigation];
const swiper = ref<SwiperInstance | null>(null);

function onSwiper(instance: SwiperInstance) {
    swiper.value = instance;
}

function select(id: number) {
    model.value = id;
}

function isSelected(id: number) {
    return String(model.value ?? '') === String(id);
}

function previewSrc(tpl: TemplateOption) {
    return tpl.preview ? resolvePublicFileUrl(tpl.preview) : null;
}

function tplLabel(tpl: TemplateOption) {
    return label(props.kind, tpl.name);
}

function tplDescription(tpl: TemplateOption) {
    return description(props.kind, tpl.name, tpl.description);
}

function slidePrev() {
    swiper.value?.slidePrev();
}

function slideNext() {
    swiper.value?.slideNext();
}

watch(() => props.templates.length, () => {
    nextTick(() => swiper.value?.update());
});

watch(dir, (value) => {
    swiper.value?.changeLanguageDirection(value);
});
</script>

<template>
    <div class="tpl-slider">
        <div class="tpl-slider__nav">
            <button type="button" class="tpl-slider__arrow" :aria-label="t('portal.cvs.template_prev')" @click="slidePrev">
                <Icon name="chevron-left" :size="16" />
            </button>
            <button type="button" class="tpl-slider__arrow" :aria-label="t('portal.cvs.template_next')" @click="slideNext">
                <Icon name="chevron-right" :size="16" />
            </button>
        </div>

        <ClientOnly>
            <Swiper
                class="tpl-slider__swiper"
                :modules="modules"
                :dir="dir"
                slides-per-view="auto"
                :space-between="10"
                :watch-overflow="true"
                :grab-cursor="true"
                @swiper="onSwiper"
            >
                <SwiperSlide
                    v-for="tpl in templates"
                    :key="tpl.id"
                    class="tpl-slider__slide"
                >
                    <button
                        type="button"
                        class="tpl-slider__card"
                        :class="{ 'tpl-slider__card--selected': isSelected(tpl.id) }"
                        :aria-pressed="isSelected(tpl.id)"
                        :aria-label="tplLabel(tpl)"
                        :title="tplDescription(tpl) || tplLabel(tpl)"
                        @click="select(tpl.id)"
                    >
                        <span class="tpl-slider__preview">
                            <img
                                v-if="previewSrc(tpl)"
                                :src="previewSrc(tpl)!"
                                :alt="tplLabel(tpl)"
                                class="tpl-slider__img"
                                loading="lazy"
                            >
                            <span v-else class="tpl-slider__placeholder" aria-hidden="true">
                                <Icon name="file" :size="22" />
                            </span>
                            <span v-if="isSelected(tpl.id)" class="tpl-slider__check" aria-hidden="true">
                                <Icon name="check" :size="12" />
                            </span>
                            <span v-if="tpl.is_default" class="tpl-slider__badge">{{ t('portal.cvs.template_default') }}</span>
                        </span>
                        <span class="tpl-slider__name">{{ tplLabel(tpl) }}</span>
                    </button>
                </SwiperSlide>
            </Swiper>
            <template #fallback>
                <div class="tpl-slider__fallback" aria-hidden="true" />
            </template>
        </ClientOnly>
    </div>
</template>
