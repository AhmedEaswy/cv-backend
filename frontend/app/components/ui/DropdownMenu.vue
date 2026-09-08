<script setup lang="ts">
/**
 * Icon-triggered action menu (vertical dots). Reuses create-menu panel styles.
 */
export type DropdownMenuItem = {
    key: string;
    label: string;
    icon?: string;
    danger?: boolean;
    disabled?: boolean;
    to?: string;
};

const props = withDefaults(defineProps<{
    items: DropdownMenuItem[];
    label?: string;
    align?: 'start' | 'end';
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'link';
}>(), {
    align: 'end',
    variant: 'ghost',
});

const emit = defineEmits<{ select: [key: string] }>();

const open = ref(false);
const rootEl = ref<HTMLElement | null>(null);

function close() {
    open.value = false;
}

function toggle() {
    open.value = !open.value;
}

function onSelect(item: DropdownMenuItem) {
    if (item.disabled) return;
    close();
    emit('select', item.key);
}

function onDocClick(e: MouseEvent) {
    const target = e.target as Node;
    if (rootEl.value?.contains(target)) return;
    close();
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') close();
}

onMounted(() => {
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
});
</script>

<template>
    <div ref="rootEl" class="create-menu" :class="{ 'create-menu--start': align === 'start' }">
        <Button
            type="button"
            :variant="variant"
            size="sm"
            icon
            :aria-label="label || 'Actions'"
            :aria-expanded="open"
            aria-haspopup="menu"
            @click="toggle"
        >
            <Icon name="more-vertical" :size="16" />
        </Button>
        <div
            class="create-menu__panel"
            :class="{ open }"
            role="menu"
        >
            <template v-for="item in items" :key="item.key">
                <NuxtLink
                    v-if="item.to && !item.disabled"
                    :to="item.to"
                    class="create-menu__item"
                    :class="{ 'create-menu__item--danger': item.danger }"
                    role="menuitem"
                    @click="onSelect(item)"
                >
                    <Icon v-if="item.icon" :name="item.icon" :size="14" />
                    <span>{{ item.label }}</span>
                </NuxtLink>
                <button
                    v-else
                    type="button"
                    class="create-menu__item"
                    :class="{ 'create-menu__item--danger': item.danger }"
                    role="menuitem"
                    :disabled="item.disabled"
                    @click="onSelect(item)"
                >
                    <Icon v-if="item.icon" :name="item.icon" :size="14" />
                    <span>{{ item.label }}</span>
                </button>
            </template>
        </div>
    </div>
</template>
