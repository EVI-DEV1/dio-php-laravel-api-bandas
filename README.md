# API de bandas - PHP Experience / DIO

Implementação própria do desafio **Laravel: Construindo uma API do Zero**, a partir da referência funcional [danbonattis/dio-laravel-api](https://github.com/danbonattis/dio-laravel-api). O código de referência usa bandas musicais, consultas por identificador e gênero e cadastro com validação.

Esta versão mantém as quatro rotas originais, usa Laravel 13 e acrescenta SQLite, fila persistente e testes HTTP. `gender` representa o gênero musical e mantém o nome de campo empregado na aula.

## Requisitos

- PHP 8.3 ou superior e Composer 2.
- Extensões PDO/SQLite, mbstring, OpenSSL, cURL, XML e fileinfo.
- Não exige Node.js, MySQL ou servidor externo.

## Executar

```sh
composer install
```

Copie `.env.example` para `.env`: `Copy-Item .env.example .env` no PowerShell ou `cp .env.example .env` no Linux/macOS. Depois:

```sh
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

O seed cria cinco bandas e pode ser reexecutado sem duplicá-las. SQLite e fila `database` são as configurações padrão. Em outro terminal, processe os eventos:

```sh
php artisan queue:work --tries=3
```

## Rotas

| Método | Endereço | Resultado |
|---|---|---|
| GET | `/api/bands` | Lista ordenada por ID |
| GET | `/api/bands/3` | Banda por ID ou HTTP 404 |
| GET | `/api/bands/gender/heavy%20metal` | Filtro por gênero |
| POST | `/api/bands/store` | Cadastro, HTTP 201 e cabeçalho Location |

```sh
curl -H "Accept: application/json" http://127.0.0.1:8000/api/bands
```

Corpo de um cadastro, por exemplo no Postman:

```json
{"name":"Angra","gender":"power metal"}
```

Use `Content-Type: application/json` e `Accept: application/json`. O nome deve ter entre 3 e 120 caracteres; gênero entre 3 e 80. Campos adicionais não são gravados e o ID é gerado pelo banco. Espaços externos são removidos e o gênero é normalizado para minúsculas. Entradas inválidas retornam HTTP 422 com erros por campo.

As respostas expõem somente `id`, `name` e `gender`. Gênero inexistente retorna lista vazia. As rotas aceitam até 60 requisições por minuto por cliente.

## Fila

Um cadastro válido envia `RecordBandCreated` à fila. O worker registra o evento em `band_creation_logs`. Um índice único por banda torna a operação idempotente se o job for repetido. A banda é persistida imediatamente e o registro do evento ocorre de forma assíncrona. Seeds não enviam eventos.

## Testes

```sh
php artisan test --compact
```

Os testes usam SQLite em memória e cobrem listagem, busca, 404, filtros, cadastro, validação de tipos e limites, proteção contra campos extras, persistência, envio de jobs, execução real do worker de banco, repetição idempotente e seed sem duplicação.

## Decisões

- O original em Laravel 5.8 é a referência funcional; as dependências antigas não foram copiadas.
- O projeto é um catálogo didático com cadastro público, conforme a aula. Não inclui autenticação nem edição/exclusão.
- Aplicação em `app/`, rotas em `routes/api.php`, banco em `database/migrations/`, testes em `tests/Feature/BandApiTest.php`.
- `.env`, bancos SQLite, logs e `vendor` não são versionados.
- Para produção, configure ambiente e controles de acesso adequados ao uso.

Licença MIT. A base [Laravel](https://laravel.com) e a referência DIO mantêm seus respectivos créditos.

