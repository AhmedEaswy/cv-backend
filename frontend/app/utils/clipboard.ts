/**
 * Copy text even after an await (fetch) has consumed the user-activation token.
 * Prefer Clipboard API; fall back to a hidden textarea + execCommand.
 */
export async function copyToClipboard(text: string): Promise<void> {
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);
            return;
        } catch {
            // Fall through — common after async work loses transient activation.
        }
    }

    if (typeof document === 'undefined') {
        throw new Error('Clipboard is not available');
    }

    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '0';
    ta.style.left = '0';
    ta.style.width = '1px';
    ta.style.height = '1px';
    ta.style.padding = '0';
    ta.style.border = 'none';
    ta.style.outline = 'none';
    ta.style.boxShadow = 'none';
    ta.style.background = 'transparent';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    ta.setSelectionRange(0, ta.value.length);

    let ok = false;
    try {
        ok = document.execCommand('copy');
    } finally {
        document.body.removeChild(ta);
    }

    if (!ok) {
        throw new Error('Copy command failed');
    }
}
