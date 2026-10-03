<script setup lang="ts">
import type { CvEducation, CvExperience, CvInterest, CvLanguage, CvProject, CvSkill, CvUserData } from '~/composables/usePortalApi';
import type { CvEntryField } from './CvEntriesEditor.vue';
import {
    AlignLeftIcon,
    BookOpen01Icon,
    Briefcase01Icon,
    Building01Icon,
    CodeIcon,
    Award01Icon,
    FavouriteIcon,
    Folder01Icon,
    GraduationCapIcon,
    Link01Icon,
    Location01Icon,
    School01Icon,
} from '@hugeicons/core-free-icons';

const userData = defineModel<CvUserData>('userData', { required: true });
const sectionsOrder = defineModel<string[]>('sectionsOrder', { required: true });

const { t } = useI18n();
const openId = ref<string>('personal');

const ORDER_LABEL = {
    personal: 'Personal Information',
    skills: 'Skills',
    education: 'Education',
    experience: 'Experience',
    projects: 'Projects',
    languages: 'Languages',
    interests: 'Interests',
} as const;

type SectionId = keyof typeof ORDER_LABEL;

const ID_BY_LABEL = Object.fromEntries(
    Object.entries(ORDER_LABEL).map(([id, label]) => [label, id]),
) as Record<string, SectionId>;

const DEFAULT_ORDER: SectionId[] = ['personal', 'skills', 'education', 'experience', 'projects', 'languages', 'interests'];

const orderedIds = computed<SectionId[]>({
    get() {
        const ids = sectionsOrder.value
            .map((label) => ID_BY_LABEL[label])
            .filter((id): id is SectionId => !!id);
        const seen = new Set(ids);
        for (const id of DEFAULT_ORDER) {
            if (!seen.has(id)) ids.push(id);
        }
        return ids;
    },
    set(ids) {
        sectionsOrder.value = ids.map((id) => ORDER_LABEL[id]);
    },
});

const { dragIndex, overIndex, onDragStart, onDragOver, onDrop, onDragEnd } = useSortableList(orderedIds);
let ignoreClick = false;

const sections = computed(() => ({
    personal: { title: t('portal.cvs.section.personal'), hint: personalHint.value },
    education: { title: t('portal.cvs.section.education'), hint: countHint(userData.value.educations?.length) },
    experience: { title: t('portal.cvs.section.experience'), hint: countHint(userData.value.experiences?.length) },
    projects: { title: t('portal.cvs.section.projects'), hint: countHint(userData.value.projects?.length) },
    skills: { title: t('portal.cvs.section.skills'), hint: countHint(userData.value.skills?.length) },
    languages: { title: t('portal.cvs.section.languages'), hint: countHint(userData.value.languages?.length) },
    interests: { title: t('portal.cvs.section.interests'), hint: countHint(userData.value.interests?.length) },
}));

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

function onHeaderClick(id: string) {
    if (ignoreClick) return;
    toggle(id);
}

function onSectionDragStart(index: number, event: DragEvent) {
    ignoreClick = true;
    onDragStart(index, event);
}

function onSectionDragEnd() {
    onDragEnd();
    window.setTimeout(() => { ignoreClick = false; }, 0);
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
    { key: 'institution', label: t('portal.cvs.field.institution'), type: 'text', icon: School01Icon, required: true, placeholder: t('portal.cvs.field.institution_placeholder') },
    { key: 'degree', label: t('portal.cvs.field.degree'), type: 'text', icon: GraduationCapIcon, required: true, placeholder: t('portal.cvs.field.degree_placeholder') },
    { key: 'fieldOfStudy', label: t('portal.cvs.field.field_of_study'), type: 'text', icon: BookOpen01Icon, required: true, span: 'full', placeholder: t('portal.cvs.field.field_of_study_placeholder') },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', icon: AlignLeftIcon, placeholder: t('portal.cvs.field.education_description_placeholder') },
]);

const experienceFields = computed<CvEntryField[]>(() => [
    { key: 'position', label: t('portal.cvs.field.position'), type: 'text', icon: Briefcase01Icon, required: true, placeholder: t('portal.cvs.field.position_placeholder') },
    { key: 'company', label: t('portal.cvs.field.company'), type: 'text', icon: Building01Icon, placeholder: t('portal.cvs.field.company_placeholder') },
    { key: 'location', label: t('portal.cvs.field.location'), type: 'text', icon: Location01Icon, span: 'full', placeholder: t('portal.cvs.field.location_placeholder') },
    {
        key: 'locationType',
        label: t('portal.cvs.field.location_type'),
        type: 'select',
        icon: Location01Icon,
        placeholder: t('portal.cvs.field.please_select'),
        options: [
            { value: 'on_site', label: t('portal.cvs.location_type.on_site') },
            { value: 'hybrid', label: t('portal.cvs.location_type.hybrid') },
            { value: 'remote', label: t('portal.cvs.location_type.remote') },
        ],
    },
    {
        key: 'employmentType',
        label: t('portal.cvs.field.employment_type'),
        type: 'select',
        icon: Briefcase01Icon,
        placeholder: t('portal.cvs.field.please_select'),
        options: [
            { value: 'full_time', label: t('portal.cvs.employment_type.full_time') },
            { value: 'part_time', label: t('portal.cvs.employment_type.part_time') },
            { value: 'self_employed', label: t('portal.cvs.employment_type.self_employed') },
            { value: 'freelance', label: t('portal.cvs.employment_type.freelance') },
            { value: 'contract', label: t('portal.cvs.employment_type.contract') },
            { value: 'internship', label: t('portal.cvs.employment_type.internship') },
            { value: 'apprenticeship', label: t('portal.cvs.employment_type.apprenticeship') },
            { value: 'seasonal', label: t('portal.cvs.employment_type.seasonal') },
        ],
    },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'current', label: t('portal.cvs.field.current_role'), type: 'checkbox' },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', icon: AlignLeftIcon, placeholder: t('portal.cvs.field.experience_description_placeholder') },
]);

