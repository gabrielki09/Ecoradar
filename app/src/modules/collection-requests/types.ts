export type CollectionStatus = 'open' | 'accepted' | 'collected' | 'completed' | 'cancelled';
export interface CollectionItem { id?: number; waste_category_id: number; approximate_quantity: number | string; description?: string | null }
export interface CollectionRequest {
  id: number; requester_user_id: number; city_id: number; cep: string; neighborhood: string; address: string;
  status: CollectionStatus; items?: CollectionItem[]; requester?: { id: number; name: string };
  accepted_company_id?: number | null; accepted_collector_profile_id?: number | null;
  acceptance_date?: string | null; withdrawal_date?: string | null; completion_date?: string | null;
  canceled_date?: string | null; created_at?: string | null;
}
export interface Page<T> { data: T[]; current_page: number; last_page: number; per_page: number; total?: number }
export type Area = 'all' | 'available' | 'for-me' | 'mine';
export const statusLabels: Record<CollectionStatus, string> = { open: 'Aberta', accepted: 'Aceita', collected: 'Coletada', completed: 'Concluída', cancelled: 'Cancelada' };
export const areaLabels: Record<Area, string> = { all: 'Todas as requisições', available: 'Aplicáveis', 'for-me': 'Minhas coletas', mine: 'Minhas solicitações' };
