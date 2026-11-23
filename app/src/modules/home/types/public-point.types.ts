export interface PublicPoint {
    id: number;
    name: string;
    address: string;
    neighborhood: string;
    cep: string;
    guidelines: string;
    latitude: number | null;
    longitude: number | null;
    opening_hours: { opening_day: number; opens_at: string; closes_at: string }[];
    categories: { id: number; name: string }[];
}
