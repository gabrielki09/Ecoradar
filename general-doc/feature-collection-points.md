# Pontos de coleta: implementação da API e do frontend

Este documento descreve o código atual de pontos de coleta. Um ponto é um endereço que **recebe materiais**; solicitações de coleta, que retiram materiais no endereço do solicitante, são outro fluxo. As rotas e telas abaixo exigem sessão autenticada.

## Fluxo

1. Qualquer conta autenticada acessa `/pontos` e consulta a lista de pontos ativos.
2. Um prestador com onboarding concluído acessa `/pontos/meus`, escolhe sua empresa quando aplicável e cria um ponto em `/pontos/novo`.
3. O formulário envia endereço, ao menos um horário e ao menos uma categoria de material. A API valida o prestador e grava ponto e vínculos em uma transação.
4. O responsável abre detalhes, edita ou exclui o ponto. Pontos excluídos podem ser mostrados em **Meus pontos** e recuperados.

## Backend

### Organização e dados

- Rotas: `api/routes/collections/collection-points.php`, incluídas em `api/routes/api.php` sob `/api/v1` e `auth:sanctum`.
- Entrada HTTP: `CollectionPointController` e Requests em `api/app/Http/Requests/CollectionPoint/`.
- Regras e transações: `api/app/Services/Collection/CollectionPointService.php`; seleção do prestador em `api/app/Helpers/provider/resolveProvider.php`.
- Consultas: `CollectionPointEloquent`, apoiado em `BaseEloquent`; persistência de horários e categorias em repositórios próprios.
- Tabelas: `collection_points`, `collection_point_opening_hours` e `collection_point_waste_categories`. As três têm exclusão lógica. Horários são únicos por `(collection_point_id, opening_day)`; categorias, por `(collection_point_id, waste_category_id)`.

O ponto guarda nome, CEP de oito dígitos, bairro, endereço, orientações, contato, status e o par `responsible_operator_type`/`responsible_operator_id`. O tipo distingue `collector` de `company` inclusive nas consultas de propriedade. O status inicial é `Recebendo materiais`; existe também o enum `Temporariamente indisponível.`, mas esta API não oferece mudança de status.

### Rotas e acesso

| Método e caminho após `/api/v1` | Uso | Acesso |
| --- | --- | --- |
| `GET /collection-points` | Lista paginada de pontos ativos | Qualquer conta autenticada |
| `GET /collection-points/for-provider` | Lista dos pontos do prestador; aceita `include_trashed=1` | Prestador responsável |
| `POST /collection-points` | Criação | Prestador responsável |
| `GET /collection-points/{id}` | Detalhe de ponto ativo próprio | Prestador responsável e proprietário |
| `PUT` ou `PATCH /collection-points/{id}` | Atualização parcial | Prestador responsável e proprietário |
| `DELETE /collection-points/{id}` | Exclusão lógica | Prestador responsável e proprietário |
| `POST /collection-points/{id}/recovery` | Recuperação | Prestador responsável e proprietário |

`CollectionAccess::provider` exige perfil `collector` ou `company` e onboarding concluído nas rotas de gestão. Para empresa, `company_id` é obrigatório; `resolveProvider(..., responsibleOnly: true)` exige vínculo com papel `responsible`. Um coletor usa seu `collectorProfile`. O serviço compara ID **e tipo** do prestador ao buscar um ponto próprio. `company_id` identifica o contexto de autorização e não transfere o ponto.

Listagens devolvem `data.collection_points` paginado; criação, detalhe e atualização devolvem `data.collection_point`. A API usa o envelope `success`, `message`, `data` e `status`. A lista geral não tem rota anônima nem tela pública de detalhe; o detalhe implementado é para gestão do próprio ponto.

### Criação e edição

Na criação são obrigatórios `name`, `cep`, `neighborhood`, `address`, `guidelines`, `contact`, ao menos um item em `opening_hours` e ao menos um em `waste_categories`. `company_id` é exigido para conta de empresa. A Request remove a pontuação do CEP e valida oito dígitos. Cada horário recebe `opening_day` de `0` (domingo) a `6` (sábado), `opens_at` e `closes_at` no formato `H:i`, com fechamento posterior à abertura. Cada categoria deve existir em `waste_categories`. Dias ou categorias repetidos no mesmo payload recebem erro de validação.

Exemplo de criação para um coletor; uma empresa acrescenta `"company_id": 12`:

```json
{
  "name": "Ponto Central",
  "cep": "01001000",
  "neighborhood": "Sé",
  "address": "Praça da Sé, 1",
  "guidelines": "Entregar materiais limpos.",
  "contact": "contato@exemplo.com",
  "opening_hours": [
    { "opening_day": 1, "opens_at": "08:00", "closes_at": "18:00" }
  ],
  "waste_categories": [
    { "waste_category_id": 3 }
  ]
}
```

