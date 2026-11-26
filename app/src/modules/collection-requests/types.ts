import type { CityOption } from '@/modules/collector-profile/types/collector-profile.types';

export type CollectionStatus = 'open' | 'accepted' | 'collected' | 'completed' | 'cancelled';
export const quantityUnitOptions = [
  { value: 'UN', label: 'Unidade (UN)', symbol: 'UN' },
  { value: 'QDE', label: 'Quantidade (Qde)', symbol: 'Qde' },
  { value: 'L', label: 'Litro (L)', symbol: 'L' },
  { value: 'ML', label: 'Mililitro (mL)', symbol: 'mL' },
  { value: 'KG', label: 'Quilograma (kg)', symbol: 'kg' },
  { value: 'G', label: 'Grama (g)', symbol: 'g' },
  { value: 'T', label: 'Tonelada (t)', symbol: 't' },
  { value: 'M3', label: 'Metro cúbico (m³)', symbol: 'm³' },
] as const;
export type QuantityUnit = typeof quantityUnitOptions[number]['value'];
export interface CollectionItem {
  id?: number;
  waste_category_id: number;
  approximate_quantity: number | string;
  quantity_unit: QuantityUnit | null;
  description?: string | null;
  waste_category?: { name: string };
}
export interface CollectionRequest {
  id: number; requester_user_id?: number; city_id: number; city?: CityOption; cep?: string; neighborhood: string; address?: string;
  status: CollectionStatus; items?: CollectionItem[]; requester?: { id: number; name: string };
  assigned_to_provider?: boolean; acceptance_block_reason?: string | null;
  accepted_company_id?: number | null; accepted_collector_profile_id?: number | null;
  acceptance_date?: string | null; withdrawal_date?: string | null; completion_date?: string | null;
  canceled_date?: string | null; created_at?: string | null;
}
export interface Page<T> { data: T[]; current_page: number; last_page: number; per_page: number; total?: number }
export type Area = 'all' | 'available' | 'for-me' | 'mine';
export const statusLabels: Record<CollectionStatus, string> = { open: 'Aberta', accepted: 'Aceita', collected: 'Coletada', completed: 'Concluída', cancelled: 'Cancelada' };
export const areaLabels: Record<Area, string> = { all: 'Todas as solicitações', available: 'Aplicáveis', 'for-me': 'Minhas coletas', mine: 'Minhas solicitações' };
