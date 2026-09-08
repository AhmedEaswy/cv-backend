/**
 * Builds a clipboard prompt that teaches an external AI how to use the
 * anonymous CV / cover-letter API with a specific template (outside this website).
 *
 * skill.md is embedded at build time so copy-prompt stays inside the user gesture
 * (fetch latency was causing Clipboard API failures in Chrome).
 */
import embeddedSkill from '~/assets/agent/skill.md?raw';

export type TemplateKind = 'cv' | 'cover-letter';

export type TemplatePromptInput = {
    id: number | string;
    name: string;
    description?: string | null;
    kind?: TemplateKind;
    /** Pre-resolved label (prefer passing from a Vue setup context). */
    displayName?: string | null;
    /** Pre-resolved description blurb. */
    blurb?: string | null;
};

let skillCache = String(embeddedSkill || '').trim();
let skillPromise: Promise<string> | null = null;

function skillOrigin(): string {
    const config = useRuntimeConfig();
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    return laravel || (import.meta.client ? window.location.origin : '');
}

export async function loadCvSkillText(): Promise<string> {
    if (skillCache) return skillCache;
    if (skillPromise) return skillPromise;

    skillPromise = (async () => {
        const origin = skillOrigin();
        const res = await fetch(`${origin}/skill.md`);
        if (!res.ok) {
            throw new Error(`Failed to load skill.md (${res.status})`);
        }
        skillCache = await res.text();
        return skillCache;
    })().finally(() => {
        skillPromise = null;
    });

    return skillPromise;
}

/** Optionally refresh embedded skill from Laravel (non-blocking). */
export function prefetchCvSkill(): void {
    if (!import.meta.client || skillPromise) return;
    const origin = skillOrigin();
    skillPromise = fetch(`${origin}/skill.md`)
        .then(async (res) => {
            if (!res.ok) return skillCache;
            const body = (await res.text()).trim();
            if (body) skillCache = body;
            return skillCache;
        })
        .catch(() => skillCache)
        .finally(() => {
            skillPromise = null;
        });
}

export function templateDisplayName(name: string): string {
    return name
        .split(/[-_]/)
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function resolveDisplay(template: TemplatePromptInput): { display: string; blurb: string } {
    const display = (template.displayName || '').trim()
        || templateDisplayName(String(template.name));
    const blurb = (template.blurb ?? template.description ?? '').trim();
    return { display, blurb };
}

export async function buildTemplatePrompt(template: TemplatePromptInput): Promise<string> {
    const kind = template.kind || 'cv';
    if (kind === 'cover-letter') {
        return buildCoverLetterPrompt(template);
    }
    return buildCvPrompt(template);
}

async function buildCvPrompt(template: TemplatePromptInput): Promise<string> {
    const origin = skillOrigin();
    const skill = await loadCvSkillText();
    const { display, blurb } = resolveDisplay(template);

    return [
        `# CV template: ${display}`,
        '',
        `Origin: ${origin}`,
        `Preferred template_id: ${template.id}`,
        `Template name (slug): ${template.name}`,
        `Template label: ${display}`,
        blurb ? `Description: ${blurb}` : null,
        '',
        'You are helping the user create a CV **outside** the CV website, using anonymous public HTTP endpoints. Do not invent employment history, education, or contact details.',
        '',
        '## Quick start for this template',
        '',
        `1. GET ${origin}/api/v1/shares/templates — confirm \`template_id\` **${template.id}** (\`${template.name}\`).`,
        '2. Collect the user\'s real details into `user_data` (see skill below).',
        `3. Optional: POST ${origin}/api/v1/cvs/ats-check with the same \`user_data\` and a job description.`,
        `4. POST ${origin}/api/v1/cvs with JSON body:`,
        '```json',
        JSON.stringify({
            name: `${display} CV`,
            language: 'en',
            template_id: Number(template.id) || template.id,
            user_data: { firstName: '…', lastName: '…', jobTitle: '…', email: '…', summary: '…', skills: [], experiences: [], educations: [] },
        }, null, 2),
        '```',
        '   Without auth + `template_id` → response `{ "result": { "url": "<pdf>" } }`.',
        `5. Or POST ${origin}/api/v1/cvs/print with \`{ "template_id": ${template.id}, "user_data": { … } }\`.`,
        '',
        'Headers on every call:',
        '- `Accept: application/json`',
        '- `Content-Type: application/json`',
        '- `X-Agent-Client: other` (or chatgpt / claude / cursor / …)',
        '',
        '---',
        '',
        'Then follow the full CV Skill:',
        '',
        `Origin: ${origin}`,
        '',
        skill.trim(),
    ].filter((line) => line !== null).join('\n');
}

async function buildCoverLetterPrompt(template: TemplatePromptInput): Promise<string> {
    const origin = skillOrigin();
    const skill = await loadCvSkillText();
    const { display, blurb } = resolveDisplay(template);

    return [
        `# Cover letter template: ${display}`,
        '',
        `Origin: ${origin}`,
        `Preferred cover_letter_template_id: ${template.id}`,
        `Template name (slug): ${template.name}`,
        `Template label: ${display}`,
        blurb ? `Description: ${blurb}` : null,
        '',
        'You are helping the user create a cover letter **outside** the CV website, using anonymous public HTTP endpoints. Do not invent company names, roles, or personal details.',
        '',
        '## Quick start for this template',
        '',
        `1. GET ${origin}/api/v1/cover-letters/templates — confirm id **${template.id}** (\`${template.name}\`).`,
        '2. Collect the user\'s real letter details (company, role, body).',
        `3. POST ${origin}/api/v1/cover-letters with JSON body:`,
        '```json',
        JSON.stringify({
            name: `${display} letter`,
            language: 'en',
            cover_letter_template_id: Number(template.id) || template.id,
            user_data: { company: '…', role: '…', body: '…' },
        }, null, 2),
        '```',
        `4. POST ${origin}/api/v1/cover-letters/print with \`{ "template_id": ${template.id}, "user_data": { … } }\` → \`{ "result": { "url": "<pdf>" } }\`.`,
        '',
        'Headers on every call:',
        '- `Accept: application/json`',
        '- `Content-Type: application/json`',
        '- `X-Agent-Client: other`',
        '',
        '---',
        '',
        'Then follow the full CV Skill (cover-letter sections):',
        '',
        `Origin: ${origin}`,
        '',
        skill.trim(),
    ].filter((line) => line !== null).join('\n');
}
