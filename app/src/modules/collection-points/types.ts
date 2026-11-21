export interface Page<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total?: number;
    next_page_url?: string | null;
}

export interface OpeningHour {
    id?: number;
    opening_day: number;
    opens_at: string;
    closes_at: string;
}

export interface LinkedCategory {
    id?: number;
    waste_category_id: number;
}

export interface WasteCategory {
    id: number;
    name: string;
}

export interface CollectionPoint {
    id: number;
    name: string;
    cep: string;
    neighborhood: string;
    address: string;
    guidelines: string;
    contact: string;
    status: string;
    responsible_operator_type: string;
    responsible_operator_id: number;
    opening_hours?: OpeningHour[];
    collection_waste_category?: LinkedCategory[];
    deleted_at?: string | null;
}

export interface PointFields {
    name: string;
    cep: string;
    neighborhood: string;
    address: string;
    guidelines: string;
    contact: string;
}

export type HourChange = OpeningHour | { opening_day: number; delete: true };
export type CategoryChange = LinkedCategory | { waste_category_id: number; delete: true };

export interface CreatePointPayload extends PointFields {
    company_id?: number;
    opening_hours: OpeningHour[];
    waste_categories: LinkedCategory[];
}

export interface UpdatePointPayload extends Partial<PointFields> {
    company_id?: number;
    opening_hours?: HourChange[];
    waste_categories?: CategoryChange[];
}

export const dayLabels = [
    'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira',
    'Quinta-feira', 'Sexta-feira', 'Sábado',
];
