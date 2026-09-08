/**
 * Copy text even after an await has consumed the user-activation token.
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
    ta.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;padding:0;border:none;outline:none;box-shadow:none;background:transparent;opacity:0;';
    document.body.appendChild(ta);

    const selection = document.getSelection();
    const previous = selection && selection.rangeCount > 0 ? selection.getRangeAt(0) : null;

    ta.focus();
    ta.select();
    ta.setSelectionRange(0, ta.value.length);

    let ok = false;
    try {
        ok = document.execCommand('copy');
    } finally {
        document.body.removeChild(ta);
        if (previous && selection) {
            selection.removeAllRanges();
            selection.addRange(previous);
        }
    }

    if (!ok) {
        throw new Error('Copy command failed');
    }
}
