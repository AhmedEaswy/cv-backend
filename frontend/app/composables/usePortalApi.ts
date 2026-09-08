/**
 * usePortalApi — typed wrappers around the authed portal endpoints.
 * Laravel BaseApiController returns `{ success, message, result }`.
 */
export interface CvSkill {
    name: string;
}

export interface CvEducation {
    institution: string;
    degree: string;
    fieldOfStudy: string;
    description?: string | null;
    from?: string | null;
    to?: string | null;
}

export interface CvExperience {
    position: string;
    company?: string | null;
    location?: string | null;
    description?: string | null;
    from?: string | null;
    to?: string | null;
    current?: boolean;
}

export interface CvProject {
    title: string;
    description?: string | null;
    technologies?: string | null;
    url?: string | null;
    from?: string | null;
    to?: string | null;
    current?: boolean;
}

export interface CvLanguage {
    name: string;
    proficiencyLevel: number;
}

export interface CvInterest {
    name: string;
}

export interface CvUserData {
    firstName?: string | null;
    lastName?: string | null;
    jobTitle?: string | null;
    email?: string | null;
    address?: string | null;
    portfolioUrl?: string | null;
    phone?: string | null;
    summary?: string | null;
    birthdate?: string | null;
    photo?: string | null;
    skills?: CvSkill[];
    educations?: CvEducation[];
    experiences?: CvExperience[];
    projects?: CvProject[];
    languages?: CvLanguage[];
    interests?: CvInterest[];
}

export const DEFAULT_CV_SECTIONS = [
    'Personal Information',
    'Skills',
    'Education',
    'Experience',
    'Projects',
    'Languages',
    'Interests',
] as const;

export interface CVSummary {
    id: number;
    name: string;
    language?: string;
    template_id?: number | null;
    is_public?: boolean;
    sections_order?: string[] | null;
    user_data?: CvUserData;
    latest_ats_score?: number | null;
    latest_ats_grade?: string | null;
    updated_at?: string;
    created_at?: string;
    [k: string]: any;
}

export interface CoverLetterSummary {
    id: number;
    name: string;
    company?: string;
    role?: string;
    template_id?: number | null;
    updated_at?: string;
    [k: string]: any;
}

export interface ProfileData {
    id?: number;
    slug?: string;
    public_url?: string;
    is_public?: boolean;
    headline?: string;
    about?: string;
    public_profile_template_id?: number | null;
    language?: string;
    user_data?: {
        firstName?: string;
        lastName?: string;
        email?: string;
        phone?: string;
        website?: string;
        address?: string;
        photo?: string;
        [k: string]: any;
    };
    created_at?: string;
    updated_at?: string;
    /** @deprecated use is_public */
    is_published?: boolean;
    [k: string]: any;
}

export interface InboxMessage {
    id: number;
    name?: string;
    email?: string;
    subject?: string;
    message?: string;
    is_read?: boolean;
    created_at?: string;
    [k: string]: any;
}

export interface AtsResult {
    score: number;
    grade: string;
    source: 'structured' | 'pdf';
    categories: Record<string, number>;
    checks: Array<{
        id: string;
        category: string;
        label?: string;
        message?: string;
        passed: boolean;
        weight: number;
        tip?: string;
    }>;
    keywords?: { coverage_percent: number; matched: string[]; missing: string[] };
    check_id?: number;
}

type ApiEnvelope<T> = { success?: boolean; message?: string; result?: T; data?: T };

function unwrap<T>(res: ApiEnvelope<T> | null | undefined): T | null {
    if (!res) return null;
    if (res.result !== undefined) return res.result ?? null;
    if (res.data !== undefined) return res.data ?? null;
    return null;
}

export function emptyCvUserData(): CvUserData {
    return {
        firstName: '',
        lastName: '',
        jobTitle: '',
        email: '',
        address: '',
        portfolioUrl: '',
        phone: '',
        summary: '',
        birthdate: '',
        skills: [],
        educations: [],
        experiences: [],
        projects: [],
        languages: [],
        interests: [],
    };
}

