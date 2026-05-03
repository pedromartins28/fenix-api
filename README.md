# Fenix Challenge

Aplicação para gerenciamento e execução de provas.

O projeto possui:

- API Laravel com PostgreSQL e Redis.
- Front-end Quasar/Vue.
- Documentação OpenAPI/Swagger.
- Testes automatizados com cobertura mínima configurada.

## Requisitos

- Docker
- Docker Compose

## Subindo o projeto

1. Copie o arquivo de variáveis da API:

```bash
cp api/.env.example api/.env
```

2. Confira se o arquivo `api/.env` está com os valores do Docker:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=fenix
DB_USERNAME=admin
DB_PASSWORD=fenix

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

3. Suba os containers:

```bash
docker compose up -d --build
```

4. Instale as dependências da API:

```bash
docker compose exec api composer install
```

5. Gere a chave da aplicação:

```bash
docker compose exec api php artisan key:generate
```

6. Rode migrations e seeders:

```bash
docker compose exec api php artisan migrate --seed
```

## URLs

- Front-end: http://localhost:9000
- API: http://localhost:8000/api
- Swagger: http://localhost:8000/docs
- OpenAPI YAML: http://localhost:8000/openapi.yaml

## Dados iniciais

O seeder cria:

- Turmas: `CLASS-A` e `CLASS-B`
- Professor:
  - email: `teacher@gmail.com`
  - senha: `teacher123`
- Alunos:
  - `student@gmail.com`
  - `student2@gmail.com`
  - `student3@gmail.com`
  - senha: `student123`

Observação: não há fluxo de login. O front usa IDs fixos para simular os acessos.

## Testes

Rodar todos os testes da API:

```bash
docker compose exec api php artisan test
```

Rodar cobertura com mínimo de 80%:

```bash
docker compose exec api composer test:coverage
```

## Documentação da API

A documentação Swagger está disponível em:

```text
http://localhost:8000/docs
```

O contrato OpenAPI fica versionado em:

```text
api/public/openapi.yaml
```

## Arquitetura

A API foi organizada em camadas:

- `Controllers`: recebem requests HTTP e retornam responses.
- `Requests`: validam payloads de entrada.
- `Services`: concentram regras de negócio.
- `Models`: representam entidades e relacionamentos.
- `Migrations`: definem schema, chaves estrangeiras, uniques e restrições.
- `Tests`: cobrem regras de negócio e endpoints principais.

Essa separação evita controllers inchados e facilita manutenção/testes.

## Principais regras de negócio

- Professor pode criar, editar, listar e excluir provas.
- Prova possui questões e alternativas.
- Cada questão deve ter exatamente uma alternativa correta.
- A quantidade de questões deve bater com `questions_count`.
- Prova pode ser vinculada/desvinculada de turmas.
- Prova com tentativa de aluno não pode ser editada/excluída.
- Prova já tentada por aluno de uma turma não pode ser desvinculada daquela turma.
- Aluno lista apenas provas disponíveis para sua turma.
- Aluno só vê questões após iniciar tentativa.
- Aluno só pode iniciar uma tentativa por prova.
- Ao finalizar, o sistema calcula nota e quantidade de acertos.
- Dashboard exibe média, maior nota e classificação paginada.
- Dashboard usa cache Redis e invalida ao finalizar tentativa.
