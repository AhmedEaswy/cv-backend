/**
 * usePortalApi — typed wrappers around the authed portal endpoints.
 * Surfaces a clean { data, error, loading } shape and uses the global
 * toast on failures.
 */
export interface CVSummary {
    id: number;
    name: string;
    language?: string;
    template_id?: number | null;
    is_public?: boolean;
    updated_at?: string;
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
    name?: string;
    headline?: string;
    bio?: string;
    avatar?: string;
    email?: string;
    phone?: string;
    website?: string;
    location?: string;
    template_id?: number | null;
    color?: string;
    is_published?: boolean;
    slug?: string;
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
    checks: Array<{ id: string; category: string; label?: string; passed: boolean; weight: number; tip?: string }>;
    keywords?: { coverage_percent: number; matched: string[]; missing: string[] };
    check_id?: number;
}

export const usePortalApi = () => {
    const api = useApi();
    const toast = useToast();

    async function list<T>(url: string): Promise<T[]> {
        try {
            const res = await api<{ data: T[] }>(url);
            return res.data ?? [];
        } catch (e: any) {
            toast.error(e?.data?.message || 'Request failed');
            return [];
        }
    }

    async function show<T>(url: string): Promise<T | null> {
        try {
            const res = await api<{ data: T }>(url);
            return res.data ?? null;
        } catch (e: any) {
            toast.error(e?.data?.message || 'Not found');
            return null;
        }
    }

    async function create<T>(url: string, body: any): Promise<T | null> {
        try {
            const res = await api<{ data: T }>(url, { method: 'POST', body });
            toast.success('Created');
            return res.data ?? null;
        } catch (e: any) {
            toast.error(e?.data?.message || 'Could not create');
            return null;
        }
    }

    async function update<T>(url: string, body: any, successMessage = 'Saved'): Promise<T | null> {
        try {
            const res = await api<{ data: T }>(url, { method: 'PUT', body });
            toast.success(successMessage);
            return res.data ?? null;
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
            const res = await api(url, { method: 'POST' });
            toast.success('Duplicated');
            return res.data;
        } catch (e: any) {
            toast.error(e?.data?.message || 'Could not duplicate');
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
            const res = await api<{ data: AtsResult }>(url, { method: 'POST', body });
            return res.data;
        } catch (e: any) {
            toast.error(e?.data?.message || 'ATS check failed');
            return null;
        }
    }

    return { list, show, create, update, destroy, duplicate, atsCheck };
};
