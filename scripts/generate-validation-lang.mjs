#!/usr/bin/env node
/**
 * Generate lang/{locale}/validation.php from Laravel-Lang php.json sources.
 *
 *   node scripts/generate-validation-lang.mjs
 */
import { mkdir, readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = resolve(__dirname, '..');
const langDir = resolve(projectRoot, 'resources', 'lang');

const LOCALES = {
    en: null,
    ar: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/ar/php.json',
    tr: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/tr/php.json',
    es: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/es/php.json',
    fr: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/fr/php.json',
    de: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/de/php.json',
    ur: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/ur/php.json',
};

const ATTRIBUTE_URLS = {
    ar: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/ar/json.json',
    tr: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/tr/json.json',
    es: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/es/json.json',
    fr: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/fr/json.json',
    de: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/de/json.json',
    ur: 'https://raw.githubusercontent.com/Laravel-Lang/lang/main/locales/ur/json.json',
};

/** App-specific attribute keys merged into every locale. */
const APP_ATTRIBUTES = {
    en: {
        name: 'name',
        email: 'email address',
        password: 'password',
        password_confirmation: 'password confirmation',
        current_password: 'current password',
        token: 'token',
        slug: 'URL slug',
        title: 'title',
        message: 'message',
        website: 'website',
        template_id: 'template',
        profile_id: 'profile',
        cover_letter_id: 'cover letter',
        locale: 'language',
        remember: 'remember me',
        abilities: 'abilities',
        user_data: 'profile data',
        'user_data.skills': 'skills',
        'user_data.educations': 'education',
        'user_data.experiences': 'experience',
        'user_data.projects': 'projects',
        'user_data.languages': 'languages',
        'user_data.interests': 'interests',
        first_name: 'first name',
        last_name: 'last name',
        phone: 'phone',
        photo: 'photo',
        file: 'file',
    },
    ar: {
        name: 'الاسم',
        email: 'البريد الإلكتروني',
        password: 'كلمة المرور',
        password_confirmation: 'تأكيد كلمة المرور',
        current_password: 'كلمة المرور الحالية',
        token: 'الرمز',
        slug: 'رابط URL',
        title: 'العنوان',
        message: 'الرسالة',
        website: 'الموقع الإلكتروني',
        template_id: 'القالب',
        profile_id: 'الملف الشخصي',
        cover_letter_id: 'خطاب التقديم',
        locale: 'اللغة',
        remember: 'تذكرني',
        abilities: 'الصلاحيات',
        user_data: 'بيانات الملف',
        'user_data.skills': 'المهارات',
        'user_data.educations': 'التعليم',
        'user_data.experiences': 'الخبرة',
        'user_data.projects': 'المشاريع',
        'user_data.languages': 'اللغات',
        'user_data.interests': 'الاهتمامات',
        first_name: 'الاسم الأول',
        last_name: 'اسم العائلة',
        phone: 'الهاتف',
        photo: 'الصورة',
        file: 'الملف',
    },
    tr: {
        name: 'ad',
        email: 'e-posta adresi',
        password: 'şifre',
        password_confirmation: 'şifre onayı',
        current_password: 'mevcut şifre',
        token: 'jeton',
        slug: 'URL slug',
        title: 'başlık',
        message: 'mesaj',
        website: 'web sitesi',
        template_id: 'şablon',
        profile_id: 'profil',
        cover_letter_id: 'ön yazı',
        locale: 'dil',
        remember: 'beni hatırla',
        abilities: 'yetkiler',
        user_data: 'profil verileri',
        'user_data.skills': 'beceriler',
        'user_data.educations': 'eğitim',
        'user_data.experiences': 'deneyim',
        'user_data.projects': 'projeler',
        'user_data.languages': 'diller',
        'user_data.interests': 'ilgi alanları',
        first_name: 'ad',
        last_name: 'soyad',
        phone: 'telefon',
        photo: 'fotoğraf',
        file: 'dosya',
    },
    es: {
        name: 'nombre',
        email: 'correo electrónico',
        password: 'contraseña',
        password_confirmation: 'confirmación de contraseña',
        current_password: 'contraseña actual',
        token: 'token',
        slug: 'URL slug',
        title: 'título',
        message: 'mensaje',
        website: 'sitio web',
        template_id: 'plantilla',
        profile_id: 'perfil',
        cover_letter_id: 'carta de presentación',
        locale: 'idioma',
        remember: 'recordarme',
        abilities: 'permisos',
        user_data: 'datos del perfil',
        'user_data.skills': 'habilidades',
        'user_data.educations': 'educación',
        'user_data.experiences': 'experiencia',
        'user_data.projects': 'proyectos',
        'user_data.languages': 'idiomas',
        'user_data.interests': 'intereses',
        first_name: 'nombre',
        last_name: 'apellido',
        phone: 'teléfono',
        photo: 'foto',
        file: 'archivo',
    },
    fr: {
        name: 'nom',
        email: 'adresse e-mail',
        password: 'mot de passe',
        password_confirmation: 'confirmation du mot de passe',
        current_password: 'mot de passe actuel',
        token: 'jeton',
        slug: 'URL slug',
        title: 'titre',
        message: 'message',
        website: 'site web',
        template_id: 'modèle',
        profile_id: 'profil',
        cover_letter_id: 'lettre de motivation',
        locale: 'langue',
        remember: 'se souvenir de moi',
        abilities: 'autorisations',
        user_data: 'données du profil',
        'user_data.skills': 'compétences',
        'user_data.educations': 'formation',
        'user_data.experiences': 'expérience',
        'user_data.projects': 'projets',
        'user_data.languages': 'langues',
        'user_data.interests': 'centres d\'intérêt',
        first_name: 'prénom',
        last_name: 'nom de famille',
        phone: 'téléphone',
        photo: 'photo',
        file: 'fichier',
    },
    de: {
        name: 'Name',
        email: 'E-Mail-Adresse',
        password: 'Passwort',
        password_confirmation: 'Passwortbestätigung',
        current_password: 'aktuelles Passwort',
        token: 'Token',
        slug: 'URL-Slug',
        title: 'Titel',
        message: 'Nachricht',
        website: 'Website',
        template_id: 'Vorlage',
        profile_id: 'Profil',
        cover_letter_id: 'Anschreiben',
        locale: 'Sprache',
        remember: 'Angemeldet bleiben',
        abilities: 'Berechtigungen',
        user_data: 'Profildaten',
        'user_data.skills': 'Fähigkeiten',
        'user_data.educations': 'Ausbildung',
        'user_data.experiences': 'Berufserfahrung',
        'user_data.projects': 'Projekte',
        'user_data.languages': 'Sprachen',
        'user_data.interests': 'Interessen',
        first_name: 'Vorname',
        last_name: 'Nachname',
        phone: 'Telefon',
        photo: 'Foto',
        file: 'Datei',
    },
    ur: {
        name: 'نام',
        email: 'ای میل پتہ',
        password: 'پاس ورڈ',
        password_confirmation: 'پاس ورڈ کی تصدیق',
        current_password: 'موجودہ پاس ورڈ',
        token: 'ٹوکن',
        slug: 'URL سلگ',
        title: 'عنوان',
        message: 'پیغام',
        website: 'ویب سائٹ',
        template_id: 'سانچہ',
        profile_id: 'پروفائل',
        cover_letter_id: 'کور لیٹر',
        locale: 'زبان',
        remember: 'مجھے یاد رکھیں',
        abilities: 'اجازتیں',
        user_data: 'پروفائل ڈیٹا',
        'user_data.skills': 'مہارتیں',
        'user_data.educations': 'تعلیم',
        'user_data.experiences': 'تجربہ',
        'user_data.projects': 'منصوبے',
        'user_data.languages': 'زبانیں',
        'user_data.interests': 'دلچسپیاں',
        first_name: 'پہلا نام',
        last_name: 'آخری نام',
        phone: 'فون',
        photo: 'تصویر',
        file: 'فائل',
    },
};

function unflatten(flat) {
    const out = {};
    for (const [key, value] of Object.entries(flat)) {
        const parts = key.split('.');
        let node = out;
        for (let i = 0; i < parts.length - 1; i++) {
            node[parts[i]] ??= {};
            node = node[parts[i]];
        }
        node[parts[parts.length - 1]] = value;
    }
    return out;
}

function toPhpValue(value, indent = 4) {
    const pad = ' '.repeat(indent);
    if (typeof value === 'string') {
        return `'${value.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
    }
    if (Array.isArray(value)) {
        const inner = value.map((v) => `${pad}    ${toPhpValue(v, indent + 4)},`).join('\n');
        return `[\n${inner}\n${pad}]`;
    }
    const entries = Object.entries(value).map(
        ([k, v]) => `${pad}    '${k}' => ${toPhpValue(v, indent + 4)},`,
    );
    return `[\n${entries.join('\n')}\n${pad}]`;
}

function toPhpArray(obj, indent = 4) {
    const pad = ' '.repeat(indent);
    const lines = Object.entries(obj).map(
        ([k, v]) => `${pad}'${k}' => ${toPhpValue(v, indent)},`,
    );
    return `[\n${lines.join('\n')}\n${' '.repeat(indent - 4)}]`;
}

async function fetchJson(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`Failed to fetch ${url}: ${res.status}`);
    return res.json();
}

async function main() {
for (const [locale, url] of Object.entries(LOCALES)) {
    if (locale === 'en') {
        const path = resolve(langDir, 'en', 'validation.php');
        let content = readFileSync(path, 'utf8');
        const attrs = toPhpArray(APP_ATTRIBUTES.en, 4);
        if (content.includes("'attributes' => [],")) {
            content = content.replace("'attributes' => [],", `'attributes' => ${attrs},`);
        }
        writeFileSync(path, content, 'utf8');
        console.log('  ✓ lang/en/validation.php (attributes updated)');
        continue;
    }

    let rules;
    const flat = await fetchJson(url);
    const skip = new Set(['failed', 'password', 'reset', 'sent', 'throttle', 'throttled', 'token', 'user', 'next', 'previous']);
    const filtered = Object.fromEntries(
        Object.entries(flat).filter(([k]) => !skip.has(k.split('.')[0])),
    );
    rules = unflatten(filtered);

    rules.custom = rules.custom ?? [];
    rules.attributes = { ...(rules.attributes ?? {}), ...APP_ATTRIBUTES[locale] };

    const dir = resolve(langDir, locale);
    mkdir(dir, { recursive: true }, () => {});

    const php = `<?php

return ${toPhpArray(rules).replace(/^\[/, '[\n').replace(/\]$/, '\n];')};
`;

    writeFileSync(resolve(dir, 'validation.php'), php, 'utf8');
    console.log(`  ✓ lang/${locale}/validation.php`);
}
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});
