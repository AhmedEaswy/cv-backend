import GoogleProvider from 'next-auth/providers/google'
import { NuxtAuthHandler } from '#auth'

const config = useRuntimeConfig()

export default NuxtAuthHandler({
    secret: config.authSecret || '',
    providers: [
        // @ts-expect-error — next-auth CJS default export under Nuxt ESM
        GoogleProvider.default({
            clientId: config.public.googleClientId || config.googleClientId || '',
            clientSecret: config.private.googleClientSecret || '',
        }),
    ],
    callbacks: {
        async jwt({ token, account }) {
            // Keep Google's access token server-side only (Auth.js JWT cookie).
            if (account?.access_token) {
                token.accessToken = account.access_token
            }
            return token
        },
        async session({ session }) {
            // Do not expose the Google access token to the client session.
            return {
                user: {
                    ...session.user,
                },
            }
        },
    },
})
