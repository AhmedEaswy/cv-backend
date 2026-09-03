<script setup lang="ts">
/**
 * Scripted marketing chat. Visitors pick a scenario chip; they cannot type.
 */
const { t } = useI18n();
const root = ref<HTMLElement | null>(null);
const transcript = ref<HTMLElement | null>(null);

const scenarios = computed(() => ({
    cv: [
        { role: 'user' as const, text: t('landing.ai_connect_demo_cv_user') },
        { role: 'bot' as const, text: t('landing.ai_connect_demo_cv_bot') },
    ],
    ats: [
        { role: 'user' as const, text: t('landing.ai_connect_demo_ats_user') },
        { role: 'bot' as const, text: t('landing.ai_connect_demo_ats_bot') },
    ],
    cover: [
        { role: 'user' as const, text: t('landing.ai_connect_demo_cover_user') },
        { role: 'bot' as const, text: t('landing.ai_connect_demo_cover_bot') },
    ],
    profile: [
        { role: 'user' as const, text: t('landing.ai_connect_demo_profile_user') },
        { role: 'bot' as const, text: t('landing.ai_connect_demo_profile_bot') },
    ],
}));

const chips = [
    { key: 'cv', labelKey: 'landing.ai_connect_chip_cv' },
    { key: 'ats', labelKey: 'landing.ai_connect_chip_ats' },
    { key: 'cover', labelKey: 'landing.ai_connect_chip_cover' },
    { key: 'profile', labelKey: 'landing.ai_connect_chip_profile' },
] as const;

const {
    selectedKey: _selectedKey,
    messages,
    composerText,
    isTypingComposer,
    isSending,
    clearing,
    play,
    isSelected,
} = useScriptedChat({
    scenarios,
    autoplayKey: 'cv',
    root,
});

const playChip = (key: string) => play(key, transcript.value);
</script>

<template>
    <div ref="root" class="ai-chat" dir="ltr">
        <div class="ai-chat__head">
            <span class="ai-chat__dot" aria-hidden="true" />
            <span class="ai-chat__title">{{ t('landing.ai_connect_cta') }}</span>
        </div>

        <div class="ai-chat__chips">
            <button
                v-for="chip in chips"
                :key="chip.key"
                type="button"
                class="ai-chat__chip"
                :class="{ 'is-on': isSelected(chip.key) }"
                :aria-pressed="isSelected(chip.key).toString()"
                @click="playChip(chip.key)"
            >
                {{ t(chip.labelKey) }}
            </button>
        </div>

        <div
            ref="transcript"
            class="ai-chat__log"
            :class="{ 'is-clearing': clearing }"
            aria-live="polite"
        >
            <div
                v-for="message in messages"
                :key="message.id"
                :class="['ai-chat__row', `ai-chat__row--${message.role}`]"
            >
                <span v-if="message.role === 'bot'" class="ai-chat__avatar" aria-hidden="true">
                    <Icon name="sparkles" :size="14" />
                </span>
                <div v-if="message.typing" class="ai-chat__bubble ai-chat__bubble--bot" aria-hidden="true">
                    <span class="ai-chat__pulse" /><span class="ai-chat__pulse" /><span class="ai-chat__pulse" />
                </div>
                <p v-else class="ai-chat__bubble" :class="`ai-chat__bubble--${message.role}`">
                    {{ message.visibleText }}
                </p>
            </div>
        </div>

        <div class="ai-chat__composer" aria-hidden="true">
            <p class="ai-chat__input">
                <span v-if="!composerText && !isTypingComposer">
                    {{ t('landing.ai_connect_demo_placeholder') }}
                </span>
                <span
                    v-else
                    class="ai-chat__typed"
                    :class="{ 'has-caret': isTypingComposer }"
                >{{ composerText }}</span>
            </p>
            <span class="ai-chat__send" :class="{ 'is-pulse': isSending }">
                <Icon name="arrow-right" :size="16" />
            </span>
        </div>
    </div>
</template>

