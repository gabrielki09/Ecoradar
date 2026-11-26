import type { HowItWorksStep, WasteMaterial } from '../types/home.types';

const coletaSeletivaFranca = {
    name: 'Prefeitura de Franca',
    url: 'https://www3.franca.sp.gov.br/noticia/37794/coleta-seletiva.html',
};

export const wasteMaterials: WasteMaterial[] = [
    {
        name: 'Eletrônicos', description: 'Celulares, cabos e aparelhos',
        icon: 'devices', color: '#1976d2', background: '#eaf4ff',
        disposalGuide: 'Se o celular ou computador ainda liga, salve os arquivos que quer guardar e apague seus dados pessoais. Leve o aparelho ou os cabos a um ponto que receba eletrônicos.',
        source: {
            name: 'Ministério das Comunicações',
            url: 'https://www.gov.br/mcom/pt-br/noticias/2026/outubro/nao-sabe-o-que-fazer-com-aquele-aparelho-eletronico-que-voce-nao-usa-mais/',
        },
    },
    {
        name: 'Pilhas e baterias', description: 'Pilhas e baterias portáteis',
        icon: 'battery_charging_full', color: '#7b61a8', background: '#f1ecf8',
        disposalGuide: 'Separe as pilhas e baterias portáteis usadas. Entregue-as em um coletor próprio para esse material, disponível em algumas lojas e supermercados.',
        source: {
            name: 'Sistema Nacional de Informações sobre a Gestão de Resíduos Sólidos',
            url: 'https://sinir.gov.br/perfis/logistica-reversa/logistica-reversa/pilhas-e-baterias/',
        },
    },
    {
        name: 'Óleo de cozinha', description: 'Óleo usado e armazenado',
        icon: 'water_drop', color: '#d99521', background: '#fff5de',
        disposalGuide: 'Deixe o óleo da fritura esfriar e passe-o para uma garrafa PET. Feche bem a tampa e leve a garrafa a um ponto de coleta de óleo de cozinha usado.',
        source: { name: 'Prefeitura de Garça', url: 'https://www.garca.sp.gov.br/coleta-de-oleo-de-cozinha/' },
    },
    {
        name: 'Vidro', description: 'Garrafas, frascos e potes',
        icon: 'wine_bar', color: '#14a37f', background: '#e7f7f2',
        disposalGuide: 'Esvazie e limpe as garrafas, potes e frascos para a coleta de vidro. Se houver cacos, coloque-os em uma caixa resistente, feche-a e escreva “vidro quebrado” para proteger quem faz a coleta.',
        source: coletaSeletivaFranca,
    },
    {
        name: 'Plástico', description: 'Embalagens, garrafas e potes',
        icon: 'recycling', color: '#e16b3d', background: '#fff0e9',
        disposalGuide: 'Esvazie garrafas e potes, retire os restos de comida ou de produto e deixe as embalagens secarem. Separe-as para a coleta seletiva da sua região.',
        source: coletaSeletivaFranca,
    },
    {
        name: 'Papel e papelão', description: 'Caixas, jornais e revistas',
        icon: 'inventory_2', color: '#957048', background: '#f7f0e9',
        disposalGuide: 'Separe jornais, folhas e caixas de papelão secos para a coleta seletiva. Se uma caixa de pizza estiver engordurada, separe apenas as partes limpas para reciclar.',
        source: { name: 'Prefeitura de Caraguatatuba', url: 'https://www.caraguatatuba.sp.gov.br/pmc/coletaseletiva/' },
    },
];

export const howItWorksSteps: HowItWorksStep[] = [
    { number: '01', title: 'Encontre', description: 'Consulte o mapa e a lista de pontos de coleta.', icon: 'search' },
    { number: '02', title: 'Escolha', description: 'Compare locais, materiais aceitos, horários e escolha a melhor opção.', icon: 'location_on' },
    { number: '03', title: 'Descarte', description: 'Leve seus resíduos até o ponto ou solicite uma coleta domiciliar.', icon: 'recycling' },
    { number: '04', title: 'Acompanhe', description: 'Acompanhe o status de cada solicitação na sua conta.', icon: 'insights' },
];

