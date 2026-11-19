# Como rodar o EcoRadar localmente

O projeto tem uma API Laravel em `api/`, uma aplicação Vue/Quasar em `app/` e um banco PostgreSQL. Em desenvolvimento, a API atende em `http://localhost:8000`, o frontend em `http://localhost:9000` e as rotas da API começam com `/api/v1`.

Execute os comandos abaixo a partir da raiz do repositório, exceto onde houver `cd`.

## Ferramentas necessárias

| Caminho | Instale no computador |
| --- | --- |
| Com Docker | Docker Engine ou Docker Desktop, com o plugin `docker compose` (Compose v2), e Git. O Compose usa imagens com PHP 8.4, Node 22 e PostgreSQL 18. |
| Sem Docker | Git, PHP 8.3 ou superior com extensões usuais do Laravel e `pdo_pgsql` (também `mbstring`, `xml`, `zip` e `bcmath`), Composer 2, Node.js 22.12+ (ou 24/26), pnpm via Corepack e PostgreSQL 18. |

Tenha as portas **9000** e **8000** livres. O Compose publica o PostgreSQL na porta **5548** do computador; na instalação local padrão, ele costuma usar **5432**. É necessário acesso à internet na primeira instalação das dependências e para os serviços externos de CEP e CNPJ usados pelo frontend.

## Configuração comum

Os arquivos `.env` são locais e não são versionados. Crie `api/.env` a partir de `api/.env.example` se ainda não existir:

```bash
cp api/.env.example api/.env
```

No `api/.env`, mantenha `APP_URL=http://localhost:8000`, configure o PostgreSQL conforme o modo escolhido abaixo e, se necessário, defina `CORS_ALLOWED_ORIGINS=http://localhost:9000`. A chave `APP_KEY` será criada com `php artisan key:generate` nos passos seguintes.

Crie `app/.env` com este conteúdo (ou confira os valores, caso já exista):

```dotenv
VITE_APP_ENV=dev
VITE_API_URL=http://localhost:8000/api/v1
VITE_API_CNPJ=https://open.cnpja.com/office/
VITE_API_CEP=https://brasilapi.com.br/api/cep/v2
```

`VITE_API_URL` é acessada pelo **navegador**, por isso deve apontar para `localhost:8000` nos dois modos. O proxy `/api` em `app/quasar.config.ts` aponta para `backend:3000`, enquanto a API deste Compose escuta na porta `8000`; a URL direta acima evita depender desse proxy.

## Com Docker

No `api/.env`, configure a conexão com o serviço PostgreSQL do Compose:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ecoradar_db
DB_USERNAME=ecoradar
DB_PASSWORD=masterkey
```

Esses valores correspondem aos definidos em `compose.yaml` e são somente para desenvolvimento local. Para iniciar pela primeira vez:

```bash
docker compose build
docker compose up -d postgres
docker compose run --rm --no-deps backend composer install
docker compose run --rm --no-deps frontend pnpm install --frozen-lockfile
docker compose run --rm --no-deps backend php artisan key:generate
docker compose run --rm --no-deps backend php artisan migrate
docker compose run --rm --no-deps backend php artisan db:seed
docker compose run --rm --no-deps backend php artisan db:seed --class=CitySeeder
docker compose up -d backend frontend
```

Os comandos de instalação dentro dos contêineres são necessários porque o Compose monta volumes próprios em `api/vendor` e `app/node_modules`. Espere o banco ficar saudável antes das migrações (`docker compose ps`). Depois da primeira instalação, inicie com `docker compose up -d` e pare com `docker compose down`. Os dados do banco continuam no volume do Compose.

## Sem Docker

Inicie o PostgreSQL local e crie o usuário e o banco. Em uma sessão `psql` conectada como administrador (por exemplo, `psql -U postgres`), execute:

```sql
CREATE USER ecoradar WITH PASSWORD 'masterkey';
CREATE DATABASE ecoradar_db OWNER ecoradar;
```

No `api/.env`, configure a conexão local:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecoradar_db
DB_USERNAME=ecoradar
DB_PASSWORD=masterkey
```

Instale as dependências, prepare o banco e rode a API:

```bash
cd api
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan db:seed --class=CitySeeder
php artisan serve --host=0.0.0.0 --port=8000
```

Em outro terminal, a partir da raiz do repositório, rode o frontend:

```bash
cd app
corepack enable
pnpm install --frozen-lockfile
pnpm dev
```

O `pnpm dev` usa a porta 9000 configurada em `app/quasar.config.ts`. Em computadores onde o Corepack não estiver disponível, instale o pnpm compatível com a versão do Node e use os mesmos comandos `pnpm`.

## Conferência e problemas comuns

- Abra `http://localhost:9000` no navegador. `http://localhost:8000/up` deve responder quando a API estiver no ar. A rota `http://localhost:8000/api/v1/general/cities` permite verificar a API e a carga de municípios.
- `php artisan db:seed` carrega as categorias de resíduos. O cadastro de municípios é separado: `php artisan db:seed --class=CitySeeder`. Ambos podem ser executados novamente.
- Se a API não conectar ao banco, confira se o PostgreSQL está pronto e se `DB_HOST`/`DB_PORT` correspondem ao modo usado: `postgres:5432` no Compose, `127.0.0.1:5432` na instalação local. A porta `5548` serve para acessar **do computador** o banco criado pelo Compose.
- Se alterar `api/.env` depois de iniciar a API, execute `php artisan config:clear` (ou `docker compose exec backend php artisan config:clear`) e reinicie o servidor. Alterações em `app/.env` exigem reiniciar o frontend.
- Para consultar os logs dos contêineres: `docker compose logs -f backend frontend postgres`.
