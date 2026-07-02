/**
 * Admin JavaScript for automatically adding keys to fields and keeping linked
 * plan selects in sync.
 */
import { ready } from './utils';

interface PlanOption {
    text: string;
    selected: boolean;
}

/**
 * Sanitizes a string to a key, similar to the WordPress `sanitize_key` function.
 *
 * @param value  The value that needs to be turned into a key.
 * @param target The target input field that needs a sanitized key.
 */
function sanitizeKey(value: string, target: HTMLElement | null): string {
    let context = '';

    if (target) {
        if (target.classList.contains('wfr-property-option')) {
            context = '_property';
        }

        if (target.classList.contains('wfr-criteria-option')) {
            const criteria = target.classList[3].replace('wfr-criteria-', '_');
            context = `${criteria}_attribute`;
        }
    }

    return value.toLowerCase().replace(/[^a-z0-9_-]/g, '') + context;
}

/**
 * Sets the sanitized key for a field, unless the target already has a value.
 */
function fillKeyTarget(fieldName: HTMLInputElement): void {
    const target = fieldName
        .closest('.wpcf-repeatable-fields')
        ?.querySelector<HTMLInputElement>('.wfr-key-target');

    if (fieldName.value && target && !target.value) {
        target.value = sanitizeKey(fieldName.value, target);
    }
}

/**
 * Listens to changes of our plans, and adds them to any related fields.
 */
function addPlansDynamically(): void {
    const plansElementGroups = document.querySelector('.wfr-plans-meta .wpcf-repeatable-groups');

    if (!plansElementGroups) {
        return;
    }

    const triggerReplaceFields = (): void => {
        const fields = document.querySelectorAll<HTMLInputElement>(
            '.wfr-plans-meta .field-id-name input',
        );

        if (!fields.length) {
            return;
        }

        const plans: Record<string, PlanOption> = {};
        fields.forEach((field) => {
            if (!field.value) {
                return;
            }

            const key = sanitizeKey(field.value, null);
            plans[key] = { text: field.value, selected: false };
        });

        if (Object.keys(plans).length < 1) {
            return;
        }

        // Retrieve all select fields.
        const selectFields = document.querySelectorAll<HTMLSelectElement>(
            '.wfr-meta-linked-plans .field-id-plan select',
        );

        if (!selectFields.length) {
            return;
        }

        selectFields.forEach((selectField) => {
            selectField.querySelectorAll('option').forEach((option) => {
                if (option.value === '') {
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(plans, option.value)) {
                    plans[option.value] = { text: option.text, selected: option.selected };
                }

                option.remove();
            });

            Object.keys(plans).forEach((planKey) => {
                const newOption = document.createElement('option');
                newOption.value = planKey;
                newOption.innerHTML = plans[planKey].text;
                newOption.selected = plans[planKey].selected;
                selectField.append(newOption);
            });
        });
    };

    // Watch changes in our plans.
    document.addEventListener('change', (event) => {
        if ((event.target as HTMLElement | null)?.closest('.wfr-plans-meta .field-id-name')) {
            triggerReplaceFields();
        }
    });

    document.addEventListener('click', (event) => {
        if (
            (event.target as HTMLElement | null)?.closest(
                '.wfr-plans-meta .wpcf-repeatable-remove-group',
            )
        ) {
            window.setTimeout(() => triggerReplaceFields(), 600);
        }
    });
}

/**
 * Inserts the calculated rating into the general rating field after saving.
 */
function calculateAutomaticRating(): void {
    const meta = document.querySelector('.wfr-review-meta');

    if (!meta || !meta.classList.contains('wfr-rating-calculation-automatic')) {
        return;
    }

    if (typeof wp === 'undefined' || typeof wp.data === 'undefined') {
        return;
    }

    let updatedMeta: Record<string, unknown> = {};
    let wasSaving = false;
    const wpEditor = wp.data.select('core/editor');

    // After saving, insert our updated rating.
    wp.data.subscribe(() => {
        // Only applied to the reviews post type.
        if (wpEditor.getCurrentPostType() !== 'reviews') {
            return;
        }

        if (wpEditor.isSavingPost()) {
            wasSaving = true;
            return;
        }

        if (wasSaving) {
            const postId = wpEditor.getCurrentPostId();

            // The updated meta is not immediately available, so we fetch it slightly later.
            window.setTimeout(() => {
                wp.apiFetch({ path: `wp/v2/reviews/${postId}` })
                    .then((post: { meta: Record<string, unknown> }) => {
                        // Nothing to update.
                        if (Object.keys(post.meta).length === 0 || updatedMeta === post.meta) {
                            return;
                        }

                        updatedMeta = post.meta;

                        if (Object.prototype.hasOwnProperty.call(updatedMeta, 'rating')) {
                            const rating = document.querySelector<HTMLInputElement>('#rating');
                            if (rating) {
                                rating.value = String(updatedMeta.rating);
                            }
                        }
                    })
                    .catch((error: unknown) => console.log(error));
            }, 1000);
        }

        wasSaving = false;
    });
}

ready(() => {
    // Loop through all our fields and set keys if not set already.
    document
        .querySelectorAll<HTMLInputElement>('#waterfall_options .wfr-key-field')
        .forEach((fieldName) => fillKeyTarget(fieldName));

    // Listen to change events for new fields.
    document.addEventListener('change', (event) => {
        const fieldName = (event.target as HTMLElement | null)?.closest<HTMLInputElement>(
            '.wfr-key-field',
        );

        if (fieldName) {
            fillKeyTarget(fieldName);
        }
    });

    addPlansDynamically();
    calculateAutomaticRating();
});
