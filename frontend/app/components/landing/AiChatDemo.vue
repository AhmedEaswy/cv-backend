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

<style scoped>
.ai-chat {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 28rem;
    min-height: 28rem;
    border-radius: 1.5rem;
    border: 1px solid var(--color-line);
    background: var(--color-white);
    padding: 1.15rem 1.1rem 1rem;
    box-shadow: var(--shadow-2);
}
.ai-chat__head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.9rem;
}
.ai-chat__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-success);
}
.ai-chat__title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--color-ink-soft);
}
.ai-chat__chips {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.45rem;
    margin-bottom: 0.85rem;
}
.ai-chat__chip {
    height: 2.35rem;
    border-radius: 999px;
    border: 1px solid var(--color-line);
    background: var(--color-paper);
    color: var(--color-ink);
    font-size: 0.8rem;
    cursor: pointer;
}
.ai-chat__chip.is-on,
.ai-chat__chip:hover {
    background: var(--color-ink);
    color: var(--color-paper);
    border-color: var(--color-ink);
}
.ai-chat__log {
    flex: 1;
    min-height: 11rem;
    max-height: 16rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding-block: 0.25rem;
    transition: opacity 0.2s ease;
}
.ai-chat__log.is-clearing { opacity: 0; }
.ai-chat__row { display: flex; gap: 0.45rem; align-items: flex-end; }
.ai-chat__row--user { justify-content: flex-end; }
.ai-chat__avatar {
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--color-paper-2);
    color: var(--color-ink);
    flex-shrink: 0;
}
.ai-chat__bubble {
    margin: 0;
    max-width: 85%;
    padding: 0.55rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    line-height: 1.45;
}
.ai-chat__bubble--bot { background: var(--color-paper-2); color: var(--color-ink); }
.ai-chat__bubble--user { background: var(--color-ink); color: var(--color-paper); }
.ai-chat__pulse {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--color-muted);
    display: inline-block;
    margin-inline: 2px;
    animation: ai-pulse 1s ease-in-out infinite;
}
.ai-chat__pulse:nth-child(2) { animation-delay: 0.15s; }
.ai-chat__pulse:nth-child(3) { animation-delay: 0.3s; }
@keyframes ai-pulse {
    0%, 100% { opacity: 0.3; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-2px); }
}
.ai-chat__composer {
    margin-top: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    border: 1px solid var(--color-line);
    border-radius: 0.9rem;
    padding: 0.45rem 0.5rem 0.45rem 0.85rem;
    min-height: 3.15rem;
}
.ai-chat__input {
    flex: 1;
    margin: 0;
    font-size: 0.82rem;
    color: var(--color-muted);
    min-width: 0;
}
.ai-chat__typed { color: var(--color-ink); }
.ai-chat__typed.has-caret::after {
    content: '';
    display: inline-block;
    width: 1px;
    height: 0.9em;
    margin-inline-start: 2px;
    background: var(--color-ink);
    animation: ai-caret 1s step-end infinite;
    vertical-align: text-bottom;
}
@keyframes ai-caret { 50% { opacity: 0; } }
.ai-chat__send {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--color-ink);
    color: var(--color-paper);
    flex-shrink: 0;
}
.ai-chat__send.is-pulse { transform: scale(0.94); }
@media (prefers-reduced-motion: reduce) {
    .ai-chat__pulse, .ai-chat__typed.has-caret::after { animation: none; }
}
</style>
