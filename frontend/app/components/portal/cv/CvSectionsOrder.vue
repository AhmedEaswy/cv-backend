<script setup lang="ts">
const sections = defineModel<string[]>({ required: true });
const { t } = useI18n();

function move(index: number, delta: number) {
    const next = index + delta;
    if (next < 0 || next >= sections.value.length) return;
    const copy = [...sections.value];
    const [item] = copy.splice(index, 1);
    copy.splice(next, 0, item);
    sections.value = copy;
}

function labelFor(section: string) {
    const map: Record<string, string> = {
        'Personal Information': 'portal.cvs.section.personal',
        Skills: 'portal.cvs.section.skills',
        Education: 'portal.cvs.section.education',
        Experience: 'portal.cvs.section.experience',
        Projects: 'portal.cvs.section.projects',
        Languages: 'portal.cvs.section.languages',
        Interests: 'portal.cvs.section.interests',
    };
    return map[section] ? t(map[section]) : section;
}
</script>

<template>
    <ol class="cv-order">
        <li v-for="(section, index) in sections" :key="section" class="cv-order__item">
            <span class="cv-order__label">{{ labelFor(section) }}</span>
            <div class="cv-order__actions">
                <button
                    type="button"
                    class="btn btn--ghost btn--sm btn--icon"
                    :disabled="index === 0"
                    :aria-label="$t('portal.cvs.move_up')"
                    @click="move(index, -1)"
                >
                    <Icon name="chevron-up" :size="14" />
                </button>
                <button
                    type="button"
                    class="btn btn--ghost btn--sm btn--icon"
                    :disabled="index === sections.length - 1"
                    :aria-label="$t('portal.cvs.move_down')"
                    @click="move(index, 1)"
                >
                    <Icon name="chevron-down" :size="14" />
                </button>
            </div>
        </li>
    </ol>
</template>
