import { nextTick } from 'vue';

export const scrollToFirstError = async (fieldOrder: string[], errors: Readonly<Record<string, string>>) => {
    await nextTick();

    const firstField = fieldOrder.find(field => Boolean(errors[field]));
    if (!firstField) return;

    const input = document.querySelector<HTMLElement>(`[name="${CSS.escape(firstField)}"]`)
        ?? [...document.querySelectorAll<HTMLElement>('[data-error-field]')]
            .find(element => element.dataset.errorField === firstField);

    if (!input) return;

    input.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ? 'instant'
            : 'smooth',
        block: 'center'
    });

    if (!window.matchMedia('(pointer: coarse)').matches) input.focus({ preventScroll: true });

};
