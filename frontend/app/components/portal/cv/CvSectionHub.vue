<script setup lang="ts">
import type { CvEducation, CvExperience, CvInterest, CvLanguage, CvProject, CvSkill, CvUserData } from '~/composables/usePortalApi';
import type { CvEntryField } from './CvEntriesEditor.vue';

const userData = defineModel<CvUserData>('userData', { required: true });
const sectionsOrder = defineModel<string[]>('sectionsOrder', { required: true });

const { t } = useI18n();
const openId = ref<string>('personal');

const sections = computed(() => [
    { id: 'personal', title: t('portal.cvs.section.personal'), hint: personalHint.value },
    { id: 'education', title: t('portal.cvs.section.education'), hint: countHint(userData.value.educations?.length) },
    { id: 'experience', title: t('portal.cvs.section.experience'), hint: countHint(userData.value.experiences?.length) },
    { id: 'projects', title: t('portal.cvs.section.projects'), hint: countHint(userData.value.projects?.length) },
    { id: 'skills', title: t('portal.cvs.section.skills'), hint: countHint(userData.value.skills?.length) },
    { id: 'languages', title: t('portal.cvs.section.languages'), hint: countHint(userData.value.languages?.length) },
    { id: 'interests', title: t('portal.cvs.section.interests'), hint: countHint(userData.value.interests?.length) },
    { id: 'order', title: t('portal.cvs.section.order'), hint: t('portal.cvs.section.order_hint') },
]);

const personalHint = computed(() => {
    const first = userData.value.firstName?.trim();
    const last = userData.value.lastName?.trim();
    const name = [first, last].filter(Boolean).join(' ');
    return name || t('portal.cvs.section.empty');
});

function countHint(count?: number) {
    if (!count) return t('portal.cvs.section.empty');
    return t('portal.cvs.section.count', { n: count });
}

function toggle(id: string) {
    openId.value = openId.value === id ? '' : id;
}

const educations = computed({
    get: () => (userData.value.educations || []) as Record<string, any>[],
    set: (value) => { userData.value = { ...userData.value, educations: value as CvEducation[] }; },
});
const experiences = computed({
    get: () => (userData.value.experiences || []) as Record<string, any>[],
    set: (value) => { userData.value = { ...userData.value, experiences: value as CvExperience[] }; },
});
const projects = computed({
    get: () => (userData.value.projects || []) as Record<string, any>[],
    set: (value) => { userData.value = { ...userData.value, projects: value as CvProject[] }; },
});
const skills = computed({
    get: () => userData.value.skills || [],
    set: (value: CvSkill[]) => { userData.value = { ...userData.value, skills: value }; },
});
const languages = computed({
    get: () => userData.value.languages || [],
    set: (value: CvLanguage[]) => { userData.value = { ...userData.value, languages: value }; },
});
const interests = computed({
    get: () => userData.value.interests || [],
    set: (value: CvInterest[]) => { userData.value = { ...userData.value, interests: value }; },
});

const educationFields = computed<CvEntryField[]>(() => [
    { key: 'institution', label: t('portal.cvs.field.institution'), type: 'text', required: true, placeholder: t('portal.cvs.field.institution_placeholder') },
    { key: 'degree', label: t('portal.cvs.field.degree'), type: 'text', required: true, placeholder: t('portal.cvs.field.degree_placeholder') },
    { key: 'fieldOfStudy', label: t('portal.cvs.field.field_of_study'), type: 'text', required: true, span: 'full', placeholder: t('portal.cvs.field.field_of_study_placeholder') },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', placeholder: t('portal.cvs.field.education_description_placeholder') },
]);

const experienceFields = computed<CvEntryField[]>(() => [
    { key: 'position', label: t('portal.cvs.field.position'), type: 'text', required: true, placeholder: t('portal.cvs.field.position_placeholder') },
    { key: 'company', label: t('portal.cvs.field.company'), type: 'text', placeholder: t('portal.cvs.field.company_placeholder') },
    { key: 'location', label: t('portal.cvs.field.location'), type: 'text', span: 'full', placeholder: t('portal.cvs.field.location_placeholder') },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'current', label: t('portal.cvs.field.current_role'), type: 'checkbox' },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', placeholder: t('portal.cvs.field.experience_description_placeholder') },
]);

const projectFields = computed<CvEntryField[]>(() => [
    { key: 'title', label: t('portal.cvs.field.project_title'), type: 'text', required: true, placeholder: t('portal.cvs.field.project_title_placeholder') },
    { key: 'url', label: t('portal.cvs.field.project_url'), type: 'url', placeholder: t('portal.cvs.field.project_url_placeholder') },
    { key: 'technologies', label: t('portal.cvs.field.technologies'), type: 'text', span: 'full', placeholder: t('portal.cvs.field.technologies_placeholder') },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'current', label: t('portal.cvs.field.current_project'), type: 'checkbox' },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', placeholder: t('portal.cvs.field.project_description_placeholder') },
]);
</script>

<template>
    <div class="cv-hub">
        <section v-for="section in sections" :key="section.id" class="cv-hub__section">
            <button
                type="button"
                class="cv-hub__toggle"
                :aria-expanded="openId === section.id"
                @click="toggle(section.id)"
            >
                <span class="cv-hub__copy">
                    <span class="cv-hub__title">{{ section.title }}</span>
                    <span class="cv-hub__hint">{{ section.hint }}</span>
                </span>
                <Icon name="chevron-down" :size="16" class="cv-hub__chevron" :class="{ 'is-open': openId === section.id }" />
            </button>
            <Collapse :open="openId === section.id">
                <div class="cv-hub__body">
                    <CvPersonalForm v-if="section.id === 'personal'" v-model="userData" />
                    <CvEntriesEditor
                        v-else-if="section.id === 'education'"
                        v-model="educations"
                        :fields="educationFields"
                        :defaults="{ institution: '', degree: '', fieldOfStudy: '', description: '', from: '', to: '' }"
                        :add-label="t('portal.cvs.add_education')"
                        :empty-text="t('portal.cvs.empty_education')"
                    />
                    <CvEntriesEditor
                        v-else-if="section.id === 'experience'"
                        v-model="experiences"
                        :fields="experienceFields"
                        :defaults="{ position: '', company: '', location: '', description: '', from: '', to: '', current: false }"
                        :add-label="t('portal.cvs.add_experience')"
                        :empty-text="t('portal.cvs.empty_experience')"
                    />
                    <CvEntriesEditor
                        v-else-if="section.id === 'projects'"
                        v-model="projects"
                        :fields="projectFields"
                        :defaults="{ title: '', description: '', technologies: '', url: '', from: '', to: '', current: false }"
                        :add-label="t('portal.cvs.add_project')"
                        :empty-text="t('portal.cvs.empty_projects')"
                    />
                    <CvSimpleListEditor
                        v-else-if="section.id === 'skills'"
                        v-model="skills"
                        :add-label="t('portal.cvs.add_skill')"
                        :empty-text="t('portal.cvs.empty_skills')"
                        :placeholder="t('portal.cvs.field.skill_placeholder')"
                    />
                    <CvLanguagesEditor v-else-if="section.id === 'languages'" v-model="languages" />
                    <CvSimpleListEditor
                        v-else-if="section.id === 'interests'"
                        v-model="interests"
                        :add-label="t('portal.cvs.add_interest')"
                        :empty-text="t('portal.cvs.empty_interests')"
                        :placeholder="t('portal.cvs.field.interest_placeholder')"
                    />
                    <CvSectionsOrder v-else-if="section.id === 'order'" v-model="sectionsOrder" />
                </div>
            </Collapse>
        </section>
    </div>
</template>
