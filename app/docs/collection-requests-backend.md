# Contratos do módulo de coletas

## Integrados neste workspace

- `GET /api/v1/collection-requests/all` é a listagem global. A consulta não recebe `company_id`; o acesso exige perfil de prestador com onboarding concluído.
- `/auth/me` e a resposta de login incluem `user.companies` para contas de empresa, com `id` e `company_name` obtidos do vínculo persistido em `company_users`. O frontend aceita somente esses IDs na seleção de empresa. Ao trocar a empresa, limpa os dados anteriores e recarrega as consultas dependentes.
- O backend rejeita operações incompatíveis com o perfil ou onboarding, valida o vínculo com a empresa informada, restringe o detalhe do solicitante às próprias solicitações e verifica propriedade, atribuição e status nas transições.

## Contrato ainda necessário

- Para mostrar elegibilidade e motivos em cada pedido da listagem global ou do detalhe, a API precisa retornar uma avaliação no contexto do prestador selecionado: `eligible: boolean`, códigos e mensagens de motivos e identidade do prestador avaliado (`collector_profile_id` ou `company_id`). A avaliação deve considerar cidade e todas as categorias dos itens. A ausência de um pedido em uma página de `/available` não informa inelegibilidade.
