<script setup lang="ts">
export interface CvEntryField {
    key: string;
    label: string;
    type: 'text' | 'textarea' | 'month' | 'url' | 'checkbox';
    required?: boolean;
    span?: 'half' | 'full';
    placeholder?: string;
}

const items = defineModel<Record<string, any>[]>({ required: true });

const props = defineProps<{
    addLabel: string;
    emptyText: string;
    fields: CvEntryField[];
    defaults: Record<string, any>;
}>();

const { overIndex, moveToTop, moveToBottom, onDragStart, onDragOver, onDrop, onDragEnd } = useSortableList(items);

function addItem() {
    items.value = [...items.value, { ...props.defaults }];
}

function removeItem(index: number) {
    items.value = items.value.filter((_, i) => i !== index);
}

function showField(field: CvEntryField, item: Record<string, any>) {
    if (field.key === 'to' && item.current) return false;
    return true;
}
</script>

<template>
    <div class="cv-entries">
        <p v-if="items.length === 0" class="cv-entries__empty">{{ emptyText }}</p>
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
            <div class="cv-fields">
                <template v-for="field in fields" :key="field.key">
                    <div
                        v-if="showField(field, item)"
                        class="field"
                        :class="{ 'field--full': field.type === 'textarea' || field.type === 'checkbox' || field.span === 'full' }"
                    >
                        <label v-if="field.type !== 'checkbox'" class="field-label">{{ field.label }}</label>
                        <textarea
                            v-if="field.type === 'textarea'"
                            v-model="item[field.key]"
                            class="textarea"
                            rows="4"
                            :placeholder="field.placeholder"
                        />
                        <Switch
                            v-else-if="field.type === 'checkbox'"
                            v-model="item[field.key]"
                        >
                            {{ field.label }}
                        </Switch>
                        <DatePicker
                            v-else-if="field.type === 'month'"
                            v-model="item[field.key]"
                            mode="month"
                            :placeholder="field.placeholder"
                        />
                        <input
                            v-else
                            v-model="item[field.key]"
                            :type="field.type === 'url' ? 'url' : 'text'"
                            class="input"
                            :placeholder="field.placeholder"
                            :required="field.required"
                        />
                    </div>
                </template>
            </div>
        </article>
        <button type="button" class="btn btn--secondary btn--sm" @click="addItem">
            <Icon name="plus" :size="14" />
            {{ addLabel }}
        </button>
    </div>
</template>
