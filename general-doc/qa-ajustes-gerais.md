# EcoRadar | Guia de testes dos ajustes gerais

Use este roteiro para conferir navegação, permissões e fluxos completos no navegador. Os identificadores da tabela ajudam a registrar o resultado de cada cenário.

## Prepare o ambiente

1. Suba frontend, API e PostgreSQL de teste conforme o [guia de execução local](how_to_run.md). Aplique as migrações e carregue os municípios e as categorias de resíduos.
2. Crie quatro contas de teste com documentos e e-mails distintos: dois usuários comuns, um coletor individual e uma empresa. O cadastro da conta pede o CPF da pessoa. O CNPJ é informado na etapa **Dados da empresa**.
3. Configure o coletor e a empresa para atender ao mesmo município e aceitar pelo menos um material em comum, como **Papel e papelão**. Escolha outro material para testar incompatibilidade.
4. Crie um ponto com horários, materiais e coordenadas válidas no ambiente de teste. Crie outro com latitude e longitude em branco para conferir a lista de pontos sem marcador.
5. Separe as contas por janela anônima ou perfil de navegador quando precisar alternar entre solicitante e prestador. Use somente registros de teste nas operações de cancelamento, exclusão e recuperação.

## O que mudou

- **Navegação e sessão.** O botão **Solicitar coleta** na página inicial abre o formulário para usuários comuns. Se a pessoa precisar entrar, o aplicativo retoma a URL solicitada depois do login, conforme as permissões e o onboarding. O menu autenticado se adapta ao perfil e à largura da tela.
- **Solicitações.** O detalhe consulta a API para saber se o prestador pode aceitar ou avançar a coleta, inclusive após F5. As listas **Todas as solicitações** e **Aplicáveis** mostram cidade e bairro. Endereço completo e CEP ficam disponíveis ao solicitante e ao prestador atribuído no detalhe.
- **Perfis de prestador.** O perfil do coletor recupera a descrição, as cidades e as categorias salvas. A empresa é encaminhada ao perfil depois do cadastro para configurar cobertura e editar dados cadastrais. A edição da empresa exige o papel de responsável.
- **Pontos de coleta.** A lista autenticada abre orientações, contato, materiais e horários. A gestão valida o horário de fechamento em relação à abertura do mesmo dia e mantém o ponto associado ao prestador correto. Se o mapa-base falhar, a página oferece nova tentativa e mantém a lista disponível.
- **Estados de tela.** As listas e os formulários afetados exibem carregamento, erros e opções de recuperação. As ações destrutivas pedem confirmação. Métricas de impacto demonstrativas e a rota de categorias sem fluxo completo foram retiradas.

## Roteiro funcional

### Navegação, cadastro e sessão

| Cenário | Como testar | Resultado esperado |
| --- | --- | --- |
| NAV-01 | Na landing, use os links **Guia de descarte**, **Como funciona** e **Pontos de coleta**. Repita pelo menu móvel. | Cada link leva à seção correspondente. O logo volta ao início. |
| NAV-02 | Sem sessão, clique em **Solicitar coleta** e entre como usuário comum. Recarregue o formulário e use **Cancelar**. | O login retoma o formulário em `/area/coletas/nova`. A sessão continua após F5. Cancelar retorna a **Minhas solicitações**. |
| NAV-03 | Cadastre os três perfis. Com um prestador que ainda não concluiu o onboarding, tente abrir `/pontos/meus` direto pela barra de endereços. | O usuário comum entra na área autenticada. O prestador fica na etapa de cadastro correspondente, com o restante do menu disponível após concluí-la. |
| NAV-04 | Em uma conta autenticada, clique em **Sair da conta** e abra novamente uma URL protegida. | A página volta ao login. Após sair, a API retorna `401` em `GET /api/v1/auth/me` se chamada com o token revogado. |

### Solicitações e perfis de coleta

| Cenário | Como testar | Resultado esperado |
| --- | --- | --- |
| SOL-01 | Como usuário comum, envie o formulário sem cidade ou com quantidade zero. Corrija os campos e crie a solicitação. | A validação aparece perto do campo. O envio válido abre o detalhe e a solicitação aparece em **Minhas solicitações** com status **Aberta**. |
| SOL-02 | Crie uma segunda solicitação. Abra o detalhe, clique em **Cancelar solicitação**, informe o motivo e confirme. | O status muda para **Cancelada** e o botão de cancelamento deixa de ser exibido. |
| SOL-03 | Entre como outro usuário comum e tente abrir o detalhe da primeira solicitação pela URL copiada. | A API retorna `403` e a interface exibe erro de acesso. |
| SOL-04 | Como prestador, consulte **Todas as solicitações** e **Aplicáveis**. Compare uma solicitação compatível com outra de cidade diferente ou com pelo menos um material fora da cobertura. | A lista global inclui os dois pedidos. **Aplicáveis** inclui apenas pedidos abertos na cidade atendida e com todos os materiais aceitos. A prévia dos pedidos não atribuídos omite endereço completo e CEP. |
| SOL-05 | Abra o pedido compatível por URL direta, aceite, abra **Minhas coletas** e volte ao detalhe. Clique em **Marcar como coletado**, recarregue e clique em **Concluir coleta**. | O status percorre **Aberta → Aceita → Coletada → Concluída**. O prestador atribuído vê o endereço completo no detalhe e pode avançar a coleta após F5. Um outro prestador recebe `403` se tentar marcar a retirada. Repita o ciclo com um novo pedido para testar a empresa. |
| PERF-01 | No **Perfil de coleta**, edite a descrição e troque uma cidade ou categoria. Salve, recarregue e inclua novamente o item removido. | A configuração persiste após recarregar. O item pode voltar à cobertura sem duplicação. |
| PERF-02 | Conclua o cadastro da empresa, edite a razão social e salve cidades e categorias em **Perfil da empresa**. Recarregue e consulte **Aplicáveis**. | Os dados permanecem preenchidos e a lista usa a cobertura salva. Uma conta sem o vínculo de responsável não pode editar o perfil da empresa. |

