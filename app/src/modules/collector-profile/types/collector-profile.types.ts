export interface WasteCategoryOption {
    id: number;
    name: string;
    is_system: boolean;
    company_id?: number | null;
}

export interface CollectorProfileForm {
    service_description: string;
    cities?: { city_id: number; delete: boolean }[];
    waste_categories?: { waste_category_id: number; delete: boolean }[];
}

export interface CollectorProfileData {
    service_description: string | null;
    cities: { city_id: number }[];
    waste_categories: { waste_category_id: number }[];
}

export interface CityOption {
    id: number;
    municipio_tom: string | null;
    municipio_ibge: string | null;
    uf: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
}

export interface WasteCategoryIndexData {
    waste_categories: WasteCategoryOption[] | PaginatedData<WasteCategoryOption>;
}

export interface CityIndexData {
    cities: PaginatedData<CityOption>;
}
