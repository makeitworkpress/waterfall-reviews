/**
 * Handles our filter actions.
 */
import { ajax, rangeValue } from '../utils';
import { charts } from './charts';

class Filter {
    /** The page currently being requested, when paginating filtered results. */
    private page = 0;

    public initialize(): void {
        document
            .querySelectorAll<HTMLFormElement>('.wfr-filter')
            .forEach((form) => this.listener(form));

        this.ratingSlider();
    }

    /**
     * Filters our results.
     *
     * @param form The form initiating the filter action.
     */
    public filter(form: HTMLFormElement): void {
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
        if (this.page) {
            data.append('page', String(this.page));
            this.page = 0;
        }

        void ajax({
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
                    const view = document.querySelector(`div[data-id="${target}"]`);
                    if (view) {
                        view.outerHTML = response.data.html;
                    }

                    if (typeof lazyload !== 'undefined') {
                        lazyload.update();
                    } else if (typeof wpOptimizeLazyLoad !== 'undefined') {
                        wpOptimizeLazyLoad.update();
                    } else if (typeof wpComponentsLazyLoad !== 'undefined') {
                        wpComponentsLazyLoad.update();
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
