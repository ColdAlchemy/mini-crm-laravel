# Balcão — mini CRM para o comércio (Laravel)

CRM enxuto para o dono de um pequeno comércio: quem são os clientes, o que foi combinado com cada um, quanto há no funil de vendas e **quem preciso chamar hoje**.

> Desenvolvido por mim com o auxílio do Claude Code. Eu defini o escopo, revisei e estudei o código. Todos os dados de exemplo são fictícios.

## O que ele faz
| Tela | O que tem |
|---|---|
| **Painel** | Saudação do dia, indicadores (clientes ativos, negócios em aberto, ganho no mês, taxa de fechamento), retornos de hoje e atrasados, clientes esquecidos há mais de 30 dias, funil em aberto e vendas fechadas por mês (gráficos em HTML/CSS, com texto equivalente) |
| **Clientes** | Lista com busca (nome, e-mail, telefone, empresa), filtros por situação e etiqueta, ordenação por "mais tempo sem contato", botão **Chamar no WhatsApp**, exportação e importação em CSV |
| **Perfil do cliente** | Registro de contatos (ligação, e-mail, reunião, WhatsApp, nota), **linha do tempo** (contatos, negócios abertos/ganhos/perdidos, retornos concluídos), retornos agendados, negócios e etiquetas |
| **Funil** | Quadro kanban (Novo → Proposta → Negociação → Ganho / Perdido) com **arrastar e soltar** e um seletor "Mover para" para quem usa teclado; total por coluna; valor aceito como "1.500,50" |
| **Retornos** | Tarefas por cliente, separadas em atrasados, hoje, próximos e concluídos; selo no menu com o que vence hoje |
| **Etiquetas** | Grupos coloridos de clientes (Atacado, VIP…) para marcar e filtrar |
| **Importar planilha** | CSV do Excel (UTF-8 ou Windows-1252, `,` ou `;`); atualiza pelo e-mail em vez de duplicar; relatório com as linhas ignoradas e o motivo; planilha de exemplo para baixar |
| **Entrar / Criar conta** | Autenticação por sessão do Laravel, com limite de tentativas |

O botão do WhatsApp usa o endereço `wa.me` com o número normalizado (aceita com ou sem o 55 e só números com DDD válidos) e uma mensagem inicial. O sistema **não envia** mensagem por conta própria.

## Stack
PHP 8.4 · Laravel 13 · Eloquent · Blade · JavaScript puro (arrastar e soltar) · SQLite · PHPUnit

## Design
Verde-petróleo e coral, sans arredondada (**Nunito**, servida pelo próprio projeto), cartões coloridos e bastante contraste. Menu lateral no computador e barra de navegação inferior no celular. Respeita `prefers-reduced-motion`, tem foco visível, link para pular ao conteúdo e confirmação antes de qualquer exclusão.

## Como rodar
```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite      # no Windows: type nul > database\database.sqlite
php artisan migrate --seed          # 12 clientes fictícios com contatos, negócios, retornos e etiquetas
php artisan serve
```
Acesse http://localhost:8000 e entre com a conta de demonstração: `demo@balcao.test` / `demonstracao`. Precisa das extensões `pdo_sqlite` e `sqlite3` do PHP. O fuso padrão é `America/Sao_Paulo` (`APP_TIMEZONE`).

## Testes
```bash
php artisan test
```
54 testes: clientes (busca, filtros, ordenação, cascata, exportação segura contra fórmulas de planilha), WhatsApp, linha do tempo, funil (etapas, data de fechamento, JSON do arrastar e soltar, valores em formato brasileiro), painel (indicadores, funil, meses, retornos, clientes esquecidos), retornos, etiquetas, importação de CSV (separadores, codificações, duplicados, linhas inválidas) e autenticação.

## Estrutura
- `app/Models` — `Cliente` (linha do tempo, link do WhatsApp), `Negocio` (`moverPara`), `Interacao`, `Tarefa`, `Etiqueta`
- `app/Services` — `Painel` (números do painel) e `ImportadorCsv`
- `app/Support/Formato.php` — moeda e leitura de valores no padrão brasileiro
- `app/Http/Controllers` — controllers enxutos, com validação e mensagens em português
- `resources/views` — layout, painel, clientes, funil, retornos, etiquetas e importação
- `resources/js/app.js` — arrastar e soltar, confirmação de exclusões e avisos para leitores de tela

## Limitações conhecidas
- Todos os usuários logados enxergam os mesmos dados (um balcão, uma loja). Não há papéis nem várias lojas.
- Sem envio automático de WhatsApp ou e-mail: o botão só abre a conversa.
- Sem lembretes por e-mail; os retornos aparecem no painel e no selo do menu.
- Tema claro apenas.
