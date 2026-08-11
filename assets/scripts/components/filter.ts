/**
 * Handles our filter actions.
 *
 * Pagination for filtered results can't follow the plain archive URLs that
 * `paginate_links()` renders: those links don't carry the active filter
 * arguments (category, tags, price, sort, ...), so following them would
 * silently drop the filter. Instead, pagination and infinite scroll both
 * simply re-run the filter request for the page they point to.
 */
import { ajax, hide, rangeValue } from '../utils';
import { charts } from './charts';

interface FilterOptions {
    /** The page of results to request. */
    page?: number;
    /** Appends the results instead of replacing them, used by infinite scroll. */
    append?: boolean;
}

class Filter {
    /**
     * The page to request on the next filter action. Kept public for backwards
     * compatibility; prefer passing the page through the filter options.
     */
    public page = 0;

    /** Targets with an in-flight infinite scroll request. */
    private loading = new Set<string>();

    /** Targets that already have their infinite scroll listener bound. */
    private infiniteScrollBound = new Set<string>();

    public initialize(): void {
        document
            .querySelectorAll<HTMLFormElement>('.wfr-filter')
            .forEach((form) => this.listener(form));

        this.ratingSlider();
    }

    /**
     * Filters our results.
     *
     * @param form    The form initiating the filter action.
     * @param options Pagination options for the request.
     */
    public filter(form: HTMLFormElement, options: FilterOptions = {}): Promise<void> {
        // The `wfr` object is localized on waterfall-reviews.min.js.
        const data = new FormData(form);
        data.append('action', 'filter_reviews');

        const target = form.dataset.target ?? '';

        // We're viewing a review category page.
        const category = form.dataset.category;
        if (category) {
            data.append('category', category);
        }

        // Add the page if we are filtering for pages.
        const page = options.page ?? this.page;
        if (page) {
            data.append('page', String(page));
        }
        this.page = 0;

        return ajax({
            beforeSend: () => {
                document
                    .querySelector(`div[data-id="${target}"]`)
                    ?.classList.add('components-loading');
            },
            complete: () => {
                document
                    .querySelector(`div[data-id="${target}"]`)
                    ?.classList.remove('components-loading');
            },
            data,
            success: (response) => {
                if (wfr.debug) {
                    console.log(response);
                }

                if (!response.success) {
                    return;
                }

                if (typeof response.data.html !== 'undefined') {
                    const container = this.applyResults(
                        target,
                        response.data.html,
                        options.append === true,
                    );

                    if (container) {
                        this.setupPagination(container, form);
                        this.setupInfiniteScroll(container, form);
                    }

                    if (typeof lazyload !== 'undefined') {
                        lazyload.update();
                    } else if (typeof wpOptimizeLazyLoad !== 'undefined') {
                        wpOptimizeLazyLoad.update();
                    } else if (typeof wpComponentsLazyLoad !== 'undefined') {
                        wpComponentsLazyLoad.update();
                    }

                    // Sync scroll-reveal, just like the upstream posts component.
                    if (typeof sr !== 'undefined') {
                        sr.sync();
                    }
                }

                // Emit a chart change - the chart component listens to this change.
                if (typeof response.data.posts !== 'undefined') {
                    const select =
                        document
                            .querySelector(`div[data-id="${target}"]`)
                            ?.closest('.atom-tabs-content')
                            ?.querySelector<HTMLSelectElement>('.wfr-chart-selector select') ??
                        null;

                    charts.posts = response.data.posts;
                    charts.listener(select);
                }
            },
        });
    }

    /**
     * Puts the filtered markup on the page and returns the resulting container.
     *
     * @param target The data-id of the container that holds the results.
     * @param html   The rendered markup returned by the filter request.
     * @param append Appends the posts instead of replacing the container.
     */
    private applyResults(target: string, html: string, append: boolean): HTMLElement | null {
        const container = document.querySelector<HTMLElement>(`div[data-id="${target}"]`);
        const incoming = new DOMParser()
            .parseFromString(html, 'text/html')
            .querySelector<HTMLElement>(`div[data-id="${target}"]`);

        if (!container || !incoming) {
            return null;
        }

        if (!append) {
            container.replaceWith(incoming);
            return incoming;
        }

        // Infinite scroll: append the new posts and swap in the new pagination.
        const wrapper = container.querySelector('.molecule-posts-wrapper');
        incoming.querySelectorAll('.molecule-post').forEach((post) => wrapper?.appendChild(post));

        const pagination = container.querySelector('.atom-pagination');
        const newPagination = incoming.querySelector('.atom-pagination');
        if (pagination && newPagination) {
            pagination.replaceWith(newPagination);
        }

        return container;
    }