const projectFields = computed<CvEntryField[]>(() => [
    { key: 'title', label: t('portal.cvs.field.project_title'), type: 'text', icon: Folder01Icon, required: true, placeholder: t('portal.cvs.field.project_title_placeholder') },
    { key: 'url', label: t('portal.cvs.field.project_url'), type: 'url', icon: Link01Icon, placeholder: t('portal.cvs.field.project_url_placeholder') },
    { key: 'technologies', label: t('portal.cvs.field.technologies'), type: 'text', icon: CodeIcon, span: 'full', placeholder: t('portal.cvs.field.technologies_placeholder') },
    { key: 'from', label: t('portal.cvs.field.from'), type: 'month', placeholder: t('portal.cvs.field.from_placeholder') },
    { key: 'to', label: t('portal.cvs.field.to'), type: 'month', placeholder: t('portal.cvs.field.to_placeholder') },
    { key: 'current', label: t('portal.cvs.field.current_project'), type: 'checkbox' },
    { key: 'description', label: t('portal.cvs.field.description'), type: 'textarea', icon: AlignLeftIcon, placeholder: t('portal.cvs.field.project_description_placeholder') },
]);
</script>

<template>
    <div class="cv-hub">
        <section
            v-for="(id, index) in orderedIds"
            :key="id"
            class="cv-hub__section"
            :class="{ 'is-dragover': overIndex === index, 'is-dragging': dragIndex === index }"
            @dragover="onDragOver(index, $event)"
            @drop="onDrop(index, $event)"
        >
            <div
                class="cv-hub__toggle"
                role="button"
                tabindex="0"
                draggable="true"
                :aria-expanded="openId === id"
                @click="onHeaderClick(id)"
                @keydown.enter.prevent="toggle(id)"
                @keydown.space.prevent="toggle(id)"
                @dragstart="onSectionDragStart(index, $event)"
                @dragend="onSectionDragEnd"
            >
                <Icon name="grip" :size="16" class="cv-hub__grip" />
                <span class="cv-hub__copy">
                    <span class="cv-hub__title">{{ sections[id].title }}</span>
                    <span class="cv-hub__hint">{{ sections[id].hint }}</span>
                </span>
                <Icon name="chevron-down" :size="16" class="cv-hub__chevron" :class="{ 'is-open': openId === id }" />
            </div>
            <Collapse :open="openId === id">
                <div class="cv-hub__body">
                    <CvPersonalForm v-if="id === 'personal'" v-model="userData" />
                    <CvEntriesEditor
                        v-else-if="id === 'education'"
                        v-model="educations"
                        :fields="educationFields"
                        :defaults="{ institution: '', degree: '', fieldOfStudy: '', description: '', from: '', to: '' }"
                        :add-label="t('portal.cvs.add_education')"
                        :empty-text="t('portal.cvs.empty_education')"
                    />
                    <CvEntriesEditor
                        v-else-if="id === 'experience'"
                        v-model="experiences"
                        :fields="experienceFields"
                        :defaults="{ position: '', company: '', location: '', locationType: '', employmentType: '', description: '', from: '', to: '', current: false }"
                        :add-label="t('portal.cvs.add_experience')"
                        :empty-text="t('portal.cvs.empty_experience')"
                    />
                    <CvEntriesEditor
                        v-else-if="id === 'projects'"
                        v-model="projects"
                        :fields="projectFields"
                        :defaults="{ title: '', description: '', technologies: '', url: '', from: '', to: '', current: false }"
                        :add-label="t('portal.cvs.add_project')"
                        :empty-text="t('portal.cvs.empty_projects')"
                    />
                    <CvSimpleListEditor
                        v-else-if="id === 'skills'"
                        v-model="skills"
                        :icon="Award01Icon"
                        :add-label="t('portal.cvs.add_skill')"
                        :empty-text="t('portal.cvs.empty_skills')"
                        :placeholder="t('portal.cvs.field.skill_placeholder')"
                    />
                    <CvLanguagesEditor v-else-if="id === 'languages'" v-model="languages" />
                    <CvSimpleListEditor
                        v-else-if="id === 'interests'"
                        v-model="interests"
                        :icon="FavouriteIcon"
                        :add-label="t('portal.cvs.add_interest')"
                        :empty-text="t('portal.cvs.empty_interests')"
                        :placeholder="t('portal.cvs.field.interest_placeholder')"
                    />
                </div>
            </Collapse>
        </section>
    </div>
</template>
