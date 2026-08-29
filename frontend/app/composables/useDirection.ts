/**
 * useDirection — small helper around the active i18n locale direction.
 * Use it for classes that need to flip between LTR/RTL.
 *
 *   const { dir, isRtl } = useDirection();
 *   <div :class="dir === 'rtl' ? 'text-right' : 'text-left'" />
 */
export const useDirection = () => {
    const { locale, locales } = useI18n();

    const current = computed(() => {
        const list = (locales.value as Array<{ code: string; dir?: 'ltr' | 'rtl' }>) || [];
        return list.find((l) => l.code === locale.value);
    });

    const dir = computed<'ltr' | 'rtl'>(() => current.value?.dir ?? 'ltr');
    const isRtl = computed(() => dir.value === 'rtl');

    return { dir, isRtl };
};
