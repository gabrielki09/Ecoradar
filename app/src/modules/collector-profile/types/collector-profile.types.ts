export interface WasteCategoryOption {
    id: number;
    name: string;
    is_system: boolean;
}

export interface CollectorProfileForm {
    service_description: string;
    cities: number[];
    waste_category: number[];
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
    waste_categories: WasteCategoryOption[];
}

export interface CityIndexData {
    cities: PaginatedData<CityOption>;
}
