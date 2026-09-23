/**
 * Apple Sign In via Laravel Socialite (not Auth.js).
 * Lands on /auth/apple/redirect so nginx can keep the hop on PHP.
 * Disabled when NUXT_PUBLIC_APPLE_AUTH_ENABLED is false/0/off/no.
 */
export function useAppleAuth() {
    const config = useRuntimeConfig()
    const route = useRoute()

    const enabled = computed(() => config.public.appleAuthEnabled !== false)

    function start(opts?: { returnTo?: string }) {
        if (!enabled.value) return
        const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '')
        const raw = opts?.returnTo
            ?? (typeof route.query.redirect === 'string' ? route.query.redirect : '')
        const next = raw.startsWith('/') && !raw.startsWith('//') ? raw : '/portal'
        const params = new URLSearchParams({ return_to: next })
        window.location.href = `${laravel}/auth/apple/redirect?${params.toString()}`
    }

    return { start, enabled }
}
