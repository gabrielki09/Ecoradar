type Address = {
    zip: string; // cep
    street: string; // neighborhood
    district: string; // address
};

type Emails = {
    ownership: string;
    address: string; // contact_email
    domain: string;
};

export interface CNPJResponse {
    alias: string; // company_name
    taxId: string; // document
    address: Address;
    emails: Emails[]
};
