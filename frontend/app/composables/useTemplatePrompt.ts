/**
 * Builds a clipboard prompt that teaches an external AI how to use the
 * anonymous CV / cover-letter API with a specific template (outside this website).
 */
export type TemplateKind = 'cv' | 'cover-letter';

export type TemplatePromptInput = {
    id: number | string;
    name: string;
    description?: string | null;
    kind?: TemplateKind;
};

let skillCache = '';

export async function loadCvSkillText(): Promise<string> {
    if (skillCache) return skillCache;
    const config = useRuntimeConfig();
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    const origin = laravel || (import.meta.client ? window.location.origin : '');
    const res = await fetch(`${origin}/skill.md`);
    const body = await res.text();
    skillCache = body;
    return skillCache;
}

export function templateDisplayName(name: string): string {
    return name
        .split(/[-_]/)
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

export async function buildTemplatePrompt(template: TemplatePromptInput): Promise<string> {
    const kind = template.kind || 'cv';
    if (kind === 'cover-letter') {
        return buildCoverLetterPrompt(template);
    }
    return buildCvPrompt(template);
}

async function buildCvPrompt(template: TemplatePromptInput): Promise<string> {
    const config = useRuntimeConfig();
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    const origin = laravel || (import.meta.client ? window.location.origin : '');
    const skill = await loadCvSkillText();
    const { label, description } = useLocalizedTemplate();
    const display = label('cv', String(template.name));
    const blurb = description('cv', String(template.name), template.description);

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
    const config = useRuntimeConfig();
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    const origin = laravel || (import.meta.client ? window.location.origin : '');
    const skill = await loadCvSkillText();
    const { label, description } = useLocalizedTemplate();
    const display = label('cover-letter', String(template.name));
    const blurb = description('cover-letter', String(template.name), template.description);

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
