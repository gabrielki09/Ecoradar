# Mapa público de pontos de coleta

A seção **Pontos de coleta** da landing page consulta pontos cadastrados na API e mostra marcadores apenas para registros públicos com coordenadas válidas. A lista abaixo do mapa permite consultar os mesmos pontos sem usar o mapa. Não há pontos ou coordenadas de demonstração.

## Contrato da API

`GET /api/v1/collection-points/public?page=1&per_page=100` não exige autenticação. O backend retorna somente pontos não excluídos com status `Recebendo materiais`. A resposta segue o envelope `data.collection_points` da paginação Laravel:

```json
{
  "success": true,
  "message": "Pontos de coleta públicos.",
  "data": {
    "collection_points": {
      "current_page": 1,
      "last_page": 1,
      "data": [
        {
          "id": 1,
          "name": "Nome do ponto",
          "address": "Rua e número",
          "neighborhood": "Bairro",
          "cep": "00000000",
          "guidelines": "Como entregar os materiais",
          "latitude": -27.23417,
          "longitude": -52.02778,
          "opening_hours": [
            { "opening_day": 1, "opens_at": "08:00", "closes_at": "18:00" }
          ],
          "categories": [
            { "id": 2, "name": "Pilhas e baterias" }
          ]
        }
      ]
    }
  }
}
```

O exemplo ilustra o formato; os dados exibidos vêm da API. Cada marcador precisa de `id`, `name`, `latitude` e `longitude`. Latitude deve estar entre `-90` e `90`; longitude, entre `-180` e `180`. Endereço, bairro, CEP, categorias, horários e orientações compõem os detalhes. O modelo atual não possui campo de cidade. Contato e identificação do responsável não entram na resposta pública.

O frontend solicita páginas sucessivas até `current_page === last_page`, com até 100 pontos por página. Se uma página falhar ou vier em formato inválido, mostra o estado de erro e permite tentar novamente. O mapa é enquadrado nas coordenadas carregadas; pontos sem coordenadas continuam na lista. Nenhuma geocodificação é feita ao abrir a página.

## Interface

- Leaflet desenha o mapa com tiles do OpenStreetMap; `leaflet.markercluster` agrupa marcadores próximos. A navegação por rolagem da página não altera o zoom do mapa.
- Selecionar um marcador mostra os detalhes ao lado do mapa, ou abaixo dele no celular. Na lista, **Ver detalhes** expande horários, categorias e orientações no próprio cartão.
- Sem pontos públicos, a seção informa que não há pontos disponíveis. Se houver pontos sem coordenadas, a lista permanece disponível e explica por que não aparecem marcadores.
- A lista também serve como alternativa acessível quando o mapa ou os tiles não puderem ser usados.

## Execução local

O frontend Quasar usa a base `/api/v1` e, em desenvolvimento, encaminha `/api` para `http://127.0.0.1:8000` pelo proxy em `quasar.config.ts`. Assim, a landing page aberta em `http://localhost:9000` ou em um IP da rede chama a API sem usar `0.0.0.0` como destino no navegador. A política de segurança em `index.html` permite imagens de `https://tile.openstreetmap.org` para exibir os tiles. Após mudar `.env`, proxy ou política de segurança, reinicie o frontend e recarregue a página.

Para um ponto aparecer no mapa, a migração `2026_11_23_000000_add_coordinates_to_collection_points_table` deve estar aplicada e o registro deve ter latitude e longitude reais, status `Recebendo materiais` e não estar excluído. Pontos cadastrados antes da migração precisam receber coordenadas; o sistema não as calcula a partir do CEP.

## Diagnóstico rápido

1. Consulte `/api/v1/collection-points/public?page=1&per_page=100`. Confirme `data.collection_points.data`, `current_page` e `last_page`.
2. Se a resposta vier vazia, verifique status e exclusão dos registros. Se o ponto estiver na lista, mas sem marcador, verifique latitude e longitude.
3. Se o mapa aparecer em branco, confira no navegador se as imagens de `tile.openstreetmap.org` carregam e se a política de segurança da página não as bloqueia.
4. Se **Ver detalhes** não expandir o cartão, faça uma atualização completa da página para carregar o JavaScript atual.

Arquivos principais: `src/modules/home/pages/HomePage.vue`, `src/modules/home/components/PointsMap.vue`, `src/modules/home/components/CollectionPointCard.vue`, `src/modules/home/services/public-points.service.ts`, `src/boot/axios.ts`, `quasar.config.ts` e `index.html`. No backend: `routes/api.php`, `PublicCollectionPointController.php`, `CollectionPointService.php`, `CollectionPointEloquent.php` e a migração de coordenadas.

Para volumes maiores, vale carregar `openingHours`, `collectionWasteCategory` e `wasteCategory` antecipadamente na consulta pública do backend. A transformação atual acessa essas relações por ponto e pode produzir consultas adicionais por registro.
