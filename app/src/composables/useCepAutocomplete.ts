import { apiCep } from "@/boot/axios";
import { cepDigits } from "@/utils/cep";

type AddressForm = {
    cep: string;
    address: string;
    neighborhood: string;
};

type CepResponse = {
    street: string;
    neighborhood: string;
};

export function useCepAutocomplete(form: AddressForm) {
    let sequence = 0;

    return async function handleAutocompleteCEP(value: string) {
        const request = ++sequence;
        const digits = cepDigits(value);

        if (digits.length !== 8 || (form.address && form.neighborhood)) {
            return;
        }

        try {
            const { data } = await apiCep.get<CepResponse>(`/${digits}`);

            if (request !== sequence || cepDigits(form.cep) !== digits) {
                return;
            }

            if (!form.address) form.address = data.street ?? "";
            if (!form.neighborhood) form.neighborhood = data.neighborhood ?? "";
        } catch {
            // O endereço pode ser preenchido manualmente quando a consulta falhar.
        }
    };
}
