/**
 * useAuth — landing-page auth state. Hits /api/v1/auth/me once, caches the
 * result, and exposes a reactive `user`. Returns null when the user is not
 * logged in. Errors are swallowed silently (401 is expected when logged out).
 *
 * In the future this will be wired to the httpOnly cookie / Bearer flow;
 * for the landing page it just needs to know whether to show "Open the
 * portal" vs "Login" in the header.
 */
export interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}

export const useAuth = async () => {
    const user = useState<AuthUser | null>('auth.user', () => null);

    if (user.value === null) {
        try {
            const api = useApi();
            const res = await api<{ data: AuthUser }>('/auth/me');
            user.value = res.data ?? null;
        } catch {
            user.value = null;
        }
    }

    return { user };
};
