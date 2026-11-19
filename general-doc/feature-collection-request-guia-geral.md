# EcoRadar: guia geral da branch `feature/collection-request`

Atualizado em 19/11/2026. Este documento descreve o estado da árvore de trabalho nesta data, incluindo alterações ainda não commitadas em `api/` e `app/`. Para instalar e iniciar o ambiente, consulte [COMO_RODAR.md](COMO_RODAR.md).

## Visão geral

O fluxo de solicitações de coleta conecta três experiências: o usuário comum cria e acompanha pedidos; o coletor individual consulta pedidos que atende e executa a coleta; a empresa faz o mesmo no contexto de uma empresa vinculada à sua conta. A API é Laravel 13 com Sanctum; o frontend é Vue 3, Quasar, TypeScript e Pinia.

O estado segue `open` → `accepted` → `collected` → `completed`. Um pedido `open` também pode passar a `cancelled` pelo solicitante. Aceitar, marcar como coletado, concluir e cancelar são ações separadas. A API valida o estado e o vínculo do operador, inclusive no momento do aceite; ocultar um botão no frontend não substitui essa validação.

## O que foi implementado na API

- `CollectionRequestController` separa listagens globais, aplicáveis, do prestador e do solicitante. `CollectionAccess` centraliza as regras de perfil e onboarding concluído, usadas também nas requisições de coleta: `common` cria, lista os próprios pedidos e cancela; `collector` e `company` consultam pedidos e atuam como prestadores. O detalhe de um usuário comum exige que ele seja o solicitante.
- `CollectionRequestService` resolve o prestador autenticado. Para empresas, `company_id` é conferido na relação `user->companies()`, de modo que o ID do usuário não substitui o ID da empresa. No aceite, cidade e **todas** as categorias dos itens devem ser atendidas pelo prestador.
- `CollectionRequestEloquent::availableFor()` retorna apenas pedidos `open` com itens, em cidades atendidas e sem qualquer item de categoria fora do catálogo do prestador. `allForProvider()` filtra pelo ID do coletor ou da empresa que aceitou. `mine()` filtra pelo solicitante. A paginação usa `per_page`, limitada a 100 no repositório.
- `UserResource` passa a incluir `companies: [{ id, company_name }]` para contas do tipo empresa; o frontend usa essa lista para selecionar a empresa. Esse campo é obtido em login/registro e em `/auth/me` quando o recurso é retornado.
- Foram corrigidos os vínculos de repositório de itens e cidades do coletor, o `StandardWasteSeeder` e o tratamento de `per_page` no repositório base. O enum antigo `ProviderType` foi removido.

### Rotas de coleta

Todas as rotas abaixo usam o prefixo `/api/v1/collection-requests` e ficam dentro de `auth:sanctum`.

| Método e caminho | Uso | Perfil |
| --- | --- | --- |
| `GET /all` | Todas as solicitações, com filtros e paginação | Prestador |
| `GET /available` | Pedidos abertos e aplicáveis ao prestador | Prestador; empresa envia `company_id` |
| `GET /for-me` | Pedidos aceitos pelo prestador selecionado | Prestador; empresa envia `company_id` |
| `GET /mine` | Pedidos criados pelo usuário autenticado | Usuário comum |
| `GET /{id}` | Detalhe com itens e solicitante | Usuário comum dono do pedido ou prestador |
| `POST /` | Criar solicitação | Usuário comum |
| `POST /accept` | Aceitar pedido `open` | Prestador |
| `POST /collected` | Mudar `accepted` para `collected` | Prestador responsável |
| `POST /complete` | Mudar `collected` para `completed` | Prestador responsável |
| `POST /cancel` | Cancelar pedido `open`, com motivo | Solicitante |

As listagens retornam um paginador Laravel em `data.collection_requests`, **exceto `/mine`**, cuja chave atual é `data.collection_request`. O detalhe e as ações usam `data.collection_request`. `GET /all` é realmente global para prestadores autorizados: sem filtro, inclui todos os status e os dados retornados pelo modelo. `GET /available` não fornece um motivo de inelegibilidade para os pedidos que exclui.

