import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const appDir = path.join(root, 'app');
const cssDir = path.join(appDir, 'assets', 'css');

const json = JSON.parse(
    fs.readFileSync(path.join(__dirname, 'scoped-styles-output.json'), 'utf8'),
);

const groups = {
    'sections.css': [
        'components/landing/HeroSection.vue',
        'components/landing/MockupSection.vue',
        'components/landing/PlatformsSection.vue',
        'components/landing/FeaturesSection.vue',
        'components/landing/PricingSection.vue',
        'components/landing/TemplatesSection.vue',
        'components/landing/AiConnectSection.vue',
        'components/landing/DownloadSection.vue',
        'pages/index.vue',
    ],
    'header.css': [
        'components/landing/SiteHeader.vue',
        'components/layout/SiteHeader.vue',
        'layouts/legal.vue',
    ],
    'footer.css': ['components/landing/SiteFooter.vue'],
    'cards.css': [
        'components/landing/MarqueeStrip.vue',
        'pages/portal/index.vue',
        'pages/portal/inbox.vue',
        'pages/portal/cvs/index.vue',
        'pages/portal/cover-letters/index.vue',
    ],
    'forms.css': [
        'pages/auth/login.vue',
        'pages/auth/register.vue',
        'pages/auth/forgot-password.vue',
        'pages/auth/reset-password.vue',
        'pages/auth/verify-email.vue',
        'pages/portal/cvs/create.vue',
        'pages/portal/cvs/[id]/edit.vue',
        'pages/portal/cover-letters/create.vue',
        'pages/portal/cover-letters/[id]/edit.vue',
        'pages/portal/public-profile.vue',
        'pages/portal/settings.vue',
        'pages/portal/settings/ai-access.vue',
    ],
    'auth.css': ['layouts/auth.vue'],
    'portal.css': ['layouts/portal.vue'],
    'components.css': [
        'components/landing/AiChatDemo.vue',
        'components/landing/AiConnectModal.vue',
        'components/portal/AtsModal.vue',
        'components/ui/Button.vue',
        'components/ui/Toaster.vue',
        'components/legal/LegalDoc.vue',
    ],
};

function stripStyleBlocks(fileRel) {
    const fp = path.join(appDir, fileRel.replace(/\//g, path.sep));
    if (!fs.existsSync(fp)) return false;
    const before = fs.readFileSync(fp, 'utf8');
    const after = before.replace(/\r?\n<style scoped>[\s\S]*?<\/style>\r?\n?/g, '\n');
    if (after !== before) {
        fs.writeFileSync(fp, after);
        return true;
    }
    return false;
}

fs.mkdirSync(cssDir, { recursive: true });

for (const [file, keys] of Object.entries(groups)) {
    const parts = [];
    for (const k of keys) {
        const css = json.cssByFile[k];
        if (css) parts.push(`/* ${k} */\n${css}`);
    }
    fs.writeFileSync(path.join(cssDir, file), `${parts.join('\n\n')}\n`);
}

let stripped = 0;
for (const k of Object.keys(json.cssByFile)) {
    if (stripStyleBlocks(k)) stripped++;
}

console.log(`Wrote ${Object.keys(groups).length} css files`);
console.log(`Stripped style blocks from ${stripped} vue files`);
