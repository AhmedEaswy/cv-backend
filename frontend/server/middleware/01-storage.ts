import { createReadStream, existsSync, statSync } from 'node:fs';
import { extname, relative, resolve } from 'node:path';

/**
 * Serve Laravel public-disk files when nginx still proxies /storage to Nuxt.
 * Middleware is more reliable than a server route on Windows catch-all paths.
 */
export default defineEventHandler((event) => {
    const pathname = getRequestURL(event).pathname;
    if (!pathname.startsWith('/storage/')) return;

    const relativePath = pathname.slice('/storage/'.length);
    if (!relativePath || relativePath.includes('\0')) {
        throw createError({ statusCode: 404, statusMessage: 'Not found' });
    }

    const roots = [
        resolve(process.cwd(), '../storage/app/public'),
        resolve(process.cwd(), 'storage/app/public'),
        resolve(process.cwd(), '../public/storage'),
        resolve(process.cwd(), 'public/storage'),
    ];

    let file: string | null = null;
    for (const root of roots) {
        const candidate = resolve(root, relativePath);
        const rel = relative(root, candidate);
        if (!rel || rel.startsWith('..') || rel.includes('..')) continue;
        if (existsSync(candidate) && statSync(candidate).isFile()) {
            file = candidate;
            break;
        }
    }

    if (!file) {
        throw createError({ statusCode: 404, statusMessage: 'Not found' });
    }

    const ext = extname(file).toLowerCase();
    const types: Record<string, string> = {
        '.pdf': 'application/pdf',
        '.png': 'image/png',
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.webp': 'image/webp',
        '.svg': 'image/svg+xml',
    };
    setHeader(event, 'Content-Type', types[ext] || 'application/octet-stream');
    setHeader(event, 'Cache-Control', 'public, max-age=86400');
    return sendStream(event, createReadStream(file));
});