Envie `page` e `per_page` nas listagens. O filtro de status usa `filtersV1`, por exemplo `filtersV1[0][field]=status`, `filtersV1[0][filterType]=EQUAL` e `filtersV1[0][filterValue]=open`. Os valores de status são `open`, `accepted`, `collected`, `completed` e `cancelled`. O `page` é interpretado pelo paginador do Laravel.

Para criar, envie `city_id`, `cep` com oito dígitos, `neighborhood`, `address` e `items` não vazio. Cada item precisa de `waste_category_id`, `approximate_quantity` maior que zero e pode ter `description`. As ações de prestador recebem `collection_request_id` e, para empresa, `company_id`; o cancelamento recebe `collection_request_id` e `cancellation_reason`.

## O que foi implementado no frontend

- O módulo `app/src/modules/collection-requests/` contém tipos, serviço HTTP, store Pinia e páginas de listagem, criação e detalhe. A listagem tem as áreas **Todas as requisições**, **Aplicáveis**, **Minhas coletas** e **Minhas solicitações**, com filtro por status onde cabível, paginação comum e estados de carga, erro e vazio. O scroll infinito pedido é no seletor de cidades, não nessa listagem.
- A página de criação valida cidade, CEP, endereço, itens, categorias distintas e quantidade positiva antes de enviar à API. A página de detalhe mostra status, endereço, itens e datas; pede confirmação para ações e motivo para cancelamento. Em conflito HTTP 409, recarrega os dados.
- Os menus, atalhos da área autenticada e guardas do roteador distinguem usuário comum e prestador. Contas com onboarding pendente são direcionadas à etapa correspondente. A store de coleta guarda a empresa selecionada entre as empresas recebidas da sessão e invalida listas após ações.
- `CitySelect.vue` substitui a carga antecipada de todos os municípios nas telas de onboarding e perfil do coletor e também é usado na criação de pedidos. Ele consulta `GET /general/cities` em páginas de 30, carrega a próxima ao rolar as opções, busca com debounce por nome TOM, nome IBGE ou UF, evita IDs repetidos e descarta respostas de buscas antigas.
- O catálogo de categorias do perfil do coletor passa a tratar resposta em lista ou paginador. Há também ajustes pontuais de tipagem, formatação e ação de exclusão em categorias.

As rotas de tela são `/area/coletas/all`, `/area/coletas/available`, `/area/coletas/for-me`, `/area/coletas/mine`, `/area/coletas/nova` e `/area/coletas/detalhe/:id`. O serviço HTTP usa a instância `api`, cuja `VITE_API_URL` deve terminar em `/api/v1`.

## Como executar e testar manualmente

Prepare o projeto conforme [COMO_RODAR.md](COMO_RODAR.md): PostgreSQL, migrações, seed de categorias e seed de municípios, API na porta 8000 e frontend na 9000. Confirme `GET /api/v1/general/cities` e `php artisan route:list --path=collection-requests`. Depois, use contas de teste **separadas** para solicitante comum, coletor e empresa, todas com onboarding concluído. Para testar empresas, o vínculo deve aparecer em `user.companies` na resposta de `/auth/me`.

