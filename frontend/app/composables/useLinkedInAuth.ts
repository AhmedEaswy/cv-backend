/**
 * LinkedIn OpenID sign-in via Laravel Socialite (not Auth.js).
 * Lands on /auth/linkedin/redirect so nginx can keep the hop on PHP.
 */
export function useLinkedInAuth() {
    const config = useRuntimeConfig()
    const route = useRoute()

    function start(opts?: { intent?: 'import' | 'login'; returnTo?: string }) {
        const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '')
        const raw = opts?.returnTo
            ?? (typeof route.query.redirect === 'string' ? route.query.redirect : '')
        const next = raw.startsWith('/') && !raw.startsWith('//') ? raw : '/portal'
        const params = new URLSearchParams({ return_to: next })
        if (opts?.intent === 'import') {
            params.set('intent', 'import')
        }
        window.location.href = `${laravel}/auth/linkedin/redirect?${params.toString()}`
    }

    return { start }
}
