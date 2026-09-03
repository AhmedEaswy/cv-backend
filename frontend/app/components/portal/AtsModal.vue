<script setup lang="ts">
/**
 * <AtsModal :cv-id="123" /> — runs an ATS check on a CV against a
 * job description, and visualises the score + category bars + checks.
 */
import type { AtsResult } from '~/composables/usePortalApi';

const props = defineProps<{ open: boolean; cvId?: number | null }>();
const emit = defineEmits<{ 'update:open': [v: boolean] }>();

const { t } = useI18n();
const portal = usePortalApi();
const jobDescription = ref('');
const result = ref<AtsResult | null>(null);
const running = ref(false);
const mode = ref<'cv' | 'pdf'>('cv');
const pdfFile = ref<File | null>(null);

function close() { emit('update:open', false); }

async function run() {
    if (!props.cvId && mode.value === 'cv') return;
    if (mode.value === 'pdf' && !pdfFile.value) return;

    running.value = true;
    result.value = null;
    const payload: any = { mode: mode.value, jobDescription: jobDescription.value || undefined };
    if (mode.value === 'cv') payload.cvId = props.cvId;
    if (mode.value === 'pdf') payload.file = pdfFile.value;
    result.value = await portal.atsCheck(payload);
    running.value = false;
}

function onFileChange(e: Event) {
    const f = (e.target as HTMLInputElement).files?.[0];
    pdfFile.value = f || null;
}

const categoryLabel = (key: string) => {
    const map: Record<string, string> = {
        contact: t('portal.ats.category.contact'),
        summary: t('portal.ats.category.summary'),
        experience: t('portal.ats.category.experience'),
        education: t('portal.ats.category.education'),
        skills: t('portal.ats.category.skills'),
        formatting: t('portal.ats.category.formatting'),
        keyword_fit: t('portal.ats.category.keyword_fit'),
    };
    return map[key] || key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};

const scoreColor = computed(() => {
    if (!result.value) return 'var(--color-ink)';
    if (result.value.score >= 70) return 'var(--color-success)';
    if (result.value.score >= 50) return 'var(--color-warning)';
    return 'var(--color-danger)';
});
</script>

<template>
    <Modal :open="open" size="lg" @update:open="(v) => emit('update:open', v)">
        <template #header>
            <div>
                <h3 class="modal__title">{{ t('portal.ats.title') }}</h3>
                <p class="modal__sub">{{ t('portal.ats.subtitle') }}</p>
            </div>
        </template>

        <div class="ats">
            <div class="ats__tabs">
                <button :class="['ats__tab', mode === 'cv' && 'ats__tab--active']" @click="mode = 'cv'">
                    <Icon name="file" :size="14" />
                    {{ t('portal.ats.tab_cv') }}
                </button>
                <button :class="['ats__tab', mode === 'pdf' && 'ats__tab--active']" @click="mode = 'pdf'">
                    <Icon name="upload" :size="14" />
                    {{ t('portal.ats.tab_pdf') }}
                </button>
            </div>

            <div v-if="mode === 'pdf'" class="field">
                <label class="field-label">{{ t('portal.ats.tab_pdf') }}</label>
                <label class="upload-zone">
                    <input type="file" accept="application/pdf" @change="onFileChange" />
                    <span v-if="!pdfFile" class="upload-zone__hint">{{ t('portal.ats.upload_help') }}</span>
                    <span v-else class="upload-zone__file">
                        <Icon name="check-circle" :size="16" /> {{ pdfFile.name }}
                    </span>
                </label>
            </div>

            <div class="field">
                <label class="field-label" for="job-description">{{ t('portal.ats.job_description') }}</label>
                <textarea
                    id="job-description"
                    v-model="jobDescription"
                    class="textarea"
                    rows="5"
                    :placeholder="t('portal.ats.job_description')"
                />
                <span class="field-hint">{{ t('portal.ats.job_description_help') }}</span>
            </div>

            <div v-if="result" class="ats__result">
                <div class="ats__score-wrap">
                    <div
                        class="ats-score"
                        :style="{ '--pct': result.score, '--color': scoreColor }"
                    >
                        <span class="ats-score__value">{{ result.score }}</span>
                        <span class="ats-score__grade">Grade {{ result.grade }}</span>
                    </div>
                </div>

                <div class="ats__bars">
                    <h4 class="ats__section-title">{{ t('portal.ats.score') }}</h4>
                    <div v-for="(value, key) in result.categories" :key="key" class="ats-bar">
                        <div class="ats-bar__label">
                            <span>{{ categoryLabel(String(key)) }}</span>
                            <span>{{ value }}%</span>
                        </div>
                        <div class="ats-bar__track">
                            <div class="ats-bar__fill" :style="{ width: value + '%' }" />
                        </div>
                    </div>
                </div>

                <div v-if="result.keywords" class="ats__keywords">
                    <h4 class="ats__section-title">{{ t('portal.ats.coverage') }} — {{ result.keywords.coverage_percent }}%</h4>
                    <div class="ats__kw">
                        <div>
                            <div class="ats__kw-title">{{ t('portal.ats.matched_keywords') }}</div>
                            <div class="ats__kw-chips">
                                <Tag v-for="k in result.keywords.matched.slice(0, 12)" :key="k" variant="soft">{{ k }}</Tag>
                            </div>
                        </div>
                        <div>
                            <div class="ats__kw-title">{{ t('portal.ats.missing_keywords') }}</div>
                            <div class="ats__kw-chips">
                                <Tag v-for="k in result.keywords.missing.slice(0, 12)" :key="k" variant="danger">{{ k }}</Tag>
                            </div>
                        </div>
                    </div>
                </div>

                <details class="ats__checks">
                    <summary>{{ t('portal.ats.score') }} details</summary>
                    <ul class="ats__check-list">
                        <li v-for="c in result.checks" :key="c.id" :class="['ats__check', c.passed ? 'ats__check--pass' : 'ats__check--fail']">
                            <Icon :name="c.passed ? 'check-circle' : 'alert'" :size="14" />
                            <span>{{ c.label || c.id }}</span>
                            <Tag v-if="c.passed" variant="success">{{ t('portal.ats.check.passed') }}</Tag>
                            <Tag v-else variant="warning">{{ t('portal.ats.check.failed') }}</Tag>
                        </li>
                    </ul>
                </details>
            </div>

            <div v-else-if="!running" class="ats__placeholder">
                <Icon name="target" :size="28" />
                <p>{{ t('portal.ats.no_result') }}</p>
            </div>

            <div v-if="running" class="ats__loading">
                <span class="ats__spinner" /> {{ t('portal.ats.running') }}
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="close">{{ t('portal.common.cancel') }}</Button>
            <Button variant="primary" :loading="running" @click="run">
                <Icon name="zap" :size="14" /> {{ t('portal.ats.run') }}
            </Button>
        </template>
    </Modal>
</template>

