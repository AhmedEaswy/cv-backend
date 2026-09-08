export function useSortableList<T>(items: { value: T[] }) {
    const dragIndex = ref<number | null>(null);
    const overIndex = ref<number | null>(null);

    function reorder(from: number, to: number) {
        if (from === to || from < 0 || to < 0 || to >= items.value.length) return;
        const copy = [...items.value];
        const [item] = copy.splice(from, 1);
        copy.splice(to, 0, item);
        items.value = copy;
    }

    function moveToTop(index: number) {
        reorder(index, 0);
    }

    function moveToBottom(index: number) {
        reorder(index, items.value.length - 1);
    }

    function onDragStart(index: number, event: DragEvent) {
        dragIndex.value = index;
        event.dataTransfer?.setData('text/plain', String(index));
        if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
    }

    function onDragOver(index: number, event: DragEvent) {
        event.preventDefault();
        overIndex.value = index;
    }

    function onDrop(index: number, event: DragEvent) {
        event.preventDefault();
        if (dragIndex.value != null) reorder(dragIndex.value, index);
        dragIndex.value = null;
        overIndex.value = null;
    }

    function onDragEnd() {
        dragIndex.value = null;
        overIndex.value = null;
    }

    return {
        dragIndex,
        overIndex,
        moveToTop,
        moveToBottom,
        onDragStart,
        onDragOver,
        onDrop,
        onDragEnd,
    };
}
