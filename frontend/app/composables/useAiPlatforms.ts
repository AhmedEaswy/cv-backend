export type AiPlatform = {
    id: string;
    name: string;
    logo: string;
    url: string;
    supportsMcp: boolean;
};

export const useAiPlatforms = () => {
    const config = useRuntimeConfig();
    const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
    const asset = (file: string) => `${laravel}/images/ai-platforms/${file}`;

    const platforms: AiPlatform[] = [
        { id: 'chatgpt', name: 'ChatGPT', logo: asset('chatgpt.png'), url: 'https://chatgpt.com', supportsMcp: false },
        { id: 'claude', name: 'Claude', logo: asset('claude.png'), url: 'https://claude.ai/new', supportsMcp: true },
        { id: 'copilot', name: 'Copilot', logo: asset('copilot.png'), url: 'https://copilot.microsoft.com', supportsMcp: false },
        { id: 'gemini', name: 'Gemini', logo: asset('gemini.png'), url: 'https://gemini.google.com/app', supportsMcp: false },
        { id: 'deepseek', name: 'DeepSeek', logo: asset('deepseek.png'), url: 'https://chat.deepseek.com', supportsMcp: false },
        { id: 'minimax', name: 'MiniMax', logo: asset('minimax.svg'), url: 'https://chat.minimax.io', supportsMcp: false },
        { id: 'grok', name: 'Grok', logo: asset('grok.svg'), url: 'https://grok.com', supportsMcp: false },
        { id: 'perplexity', name: 'Perplexity', logo: asset('perplexity.svg'), url: 'https://www.perplexity.ai', supportsMcp: false },
    ];

    return { platforms, laravel };
};

export const useAiConnectModal = () => {
    const open = useState('ai-connect-modal', () => false);

    return {
        open,
        show: () => {
            open.value = true;
        },
        hide: () => {
            open.value = false;
        },
    };
};
