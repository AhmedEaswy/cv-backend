export interface PortalStats {
    cvs_count: number
    cover_letters_count: number
    top_ats_score: number | null
    views_count: number
    unread_messages: number
    has_public_profile: boolean
    public_profile_is_published: boolean
    public_profile_slug?: string | null
    public_profile_url?: string | null
}

/**
 * Shared portal stats for sidebar badges and dashboard cards.
 */
export function usePortalStats() {
    const api = useApi()
    const stats = useState<PortalStats | null>('portal-stats', () => null)
    const loading = useState('portal-stats-loading', () => false)

    async function refresh() {
        loading.value = true
        try {
            const res = await api<{ result?: PortalStats }>('/portal/stats')
            stats.value = res?.result ?? null
        } catch {
            /* keep previous */
        } finally {
            loading.value = false
        }
    }

    return { stats, loading, refresh }
}
