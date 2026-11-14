import type { CollectionPoint, HowItWorksStep, WasteMaterial } from '../types/home.types';

export const wasteMaterials: WasteMaterial[] = [
    { name: 'Eletrônicos', description: 'Celulares, cabos e aparelhos', icon: 'devices', color: '#1976d2', background: '#eaf4ff' },
    { name: 'Pilhas e baterias', description: 'Pilhas e baterias portáteis', icon: 'battery_charging_full', color: '#7b61a8', background: '#f1ecf8' },
    { name: 'Óleo de cozinha', description: 'Óleo usado e armazenado', icon: 'water_drop', color: '#d99521', background: '#fff5de' },
    { name: 'Vidro', description: 'Garrafas, frascos e potes', icon: 'wine_bar', color: '#14a37f', background: '#e7f7f2' },
    { name: 'Plástico', description: 'Embalagens, garrafas e potes', icon: 'recycling', color: '#e16b3d', background: '#fff0e9' },
    { name: 'Papel e papelão', description: 'Caixas, jornais e revistas', icon: 'inventory_2', color: '#957048', background: '#f7f0e9' },
];

export const howItWorksSteps: HowItWorksStep[] = [
    { number: '01', title: 'Encontre', description: 'Busque pelo material que deseja descartar ou por um ponto perto de você.', icon: 'search' },
    { number: '02', title: 'Escolha', description: 'Compare locais, materiais aceitos, horários e escolha a melhor opção.', icon: 'location_on' },
    { number: '03', title: 'Descarte', description: 'Leve seus resíduos até o ponto ou solicite uma coleta domiciliar.', icon: 'recycling' },
    { number: '04', title: 'Acompanhe', description: 'Veja seu impacto positivo e acompanhe cada solicitação em um só lugar.', icon: 'insights' },
];

export const collectionPoints: CollectionPoint[] = [
    { id: 1, name: 'Ecoponto Centro', address: 'Rua do Comércio, 280 · Centro', distance: '1,2 km', schedule: 'Aberto até 18h', materials: ['Eletrônicos', 'Pilhas', 'Papel'], icon: 'storefront' },
    { id: 2, name: 'Cooperativa Recicla+', address: 'Rua das Indústrias, 45 · Industrial', distance: '2,8 km', schedule: 'Aberto até 17h', materials: ['Plástico', 'Vidro', 'Metal'], icon: 'warehouse' },
    { id: 3, name: 'Ponto Verde Norte', address: 'Av. Brasil, 850 · Norte', distance: '3,4 km', schedule: 'Abre amanhã às 8h', materials: ['Óleo', 'Papel', 'Plástico'], icon: 'park' },
];
