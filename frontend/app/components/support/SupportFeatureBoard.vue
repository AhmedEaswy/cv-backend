<script setup lang="ts">
import {
    FEATURE_REQUEST_STATUSES,
    type FeatureRequestItem,
    type FeatureRequestStatus,
} from '~/types/support-api';

const props = defineProps<{
    posts: FeatureRequestItem[];
    loading?: boolean;
}>();

const emit = defineEmits<{
    vote: [id: number];
    submit: [payload: { title: string; body: string }];
}>();

const { t } = useI18n();
const { user } = useAuthSession();
const router = useRouter();

const composeOpen = ref(false);
const title = ref('');
const body = ref('');
const submitting = ref(false);
const submittedPending = ref(false);

const postsByStatus = computed(() => {
    const map: Record<FeatureRequestStatus, FeatureRequestItem[]> = {
        under_review: [],
        planned: [],
        in_progress: [],
        complete: [],
    };
    for (const post of props.posts) {
        const status = post.status as FeatureRequestStatus;
        if (map[status]) map[status].push(post);
    }
    for (const key of FEATURE_REQUEST_STATUSES) {
        map[key].sort((a, b) => (b.vote_count ?? 0) - (a.vote_count ?? 0));
    }
    return map;
});

function statusLabel(status: FeatureRequestStatus) {
    return t(`support.features.status.${status}`);
}

function requireAuth() {
    router.push({ path: '/auth/login', query: { redirect: '/support#support-features' } });
}

async function onSubmitPost() {
    if (!user.value) {
        requireAuth();
        return;
    }
    const trimmedTitle = title.value.trim();
    const trimmedBody = body.value.trim();
    if (trimmedTitle.length < 3 || trimmedBody.length < 10) return;
    submitting.value = true;
    emit('submit', { title: trimmedTitle, body: trimmedBody });
    submitting.value = false;
    composeOpen.value = false;
    title.value = '';
    body.value = '';
    submittedPending.value = true;
}

function onVote(id: number) {
    if (!user.value) {
        requireAuth();
        return;
    }
    emit('vote', id);
}
</script>

<template>
    <section id="support-features" class="support-section" aria-labelledby="support-features-title">
        <header class="support-section__head support-section__head--row">
            <div>
                <h2 id="support-features-title">{{ t('support.features.title') }}</h2>
                <p class="support-section__lede">{{ t('support.features.lede') }}</p>
            </div>
            <Button variant="secondary" size="sm" @click="composeOpen = true">
                {{ t('support.features.new_request') }}
            </Button>
        </header>

        <p v-if="submittedPending" class="support-features__pending">
            {{ t('support.features.submitted_pending') }}
        </p>

        <p v-if="loading" class="support-muted">{{ t('support.loading') }}</p>

        <div v-else class="support-board">
            <div
                v-for="status in FEATURE_REQUEST_STATUSES"
                :key="status"
                class="support-board__column"
            >
                <h3 class="support-board__column-title">{{ statusLabel(status) }}</h3>
                <ul class="support-board__list">
                    <li v-for="post in postsByStatus[status]" :key="post.id" class="support-board__card">
                        <div class="support-board__card-main">
                            <h4>{{ post.title }}</h4>
                            <span class="support-board__votes">
                                {{ t('support.features.votes', { count: post.vote_count ?? 0 }) }}
                            </span>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            :disabled="!user || post.has_voted"
                            @click="onVote(post.id)"
                        >
                            {{ post.has_voted ? t('support.features.voted') : t('support.features.vote') }}
                        </Button>
                    </li>
                    <li v-if="!postsByStatus[status].length" class="support-board__empty">
                        {{ t('support.features.column_empty') }}
                    </li>
                </ul>
            </div>
        </div>

        <p class="support-features__hint">{{ t('support.features.hint') }}</p>

        <Modal v-model:open="composeOpen" :title="t('support.features.new_request')" size="md">
            <p class="support-modal__lede">{{ t('support.features.compose_lede') }}</p>
            <form class="support-form" @submit.prevent="onSubmitPost">
                <label class="support-form__field">
                    <span>{{ t('support.features.field_title') }}</span>
                    <input v-model="title" class="input" required minlength="3" maxlength="200" />
                </label>
                <label class="support-form__field">
                    <span>{{ t('support.features.field_description') }}</span>
                    <textarea v-model="body" class="input support-form__textarea" required minlength="10" maxlength="8000" rows="4" />
                </label>
                <div class="support-form__actions">
                    <Button type="button" variant="ghost" @click="composeOpen = false">{{ t('support.form.cancel') }}</Button>
                    <Button type="submit" variant="primary" :loading="submitting" :disabled="!user">
                        {{ t('support.features.submit') }}
                    </Button>
                </div>
                <p v-if="!user" class="support-muted">{{ t('support.features.login_required') }}</p>
            </form>
        </Modal>
    </section>
</template>
