/**
 * Cursor spotlight for solid `.btn` markup that isn't the <Button> component
 * (raw <a class="btn btn--primary"> etc.).
 */
export default defineNuxtPlugin(() => {
    if (!import.meta.client) return;

    function targetBtn(e: Event): HTMLElement | null {
        const el = (e.target as Element | null)?.closest?.('.btn') as HTMLElement | null;
        if (!el) return null;
        if (el.classList.contains('btn--link')) return null;
        if (el.getAttribute('aria-disabled') === 'true') return null;
        if ((el as HTMLButtonElement).disabled) return null;
        return el;
    }

    document.addEventListener(
        'pointermove',
        (e: PointerEvent) => {
            const el = targetBtn(e);
            if (!el) return;
            const rect = el.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            el.style.setProperty('--btn-spot-x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
            el.style.setProperty('--btn-spot-y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
        },
        { passive: true },
    );

    document.addEventListener(
        'pointerout',
        (e: PointerEvent) => {
            const el = targetBtn(e);
            if (!el) return;
            const related = e.relatedTarget as Node | null;
            if (related && el.contains(related)) return;
            el.style.removeProperty('--btn-spot-x');
            el.style.removeProperty('--btn-spot-y');
        },
        true,
    );
});
