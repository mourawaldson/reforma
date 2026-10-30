# Reforma

Sistema local para gerenciar despesas da reforma, com API, backoffice, dashboard e banco MySQL.

## Requisitos

- Docker Desktop com Docker Compose v2
- Git, caso ainda precise clonar o repositório

PHP, Composer e MySQL não precisam ser instalados no computador: os serviços rodam nos containers.

## Iniciar do zero

Execute os comandos a partir da pasta raiz do projeto.

1. Crie o arquivo local de configuração a partir do exemplo:

   ```powershell
   Copy-Item .env.example .env
   ```

2. Abra `.env` e substitua os três valores `replace-with-...` por senhas únicas e fortes:

   | Variável | Uso |
   | --- | --- |
   | `APP_AUTH_USERNAME` | Usuário de login do backoffice e da dashboard |
   | `APP_AUTH_PASSWORD` | Senha compartilhada por essas duas telas e usada pela API internamente |
   | `MYSQL_ROOT_PASSWORD` | Senha administrativa do MySQL |
   | `DB_NAME` | Nome do banco criado no primeiro início |
   | `DB_USER` | Usuário que os aplicativos usam para acessar o banco |
   | `DB_PASS` | Senha desse usuário do banco |
   | `HOST_BIND_ADDRESS` | Endereço de rede das portas; mantenha `127.0.0.1` para acesso apenas neste computador |

   `DB_NAME` e `DB_USER` já têm valores adequados no exemplo. `HOST_BIND_ADDRESS` pode ser omitida; o padrão também é `127.0.0.1`. O `.env` da raiz é o único arquivo necessário para configurar o Compose. Não coloque credenciais reais no `.env.example` nem o versione.

3. Construa e inicie os serviços:

   ```powershell
   docker compose up --build -d
   ```

   No primeiro início, o Compose cria o volume do MySQL, executa `sql/schema.sql` e instala as dependências PHP travadas pelos arquivos `composer.lock`. Aguarde a conclusão da instalação antes de abrir as páginas.

4. Abra uma das páginas abaixo e entre com `APP_AUTH_USERNAME` e `APP_AUTH_PASSWORD` do `.env`:

   - Backoffice: <http://localhost:8001>
   - Dashboard: <http://localhost:8002>
   - API: <http://localhost:8000>
   - phpMyAdmin: <http://localhost:8080>

   Para entrar no phpMyAdmin, use `DB_USER` e `DB_PASS`.

O banco começa vazio; cadastre as despesas e demais informações pelo backoffice. A dashboard passa a exibir os dados depois dos cadastros.

## Verificar inicialização e consultar logs

```powershell
docker compose ps
docker compose logs -f
```

Para acompanhar apenas um serviço, por exemplo a API:

```powershell
docker compose logs -f api
```

## Parar, reiniciar e preservar os dados

```powershell
docker compose stop
docker compose start
```

Ou pare e remova os containers sem apagar o banco:

```powershell
docker compose down
```

Os dados ficam no volume Docker `db_data` e continuam lá após `stop`, `start` ou `down`. **Não use `docker compose down --volumes` para uma parada normal**: isso apaga o banco local. Os scripts em `sql/` são executados automaticamente apenas quando o volume é criado pela primeira vez; iniciar de novo não apaga nem recria um banco existente.

Para reconstruir as imagens depois de alterações no Dockerfile ou nas dependências:

```powershell
docker compose up --build -d
```

## Configuração do ambiente

O Docker Compose lê `.env` na raiz e entrega aos containers as configurações necessárias. `DB_HOST` é definido internamente como `db`; não é necessário adicioná-lo ao `.env` da raiz. Os aplicativos aceitam que não exista um `.env` próprio dentro de cada pasta, o que permite iniciar um clone limpo usando apenas a configuração da raiz.

As portas são vinculadas a `127.0.0.1` por padrão. Para disponibilizar o serviço na rede local, altere `HOST_BIND_ADDRESS` conscientemente; não exponha as portas diretamente à internet sem configurar HTTPS e controles de acesso apropriados.
