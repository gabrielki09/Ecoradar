export function cepDigits(value: string): string {
    return value.replace(/\D/g, "");
}

export function formatCep(value: string): string {
    const digits = cepDigits(value);

    return digits.length === 8 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : value;
}
