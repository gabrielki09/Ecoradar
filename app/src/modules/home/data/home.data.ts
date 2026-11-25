import type { HowItWorksStep, WasteMaterial } from '../types/home.types';

export const wasteMaterials: WasteMaterial[] = [
    { name: 'Eletrônicos', description: 'Celulares, cabos e aparelhos', icon: 'devices', color: '#1976d2', background: '#eaf4ff' },
    { name: 'Pilhas e baterias', description: 'Pilhas e baterias portáteis', icon: 'battery_charging_full', color: '#7b61a8', background: '#f1ecf8' },
    { name: 'Óleo de cozinha', description: 'Óleo usado e armazenado', icon: 'water_drop', color: '#d99521', background: '#fff5de' },
    { name: 'Vidro', description: 'Garrafas, frascos e potes', icon: 'wine_bar', color: '#14a37f', background: '#e7f7f2' },
    { name: 'Plástico', description: 'Embalagens, garrafas e potes', icon: 'recycling', color: '#e16b3d', background: '#fff0e9' },
    { name: 'Papel e papelão', description: 'Caixas, jornais e revistas', icon: 'inventory_2', color: '#957048', background: '#f7f0e9' },
];

export const howItWorksSteps: HowItWorksStep[] = [
    { number: '01', title: 'Encontre', description: 'Consulte o mapa e a lista de pontos de coleta.', icon: 'search' },
    { number: '02', title: 'Escolha', description: 'Compare locais, materiais aceitos, horários e escolha a melhor opção.', icon: 'location_on' },
    { number: '03', title: 'Descarte', description: 'Leve seus resíduos até o ponto ou solicite uma coleta domiciliar.', icon: 'recycling' },
    { number: '04', title: 'Acompanhe', description: 'Acompanhe o status de cada solicitação na sua conta.', icon: 'insights' },
];

