import { nextTick } from 'vue';

export const scrollToFirstError = async (fieldOrder: string[], errors: any) => {
    await nextTick();

    const firstField = fieldOrder.find(field => Boolean(errors[field]));
    if (!firstField) return;

    const input = document.querySelector<HTMLElement>(
        `[name="${firstField}"]`
    );

    if (!input) return;

    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();

    input.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ? 'instant'
            : 'smooth',
        block: 'center'
    });

    if (!window.matchMedia('(pointer: coarse)').matches) input.focus({ preventScroll: true });

};