### Pontos de coleta, mobile e recuperação

| Cenário | Como testar | Resultado esperado |
| --- | --- | --- |
| PON-01 | Sem login, abra **Pontos de coleta** na landing. Selecione o marcador e use **Ver detalhes** na lista. Depois entre como usuário comum e abra **Pontos de coleta** no menu. | Um ponto público com coordenadas aparece no mapa. Um ponto público sem coordenadas aparece na lista. A lista autenticada mostra também o contato. |
| PON-02 | Em **Meus pontos → Criar ponto**, preencha nome, endereço, contato, orientações, um material e um dia de atendimento. Tente fechar antes de abrir. Em seguida, corrija, replique o horário, salve e edite um dos dias. | O horário inválido é rejeitado. O cadastro válido abre o detalhe, e a edição persiste após atualizar a página. |
| PON-03 | Abra um ponto de teste, clique em **Excluir** e cancele a confirmação. Repita e confirme. Em **Meus pontos**, marque **Mostrar pontos excluídos** e use **Recuperar**. | Fechar a confirmação mantém o ponto ativo. A exclusão confirmada o retira da consulta pública. A recuperação volta a exibir o ponto e seus horários ativos. |
| PON-04 | Copie a URL de gerenciamento de um ponto e abra com outro prestador ou usuário comum. Em uma conta de empresa, teste também a URL com outro vínculo. | Consultar pontos públicos continua disponível. O detalhe de gerenciamento e as alterações são restritos ao prestador responsável pelo ponto. |
| MOB-01 | Repita os cenários principais a **360 px** e **390 px**. Abra o menu, seletores de cidade e categoria e modais de confirmação. Confira também o menu desktop a **1280 px**. | Os controles ficam acessíveis, sem rolagem horizontal ou conteúdo cortado. As ações do menu correspondem ao perfil. |
| ERR-01 | Em ambiente de teste, simule uma falha temporária da API durante o envio de uma solicitação. Reative a resposta normal e envie de novo. Repita com uma listagem. | O formulário preserva os campos e permite reenviar. A listagem apresenta erro e **Tentar novamente**. |
| ERR-02 | No DevTools, bloqueie somente o carregamento de imagens de `tile.openstreetmap.org` e recarregue a landing em **Pontos de coleta**. Remova o bloqueio e clique em **Tentar carregar o mapa**. | O carregamento do mapa-base pode ser retomado. A lista e os detalhes continuam acessíveis enquanto as imagens falham. |
| ERR-03 | Abra **Minhas solicitações** com uma conta nova. Depois, durante um cadastro válido, use a limitação de velocidade do DevTools e clique rapidamente duas vezes em **Criar solicitação**. | A lista vazia oferece ação para criar a primeira solicitação. Durante o envio há feedback visível e apenas uma solicitação é criada. |

## Checagens técnicas

Execute em terminais separados, a partir da raiz do repositório. Para os testes da API, use um banco dedicado de QA. Se for usar PostgreSQL, aplique as migrações e confira as variáveis `DB_*` do processo de teste antes de executar o Composer.

```bash
cd api
composer test
php vendor/bin/pint --test
```

```bash
cd app
npm run typecheck
npm run build
```

As regressões da API ficam em [DeliveryFlowsTest.php](../api/tests/Feature/DeliveryFlowsTest.php). A [documentação do mapa](feature-maps.md) descreve o contrato público, os requisitos das coordenadas e como diagnosticar marcadores ausentes.

## Resultados da revisão local

- PHPUnit passou no PostgreSQL isolado com **5 testes e 24 asserções**. O typecheck, o build, o lint de sintaxe PHP e o `git diff --check` também passaram.
- O Pint global apontou falhas de formatação nos mesmos **92 arquivos** da base anterior aos ajustes. Os arquivos PHP novos passaram no Pint. O frontend não possui script de lint.
- O Chromium foi usado para validar cadastro, onboarding, criação e cancelamento de solicitações, detalhes por URL direta, ciclo do coletor, perfil da empresa e gestão de pontos. O ciclo completo da empresa também foi exercitado por HTTP. A revisão verificou o mapa com tiles reais e com falha simulada.
- O preenchimento manual da empresa foi validado com a consulta externa de CNPJ bloqueada. O sucesso das consultas externas de CEP e CNPJ e o ambiente da apresentação devem ser verificados na próxima rodada.

Ao registrar um defeito, inclua o ID do cenário, o perfil, a URL, a largura da tela, os passos, o comportamento observado e o status HTTP quando houver. Use os IDs dos registros de teste e oculte tokens, senhas e documentos dos anexos.
