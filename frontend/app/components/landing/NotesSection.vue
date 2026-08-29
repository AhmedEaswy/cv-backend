<script setup lang="ts">
/**
 * <LandingNotes /> — "Notes that work the way you think."
 * Mirrors the editor preview block in the reference.
 */
const { t } = useI18n();
</script>

<template>
    <section class="section">
        <div class="container-narrow notes">
            <div class="notes__copy">
                <h2 class="display-2 notes__title">{{ t('landing.section_notes_title') }}</h2>
                <p class="lede notes__sub">{{ t('landing.section_notes_subtitle') }}</p>
            </div>

            <div class="notes__editor">
                <div class="notes__toolbar">
                    <span class="notes__chip">Style 01</span>
                    <span class="notes__btn" aria-hidden="true">B</span>
                    <span class="notes__btn" aria-hidden="true"><em>I</em></span>
                    <span class="notes__btn" aria-hidden="true">
                        <Icon name="menu" :size="14" />
                    </span>
                    <span class="notes__spacer" />
                    <span class="notes__dot notes__dot--dark" />
                    <span class="notes__dot notes__dot--light" />
                </div>
                <div class="notes__body">
                    <p class="notes__p notes__p--hl"><strong>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</strong> Etiam eu turpis molestie, dictum est a, mattis tellus. Sed dignissim, metus nec fringilla accumsan, risus sem sollicitudin lacus, ut interdum tellus elit sed risus. Maecenas eget condimentum velit, sit amet feugiat lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Praesent auctor purus luctus enim egestas, tincidunt eleifend turpis. <mark>Curabitur vel bibendum lorem.</mark></p>
                    <p class="notes__p">Morbi convallis convallis diam sit amet lacinia. Aliquam in elementum tellus.</p>
                    <p class="notes__p">Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Nullam id dolor id nibh ultricies vehicula ut id elit. Cras mattis consectetur purus sit amet fermentum. Donec sed odio dui. Cras justo odio, dapibus ut facilisis in, egestas eget quam.</p>
                </div>
                <div class="notes__actions">
                    <span class="tag tag--outline">
                        <Icon name="sparkles" :size="12" />
                        {{ t('landing.section_notes_ai_summary') }}
                    </span>
                    <span class="tag tag--soft">
                        {{ t('landing.section_notes_save') }}
                        <Icon name="check" :size="12" />
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.notes {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
    align-items: start;
}
@media (min-width: 1024px) {
    .notes { grid-template-columns: 1fr 1.1fr; gap: 4.5rem; }
}
.notes__copy { padding-top: 1rem; }
.notes__title { margin: 0 0 1rem; }
.notes__sub { margin: 0; }
.notes__editor {
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-3);
    overflow: hidden;
}
.notes__toolbar {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 0.85rem;
    border-bottom: 1px solid var(--color-line);
    background: var(--color-paper);
}
.notes__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.55rem;
    border-radius: var(--radius-pill);
    background: var(--color-ink);
    color: var(--color-paper);
    font-size: 0.7rem;
    font-weight: 500;
}
.notes__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.6rem;
    height: 1.6rem;
    padding: 0 0.4rem;
    border-radius: var(--radius-sm);
    background: var(--color-paper-2);
    color: var(--color-ink-soft);
    font-size: 0.75rem;
    font-weight: 500;
    cursor: default;
}
.notes__spacer { flex: 1; }
.notes__dot { width: 16px; height: 16px; border-radius: 50%; display: inline-block; }
.notes__dot--dark { background: var(--color-ink); }
.notes__dot--light { background: var(--color-white); border: 1px solid var(--color-line); }

.notes__body {
    padding: 1.5rem 1.75rem 1.25rem;
    font-size: 0.85rem;
    line-height: 1.7;
    color: var(--color-ink-soft);
}
.notes__p { margin: 0 0 0.85rem; }
.notes__p mark {
    background: var(--color-ink);
    color: var(--color-paper);
    padding: 0.05em 0.3em;
    border-radius: 3px;
}
.notes__actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0 1.75rem 1.25rem;
    flex-wrap: wrap;
}
</style>