export function normalizeCvUserData(data?: CvUserData | null): CvUserData {
    const empty = emptyCvUserData();
    if (!data) return empty;
    return {
        ...empty,
        ...data,
        skills: data.skills ?? [],
        educations: data.educations ?? [],
        experiences: data.experiences ?? [],
        projects: data.projects ?? [],
        languages: data.languages ?? [],
        interests: data.interests ?? [],
    };
}

function blankToNull(value?: string | null): string | null {
    if (value == null) return null;
    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
}

export function compactCvUserData(data: CvUserData): CvUserData {
    return {
        firstName: blankToNull(data.firstName),
        lastName: blankToNull(data.lastName),
        jobTitle: blankToNull(data.jobTitle),
        email: blankToNull(data.email),
        address: blankToNull(data.address),
        portfolioUrl: blankToNull(data.portfolioUrl),
        phone: blankToNull(data.phone),
        summary: blankToNull(data.summary),
        birthdate: blankToNull(data.birthdate),
        skills: (data.skills || []).filter((s) => s.name?.trim()),
        educations: (data.educations || []).map((e) => ({
            ...e,
            institution: e.institution?.trim() || '',
            degree: e.degree?.trim() || '',
            fieldOfStudy: e.fieldOfStudy?.trim() || '',
            description: blankToNull(e.description),
            from: blankToNull(e.from),
            to: blankToNull(e.to),
        })).filter((e) => e.institution && e.degree && e.fieldOfStudy),
        experiences: (data.experiences || []).map((e) => ({
            ...e,
            position: e.position?.trim() || '',
            company: blankToNull(e.company),
            location: blankToNull(e.location),
            description: blankToNull(e.description),
            from: blankToNull(e.from),
            to: e.current ? null : blankToNull(e.to),
            current: !!e.current,
        })).filter((e) => e.position),
        projects: (data.projects || []).map((p) => ({
            ...p,
            title: p.title?.trim() || '',
            description: blankToNull(p.description),
            technologies: blankToNull(p.technologies),
            url: blankToNull(p.url),
            from: blankToNull(p.from),
            to: p.current ? null : blankToNull(p.to),
            current: !!p.current,
        })).filter((p) => p.title),
        languages: (data.languages || []).filter((l) => l.name?.trim()),
        interests: (data.interests || []).filter((i) => i.name?.trim()),
    };
}

