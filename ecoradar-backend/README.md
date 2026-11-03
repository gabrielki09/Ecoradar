# EcoRadar — Backend (API)

API para o projeto acadêmico EcoRadar (ADS, Senac Concórdia — SC).

> **Status:** estrutura inicial planejada. Ainda não foi testada em execução —
> siga os passos abaixo no seu ambiente para validar e ajustar o que for
> necessário (ex: versões do Node, do PostgreSQL etc.).

## Stack

- Node.js + Express
- PostgreSQL + Prisma (ORM)
- Autenticação: JWT + bcrypt
- Validação: Zod

## Estrutura de pastas

```
src/
├── controllers/   # recebem a requisição e devolvem a resposta
├── services/       # regras de negócio (ex: autenticação)
├── routes/        # definição dos endpoints
├── middlewares/    # autenticação, tratamento de erros
└── config/         # conexão com o banco de dados
prisma/
├── schema.prisma   # modelo do banco de dados
└── seed.js         # dados iniciais (categorias de resíduo)
```

## Pré-requisitos

- Node.js 18 ou superior
- PostgreSQL instalado e rodando (local ou em um serviço como Supabase/Railway/Neon)

## Instalação

```bash
cd ecoradar-backend
npm install
cp .env.example .env
```

Edite o arquivo `.env` com os dados reais do seu banco (`DATABASE_URL`) e um
`JWT_SECRET` próprio.

## Criando o banco de dados

```bash
npx prisma migrate dev --name init
node prisma/seed.js
```

Isso cria as tabelas e popula as categorias de resíduo (Lixo eletrônico,
Pilhas, Óleo de cozinha, Vidro, Plástico, Papel e papelão, Metal, Outros).

## Executando a API

```bash
npm run dev
```

A API sobe em `http://localhost:3333` (ou na porta definida em `.env`).

## Endpoints principais

| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| POST | `/api/auth/registro` | público | Cria conta |
| POST | `/api/auth/login` | público | Autentica e retorna token |
| GET | `/api/pontos-coleta` | público | Lista pontos (filtros: `categoria`, `bairro`, `busca`) |
| GET | `/api/pontos-coleta/:id` | público | Detalhe de um ponto |
| POST | `/api/pontos-coleta` | coletor | Cria ponto de coleta |
| PUT | `/api/pontos-coleta/:id` | coletor (dono) | Edita ponto de coleta |
| POST | `/api/solicitacoes` | doador/empresa | Cria solicitação de coleta |
| GET | `/api/solicitacoes/minhas` | autenticado | Lista solicitações do usuário |
| GET | `/api/solicitacoes/recebidas` | coletor | Lista solicitações pendentes/ativas |
| PATCH | `/api/solicitacoes/:id/status` | autenticado | Atualiza status da solicitação |
| GET | `/api/impacto/publico` | público | Indicadores agregados |
| GET | `/api/usuarios/me/painel` | autenticado | Dados do painel pessoal |

Todas as rotas autenticadas exigem o cabeçalho:
`Authorization: Bearer <token recebido no login>`

## Integrando com o frontend (ecoradar.html)

O frontend hoje usa `localStorage` através de um pequeno objeto `db`
(`db.get`/`db.set`). O próximo passo é trocar essas chamadas por `fetch()`
para os endpoints acima, mantendo o restante da interface como está.

## O que ainda falta (próximos passos)

- Testar a API localmente (migrations, seed e cada endpoint)
- Definir a regra de negócio de "quais solicitações pertencem a qual coletor"
  (hoje `/solicitacoes/recebidas` retorna todas as ativas, como ponto de
  partida)
- Conectar o frontend (`ecoradar.html`) à API
- Adicionar testes automatizados
- Configurar deploy (ex: Railway, Render) e um banco de produção
