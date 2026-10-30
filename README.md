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
   | `SESSION_COOKIE_SECURE` | Use `true` quando o acesso ao backoffice/dashboard passar por HTTPS |

   `DB_NAME` e `DB_USER` já têm valores adequados no exemplo. `HOST_BIND_ADDRESS` e `SESSION_COOKIE_SECURE` podem ser omitidas; seus padrões são `127.0.0.1` e `false`. O `.env` da raiz é o único arquivo necessário para configurar o Compose. Não coloque credenciais reais no `.env.example` nem o versione.

3. Inicie o MySQL e aguarde a verificação de saúde:

   ```powershell
   docker compose up -d db
   docker compose ps
   ```

4. Aplique as migrações pendentes pelo Bash do Git:

   ```powershell
   & 'C:\Program Files\Git\bin\bash.exe' ./db/migrate.sh
   ```

   Em seguida, construa e inicie os aplicativos:

   ```powershell
   docker compose up --build -d
   ```

   Em um volume novo, o MySQL cria a estrutura inicial com `sql/schema.sql`; as migrações registram a versão e aplicam alterações pendentes. As dependências PHP são instaladas a partir dos arquivos `composer.lock`. O Compose aguarda o MySQL e a API ficarem saudáveis antes de iniciar os serviços dependentes.

5. Abra uma das páginas abaixo e entre com `APP_AUTH_USERNAME` e `APP_AUTH_PASSWORD` do `.env`:

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

Os dados ficam no volume Docker `db_data` e continuam lá após `stop`, `start` ou `down`. **Não use `docker compose down --volumes` para uma parada normal**: isso apaga o banco local. `sql/schema.sql` só é executado automaticamente quando o volume é criado pela primeira vez; as migrações em `db/migrations/` atualizam bancos existentes.

## Atualizar o schema do banco

Não gere mais `sql/schema.sql` a partir do banco em uso. Ele é a estrutura inicial para instalações novas. Para cada alteração posterior, adicione um arquivo SQL numerado em `db/migrations/` e aplique as migrações:

```powershell
& 'C:\Program Files\Git\bin\bash.exe' ./db/migrate.sh
```

O migrador registra cada arquivo aplicado na tabela `schema_migrations` e ignora versões já concluídas. Escreva migrações de forma idempotente sempre que possível; se uma migração falhar parcialmente, corrija o estado do banco antes de tentar novamente. `db/update_schema.sh` permanece como atalho compatível para o migrador.

## Fazer backup do banco

Com o Docker Desktop aberto e o serviço `db` em execução, rode na raiz do projeto:

```powershell
& 'C:\Program Files\Git\bin\bash.exe' ./db/backup.sh
```

Também é possível abrir o Git Bash na pasta do projeto e executar `bash db/backup.sh`. O script usa o banco e a senha configurados no container, sem credenciais fixas no arquivo, e grava um dump SQL completo com data e hora em `db/backups/`. Ele informa erro se o banco não estiver ativo ou se o dump falhar. A pasta de backups é ignorada pelo Git.

Para restaurar, use um arquivo SQL válido. A restauração substitui os dados atuais, cria um backup do estado atual antes de prosseguir e exige confirmação digitando `RESTAURAR`:

```powershell
& 'C:\Program Files\Git\bin\bash.exe' ./db/restore.sh ./db/backups/SEU_ARQUIVO.sql
```

Troque `SEU_ARQUIVO.sql` pelo nome do backup. Antes de depender de um backup, teste a restauração em um banco descartável, nunca sobre os dados de produção.

O projeto inclui um verificador que restaura o arquivo em um banco temporário, compara as quantidades de registros e remove esse banco ao terminar:

```powershell
& 'C:\Program Files\Git\bin\bash.exe' ./db/test_restore.sh ./db/backups/SEU_ARQUIVO.sql
```

Para reconstruir as imagens depois de alterações no Dockerfile ou nas dependências:

```powershell
docker compose up --build -d
```

## Configuração do ambiente

O Docker Compose lê `.env` na raiz e entrega aos containers as configurações necessárias. `DB_HOST` é definido internamente como `db`; não é necessário adicioná-lo ao `.env` da raiz. Os aplicativos aceitam que não exista um `.env` próprio dentro de cada pasta, o que permite iniciar um clone limpo usando apenas a configuração da raiz.

As portas são vinculadas a `127.0.0.1` por padrão. Para disponibilizar o serviço na rede local, altere `HOST_BIND_ADDRESS` conscientemente; não exponha as portas diretamente à internet sem configurar HTTPS e controles de acesso apropriados.

O login limita a cinco falhas por endereço remoto em 15 minutos. Em hospedagem com HTTPS terminado por proxy, configure `SESSION_COOKIE_SECURE=true`. Configure o Apache para obter o IP real somente de proxies confiáveis; a aplicação não confia diretamente em `X-Forwarded-For`. Mantenha as portas dos containers acessíveis somente pelo proxy e não publique diretamente a API, MySQL ou phpMyAdmin.

## Verificações automatizadas

O workflow em `.github/workflows/ci.yml` valida a configuração do Compose e os scripts, sobe um MySQL descartável, aplica as migrações duas vezes, gera e restaura um backup temporário, verifica a sintaxe PHP e executa os testes da API em cada push e pull request.
