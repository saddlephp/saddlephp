<p align="center">
  <img src="../../.github/og.png" alt="Saddle, there's a new admin panel in town" width="820">
</p>

<h3 align="center">O framework open source de painéis administrativos para Laravel, feito para Inertia e Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Documentação</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Changelog</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Como contribuir</b></a>
</p>

<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko.md">한국어</a> •
  <a href="README.nl.md">Nederlands</a> •
  <a href="README.pl.md">Polski</a> •
  <b>Português (BR)</b> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

## O que é o Saddle?

O Saddle transforma seus models Eloquent em um painel administrativo caprichado. Você descreve cada model uma única vez, como uma classe de resource em PHP com um formulário e uma tabela, e o Saddle monta as telas de listagem, criação, edição e visualização, além da busca, dos filtros e das ações. A renderização é feita com Inertia e Vue, o login e as policies que você já tem no Laravel continuam valendo, e o resto da sua aplicação fica intocado.

<table>
<tr>
<td width="50%" valign="middle">

### Tabelas que trabalham por você

Colunas ordenáveis e pesquisáveis com badges e booleanos, filtros, ações por linha e ações em massa com confirmação. Exporte a visualização atual para CSV ou importe um CSV passando pela mesma validação do seu formulário.

[Tabelas e colunas →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Uma tabela de resource do Saddle com filtros e uma barra de ações em massa">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Um formulário de edição do Saddle com uma seção, uma grade de duas colunas e abas">
</picture>
</td>
<td width="50%" valign="middle">

### Formulários com layout de verdade

Onze tipos de campo, de texto e selects a seletores de relação pesquisáveis, upload de arquivos e Markdown. Organize tudo em seções, grades e abas. Validação, visibilidade e salvamento seguem as mesmas definições de campo.

[Campos →](../sections/fields.md) · [Layout de formulários →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Uma classe por model

Um resource é PHP puro. Coloque-o em `app/Saddle/` e ele é descoberto na inicialização: nada de rotas, controllers ou arquivos Vue para escrever. O acesso segue as suas policies do Laravel e é fechado por padrão.

[Resources →](../sections/resources.md) · [Autorização →](../sections/authorization.md)

</td>
<td width="50%">

```php
class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
            BelongsTo::make('rider')->searchable(),
            Toggle::make('is_saddled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            BooleanColumn::make('is_saddled'),
        ]);
    }
}
```

</td>
</tr>
</table>

## Recursos

<table>
<tr>
<td width="33%" valign="top">

**Resources**
- Classes de resource descobertas automaticamente
- Páginas de listagem, criação, edição e visualização somente leitura
- Gerador `saddle:resource`
- Eager loading e colunas de relação

</td>
<td width="33%" valign="top">

**Formulários**
- Texto, senha, textarea, número, select, toggle
- Data, data e hora, Markdown, upload de arquivos
- Seletor `BelongsTo` pesquisável
- Seções, grades e abas

</td>
<td width="33%" valign="top">

**Tabelas**
- Colunas de texto, badge e booleano
- Ordenação, busca e `formatUsing`
- Filtros de seleção e booleanos
- Paginação

</td>
</tr>
<tr>
<td valign="top">

**Ações**
- Ações por linha e ações em massa
- Diálogos de confirmação
- Verificação de policy por registro
- Execuções em massa dentro de transação

</td>
<td valign="top">

**Relações**
- Gerenciadores de relação `HasMany`
- Crie, edite e exclua registros relacionados ali mesmo
- Escopo limitado ao registro pai

</td>
<td valign="top">

**Dashboard**
- Widgets de estatísticas com minigráficos
- Widgets de gráfico
- Notificações em banco de dados com sininho
- Busca global entre resources

</td>
</tr>
<tr>
<td valign="top">

**Acesso**
- Policies padrão do Laravel
- Fechado por padrão
- Visibilidade por campo com `canSee`
- Usa o login que você já tem

</td>
<td valign="top">

**Multi-tenancy**
- Tenants delimitados pela URL (`/admin/{tenant}`)
- Escopo automático de consultas e marcação do tenant
- Troca de tenant e autocadastro
- Hook de controle de acesso para cobrança

</td>
<td valign="top">

**Dados**
- Exportação em CSV da visualização atual
- Importação de CSV com a validação do formulário
- Soft deletes com restauração e exclusão definitiva

</td>
</tr>
<tr>
<td valign="top">

**Visual e idioma**
- Temas claro, escuro e do sistema
- Nome da marca, cor de destaque e tokens de tema
- Painel traduzido para 14 idiomas
- Suporte a nonce de CSP

</td>
<td valign="top">

**Plugins**
- Pacotes Composer comuns
- Campos e colunas personalizados como web components
- Hooks para props compartilhadas, navegação e tokens de tema

</td>
<td valign="top">

**Experiência de desenvolvimento**
- Helpers de teste para Pest e PHPUnit
- `saddle:install` e `saddle:upgrade`
- Aviso de assets desatualizados após upgrades
- Laravel 11, 12 e 13

</td>
</tr>
</table>

## Construído com

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Início rápido

Requer PHP 8.4+ e Laravel 11.48+, 12 ou 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Entre pelo login da sua aplicação, registre uma policy para o model e acesse `/admin`. Rode `php artisan saddle:upgrade` depois de cada atualização do pacote. O [guia de primeiros passos](../sections/getting-started.md) explica cada etapa.

## Saiba mais

| | |
|---|---|
| **Construindo** | [Resources](../sections/resources.md) · [Campos](../sections/fields.md) · [Layout de formulários](../sections/forms-layout.md) · [Tabelas](../sections/tables.md) · [Filtros](../sections/filters.md) · [Ações](../sections/actions.md) · [Gerenciadores de relação](../sections/relations.md) |
| **Painel** | [Widgets do dashboard](../sections/widgets.md) · [Busca global](../sections/search.md) · [Notificações](../sections/notifications.md) · [Soft deletes](../sections/soft-deletes.md) · [Importação e exportação](../sections/import-export.md) |
| **Em produção** | [Autorização](../sections/authorization.md) · [Multi-tenancy](../sections/tenancy.md) · [Extras de tenancy](../sections/tenancy-extras.md) · [Configuração](../sections/configuration.md) · [Temas](../sections/theming.md) · [Localização](../sections/i18n.md) |
| **Estendendo** | [Plugins](../sections/plugins.md) · [Helpers de teste](../sections/testing.md) · [Como contribuir](../../CONTRIBUTING.md) · [Segurança](../../SECURITY.md) |

## Licença

O Saddle é um software open source licenciado sob a [licença MIT](../../LICENSE).
