type IBGE = {
    city: string;
    state: string;
};

export interface CEPResponse {
    neighborhood: string;
    street: string;
    ibge: IBGE;
};
