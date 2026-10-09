# Mini CRM (Laravel)

CRM enxuto para pequenos negócios: cadastro de clientes, histórico de interações e funil de vendas em quadro kanban.

> Projeto de portfólio de **Albert Ryan**, desenvolvido com apoio de IA (Claude Code). A definição do escopo e a revisão são minhas; o código foi gerado e testado com o Claude.

## Funcionalidades
- **Clientes:** CRUD completo, busca por nome/e-mail/empresa, filtro por status (lead, ativo, inativo), validação e e-mail único.
- **Interações:** registro de ligações, e-mails, reuniões, WhatsApp e notas por cliente, com histórico cronológico.
- **Negócios:** funil em kanban (Novo → Proposta → Negociação → Ganho/Perdido) com total por etapa.
- Interface em PT-BR, responsiva, com tema claro/escuro automático.

## Stack
PHP 8.4 · Laravel 13 · Eloquent · Blade · SQLite · PHPUnit

## Como rodar
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # no Windows: type nul > database\database.sqlite
php artisan migrate
php artisan serve
```
Acesse http://localhost:8000. É preciso ter as extensões `pdo_sqlite` e `sqlite3` habilitadas no PHP.

## Testes
```bash
php artisan test
```
Cobrem CRUD de clientes, validações, interações, criação de negócios, mudança de etapa e exclusão em cascata.

## Estrutura
- `app/Models` — `Cliente`, `Negocio`, `Interacao` e seus relacionamentos
- `app/Http/Controllers` — controllers enxutos com validação na própria requisição
- `resources/views` — layout Blade e telas de clientes e negócios
- `tests/Feature` — testes de ponta a ponta das rotas
