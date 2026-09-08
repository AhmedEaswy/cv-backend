<script setup lang="ts">
const items = defineModel<Array<{ name: string }>>({ required: true });

defineProps<{
    addLabel: string;
    emptyText: string;
    placeholder?: string;
}>();

const draft = ref('');

function addItem() {
    const name = draft.value.trim();
    if (!name) return;
    items.value = [...items.value, { name }];
    draft.value = '';
}

function removeItem(index: number) {
    items.value = items.value.filter((_, i) => i !== index);
}
</script>

<template>
    <div class="cv-simple-list">
        <p v-if="items.length === 0" class="cv-entries__empty">{{ emptyText }}</p>
        <ul v-else class="cv-chip-list">
            <li v-for="(item, index) in items" :key="`${item.name}-${index}`" class="cv-chip">
                <span>{{ item.name }}</span>
                <button type="button" class="cv-chip__remove" :aria-label="$t('portal.cvs.remove')" @click="removeItem(index)">
                    <Icon name="close" :size="12" />
                </button>
            </li>
        </ul>
        <div class="cv-simple-list__add">
            <input
                v-model="draft"
                type="text"
                class="input"
                :placeholder="placeholder"
                maxlength="255"
                @keydown.enter.prevent="addItem"
            />
            <button type="button" class="btn btn--secondary" @click="addItem">
                <Icon name="plus" :size="14" />
                {{ addLabel }}
            </button>
        </div>
    </div>
</template>
