# Pontos de recompensa

Cada coleta que passa de `collected` para `completed` rende **10 pontos ao solicitante e 10 ao prestador**. O dashboard em `/area` mostra o saldo do contexto atual. O link **Ver histórico de recompensas** abre `/area/recompensas`. Contas empresariais podem alternar entre suas empresas vinculadas.

## Concessão e saldo

| Parte | Origem do beneficiário na solicitação bloqueada | Titular do saldo |
| --- | --- | --- |
| Solicitante | `requester_user_id` | Conta do usuário |
| Coletor individual | `accepted_collector_profile_id` → `collector_profiles.user_id` | Conta do coletor |
| Empresa | `accepted_company_id` | Empresa vinculada à execução |

O serviço de conclusão confere o status e o prestador autenticado após obter o bloqueio de linha. A mudança de status, a data de conclusão, o histórico operacional e os dois créditos são gravados na mesma transação. O solicitante só pode cancelar pedidos abertos. O fluxo atual encerra a coleta em `completed`.

O fluxo normal reserva a criação a usuários comuns e a execução a prestadores com onboarding concluído. Se dados inconsistentes identificarem a mesma conta nas duas partes, a conclusão retorna HTTP 409. Também é necessário haver um único prestador vinculado.

A tabela `reward_entries` registra o tipo e o ID do beneficiário, a solicitação de origem, o motivo `collection_completion`, os pontos inteiros e a data. Uma restrição única cobre `(collection_request_id, beneficiary_type, beneficiary_id, reward_type)`. A chave estrangeira protege a referência à coleta. O saldo é a soma dos lançamentos daquele titular. A migration cria a tabela vazia.

O valor de cada novo crédito é lido de [config/rewards.php](../api/config/rewards.php). O extrato usa a quantidade gravada no lançamento e ordena por data e ID decrescentes.

## Contrato HTTP

As rotas abaixo usam `/api/v1` e autenticação Sanctum. Saldo e histórico usam a conta autenticada. Para um perfil empresarial, envie o `company_id` selecionado. A API verifica o vínculo na mesma relação usada pelo fluxo de coleta.

| Método e rota | Parâmetros | Resultado |
| --- | --- | --- |
| `GET /reward-points` | `company_id` para empresa | Saldo inteiro em `data.balance` |
| `GET /reward-points/history` | Mesmo contexto, `page` a partir de 1 e `per_page` entre 1 e 100, padrão 15 | Lançamentos e metadados em `data.history` |
| `POST /collection-requests/complete` | `collection_request_id` e, para empresa, `company_id` | Coleta atualizada, pontos concedidos **a cada parte** e mensagem para o operador |

**Exemplo de saldo** para `GET /api/v1/reward-points?company_id=7`.

```json
{
  "success": true,
  "message": "Saldo de pontos de recompensa.",
  "data": { "balance": 10 },
  "status": 200
}
```

**Exemplo de histórico** para `GET /api/v1/reward-points/history?company_id=7&page=1&per_page=10`.

```json
{
  "success": true,
  "message": "Histórico de pontos de recompensa.",
  "data": {
    "history": {
      "data": [{
        "collection_request_id": 42,
        "points": 10,
        "reason": "collection_completion",
        "description": "Recompensa por coleta realizada",
        "created_at": "2026-11-26T14:00:00.000000Z",
        "can_view_collection": true
      }],
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  },
  "status": 200
}
```

**Exemplo de conclusão por empresa** para `POST /api/v1/collection-requests/complete`.

```json
{ "collection_request_id": 42, "company_id": 7 }
```

Trecho da resposta, com os campos da coleta abreviados.

```json
{
  "success": true,
  "message": "Coleta concluída! A empresa recebeu 10 pontos de recompensa.",
  "data": {
    "collection_request": { "id": 42, "status": "completed", "accepted_company_id": 7 },
    "points_awarded": 10
  },
  "status": 200
}
```

O histórico vazio retorna `data: []` dentro de `history`, e o saldo inicial é `0`. A API retorna 401 sem sessão, 403 para perfil ou onboarding sem permissão, 404 para empresa fora dos vínculos do usuário e 422 para contexto empresarial ausente. Repetir uma conclusão já confirmada retorna 409.

## Aplicação e verificações locais

No diretório `api/`, execute `php artisan migrate`. Em instalações que usam caches de rotas ou configuração, regenere os caches correspondentes com `php artisan route:cache` e `php artisan config:cache`. A referência para preparar o ambiente está no [guia de execução](how_to_run.md).

