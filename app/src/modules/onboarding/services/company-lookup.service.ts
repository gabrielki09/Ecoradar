import { apiCep, apiCnpj } from '@/boot/axios';
import { CEPResponse } from '../types/cep.types';
import { CNPJResponse } from '../types/cnpj.types';

const onlyDigits = (value: string) => value.replace(/\D/g, '');

/**
 * Clientes preparados para a consulta e o preenchimento do formulário.
 * A página não dispara essas requisições automaticamente.
 */
export const companyLookupService = {
    findAddressByCep(cep: string) {
        return apiCep.get<CEPResponse>(`/${onlyDigits(cep)}`);
    },

    findCompanyByCnpj(cnpj: string) {
        return apiCnpj.get<CNPJResponse>(`/${onlyDigits(cnpj)}`);
    },
};
