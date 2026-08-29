/**
 * useClickTracker — fire-and-forget ping to /api/v1/analytics/click.
 * Uses navigator.sendBeacon so the request survives navigation away
 * to the App Store / Play Store, with a fetch-keepalive fallback.
 */
export const useClickTracker = () => {
    const config = useRuntimeConfig();
    const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
    const endpoint = `${laravel}${config.public.apiPrefix}/analytics/click`;

    return (target: string, page = 'landing') => {
        if (import.meta.server) return;
        const payload = JSON.stringify({ target, page });
        let blob: Blob | null = null;
        try { blob = new Blob([payload], { type: 'application/json' }); } catch { /* IE */ }

        if (blob && typeof navigator !== 'undefined' && navigator.sendBeacon) {
            navigator.sendBeacon(endpoint, blob);
            return;
        }

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: payload,
            keepalive: true,
        }).catch(() => { /* best-effort */ });
    };
};
