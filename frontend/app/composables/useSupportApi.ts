import type {
    FeatureRequestItem,
    HelpArticleDetail,
    HelpArticleKind,
    HelpArticleSummary,
    SupportTicketResult,
} from '~/types/support-api';

export type ApiEnvelope<T> = {
    success?: boolean;
    message?: string;
    result?: T;
    data?: T;
    code?: number | string;
    errors?: Record<string, string[]>;
};

export function unwrapResult<T>(res: ApiEnvelope<T> | null | undefined): T | null {
    if (!res) return null;
    if (res.result !== undefined) return res.result ?? null;
    if (res.data !== undefined) return res.data ?? null;
    return null;
}

export function unwrapList<T>(res: ApiEnvelope<T[] | { data: T[] }> | null | undefined): T[] {
    const payload = unwrapResult(res);
    if (Array.isArray(payload)) return payload;
    if (payload && typeof payload === 'object' && Array.isArray((payload as { data: T[] }).data)) {
        return (payload as { data: T[] }).data;
    }
    return [];
}

type HelpArticlesScope = 'public' | 'portal';

export const useSupportApi = (scope: HelpArticlesScope = 'public') => {
    const api = useApi();
    const { locale, t } = useI18n();
    const toast = useToast();

    const articlesBase = scope === 'portal' ? '/portal/help-articles' : '/support/help-articles';

    const localeQuery = (extra?: Record<string, string | number | undefined>) => ({
        locale: locale.value,
        ...extra,
    });

    async function listHelpArticles(kind: HelpArticleKind, category?: string): Promise<HelpArticleSummary[]> {
        try {
            const res = await api<ApiEnvelope<HelpArticleSummary[]>>(articlesBase, {
                query: localeQuery({
                    kind,
                    ...(category ? { category } : {}),
                    per_page: 50,
                }),
            });
            return unwrapList(res).sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        } catch {
            return [];
        }
    }

    async function showHelpArticle(slug: string): Promise<HelpArticleDetail | null> {
        try {
            const res = await api<ApiEnvelope<HelpArticleDetail>>(
                `${articlesBase}/${encodeURIComponent(slug)}`,
                { query: localeQuery() },
            );
            return unwrapResult(res);
        } catch {
            return null;
        }
    }

    async function listFeatureRequests(): Promise<FeatureRequestItem[]> {
        try {
            const res = await api<ApiEnvelope<FeatureRequestItem[]>>('/support/feature-requests', {
                query: localeQuery(),
            });
            return unwrapList(res);
        } catch {
            return [];
        }
    }

    async function createFeatureRequest(body: { title: string; body: string }): Promise<FeatureRequestItem | null> {
        try {
            const res = await api<ApiEnvelope<FeatureRequestItem>>('/support/feature-requests', {
                method: 'POST',
                body,
            });
            toast.success(res?.message || t('support.toast.feature_submitted'));
            return unwrapResult(res);
        } catch (e: any) {
            toast.error(e?.data?.message || t('support.toast.request_failed'));
            return null;
        }
    }

    async function voteFeatureRequest(id: number): Promise<FeatureRequestItem | null> {
        try {
            const res = await api<ApiEnvelope<FeatureRequestItem>>(`/support/feature-requests/${id}/vote`, {
                method: 'POST',
            });
            return unwrapResult(res);
        } catch (e: any) {
            const status = e?.response?.status ?? e?.statusCode;
            if (status === 409) {
                toast.error(e?.data?.message || t('support.toast.already_voted'));
            } else {
                toast.error(e?.data?.message || t('support.toast.request_failed'));
            }
            return null;
        }
    }

    async function submitSupportTicket(body: {
        name: string;
        email: string;
        subject: string;
        body: string;
    }): Promise<SupportTicketResult | null> {
        try {
            const res = await api<ApiEnvelope<SupportTicketResult>>('/support/tickets', {
                method: 'POST',
                body,
            });
            toast.success(res?.message || t('support.toast.message_sent'));
            return unwrapResult(res);
        } catch (e: any) {
            toast.error(e?.data?.message || t('support.toast.request_failed'));
            return null;
        }
    }

    return {
        listHelpArticles,
        showHelpArticle,
        listFeatureRequests,
        createFeatureRequest,
        voteFeatureRequest,
        submitSupportTicket,
    };
};
