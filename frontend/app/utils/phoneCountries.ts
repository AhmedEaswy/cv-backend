export interface PhoneCountry {
    iso2: string;
    dial: string;
}

/** ISO 3166-1 alpha-2 + ITU dialing code. */
export const PHONE_COUNTRIES: PhoneCountry[] = [
    { iso2: 'US', dial: '1' },
    { iso2: 'CA', dial: '1' },
    { iso2: 'GB', dial: '44' },
    { iso2: 'AU', dial: '61' },
    { iso2: 'NZ', dial: '64' },
    { iso2: 'IE', dial: '353' },
    { iso2: 'DE', dial: '49' },
    { iso2: 'FR', dial: '33' },
    { iso2: 'ES', dial: '34' },
    { iso2: 'IT', dial: '39' },
    { iso2: 'PT', dial: '351' },
    { iso2: 'NL', dial: '31' },
    { iso2: 'BE', dial: '32' },
    { iso2: 'CH', dial: '41' },
    { iso2: 'AT', dial: '43' },
    { iso2: 'SE', dial: '46' },
    { iso2: 'NO', dial: '47' },
    { iso2: 'DK', dial: '45' },
    { iso2: 'FI', dial: '358' },
    { iso2: 'PL', dial: '48' },
    { iso2: 'CZ', dial: '420' },
    { iso2: 'GR', dial: '30' },
    { iso2: 'TR', dial: '90' },
    { iso2: 'RU', dial: '7' },
    { iso2: 'UA', dial: '380' },
    { iso2: 'SA', dial: '966' },
    { iso2: 'AE', dial: '971' },
    { iso2: 'QA', dial: '974' },
    { iso2: 'KW', dial: '965' },
    { iso2: 'BH', dial: '973' },
    { iso2: 'OM', dial: '968' },
    { iso2: 'EG', dial: '20' },
    { iso2: 'JO', dial: '962' },
    { iso2: 'LB', dial: '961' },
    { iso2: 'IQ', dial: '964' },
    { iso2: 'MA', dial: '212' },
    { iso2: 'TN', dial: '216' },
    { iso2: 'DZ', dial: '213' },
    { iso2: 'PK', dial: '92' },
    { iso2: 'IN', dial: '91' },
    { iso2: 'BD', dial: '880' },
    { iso2: 'LK', dial: '94' },
    { iso2: 'CN', dial: '86' },
    { iso2: 'JP', dial: '81' },
    { iso2: 'KR', dial: '82' },
    { iso2: 'SG', dial: '65' },
    { iso2: 'MY', dial: '60' },
    { iso2: 'ID', dial: '62' },
    { iso2: 'TH', dial: '66' },
    { iso2: 'VN', dial: '84' },
    { iso2: 'PH', dial: '63' },
    { iso2: 'BR', dial: '55' },
    { iso2: 'MX', dial: '52' },
    { iso2: 'AR', dial: '54' },
    { iso2: 'CL', dial: '56' },
    { iso2: 'CO', dial: '57' },
    { iso2: 'PE', dial: '51' },
    { iso2: 'ZA', dial: '27' },
    { iso2: 'NG', dial: '234' },
    { iso2: 'KE', dial: '254' },
    { iso2: 'GH', dial: '233' },
    { iso2: 'IL', dial: '972' },
    { iso2: 'IR', dial: '98' },
    { iso2: 'HK', dial: '852' },
    { iso2: 'TW', dial: '886' },
].sort((a, b) => a.iso2.localeCompare(b.iso2));

const LOCALE_DEFAULT_ISO: Record<string, string> = {
    ar: 'SA',
    tr: 'TR',
    es: 'ES',
    fr: 'FR',
    de: 'DE',
    ur: 'PK',
    en: 'US',
};

export function defaultPhoneIso(locale: string): string {
    return LOCALE_DEFAULT_ISO[locale] || 'US';
}

/** Flag image URL (emoji flags often render as letters on Windows). */
export function countryFlagUrl(iso2: string, width = 40): string {
    const code = iso2.toLowerCase();
    if (!/^[a-z]{2}$/.test(code)) return '';
    return `https://flagcdn.com/w${width}/${code}.png`;
}

export function findCountry(iso2: string): PhoneCountry | undefined {
    return PHONE_COUNTRIES.find((c) => c.iso2 === iso2.toUpperCase());
}

/**
 * Split a stored phone value into country + national digits.
 * Prefers the longest matching dial code; falls back to the given default ISO.
 */
export function parsePhoneValue(value: string | null | undefined, fallbackIso: string): {
    iso2: string;
    national: string;
} {
    const raw = String(value || '').trim();
    const digits = raw.replace(/[^\d+]/g, '');
    const fallback = findCountry(fallbackIso) || PHONE_COUNTRIES.find((c) => c.iso2 === 'US')!;

    if (!digits) {
        return { iso2: fallback.iso2, national: '' };
    }

    const numbered = digits.startsWith('+') ? digits.slice(1) : digits.replace(/\D/g, '');
    const sorted = [...PHONE_COUNTRIES].sort((a, b) => {
        const byDial = b.dial.length - a.dial.length;
        if (byDial !== 0) return byDial;
        // Same dial code (e.g. US/CA +1): prefer the locale default when it matches.
        if (a.iso2 === fallback.iso2) return -1;
        if (b.iso2 === fallback.iso2) return 1;
        return a.iso2.localeCompare(b.iso2);
    });

    for (const country of sorted) {
        if (numbered.startsWith(country.dial)) {
            return {
                iso2: country.iso2,
                national: numbered.slice(country.dial.length),
            };
        }
    }

    return { iso2: fallback.iso2, national: numbered };
}

export function formatPhoneValue(iso2: string, national: string): string {
    const country = findCountry(iso2);
    const digits = national.replace(/\D/g, '');
    if (!country || !digits) {
        return digits ? `+${digits}` : '';
    }
    return `+${country.dial}${digits}`;
}
