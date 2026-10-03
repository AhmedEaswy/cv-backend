<script setup lang="ts">
/**
 * Page title with an edit icon that toggles an inline text input.
 */
const model = defineModel<string>({ required: true });

const props = withDefaults(defineProps<{
    placeholder?: string;
    maxlength?: number;
}>(), {
    placeholder: '',
    maxlength: 120,
});

const { t } = useI18n();
const editing = ref(false);
const draft = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

const displayTitle = computed(() => model.value.trim() || props.placeholder);

async function startEdit() {
    draft.value = model.value;
    editing.value = true;
    await nextTick();
    inputRef.value?.focus();
    inputRef.value?.select();
}

function commit() {
    if (!editing.value) return;
    const next = draft.value.trim();
    if (next) {
        model.value = next.slice(0, props.maxlength);
    }
    editing.value = false;
}

function cancel() {
    editing.value = false;
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
        event.preventDefault();
        commit();
    } else if (event.key === 'Escape') {
        event.preventDefault();
        cancel();
    }
}
</script>

<template>
    <div class="editable-title" :class="{ 'is-editing': editing }">
        <input
            v-if="editing"
            ref="inputRef"
            v-model="draft"
            type="text"
            class="editable-title__input"
            :maxlength="maxlength"
            :placeholder="placeholder"
            :aria-label="t('portal.editable_title.name')"
            @blur="commit"
            @keydown="onKeydown"
        >
        <template v-else>
            <h1 class="page-header__title editable-title__text">{{ displayTitle }}</h1>
            <button
                type="button"
                class="btn btn--ghost btn--sm btn--icon editable-title__edit"
                :aria-label="t('portal.editable_title.edit')"
                :title="t('portal.editable_title.edit')"
                @click="startEdit"
            >
                <Icon name="edit" :size="16" />
            </button>
        </template>
    </div>
</template>
