import type { ProfileData } from '~/composables/usePortalApi';

/**
 * Shared public-profile state for the portal (dashboard + editor).
 * Survives client-side navigations so the dashboard card stays in sync
 * after create/save.
 */
export function usePortalPublicProfile() {
    const profile = useState<ProfileData | null>('portal.publicProfile', () => null);

    async function refresh(opts?: { silent?: boolean }): Promise<ProfileData | null> {
        const portal = usePortalApi();
        const next = await portal.show<ProfileData>('/public-profiles', {
            silent: opts?.silent ?? true,
        });
        profile.value = next?.id ? next : null;
        return profile.value;
    }

    function setProfile(next: ProfileData | null) {
        profile.value = next?.id ? next : null;
    }

    return { profile, refresh, setProfile };
}
