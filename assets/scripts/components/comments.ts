/**
 * Handles our comment form actions.
 */
import { prevAll, rangeValue, slideDown, slideUp } from '../utils';

class Comments {
    public initialize(): void {
        // Reflect the rating slider value into its label.
        document
            .querySelectorAll<HTMLInputElement>('.comment-form-rating input')
            .forEach((input) => {
                input.addEventListener('change', () => rangeValue(input));
            });

        // Toggle the rating fields when the "reply" checkbox changes.
        const reply = document.querySelector<HTMLInputElement>('#wfr-reply');
        reply?.addEventListener('change', () => {
            const replyContainer = reply.closest<HTMLElement>('.comment-form-reply');
            if (!replyContainer) {
                return;
            }

            prevAll(replyContainer, '.comment-form-rating').forEach((rating) => {
                if (reply.checked) {
                    slideUp(rating);
                } else {
                    slideDown(rating);
                }
            });
        });
    }
}

export const comments = new Comments();
