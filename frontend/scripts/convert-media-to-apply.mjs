import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const cssDir = path.join(path.dirname(fileURLToPath(import.meta.url)), '..', 'app', 'assets', 'css');

const bp = { '640px': 'sm', '768px': 'md', '1024px': 'lg', '1280px': 'xl' };

/** Map common CSS declarations to Tailwind @apply tokens. */
const declToApply = {
    'display: flex': 'flex',
    'display: none': 'hidden',
    'display: inline-flex': 'inline-flex',
    'display: grid': 'grid',
    'flex-direction: column': 'flex-col',
    'align-items: center': 'items-center',
    'justify-content: center': 'justify-center',
    'justify-content: space-between': 'justify-between',
    'justify-content: flex-end': 'justify-end',
    'text-align: center': 'text-center',
    'grid-template-columns: 1fr 1fr': 'grid-cols-2',
    'grid-template-columns: repeat(2, 1fr)': 'grid-cols-2',
    'grid-template-columns: repeat(3, 1fr)': 'grid-cols-3',
    'grid-template-columns: repeat(4, 1fr)': 'grid-cols-4',
    'grid-template-columns: 22rem 1fr': 'grid-cols-[22rem_1fr]',
    'grid-template-columns: minmax(0, 22rem)': 'grid-cols-[minmax(0,22rem)]',
    'grid-column: span 2': 'col-span-2',
    'flex-wrap: wrap': 'flex-wrap',
    'width: 100%': 'w-full',
    'order: 1': 'order-1',
    'order: 2': 'order-2',
    'align-self: center': 'self-center',
    'padding: 3rem 1rem': 'p-12 px-4',
    'padding: 0rem 1.25rem 0': 'px-5 pt-0',
    'padding-inline: 2rem': 'px-8',
    'padding-inline: 2.5rem': 'px-10',
    'padding-block: 6rem': 'py-24',
    'border-radius: 2rem': 'rounded-[2rem]',
    'border-radius: 1.25rem': 'rounded-[1.25rem]',
    'min-height: min(88vh, 720px)': 'min-h-[min(88vh,720px)]',
    'flex-direction: column': 'flex-col',
    'align-items: stretch': 'items-stretch',
};

function convertMediaBlocks(css) {
    const re = /@media\s*\(\s*min-width:\s*(\d+px)\s*\)\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/g;
    let result = css;
    let match;
    const additions = new Map();

    while ((match = re.exec(css)) !== null) {
        const width = match[1];
        const prefix = bp[width];
        if (!prefix) continue;

        const inner = match[2];
        const ruleRe = /([.#][\w-]+(?:\.[\w-]+)*)\s*\{([^}]+)\}/g;
        let ruleMatch;
        while ((ruleMatch = ruleRe.exec(inner)) !== null) {
            const selector = ruleMatch[1].trim();
            const decls = ruleMatch[2].trim().split(';').map((d) => d.trim()).filter(Boolean);
            const applies = [];
            for (const decl of decls) {
                const normalized = decl.replace(/\s+/g, ' ');
                const tw = declToApply[normalized];
                if (tw) applies.push(`${prefix}:${tw}`);
            }
            if (applies.length) {
                const key = selector;
                additions.set(key, [...(additions.get(key) ?? []), ...applies]);
            }
        }
    }

    // Remove converted min-width media blocks (single-rule only)
    result = result.replace(re, (full, width, inner) => {
        const prefix = bp[width];
        if (!prefix) return full;
        const ruleRe = /([.#][\w-]+(?:\.[\w-]+)*)\s*\{([^}]+)\}/g;
        let allConverted = true;
        let m;
        while ((m = ruleRe.exec(inner)) !== null) {
            const decls = m[2].trim().split(';').map((d) => d.trim()).filter(Boolean);
            if (!decls.every((d) => declToApply[d.replace(/\s+/g, ' ')])) {
                allConverted = false;
                break;
            }
        }
        return allConverted ? '' : full;
    });

    // Inject @apply into existing rules
    for (const [selector, classes] of additions) {
        const escaped = selector.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const ruleRe = new RegExp(`(${escaped}\\s*\\{)([^@][^}]*)`, '');
        if (ruleRe.test(result)) {
            result = result.replace(ruleRe, (_, open, body) => {
                if (body.includes('@apply')) {
                    const applyRe = /@apply\s+([^;]+);/;
                    return open + body.replace(applyRe, (_, existing) => `@apply ${existing} ${classes.join(' ')};`);
                }
                return `${open}\n    @apply ${classes.join(' ')};\n${body}`;
            });
        }
    }

    // max-width media -> max-sm: etc.
    result = result.replace(
        /@media\s*\(\s*max-width:\s*640px\s*\)\s*\{\s*(\.[^{]+)\s*\{\s*grid-template-columns:\s*1fr;\s*\}\s*\}/g,
        (_, sel) => {
            const escaped = sel.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const ruleRe = new RegExp(`(${escaped}\\s*\\{)([^@][^}]*)`, '');
            return result.replace(ruleRe, (m, open, body) => {
                if (body.includes('max-sm:grid-cols-1')) return m;
                if (body.includes('@apply')) {
                    return open + body.replace(/@apply\s+([^;]+);/, '@apply $1 max-sm:grid-cols-1;');
                }
                return `${open}\n    @apply max-sm:grid-cols-1;\n${body}`;
            }), '';
        },
    );

    return result.replace(/\n{3,}/g, '\n\n');
}

const skip = new Set(['main.css', 'base.css', 'misc.css', 'forms-controls.css']);

for (const file of fs.readdirSync(cssDir).filter((f) => f.endsWith('.css') && !skip.has(f))) {
    const fp = path.join(cssDir, file);
    const before = fs.readFileSync(fp, 'utf8');
    const after = convertMediaBlocks(before);
    if (after !== before) {
        fs.writeFileSync(fp, after);
        console.log('Updated', file);
    }
}

// Fix :deep() from Vue scoped styles
for (const file of fs.readdirSync(cssDir).filter((f) => f.endsWith('.css'))) {
    const fp = path.join(cssDir, file);
    let content = fs.readFileSync(fp, 'utf8');
    const fixed = content.replace(/:deep\(([^)]+)\)/g, '$1');
    if (fixed !== content) fs.writeFileSync(fp, fixed);
}

console.log('Done');
