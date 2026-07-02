/**
 * Handles our comparison table actions.
 */
import { ajax, next } from '../utils';

class Tables {
    /** The review ids currently selected for comparison. */
    private reviews: string[] = [];

    public initialize(): void {
        // Load a new table whenever a review is (de)selected.
        document.addEventListener('click', (event) => {
            const item = (event.target as HTMLElement | null)?.closest<HTMLElement>(
                '.wfr-tables-form li',
            );
            if (!item) {
                return;
            }

            const review = item.dataset.target;
            if (!review) {
                return;
            }

            item.classList.toggle('active');

            // Remove the item if it's already selected, otherwise add it.
            if (this.reviews.includes(review)) {
                this.reviews = this.reviews.filter((id) => id !== review);
            } else {
                this.reviews.push(review);
            }

            this.loadTables(item);
        });

        // Prevent submitting of the default form.
        document.querySelectorAll<HTMLFormElement>('.wfr-tables-form').forEach((form) => {
            form.addEventListener('submit', (event) => event.preventDefault());
        });
    }

    /**
     * Creates our table loader - upon changes it will load a new table.
     *
     * @param item The item initiating the action.
     */
    private loadTables(item: HTMLElement): void {
        if (this.reviews.length < 2) {
            return;
        }

        const form = item.closest<HTMLElement>('.wfr-tables-form');
        if (!form) {
            return;
        }

        const view = next(form, '.wfr-tables-view');

        void ajax({
            beforeSend: () => view?.classList.add('components-loading'),
            complete: () => view?.classList.remove('components-loading'),
            data: {
                action: 'load_tables',
                attributes: form.dataset.attributes,
                categories: form.dataset.categories,
                groups: form.dataset.groups,
                reviews: this.reviews,
                properties: form.dataset.properties,
                tags: form.dataset.tags,
                view: form.dataset.view,
                weight: form.dataset.weight,
            },
            success: (response) => {
                if (wfr.debug) {
                    console.log(response);
                }

                if (!response.success || !view) {
                    return;
                }

                const parsed = new DOMParser().parseFromString(response.data, 'text/html');
                const newView = parsed.querySelector('.wfr-tables-view');
                if (newView) {
                    view.outerHTML = newView.outerHTML;
                }
            },
        });
    }
}

export const tables = new Tables();
