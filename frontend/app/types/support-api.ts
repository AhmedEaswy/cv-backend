/**
 * Support platform API types (see backend PR #6).
 */

export type HelpArticleKind = 'faq' | 'guide';

export interface HelpArticleSummary {
    id: number;
    kind: HelpArticleKind;
    slug: string;
    category: string | null;
    title: string;
    sort_order: number;
    updated_at: string | null;
    excerpt?: string;
}

export interface HelpArticleDetail extends HelpArticleSummary {
    body: string;
}

export type FeatureRequestStatus = 'under_review' | 'planned' | 'in_progress' | 'complete';

export interface FeatureRequestItem {
    id: number;
    title: string;
    status: FeatureRequestStatus;
    vote_count: number;
    has_voted: boolean;
    created_at: string | null;
    body?: string;
}

export interface SupportTicketResult {
    id: number;
    status: string;
    created_at: string | null;
}

export interface ProductTourStep {
    id: number;
    sort_order: number;
    title: string;
    body: string;
}

export interface ProductTourOffer {
    key: string;
    steps: ProductTourStep[];
}

export interface ProductTourOfferResult {
    tours: ProductTourOffer[];
}

export interface ProductTourProgressResult {
    key: string;
    status: 'completed' | 'dismissed';
    completed_at: string | null;
}

export const FEATURE_REQUEST_STATUSES: FeatureRequestStatus[] = [
    'under_review',
    'planned',
    'in_progress',
    'complete',
];

/** Map tour step order to portal sidebar targets (steps have no server-side selector). */
export const PORTAL_TOUR_STEP_TARGETS = [
    '[data-portal-tour="dashboard"]',
    '[data-portal-tour="cvs"]',
    '[data-portal-tour="cover-letters"]',
    '[data-portal-tour="public-profile"]',
    '[data-portal-tour="help"]',
] as const;
