<script setup lang="ts">
/**
 * Compact ATS score ring (conic-gradient) for lists and cards.
 */
const props = withDefaults(defineProps<{
    score?: number | null
    grade?: string | null
    size?: 'sm' | 'md'
}>(), {
    score: null,
    grade: null,
    size: 'sm',
})

const { t } = useI18n()

const color = computed(() => {
    const s = props.score
    if (s == null) return 'var(--color-line)'
    if (s >= 70) return 'var(--color-success)'
    if (s >= 50) return 'var(--color-warning)'
    return 'var(--color-danger)'
})

const pct = computed(() => (props.score == null ? 0 : Math.max(0, Math.min(100, props.score))))

const label = computed(() => {
    if (props.score == null) return t('portal.cvs.ats_unchecked')
    return props.grade
        ? `${props.score} · ${props.grade}`
        : String(props.score)
})
</script>

<template>
    <div
        class="ats-score"
        :class="[
            size === 'sm' ? 'ats-score--sm' : 'ats-score--md',
            score == null && 'ats-score--empty',
        ]"
        :style="{ '--pct': pct, '--color': color }"
        :title="label"
        :aria-label="`${t('portal.cvs.ats_score')}: ${label}`"
        role="img"
    >
        <span class="ats-score__value">{{ score == null ? '—' : score }}</span>
    </div>
</template>
