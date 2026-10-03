/**
 * Gravatar URL from email (SHA-256 of trimmed lowercase email).
 * @see https://docs.gravatar.com/general/hash/
 */

async function sha256Hex(value: string): Promise<string> {
    const data = new TextEncoder().encode(value);

    // Web Crypto is only on secure contexts (HTTPS / localhost). On SSR prefer
    // Node crypto; never throw — avatar must not take down the page.
    const subtle = globalThis.crypto?.subtle;
    if (subtle?.digest) {
        const digest = await subtle.digest('SHA-256', data);
        return Array.from(new Uint8Array(digest))
            .map((b) => b.toString(16).padStart(2, '0'))
            .join('');
    }

    if (import.meta.server) {
        const { createHash } = await import('node:crypto');
        return createHash('sha256').update(value).digest('hex');
    }

    return '';
}

export async function gravatarUrl(email?: string | null, size = 80): Promise<string> {
    const s = Math.max(1, Math.min(2048, Math.round(size)));
    if (!email?.trim()) {
        return `https://www.gravatar.com/avatar/?s=${s}&d=mp`;
    }
    const hash = await sha256Hex(email.trim().toLowerCase());
    if (!hash) {
        return `https://www.gravatar.com/avatar/?s=${s}&d=identicon`;
    }
    return `https://www.gravatar.com/avatar/${hash}?s=${s}&d=identicon`;
}

export function useGravatar(email: MaybeRefOrGetter<string | null | undefined>, size = 80) {
    const url = ref(`https://www.gravatar.com/avatar/?s=${size}&d=mp`);

    watch(
        () => toValue(email),
        async (value) => {
            url.value = await gravatarUrl(value, size);
        },
        { immediate: true },
    );

    return url;
}