export const usePortalApi = () => {
    const api = useApi();
    const toast = useToast();
    const { t } = useI18n();

    async function list<T>(url: string): Promise<T[]> {
        try {
            const res = await api<ApiEnvelope<T[]>>(url);
            const payload = unwrap(res);
            return Array.isArray(payload) ? payload : [];
        } catch (e: any) {
            toast.error(e?.data?.message || 'Request failed');
            return [];
        }
    }

    async function show<T>(url: string, opts?: { silent?: boolean }): Promise<T | null> {
        try {
            const res = await api<ApiEnvelope<T>>(url);
            return unwrap(res);
        } catch (e: any) {
            if (!opts?.silent) {
                toast.error(e?.data?.message || 'Not found');
            }
            return null;
        }
    }

    async function create<T>(url: string, body: any): Promise<T | null> {
        try {
            const res = await api<ApiEnvelope<T>>(url, { method: 'POST', body });
            toast.success(res?.message || 'Created');
            return unwrap(res);
        } catch (e: any) {
            toast.error(e?.data?.message || 'Could not create');
            return null;
        }
    }

    async function createBlankCv(opts?: { template_id?: number }): Promise<{ id: number } | null> {
        const locale = useI18n().locale.value;
        const language = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'].includes(locale) ? locale : 'en';
        return create<{ id: number }>('/cvs', {
            name: t('portal.cvs.untitled'),
            language,
            ...(opts?.template_id ? { template_id: opts.template_id } : {}),
        });
    }

    async function createBlankPublicProfile(): Promise<ProfileData | null> {
        const { user } = useAuthSession();
        const locale = useI18n().locale.value;
        const language = ['en', 'ar', 'tr'].includes(locale) ? locale : 'en';
        const parts = String(user.value?.name || '').trim().split(/\s+/).filter(Boolean);
        return create<ProfileData>('/public-profiles', {
            language,
            is_public: true,
            user_data: {
                firstName: parts[0] || '',
                lastName: parts.slice(1).join(' ') || '',
                email: user.value?.email || '',
            },
        });
    }

    async function update<T>(url: string, body: any, successMessage = 'Saved'): Promise<T | null> {
        try {
            const res = await api<ApiEnvelope<T>>(url, { method: 'PUT', body });
            toast.success(successMessage);
            return unwrap(res);
        } catch (e: any) {
            toast.error(e?.data?.message || 'Could not save');
            return null;
        }
    }

    async function destroy(url: string, successMessage = 'Deleted'): Promise<boolean> {
        try {
            await api(url, { method: 'DELETE' });
            toast.success(successMessage);
            return true;
        } catch (e: any) {
            toast.error(e?.data?.message || 'Could not delete');
            return false;
        }
    }

    async function duplicate(url: string): Promise<any> {
        try {
            const res = await api<ApiEnvelope<any>>(url, { method: 'POST' });
            toast.success(res?.message || t('portal.cvs.duplicated'));
            return unwrap(res);
        } catch (e: any) {
            toast.error(e?.data?.message || t('portal.cvs.duplicate_failed'));
            return null;
        }
    }

    async function printCv(body: { profile_id: number; template_id: number }): Promise<{ url: string } | null> {
        try {
            const res = await api<ApiEnvelope<{ url: string }>>('/cvs/print', { method: 'POST', body });
            return unwrap(res);
        } catch (e: any) {
            toast.error(e?.data?.message || t('portal.cvs.pdf_failed'));
            return null;
        }
    }

    /**
     * Run an ATS check on a CV against a job description.
     * @param mode 'cv' = use stored profile, 'pdf' = upload, 'data' = send ad-hoc
     */
    async function atsCheck(payload: {
        mode: 'cv' | 'pdf' | 'data';
        cvId?: number;
        file?: File;
        jobDescription?: string;
        userData?: Record<string, any>;
    }): Promise<AtsResult | null> {
        try {
            let url = '/cvs/ats-check';
            let body: any;
            if (payload.mode === 'pdf' && payload.file) {
                url = '/cvs/ats-check/upload';
                const fd = new FormData();
                fd.append('file', payload.file);
                if (payload.jobDescription) fd.append('job_description', payload.jobDescription);
                body = fd;
            } else if (payload.mode === 'cv' && payload.cvId) {
                body = { profile_id: payload.cvId, job_description: payload.jobDescription };
            } else {
                body = { user_data: payload.userData || {}, job_description: payload.jobDescription };
            }
            const res = await api<ApiEnvelope<AtsResult>>(url, { method: 'POST', body });
            return unwrap(res);
        } catch (e: any) {
            toast.error(e?.data?.message || 'ATS check failed');
            return null;
        }
    }

    return { list, show, create, createBlankCv, createBlankPublicProfile, update, destroy, duplicate, printCv, atsCheck, resolvePublicFileUrl };
};

export function resolvePublicFileUrl(url: string): string {
    if (!url) return url;
    const config = useRuntimeConfig();
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    const origin = typeof window !== 'undefined' ? window.location.origin : laravel;
    try {
        const abs = /^https?:\/\//i.test(url)
            ? url
            : `${origin}${url.startsWith('/') ? url : `/${url}`}`;
        const parsed = new URL(abs);
        if (parsed.pathname.startsWith('/storage/')) {
            return `${origin}${parsed.pathname}${parsed.search}`;
        }
        return abs;
    } catch {
        return url;
    }
}