- **API** — `php artisan test` executa a suíte. A configuração padrão usa SQLite em memória e requer `pdo_sqlite`.
- **Concorrência** — para exercitar o bloqueio, configure as variáveis `DB_*` apontando para um PostgreSQL exclusivo de testes, migre-o com `php artisan migrate --env=testing` e execute `php artisan test` com as mesmas variáveis exportadas. O caso de concorrência usa um schema temporário próprio e dois processos. A conta de teste precisa de permissão para criar o schema.
- **App** — em `app/`, execute `pnpm typecheck` e `pnpm build`.

### Resultado da validação

| Verificação | Resultado nesta implementação |
| --- | --- |
| Suíte em PostgreSQL | 14 testes aprovados, 159 asserções. Inclui a aplicação da migration sobre uma coleta já concluída e duas transações disputando a mesma linha. |
| Suíte em SQLite | 13 testes aprovados. O caso específico de bloqueio de linha roda em PostgreSQL. |
| PHP | Sintaxe aprovada nos arquivos alterados. Pint aprovado nos arquivos novos. O Pint global apontou 92 arquivos tanto na revisão base quanto na árvore final. |
| App | Typecheck e build aprovados. O projeto não define scripts próprios de lint ou teste de frontend. |
| Navegador | Chrome com a API real em uma instância isolada. Os três perfis passaram pelo fluxo, incluindo retorno do detalhe, troca de empresa, recuperação de erro no histórico e consulta em largura de 390 px. |

## Roteiro para os três perfis

1. Entre como solicitante, anote o saldo no dashboard e crie duas solicitações em uma cidade e categoria atendidas pelos prestadores de teste.
2. Com o coletor individual, aceite a primeira e marque como coletada. Confira o saldo. Conclua, confira a confirmação e abra o histórico. A diferença deve ser de 10 pontos, com data e link para a primeira coleta.
3. Com um membro da empresa, selecione a empresa que atende a segunda solicitação. Aceite, marque como coletada e conclua. Confira os 10 pontos no saldo empresarial. Outro membro da mesma empresa deve ver o lançamento. Selecione outra empresa vinculada e confira o extrato dela.
4. Volte à conta do solicitante e abra o histórico novamente. O saldo deve ter aumentado em 20 pontos, com um lançamento para cada coleta. Cancele um terceiro pedido ainda aberto e confira que o saldo continua igual.

## Arquivos da implementação

| Área | Arquivos |
| --- | --- |
| Persistência | [Migration de recompensas](../api/database/migrations/2026_11_26_000000_create_reward_entries_table.php), [compatibilidade do vínculo empresarial com SQLite](../api/database/migrations/2026_11_14_030908_create_company_users_table.php), [RewardEntry](../api/app/Models/Collection/RewardEntry.php), [CollectionRequest](../api/app/Models/Collection/CollectionRequest.php) |
| Regra de negócio | [RewardPointService](../api/app/Services/Collection/RewardPointService.php), [configuração de pontos](../api/config/rewards.php), [CollectionRequestService](../api/app/Services/Collection/CollectionRequestService.php) |
| Repositório | [RewardEntryInterface](../api/app/Repositories/Interface/Collection/RewardEntryInterface.php), [RewardEntryEloquent](../api/app/Repositories/Eloquent/Collection/RewardEntryEloquent.php), [AppServiceProvider](../api/app/Providers/AppServiceProvider.php) |
| HTTP | [Rotas](../api/routes/api.php), [RewardPointController](../api/app/Http/Controllers/CollectionRequest/RewardPointController.php), [RewardPointsRequest](../api/app/Http/Requests/Collection/RewardPointsRequest.php), [RewardEntryResource](../api/app/Http/Resources/RewardEntryResource.php), [CollectionRequestController](../api/app/Http/Controllers/CollectionRequest/CollectionRequestController.php) |
| App | [Dashboard](../app/src/modules/account/pages/UserHomePage.vue), [saldo](../app/src/modules/rewards/components/RewardBalance.vue), [histórico](../app/src/modules/rewards/pages/RewardPointsPage.vue), [serviço HTTP](../app/src/modules/rewards/service.ts), [store](../app/src/modules/rewards/store.ts), [rotas](../app/src/router/routes.ts), [guarda de acesso](../app/src/router/index.ts), [detalhe](../app/src/modules/collection-requests/pages/CollectionDetailPage.vue), [ações de coleta](../app/src/modules/collection-requests/service.ts) |
| Testes | [Regras, consultas e transação](../api/tests/Feature/RewardPointsTest.php), [concorrência em PostgreSQL](../api/tests/Feature/RewardConcurrencyTest.php) |
