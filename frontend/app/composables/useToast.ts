/**
 * useToast — minimal global toast / banner system.
 * Usage:
 *   const toast = useToast();
 *   toast.success('Saved!');
 *   toast.error('Something went wrong', 5000);
 */
interface Toast {
    id: number;
    message: string;
    variant: 'success' | 'error' | 'info';
    timeout: number;
}

export const useToast = () => {
    const toasts = useState<Toast[]>('app.toasts', () => []);
    let nextId = 1;

    function push(message: string, variant: Toast['variant'], timeout = 3500) {
        const id = nextId++;
        toasts.value = [...toasts.value, { id, message, variant, timeout }];
        if (import.meta.client) {
            setTimeout(() => dismiss(id), timeout);
        }
    }

    function dismiss(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    return {
        toasts,
        success: (m: string, t?: number) => push(m, 'success', t),
        error: (m: string, t?: number) => push(m, 'error', t ?? 5000),
        info: (m: string, t?: number) => push(m, 'info', t),
        dismiss,
    };
};
