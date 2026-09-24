/**
 * Stable browser install UUID for guest attribution (mirrors mobile X-Anonymous-Id).
 */
const ANONYMOUS_ID_KEY = 'cv.anonymous_id';

function uuidV4(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

export const useAnonymousId = () => {
    const getOrCreate = (): string | null => {
        if (!import.meta.client) return null;
        try {
            const existing = window.localStorage.getItem(ANONYMOUS_ID_KEY);
            if (existing && existing.trim()) {
                return existing.trim().toLowerCase();
            }
            const id = uuidV4().toLowerCase();
            window.localStorage.setItem(ANONYMOUS_ID_KEY, id);
            return id;
        } catch {
            return null;
        }
    };

    return { getOrCreate };
};
