/**
 * useClickTracker — fire-and-forget ping to /api/v1/analytics/click.
 * Uses navigator.sendBeacon so the request survives navigation away
 * to the App Store / Play Store, with a fetch-keepalive fallback.
 *
 * Sends X-App-Platform: web so admin reports can segment web vs mobile.
 */
export const useClickTracker = () => {
    const config = useRuntimeConfig();
    const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
    const endpoint = `${laravel}${config.public.apiPrefix}/analytics/click`;

    const trackingHeaders = (): Record<string, string> => {
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-App-Platform': 'web',
        };

        if (import.meta.client && typeof navigator !== 'undefined') {
            if (navigator.language) {
                headers['Accept-Language'] = navigator.language;
            }
            const ua = navigator.userAgent || '';
            const mobile = /iPhone|iPad|Android/i.test(ua);
            headers['X-Device-Type'] = mobile ? 'Mobile' : 'Desktop';
            const anonymousId = useAnonymousId().getOrCreate();
            if (anonymousId) {
                headers['X-Anonymous-Id'] = anonymousId;
            }
        }

        return headers;
    };

    return (target: string, page = 'landing') => {
        if (import.meta.server) return;
        const payload = JSON.stringify({ target, page });
        let blob: Blob | null = null;
        try { blob = new Blob([payload], { type: 'application/json' }); } catch { /* IE */ }

        // sendBeacon cannot set custom headers; include platform in body meta fallback
        // is handled server-side via User-Agent. Prefer fetch with headers when possible.
        fetch(endpoint, {
            method: 'POST',
            headers: trackingHeaders(),
            body: payload,
            keepalive: true,
        }).catch(() => {
            if (blob && typeof navigator !== 'undefined' && navigator.sendBeacon) {
                navigator.sendBeacon(endpoint, blob);
            }
        });
    };
};
