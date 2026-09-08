<script setup lang="ts">
import type { CvLanguage } from '~/composables/usePortalApi';

const items = defineModel<CvLanguage[]>({ required: true });

const { overIndex, moveToTop, moveToBottom, onDragStart, onDragOver, onDrop, onDragEnd } = useSortableList(items);

const levels = [
    { value: 1, key: 'portal.cvs.proficiency.beginner' },
    { value: 2, key: 'portal.cvs.proficiency.intermediate' },
    { value: 3, key: 'portal.cvs.proficiency.advanced' },
    { value: 4, key: 'portal.cvs.proficiency.fluent' },
    { value: 5, key: 'portal.cvs.proficiency.native' },
];

function addItem() {
    items.value = [...items.value, { name: '', proficiencyLevel: 3 }];
}

function removeItem(index: number) {
    items.value = items.value.filter((_, i) => i !== index);
}
</script>

<template>
    <div class="cv-entries">
        <p v-if="items.length === 0" class="cv-entries__empty">{{ $t('portal.cvs.empty_languages') }}</p>
        <article
            v-for="(item, index) in items"
            :key="index"
            class="cv-entry"
            :class="{ 'is-dragover': overIndex === index }"
            @dragover="onDragOver(index, $event)"
            @drop="onDrop(index, $event)"
        >
            <CvRepeaterHead
                :index="index"
                :total="items.length"
                @top="moveToTop(index)"
                @bottom="moveToBottom(index)"
                @remove="removeItem(index)"
                @dragstart="onDragStart(index, $event)"
                @dragend="onDragEnd"
            />
            <div class="field-grid">
                <div class="field" style="margin-bottom: 0">
                    <label class="field-label">{{ $t('portal.cvs.field.language_name') }}</label>
                    <input v-model="item.name" type="text" class="input" maxlength="255" :placeholder="$t('portal.cvs.field.language_name_placeholder')" />
                </div>
                <div class="field" style="margin-bottom: 0">
                    <label class="field-label">{{ $t('portal.cvs.field.proficiency') }}</label>
                    <SelectInput
                        v-model="item.proficiencyLevel"
                        :placeholder="$t('portal.cvs.field.proficiency_placeholder')"
                        :options="levels.map((level) => ({ value: level.value, label: $t(level.key) }))"
                    />
                </div>
            </div>
        </article>
        <button type="button" class="btn btn--secondary btn--sm" @click="addItem">
            <Icon name="plus" :size="14" />
            {{ $t('portal.cvs.add_language') }}
        </button>
    </div>
</template>