1. **Solicitante:** entre como `common`, crie um pedido com cidade e duas categorias, confira `/mine` e o detalhe. Abra outro pedido para testar cancelamento com motivo. Verifique que não consegue cancelar pedidos já aceitos e que não abre o detalhe de outro solicitante.
2. **Aplicabilidade:** configure um coletor ou empresa com uma cidade e categorias específicas. Em `/all`, confira a listagem global. Em `/available`, o pedido deve aparecer somente se estiver `open`, tiver itens, estiver na cidade atendida e **todas** as categorias forem aceitas. Teste também cidade diferente, uma categoria não atendida e catálogo vazio.
3. **Aceite e andamento:** aceite um pedido aplicável; confira que ele some de `/available`, aparece em `/for-me` e muda para `accepted` em `/mine`. Marque como coletado e depois conclua; confirme as datas e o status após recarregar as três telas. Tente aceitar o mesmo pedido com outro prestador e verifique o conflito.
4. **Empresa:** selecione uma empresa em `user.companies`, confira que o `company_id` enviado é o **ID da empresa**. Se o usuário tiver duas empresas, alterne a seleção e confirme que `/available`, `/for-me` e as ações usam a empresa atual. Teste um `company_id` que não esteja vinculado ao usuário.
5. **Seletor de cidades:** abra o dropdown em onboarding, perfil e criação; confirme que inicialmente apenas a primeira página é pedida. Role até o fim para carregar a próxima, pesquise por nome e UF, troque rapidamente a busca, tente novamente após erro e verifique que seleções de páginas anteriores permanecem. Chegue à última página e confirme que não há novas requisições.
6. **Permissões e falhas:** confira 401 sem token, 403 para perfil indevido, erros de validação dos formulários e atualização da tela após HTTP 409. No navegador, inspecione as requisições de rede e confirme `page`, `per_page`, `company_id` e `filtersV1`.

### Comandos de checagem

```bash
cd api
php artisan route:list --path=collection-requests
php artisan test --compact

cd ../app
pnpm typecheck
pnpm build
```

Neste ambiente WSL, o `pnpm` encontrado em `PATH` é o executável do Windows e não conseguiu iniciar `vue-tsc` a partir do caminho UNC. A checagem equivalente **passou** com `node node_modules/vue-tsc/bin/vue-tsc.js --noEmit` em `app/`. O build não foi executado nesta revisão. O comando `php artisan route:list --path=collection-requests` mostrou 10 rotas. A suíte atual da API executou 5 testes: 4 passaram, incluindo 3 testes de acesso a rotas de coleta; o teste padrão da página inicial falhou porque a view `welcome` não existe. Os testes de acesso não exercitam as consultas de elegibilidade nem o ciclo completo de estados. As quatro classes PHP principais conferidas passaram em `php -l`. Faça os testes manuais acima antes de considerar o fluxo validado de ponta a ponta.

## Pontos de atenção

- **Elegibilidade na lista global:** o backend não retorna `eligible` nem motivos por pedido. O frontend não usa a ausência de um ID em uma página de `/available` para concluir que ele é inelegível, pois as listas são paginadas independentemente. Para exibir a razão diretamente no cartão global, será necessário ampliar o contrato da API.
- **Visibilidade de dados:** `/all` e o detalhe de pedidos alheios estão acessíveis a qualquer prestador autenticado e com onboarding concluído. A resposta inclui endereço do pedido. Revisar a política de leitura e a exposição desses dados antes de abrir a funcionalidade a usuários externos.
- **Contexto da empresa:** `user.companies` é a fonte dos IDs válidos. Confirmar que a conta recebe a lista após login, recarga e conclusão do onboarding. Selecionar uma empresa na interface não concede acesso: o backend confere o vínculo em cada resolução de prestador.
- **Catálogo e municípios:** executar ambos os seeders em um banco limpo. Testar a busca `ILIKE` do seletor com o banco real e nomes alternativos TOM/IBGE. Um ID de cidade salvo que não esteja nas opções carregadas pode aparecer temporariamente como `Cidade #ID` até a opção ser buscada.
- **Listas e ações:** ações dependem do estado atual do pedido. Conferir dados após mudanças concorrentes. O frontend guarda IDs vistos em páginas de `/available` e `/for-me` para decidir quais botões exibir; a API continua sendo a autoridade sobre permissão e estado.
- **Documentos existentes:** `app/docs/collection-requests-backend.md` descreve contratos pendentes de uma versão anterior; sua observação sobre a rota global e `user.companies` está desatualizada em relação a esta árvore de trabalho. Consulte este guia e as rotas atuais.
- **Versionamento:** os arquivos em `app/src/modules/collection-requests/`, `app/src/components/CitySelect.vue` e outros ainda aparecem como não rastreados ou modificados no momento deste registro. Revise `git status` antes de criar o commit. O `.gitignore` da raiz contém uma exceção específica para este guia em `doc/`.
