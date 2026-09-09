import { createError, getHeader } from 'h3'
import { getToken } from '#auth'

/**
 * BFF: Auth.js Google session → Laravel Sanctum token.
 * Mirrors digi-pedia's /api/auth/google-exchange.
 */
export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig()
    const authToken = await getToken({
        event,
        secret: config.authSecret,
    })

    const googleAccessToken = authToken?.accessToken
    if (typeof googleAccessToken !== 'string' || googleAccessToken.length === 0) {
        throw createError({
            statusCode: 401,
            statusMessage: 'Google authentication session is missing.',
        })
    }

    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '')
    const prefix = String(config.public.apiPrefix || '/api/v1').replace(/\/+$/, '')
    if (!laravel) {
        throw createError({
            statusCode: 503,
            statusMessage: 'Authentication API is not configured.',
        })
    }

    return await $fetch(`${laravel}${prefix}/auth/google`, {
        method: 'POST',
        body: { code: googleAccessToken },
        headers: {
            Accept: 'application/json',
            'Accept-Language': getHeader(event, 'accept-language') || '',
        },
    })
})
