/**
 * Shared helpers used across the Waterfall Reviews front-end modules.
 *
 * Everything here is written in vanilla TypeScript so the bundle no longer
 * depends on jQuery.
 */

export interface AjaxOptions {
    /** Payload sent to admin-ajax. Either a plain object or a FormData instance. */
    data: Record<string, unknown> | FormData;
    /** Runs right before the request is sent. */
    beforeSend?: () => void;
    /** Runs once the request settles, regardless of the outcome. */
    complete?: () => void;
    /** Runs with the parsed JSON response on success. */
    success?: (response: any) => void;
}

/**
 * Runs the callback once the DOM is ready, mirroring the old
 * `jQuery(document).ready()` behaviour.
 */
export function ready(callback: () => void): void {
    if (document.readyState !== 'loading') {
        callback();
    } else {
        document.addEventListener('DOMContentLoaded', () => callback());
    }
}

/**
 * A thin wrapper around `fetch` for talking to WordPress' admin-ajax endpoint.
 * It automatically attaches the nonce and posts the data in the format the PHP
 * handlers expect (URL-encoded for plain objects, multipart for FormData).
 */
export async function ajax(options: AjaxOptions): Promise<void> {
    const { data } = options;
    let body: BodyInit;

    if (data instanceof FormData) {
        if (!data.has('nonce')) {
            data.append('nonce', wfr.nonce);
        }
        body = data;
    } else {
        const params = new URLSearchParams();

        for (const [key, value] of Object.entries(data)) {
            if (value === undefined || value === null) {
                continue;
            }

            if (Array.isArray(value)) {
                value.forEach((item) => params.append(`${key}[]`, String(item)));
            } else if (typeof value === 'object') {
                params.append(key, JSON.stringify(value));
            } else {
                params.append(key, String(value));
            }
        }

        params.append('nonce', wfr.nonce);
        body = params;
    }

    options.beforeSend?.();

    try {
        const response = await fetch(wfr.ajaxUrl, { method: 'POST', body });
        const payload = await response.json();
        options.success?.(payload);
    } finally {
        options.complete?.();
    }
}

/**
 * Reflects the value of a range input into the adjacent `.wfr-range-value`
 * element. Accepts either a selector (used on page load) or an input element.
 */
export function rangeValue(target: string | HTMLInputElement): void {
    if (typeof target === 'string') {
        document
            .querySelectorAll<HTMLInputElement>(target)
            .forEach((input) => applyRangeValue(input));
        return;
    }

    applyRangeValue(target);
}

function applyRangeValue(input: HTMLInputElement): void {
    const style = input.dataset.style;

    if (style === '') {
        return;
    }

    let value: string = input.value;

    if (style === 'percentages') {
        const max = Number(input.getAttribute('max')) || 1;
        value = `${Math.trunc((Number(input.value) / max) * 100)}%`;
    }

    const valueElement = next(input, '.wfr-range-value');

    if (valueElement) {
        valueElement.innerHTML = value;
    }
}

/**
 * Returns the immediately following sibling, optionally filtered by a selector.
 * Mirrors jQuery's `.next(selector)` (only the direct sibling is considered).
 */
export function next(element: Element, selector?: string): HTMLElement | null {
    const sibling = element.nextElementSibling as HTMLElement | null;

    if (!sibling) {
        return null;
    }

    if (selector && !sibling.matches(selector)) {
        return null;
    }

    return sibling;
}

/**
 * Returns the immediately preceding sibling, optionally filtered by a selector.
 * Mirrors jQuery's `.prev(selector)`.
 */
export function prev(element: Element, selector?: string): HTMLElement | null {
    const sibling = element.previousElementSibling as HTMLElement | null;

    if (!sibling) {
        return null;
    }

    if (selector && !sibling.matches(selector)) {
        return null;
    }

    return sibling;
}

/**
 * Returns all preceding siblings that match the selector, mirroring jQuery's
 * `.prevAll(selector)`.
 */
export function prevAll(element: Element, selector: string): HTMLElement[] {
    const matches: HTMLElement[] = [];
    let sibling = element.previousElementSibling;

    while (sibling) {
        if (sibling.matches(selector)) {
            matches.push(sibling as HTMLElement);
        }
        sibling = sibling.previousElementSibling;
    }

    return matches;
}

/** Shows an element by clearing its inline `display` value. */
export function show(element: HTMLElement): void {
    element.style.removeProperty('display');
}

/** Hides an element by setting its inline `display` to `none`. */
export function hide(element: HTMLElement): void {
    element.style.display = 'none';
}

/** Sets an element's height in pixels, mirroring jQuery's `.height(value)`. */
export function setHeight(element: HTMLElement, height: number): void {
    element.style.height = `${height}px`;
}

/** Animated reveal, a native replacement for jQuery's `.slideDown()`. */
export function slideDown(element: HTMLElement, duration = 300): void {
    element.style.removeProperty('display');

    let display = window.getComputedStyle(element).display;
    if (display === 'none') {
        display = 'block';
    }
    element.style.display = display;

    const height = element.offsetHeight;

    element.style.overflow = 'hidden';
    element.style.height = '0';
    element.style.paddingTop = '0';
    element.style.paddingBottom = '0';
    element.style.marginTop = '0';
    element.style.marginBottom = '0';

    void element.offsetHeight; // Force a reflow so the transition kicks in.

    element.style.transitionProperty = 'height, margin, padding';
    element.style.transitionDuration = `${duration}ms`;
    element.style.height = `${height}px`;
    element.style.removeProperty('padding-top');
    element.style.removeProperty('padding-bottom');
    element.style.removeProperty('margin-top');
    element.style.removeProperty('margin-bottom');

    window.setTimeout(() => {
        element.style.removeProperty('height');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
    }, duration);
}

/** Animated collapse, a native replacement for jQuery's `.slideUp()`. */
export function slideUp(element: HTMLElement, duration = 300): void {
    element.style.transitionProperty = 'height, margin, padding';
    element.style.transitionDuration = `${duration}ms`;
    element.style.boxSizing = 'border-box';
    element.style.height = `${element.offsetHeight}px`;

    void element.offsetHeight; // Force a reflow so the transition kicks in.

    element.style.overflow = 'hidden';
    element.style.height = '0';
    element.style.paddingTop = '0';
    element.style.paddingBottom = '0';
    element.style.marginTop = '0';
    element.style.marginBottom = '0';

    window.setTimeout(() => {
        element.style.display = 'none';
        element.style.removeProperty('height');
        element.style.removeProperty('padding-top');
        element.style.removeProperty('padding-bottom');
        element.style.removeProperty('margin-top');
        element.style.removeProperty('margin-bottom');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
    }, duration);
}