`PUT` e `PATCH` usam a mesma lógica parcial: campos simples ausentes permanecem iguais e `null` não é aceito. `opening_hours` e `waste_categories` seguem este contrato:

| Payload | Efeito |
| --- | --- |
| Campo ausente ou `[]` | Não altera a coleção. |
| Item omitido | Permanece como está. |
| Item enviado sem `delete` ou com `delete: false` | Cria, atualiza ou restaura o item identificado pelo dia ou categoria dentro deste ponto. |
| Item enviado com `delete: true` | Exclui logicamente o item ativo; não cria um item ausente. |

Exemplo de alteração de horário e remoção de categoria:

```json
{
  "company_id": 12,
  "opening_hours": [
    { "opening_day": 1, "opens_at": "09:00", "closes_at": "17:00" }
  ],
  "waste_categories": [
    { "waste_category_id": 3, "delete": true }
  ]
}
```

Exclusão e recuperação ocorrem em transação. Ao excluir o ponto, o serviço marca e exclui logicamente seus horários e categorias ativos. Na recuperação, restaura apenas os filhos marcados como excluídos **junto com o ponto**; um vínculo removido individualmente antes continua excluído. `GET /{id}` não encontra pontos excluídos.

## Frontend

O módulo está em `app/src/modules/collection-points/`. `service.ts` chama a API e busca todas as páginas do catálogo em `/waste-categories`; `store.ts` mantém a empresa selecionada, filtrando os vínculos de `/auth/me` pelo papel `responsible`. `types.ts` define os payloads e modelos usados pelas telas.

| Rota | Tela e comportamento |
| --- | --- |
| `/pontos` | `PointListPage.vue`: lista geral paginada, 15 itens por página. |
| `/pontos/meus` | Mesma página, filtrada pelo prestador; seleciona empresa e pode incluir excluídos para recuperação. |
| `/pontos/novo` | `PointCreatePage.vue` com `PointForm.vue` para criação. |
| `/pontos/meus/:id` | `PointDetailPage.vue`: endereço, horários, materiais, edição e exclusão. |
| `/pontos/meus/:id/editar` | `PointEditPage.vue` com formulário preenchido pelo detalhe da API. |

O guard em `app/src/router/index.ts` protege as rotas de gestão com `pointManagement`; para empresa, exige uma empresa responsável selecionada. A autorização real permanece na API. A navegação para pontos aparece no layout autenticado e na área da conta.

`PointForm.vue` valida campos, carrega categorias, usa `HoursEditor.vue` para os dias da semana e mostra erros por campo devolvidos pela API. Ao editar, compara os campos atuais aos originais e envia apenas mudanças; horários e categorias removidos vão com `delete: true`. Após salvar, invalida o estado da lista e navega para o detalhe.

Os campos de CEP do onboarding da empresa, da solicitação de coleta e do formulário de pontos usam a máscara `00000-000`. `app/src/composables/useCepAutocomplete.ts` consulta a BrasilAPI ao completar oito dígitos e preenche apenas endereço ou bairro ainda vazios. A resposta atrasada é ignorada se o CEP tiver mudado. `app/src/utils/cep.ts` formata CEPs nas listas e detalhes e remove a pontuação antes do envio. A URL da consulta vem de `VITE_API_CEP`, configurada no ambiente do frontend.

## Execução e conferência

Siga [Como rodar o EcoRadar localmente](how_to_run.md) para configurar PostgreSQL, migrar as tabelas, carregar categorias e iniciar API e frontend. Para conferir o fluxo, entre com uma conta de coletor ou empresa com onboarding concluído; no caso de empresa, o vínculo deve ter papel `responsible`. Crie um ponto com horário e categoria, abra o detalhe, edite os dados, exclua e use **Mostrar pontos excluídos** para recuperá-lo. Entre com uma conta comum autenticada para conferir a listagem geral.

Para checar o frontend após alterações, execute `npm run typecheck` em `app/`. A configuração da API de CEP e da API principal fica em `app/.env` (`VITE_API_CEP` e `VITE_API_URL`).

## Limites atuais

- A listagem geral mostra dados resumidos; a tela de detalhe é exclusiva do responsável pelo ponto.
- Não há edição de status pela interface ou pelas rotas deste módulo.
- O catálogo de categorias vem de `/waste-categories`; a seleção de materiais é feita no cadastro e na edição, mas a lista geral ainda não expõe filtro por material.
- O autocomplete de CEP auxilia o preenchimento; falha na consulta não impede entrada manual do endereço.
