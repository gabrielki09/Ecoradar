# Contrato de atualização de pontos de coleta

Rotas em `/api/v1/collection-points`, todas com `auth:sanctum`. Operações de gestão exigem perfil `collector` ou `company`, onboarding concluído e o ponto pertencente ao perfil de coletor ou à empresa indicada por `company_id`. Na empresa, o vínculo do usuário precisa ter papel `responsible`.

`PATCH` e `PUT /{id}` aceitam alteração parcial. Campos simples ausentes permanecem iguais; `null` não é aceito. `company_id` identifica a empresa para autorização e não transfere a propriedade do ponto. `status` não pode ser alterado por essa API.

`/auth/me` inclui `companies[].role` (`responsible`, `admin` ou `member`) para o frontend identificar as empresas gerenciáveis. Somente `responsible` tem acesso às operações de gestão de pontos da empresa.

`opening_hours` e `waste_categories` têm a mesma semântica:

| Payload | Efeito |
| --- | --- |
| Campo ausente ou `[]` | Não altera a coleção. |
| Item omitido | Permanece como está. |
| Item enviado sem `delete` ou com `delete: false` | Cria, atualiza ou restaura o item identificado pelo dia/categoria **dentro deste ponto**. |
| Item enviado com `delete: true` | Exclui logicamente o item ativo identificado; não cria item ausente e não o recria na mesma operação. |

Horários usam `opening_day` inteiro de `0` (domingo) a `6` (sábado), `opens_at` e `closes_at` em `H:i`, com fechamento posterior à abertura. Os horários são obrigatórios para itens ativos. `waste_categories` usa `waste_category_id`. Dias/categorias duplicados no mesmo payload geram `422`.

`DELETE /{id}` exclui logicamente o ponto e seus filhos ativos. `POST /{id}/recovery` restaura o ponto e somente os filhos marcados como excluídos junto com ele; filhos removidos individualmente antes continuam excluídos. `GET /for-provider?include_trashed=1` permite localizar pontos excluídos para recuperação. `GET /{id}` só encontra pontos ativos.

Na criação, os campos `name`, `cep`, `neighborhood`, `address`, `guidelines`, `contact`, `opening_hours` e `waste_categories` são obrigatórios. CEP é normalizado para oito dígitos. Ambas as coleções exigem pelo menos um item. A listagem geral `GET /` exige autenticação e pode ser consultada por qualquer perfil; não é uma rota anônima.