    /**
     * Re-runs the filter for the page a pagination anchor points to, instead
     * of following its href.
     *
     * @param container The container holding the results and its pagination.
     * @param form      The filter form the results belong to.
     */
    private setupPagination(container: HTMLElement, form: HTMLFormElement): void {
        if (!container.classList.contains('molecule-posts-ajax')) {
            return;
        }

        container.querySelectorAll<HTMLAnchorElement>('.atom-pagination a').forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                event.preventDefault();

                const page = this.pageNumber(anchor, container);
                if (page) {
                    void this.filter(form, { page });
                }
            });
        });
    }

    /**
     * Appends the next page of results once the container's bottom scrolls
     * into view. Bound once per target - the handler always looks up the live
     * container, since it gets replaced on every request.
     *
     * @param container The container holding the results and its pagination.
     * @param form      The filter form the results belong to.
     */
    private setupInfiniteScroll(container: HTMLElement, form: HTMLFormElement): void {
        if (!container.classList.contains('molecule-posts-infinite')) {
            return;
        }

        const pagination = container.querySelector<HTMLElement>('.atom-pagination');
        if (pagination) {
            hide(pagination);
        }

        const target = form.dataset.target ?? '';
        if (this.infiniteScrollBound.has(target)) {
            return;
        }
        this.infiniteScrollBound.add(target);

        window.addEventListener('scroll', () => {
            if (this.loading.has(target)) {
                return;
            }

            const current = document.querySelector<HTMLElement>(`div[data-id="${target}"]`);
            if (!current || window.innerHeight < current.getBoundingClientRect().bottom) {
                return;
            }

            // paginate_links() omits the "next" link on the last page.
            if (!current.querySelector('.atom-pagination a.next')) {
                return;
            }

            this.loading.add(target);
            void this.filter(form, {
                page: this.currentPage(current) + 1,
                append: true,
            }).finally(() => this.loading.delete(target));
        });
    }

    /**
     * Resolves the page number a pagination anchor points to, based on its
     * text (numbered links) or its position relative to the current page
     * (the previous/next arrows).
     */
    private pageNumber(anchor: HTMLAnchorElement, container: HTMLElement): number | null {
        if (anchor.classList.contains('next')) {
            return this.currentPage(container) + 1;
        }

        if (anchor.classList.contains('prev')) {
            return this.currentPage(container) - 1;
        }

        const digits = (anchor.textContent ?? '').replace(/\D/g, '');
        return digits ? parseInt(digits, 10) : null;
    }

    /**
     * Reads the active page from the rendered pagination.
     */
    private currentPage(container: HTMLElement): number {
        const digits = (
            container.querySelector('.atom-pagination .page-numbers.current')?.textContent ?? ''
        ).replace(/\D/g, '');

        return digits ? parseInt(digits, 10) : 1;
    }

    /**
     * Listens to form changes, in order to filter.
     *
     * @param form The form initiating the filter action.
     */
    private listener(form: HTMLFormElement): void {
        // If the input changes, we filter, but only with instant filtering.
        if (form.classList.contains('wfr-instant-filter')) {
            form.querySelectorAll<HTMLElement>('input, select, textarea, button').forEach(
                (input) => {
                    input.addEventListener('change', () => this.filter(form));
                },
            );
        }

        // If we submit, we filter.
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.filter(form);
        });
    }

    /**
     * Sets the value of our rating slider.
     */
    private ratingSlider(): void {
        // Also set the value on page load.
        rangeValue('.wfr-filter-rating input');

        document
            .querySelectorAll<HTMLInputElement>('.wfr-filter-rating input')
            .forEach((input) => {
                input.addEventListener('change', () => rangeValue(input));
            });
    }
}

export const filter = new Filter();
