/**
 * Gravatar URL from email (SHA-256 of trimmed lowercase email).
 * @see https://docs.gravatar.com/general/hash/
 */
async function sha256Hex(value: string): Promise<string> {
    const data = new TextEncoder().encode(value);
    const digest = await crypto.subtle.digest('SHA-256', data);
    return Array.from(new Uint8Array(digest))
        .map((b) => b.toString(16).padStart(2, '0'))
        .join('');
}

export async function gravatarUrl(email?: string | null, size = 80): Promise<string> {
    const s = Math.max(1, Math.min(2048, Math.round(size)));
    if (!email?.trim()) {
        return `https://www.gravatar.com/avatar/?s=${s}&d=mp`;
    }
    const hash = await sha256Hex(email.trim().toLowerCase());
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
