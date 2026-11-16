export interface CollectorProfile {
    id: number;
    user_id: number;
}

export interface CollectorOnboardingPayload {
    service_description: string;
    cities: number[];
    waste_category: number[];
}

export interface CollectorOnboardingResponse {
    collector_profile: CollectorProfile;
}

export interface CompanyOnboardingPayload {
    company_name: string;
    document: string;
    responsible: string;
    cep: string | null;
    neighborhood: string | null;
    address: string | null;
    contact_email: string | null;
}

export interface Company {
    id: number;
    document: string;
    company_name: string;
    responsible: string;
    neighborhood: string | null;
    address: string | null;
    contact_email: string | null;
}

export interface CompanyOnboardingResponse {
    company: Company;
}
