/**
 * Ambient type declarations for the globals that WordPress (and companion
 * plugins) expose at runtime. These are provided outside of the bundle, so we
 * only describe their shape here instead of importing them.
 */
export {};

interface WfrLocalized {
    ajaxUrl: string;
    debug: boolean;
    nonce: string;
}

interface LazyLoader {
    update: () => void;
}

declare global {
    /** Data localized onto the page through `wp_localize_script`. */
    const wfr: WfrLocalized;

    /** Chart.js (v2) is enqueued separately and lives in the global scope. */
    const Chart: any;

    /** WordPress data / apiFetch helpers available inside the block editor. */
    const wp: any;

    /** Optional lazy-load integrations that other plugins may register. */
    const lazyload: LazyLoader | undefined;
    const wpOptimizeLazyLoad: LazyLoader | undefined;
    const wpComponentsLazyLoad: LazyLoader | undefined;

    /** ScrollReveal instance registered by the theme, when available. */
    const sr: { sync: () => void } | undefined;

    interface Window {
        App?: unknown;
        [key: string]: unknown;
    }
}
