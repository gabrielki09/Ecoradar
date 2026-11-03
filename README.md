# EcoRadar

Projeto acadêmico de Análise e Desenvolvimento de Sistemas — Senac Concórdia (SC).

## Estrutura

```
ecoradar-backend/    API (Node.js + Express + Prisma + SQL Server)
ecoradar-frontend/   Site (ecoradar.html — HTML/CSS/JS puros)
```

## Como rodar o projeto (cada integrante na própria máquina)

Cada pessoa do grupo precisa ter o **SQL Server** instalado e rodando
localmente (ou apontar para um banco compartilhado, se o grupo preferir —
ver observação no final).

### 1. Clonar o repositório

```powershell
git clone <url-do-repositorio>
cd ecoradar
```

### 2. Configurar e rodar o backend

```powershell
cd ecoradar-backend
npm install
copy .env.example .env
```

Edite o `.env` com os dados do **seu próprio** SQL Server (veja
`ecoradar-backend/README.md` para o passo a passo detalhado, incluindo como
habilitar o TCP/IP e criar um login).

```powershell
npx prisma generate
npx prisma migrate dev --name init
node prisma/seed.js
npm run dev
```

A API deve subir em `http://localhost:3333`.

### 3. Abrir o frontend

Com a API rodando, abra `ecoradar-frontend/ecoradar.html` direto no
navegador (duplo clique).

> O `ecoradar.html` está configurado para chamar a API em
> `http://localhost:3333`. Isso significa que **cada pessoa precisa ter sua
> própria API rodando localmente** enquanto estiver testando — os dados
> cadastrados por você não aparecem para os colegas, pois cada um tem seu
> próprio banco.

## Trabalhando em grupo: banco compartilhado (opcional)

Se o grupo quiser testar com os **mesmos dados** (ex: um cadastrou um ponto
de coleta e todos veem), é preciso que todos apontem para o **mesmo banco**.
Isso exige que uma pessoa do grupo deixe o SQL Server acessível pela rede
(ou usem um banco na nuvem, como Azure SQL ou um serviço gratuito). Essa
etapa ainda não foi configurada neste projeto — avise se quiserem seguir
por esse caminho.

## Próximos passos do grupo

- Conectar a página de Pontos de Coleta à API (hoje ainda usa dados fixos)
- Testar o fluxo completo do painel do coletor e da empresa
- Decidir sobre banco compartilhado ou deploy na nuvem
