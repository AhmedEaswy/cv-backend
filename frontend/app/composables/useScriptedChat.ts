export type ChatRole = 'user' | 'bot';

export type ChatTurn = {
    role: ChatRole;
    text: string;
};

export type ChatMessage = {
    id: string;
    role: ChatRole;
    text: string;
    visibleText: string;
    typing: boolean;
};

type UseScriptedChatOptions = {
    scenarios: MaybeRefOrGetter<Record<string, ChatTurn[]>>;
    autoplayKey?: string;
    root: Ref<HTMLElement | null>;
};

const wait = (ms: number, playId: number, current: () => number, reduced: boolean) => {
    if (reduced || ms <= 0) return Promise.resolve(playId === current());
    return new Promise<boolean>((resolve) => {
        window.setTimeout(() => resolve(playId === current()), ms);
    });
};

export const useScriptedChat = (options: UseScriptedChatOptions) => {
    const selectedKey = ref<string | null>(null);
    const messages = ref<ChatMessage[]>([]);
    const composerText = ref('');
    const isTypingComposer = ref(false);
    const isSending = ref(false);
    const clearing = ref(false);
    const playId = ref(0);
    const reducedMotion = ref(false);
    let autoplayTimer: number | null = null;
    let observer: IntersectionObserver | null = null;

    const still = (id: number) => id === playId.value;

    const scrollToEnd = (transcript: HTMLElement | null) => {
        if (!transcript) return;
        transcript.scrollTo({
            top: transcript.scrollHeight,
            behavior: reducedMotion.value ? 'auto' : 'smooth',
        });
    };

    const cancelAutoplay = () => {
        if (autoplayTimer === null) return;
        window.clearTimeout(autoplayTimer);
        autoplayTimer = null;
    };

    const play = async (key: string, transcript?: HTMLElement | null) => {
        const scenario = toValue(options.scenarios)[key];
        if (!Array.isArray(scenario) || scenario.length === 0) return;

        const id = ++playId.value;
        selectedKey.value = key;
        cancelAutoplay();

        clearing.value = messages.value.length > 0;
        composerText.value = '';
        isTypingComposer.value = false;
        isSending.value = false;
        if (clearing.value) await wait(180, id, () => playId.value, reducedMotion.value);
        if (!still(id)) return;
        messages.value = [];
        clearing.value = false;

        await wait(140, id, () => playId.value, reducedMotion.value);

        for (const turn of scenario) {
            if (!still(id)) return;

            if (turn.role === 'user') {
                composerText.value = '';
                isTypingComposer.value = true;
                if (reducedMotion.value) {
                    composerText.value = turn.text;
                } else {
                    for (const char of Array.from(turn.text)) {
                        if (!still(id)) return;
                        composerText.value += char;
                        await wait(22, id, () => playId.value, reducedMotion.value);
                    }
                    await wait(220, id, () => playId.value, reducedMotion.value);
                }

                isSending.value = true;
                await wait(180, id, () => playId.value, reducedMotion.value);
                if (!still(id)) return;
                composerText.value = '';
                isTypingComposer.value = false;
                messages.value.push({
                    id: `${id}-${messages.value.length}`,
                    role: 'user',
                    text: turn.text,
                    visibleText: turn.text,
                    typing: false,
                });
                isSending.value = false;
                await nextTick();
                scrollToEnd(transcript ?? null);
                await wait(220, id, () => playId.value, reducedMotion.value);
            } else {
                // Must be reactive so later mutations update the template
                // (mutating a plain object after push does not update the UI).
                const message = reactive<ChatMessage>({
                    id: `${id}-${messages.value.length}`,
                    role: 'bot',
                    text: turn.text,
                    visibleText: '',
                    typing: true,
                });
                messages.value.push(message);
                await nextTick();
                scrollToEnd(transcript ?? null);
                await wait(420, id, () => playId.value, reducedMotion.value);
                if (!still(id)) return;
                message.typing = false;
                if (reducedMotion.value) {
                    message.visibleText = turn.text;
                    await nextTick();
                    scrollToEnd(transcript ?? null);
                    continue;
                }
                for (const char of Array.from(turn.text)) {
                    if (!still(id)) return;
                    message.visibleText += char;
                    scrollToEnd(transcript ?? null);
                    await wait(12, id, () => playId.value, reducedMotion.value);
                }
                await wait(280, id, () => playId.value, reducedMotion.value);
            }
        }
    };

    onMounted(() => {
        reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const section = options.root.value;
        if (!section || typeof IntersectionObserver === 'undefined') {
            if (options.autoplayKey) {
                autoplayTimer = window.setTimeout(() => {
                    if (playId.value === 0 && options.autoplayKey) play(options.autoplayKey);
                }, 1000);
            }
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                const visible = entries.some((entry) => entry.isIntersecting);
                if (!visible) {
                    cancelAutoplay();
                    return;
                }
                if (autoplayTimer !== null || playId.value > 0 || !options.autoplayKey) return;
                autoplayTimer = window.setTimeout(() => {
                    autoplayTimer = null;
                    if (playId.value > 0 || !options.autoplayKey) return;
                    play(options.autoplayKey);
                }, 1000);
            },
            { threshold: 0.25 },
        );
        observer.observe(section);
    });

    onBeforeUnmount(() => {
        cancelAutoplay();
        observer?.disconnect();
        observer = null;
    });

    return {
        selectedKey,
        messages,
        composerText,
        isTypingComposer,
        isSending,
        clearing,
        play,
        isSelected: (key: string) => selectedKey.value === key,
    };
};
