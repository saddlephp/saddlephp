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

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>Prepare a sela, cowboy, chegou um novo painel administrativo na cidade.</em>
</p>

---

**Saddle** é o framework open-source de painel administrativo para Laravel, construído de forma moderna para **Inertia and
Vue**. Reúna seus modelos Eloquent em painéis de recursos bem acabados, com construtores de formulários e tabelas, papéis e acesso,
plugins e multi-inquilino.

> **Status: v1.0, importação/exportação CSV e extras de multi-inquilino.** O site de marketing fica em **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Instalação

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

O provedor de serviço é descoberto automaticamente. `saddle:install` publica o arquivo de configuração, publica os assets do painel e cria `app/Saddle/` para suas classes de recursos. Visite `/admin` para ver o painel.

## Definir um recurso

Coloque as classes de recursos em `app/Saddle/`. Cada classe estende `SaddlePHP\Resource` e implementa `form()` e `table()`.

```php
<?php

declare(strict_types=1);

namespace App\Saddle;

use App\Models\Horse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;
use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\Date;
use SaddlePHP\Fields\DateTime;
use SaddlePHP\Fields\FileUpload;
use SaddlePHP\Fields\Markdown;
use SaddlePHP\Fields\Number;
use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Forms\Layout\Grid;
use SaddlePHP\Forms\Layout\Section;
use SaddlePHP\Forms\Layout\Tab;
use SaddlePHP\Forms\Layout\Tabs;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\BadgeColumn;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Filters\BooleanFilter;
use SaddlePHP\Tables\Filters\SelectFilter;
use SaddlePHP\Tables\Table;

class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static ?string $icon = 'collection';

    public static array $with = ['rider'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->description('Who this horse is on the ranch.')->schema([
                Grid::make(2)->schema([
                    Text::make('name')->required()->rules('max:120'),
                    Select::make('breed')->options([
                        'quarter' => 'Quarter Horse',
                        'mustang' => 'Mustang',
                        'appaloosa' => 'Appaloosa',
                    ]),
                ]),
                FileUpload::make('photo')->image()->directory('horses')->maxSize(4096),
            ]),
            Tabs::make([
                Tab::make('Care')->schema([
                    Markdown::make('notes')
                        ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
                    DateTime::make('last_vet_visit'),
                    Number::make('age')->integer()->min(0)->max(50),
                    Date::make('foaled_on'),
                ]),
                Tab::make('Assignment')->schema([
                    BelongsTo::make('rider')->searchable(),
                    Toggle::make('is_saddled'),
                ]),
            ]),
        ]);
    }

    public static function actions(): array
    {
        return [
            Action::make('unsaddle')
                ->handle(fn (Horse $horse) => $horse->update(['is_saddled' => false]))
                ->requiresConfirmation('Unsaddle this horse?')
                ->color('accent'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkAction::make('saddle-up')
                ->label('Saddle up')
                ->handle(fn (Collection $horses) => $horses->each->update(['is_saddled' => true])),
            BulkAction::delete(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            BadgeColumn::make('breed')->colors([
                'quarter' => 'accent',
                'mustang' => 'ink',
                'appaloosa' => 'muted',
            ]),
            BooleanColumn::make('is_saddled'),
            TextColumn::make('rider.name')->label('Rider'),
            TextColumn::make('created_at')->date('M j, Y')->sortable(),
        ])->filters([
            SelectFilter::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            BooleanFilter::make('is_saddled'),
        ]);
    }
}
```

Os recursos são descobertos automaticamente ao escanear `app/Saddle/` na inicialização, sem necessidade de registro manual.

> **Chaves de rota reservadas.** O painel possui os segmentos de caminho estáticos `create`, `options` e `actions` sob cada recurso, então um registro cuja chave de rota seja literalmente uma dessas palavras não é acessível por suas URLs de editar/atualizar/excluir. Use uma chave inteira ou um slug diferente para esses registros.

## Layout do formulário

Os campos podem ser agrupados em contêineres de layout. Contêineres podem ser aninhados livremente uns dentro dos outros.

```php
use SaddlePHP\Forms\Layout\Grid;
use SaddlePHP\Forms\Layout\Section;
use SaddlePHP\Forms\Layout\Tab;
use SaddlePHP\Forms\Layout\Tabs;

$form->schema([
    Section::make('Identity')->description('Who this horse is on the ranch.')->schema([
        Grid::make(2)->schema([
            Text::make('name')->required(),
            Select::make('breed')->options([...]),
        ]),
        FileUpload::make('photo')->image()->directory('horses')->maxSize(4096),
    ]),
    Tabs::make([
        Tab::make('Care')->schema([
            Markdown::make('notes'),
            DateTime::make('last_vet_visit'),
        ]),
        Tab::make('Assignment')->schema([
            BelongsTo::make('rider')->searchable(),
            Toggle::make('is_saddled'),
        ]),
    ]),
]);
```

| Contêiner | Descrição |
|---|---|
| `Section` | Um grupo de cartões com rótulo. `description(string)` adiciona um subtítulo. Aceita `schema([...])` de campos e contêineres aninhados. |
| `Grid` | Organiza seus filhos em uma grade CSS. `Grid::make(2)` cria uma grade de duas colunas. Campos dentro de um Grid usam `columnSpan(int)` para ocupar várias colunas. |
| `Tabs` | Envolve um ou mais contêineres `Tab` em uma interface com abas. |
| `Tab` | Um único painel dentro de um grupo `Tabs`. `Tab::make('Label')->schema([...])`. Quando qualquer campo dentro de uma aba falha na validação, a aba mostra um indicador de erro para que os usuários localizem o problema sem alternar para cada painel. |

**Esquemas planos continuam funcionando.** Passar uma lista simples de campos para `$form->schema([...])` sem contêineres é totalmente suportado e produz o mesmo layout de antes. Validação e autorização tratam os campos de forma idêntica, independentemente de estarem dentro de um contêiner ou no nível superior.

## Campos

| Campo | Descrição |
|---|---|
| `Text` | Entrada de texto de uma linha. Modificadores: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Entrada de texto com várias linhas. Modificadores: `rows(int)`. |
| `Select` | Dropdown de opções fixas. Passe um array associativo para `options(['value' => 'Label'])`. |
| `Toggle` | Alternador booleano. Armazena `true`/`false`. |
| `BelongsTo` | Seletor de relacionamento. O argumento é o nome do método de relacionamento Eloquent no modelo (`BelongsTo::make('rider')` lê `$model->rider()` e envia a chave estrangeira). Os rótulos das opções são resolvidos a partir de `titleAttribute('name')`, com fallback para o recurso registrado `$title` do modelo relacionado e depois para sua chave. Se nem `titleAttribute()` nem um recurso registrado estiver disponível, as opções são rotuladas pela chave primária, então defina `titleAttribute('name')` para rótulos legíveis. As opções são limitadas a 100 por padrão; sobrescreva com `limit(int)`. `searchable()` muda para um seletor assíncrono que pesquisa a tabela relacionada enquanto você digita por meio de um endpoint autenticado. Na edição, apenas a seleção atual é incorporada, a lista completa não é carregada. `modifyOptionsQuery(fn ($query) => ...)` limita a lista de opções por multi-inquilino ou visibilidade. Aplica-se às opções listadas e pesquisadas, enquanto a seleção salva de um registro continua renderizando seu rótulo mesmo quando fica fora do escopo. |
| `Number` | Entrada numérica. Modificadores: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Entrada de data. Os valores são renderizados como `Y-m-d`. |
| `DateTime` | Entrada de data e hora (`datetime-local`). Os valores são armazenados e lidos de volta pelo cast datetime do seu modelo. Um valor `DateTimeInterface` é formatado como `Y-m-d\TH:i` para o navegador. |
| `Markdown` | Textarea com barra de formatação. Armazenado como string simples e limitado a 65 535 caracteres, equivalente a uma coluna MySQL `TEXT`. |
| `FileUpload` | Upload de arquivo multipart. Modificadores: `disk(string)`, `directory(string)`, `image()` (restringe a tipos de imagem), `acceptedTypes(array)` (extensões MIME, por exemplo `['pdf', 'docx']`), `maxSize(int $kilobytes)`. O valor armazenado é o caminho do arquivo retornado por `Storage::put`. No formulário de edição: deixar a entrada intocada mantém o arquivo existente, limpá-la armazena `null`, e escolher um novo arquivo substitui o caminho. Arquivos substituídos ou limpos não são excluídos do disco automaticamente. |

## Colunas

| Coluna | Descrição |
|---|---|
| `TextColumn` | Renderiza o valor bruto do atributo. Modificadores: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formata atributos DateTime, formato padrão `Y-m-d H:i`). |
| `BadgeColumn` | Renderiza um badge em formato de pílula. Use `colors(['value' => 'token'])` para mapear valores de opções para tokens de cor (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Renderiza uma marca de seleção para valores truthy e um traço para valores falsy. |

**Colunas de relacionamento e eager loading.** Nomes pontuados como `TextColumn::make('rider.name')` leem por meio de um relacionamento carregado. Declare `public static array $with = ['rider']` no recurso para que a consulta do índice carregue o relacionamento antes de renderizar. Colunas de relacionamento ainda não são ordenáveis nem pesquisáveis.

## Filtros

Os filtros são declarados na tabela via `->filters([...])`. No índice, o painel os aplica a partir de parâmetros de query string `filter[name]=value`. Os valores solicitados são validados contra a declaração, então nomes de filtro desconhecidos e valores de opção não declarados são ignorados silenciosamente.

| Filtro | Descrição |
|---|---|
| `SelectFilter` | Dropdown de correspondência exata. `options(['value' => 'Label'])` define tanto as escolhas do dropdown quanto a lista permitida de valores aceitos. |
| `BooleanFilter` | Dropdown Sim/Não sobre uma coluna booleana. |

## Ações

As ações aparecem como botões em cada linha da tabela de índice. Ações em massa aparecem em uma barra de ferramentas quando uma ou mais linhas são selecionadas.

```php
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;

public static function actions(): array
{
    return [
        Action::make('unsaddle')
            ->handle(fn (Horse $horse) => $horse->update(['is_saddled' => false]))
            ->requiresConfirmation('Unsaddle this horse?')
            ->color('accent'),
    ];
}

public static function bulkActions(): array
{
    return [
        BulkAction::make('saddle-up')
            ->label('Saddle up')
            ->handle(fn (Collection $horses) => $horses->each->update(['is_saddled' => true])),
        BulkAction::delete(),
    ];
}
```

| Fluent | Descrição |
|---|---|
| `label(string)` | Rótulo exibido no botão. Quando omitido, o nome é convertido para title case. |
| `color(string)` | Token de cor para o botão: `accent`, `ink` ou `muted`. O padrão é `ink`. |
| `requiresConfirmation(?string)` | Mostra uma caixa de confirmação antes de executar. Passe uma mensagem personalizada ou omita para usar o prompt padrão. |
| `authorize(string)` | Nomeia uma habilidade de política verificada por registro antes de o handler executar. |
| `successMessage(string)` | Mensagem flash mostrada após uma execução bem-sucedida. O padrão é `Done.`. |

Ações fazem POST para um endpoint protegido. Os registros são resolvidos pela mesma consulta base com escopo usada em todos os outros lugares, então multi-inquilino, filtros e escopos de consulta por recurso se aplicam automaticamente. Quando `authorize('ability')` é declarado, a política é verificada por registro antes de o handler executar. Execuções em massa rodam dentro de uma transação de banco de dados e são limitadas a 100 registros por requisição. Se qualquer registro solicitado estiver ausente da busca com escopo, a operação inteira aborta com 404 em vez de aplicar silenciosamente ao subconjunto resolvido. Declare `authorize()` em qualquer ação destrutiva.

`BulkAction::delete()` é um preset pronto: nome `delete`, rótulo `Delete`, cor `accent`, confirmação `Delete the selected records?`, e `authorize('delete')` já conectado.

## Autorização

Saddle consome políticas Laravel padrão. Registre uma política para um modelo e o painel a aplica em todos os lugares: índice, formulários, ações de linha e seletores de relacionamento. Sem uma política registrada, todas as habilidades são permitidas para todo usuário autenticado. Papéis ficam na sua aplicação: qualquer pacote de papéis ou camada caseira que sustente suas políticas funciona sem mudanças.

### Travar o painel

Recursos sem uma política registrada permitem todos os usuários autenticados por padrão. Defina `saddle.authorization.require_policy` como `true` para falhar fechado: recursos sem política ficam inacessíveis em vez de abertos. Você também pode adicionar um middleware de gate a `saddle.middleware` para uma verificação geral antes de qualquer rota do painel executar. Se o seu web guard é compartilhado entre usuários finais e administradores, um desses controles é essencial.

| Habilidade | Onde é verificada |
|---|---|
| `viewAny` | Página de índice do recurso, visibilidade da barra lateral |
| `create` | Formulário de criação, ação store, endpoint de opções de relacionamento |
| `update` | Formulário de edição, ação update, link Edit por linha, endpoint de opções de relacionamento (verificado contra um modelo novo quando nenhum registro está no escopo) |
| `delete` | Ação destroy, botão Delete por linha |

### Visibilidade de campo com `canSee`

Campos individuais podem ser controlados por requisição usando `canSee`. O campo `notes` em `HorseResource` é um exemplo funcional:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Campos ocultos são removidos do payload do formulário (valores armazenados nunca são serializados para o frontend), não contribuem regras de validação, nunca são gravados ao salvar, e seu endpoint de opções de relacionamento retorna 404. O callback pode executar várias vezes por requisição, então mantenha-o barato e retorne um booleano real. Por exemplo, use `Gate::allows('view-notes', $model)` em vez de `Gate::inspect(...)`, cujo objeto `Response` é sempre truthy e nunca ocultará o campo.

## Plugins

Um plugin é um pacote Composer comum. Seu provedor de serviço registra recursos, scripts e estilos por meio da facade `Saddle`, e o auto-discovery de pacotes do Laravel o inicializa automaticamente junto com sua aplicação.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Publique assets compilados do provedor de serviço do plugin para `public/vendor/{plugin}` usando o mecanismo padrão `$this->publishes([...])`, depois aponte `Saddle::script()` para o caminho publicado. Scripts e folhas de estilo de plugins são carregados em cada página do painel depois do bundle principal do painel.

### Elementos personalizados

Plugins podem enviar seus próprios renderizadores de campos e colunas como elementos personalizados. No lado PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

O painel cumpre este contrato: para campos, define as propriedades DOM `value` e `field` do elemento e escuta um CustomEvent `saddle:input` cujo `detail` é o novo valor. Para colunas, define as propriedades DOM `value` e `column` (somente leitura, nenhum evento de entrada esperado).

Um elemento personalizado vanilla mínimo implementando o contrato de campo:

```js
class MoodPicker extends HTMLElement {
    connectedCallback() {
        // The panel may set the value property before the element is
        // connected, so seed the input from whatever arrived early.
        this._input = document.createElement('input');
        this._input.value = this._value ?? '';
        this._input.addEventListener('input', () => {
            this.dispatchEvent(new CustomEvent('saddle:input', {
                bubbles: true,
                detail: this._input.value.toUpperCase(),
            }));
        });
        this.appendChild(this._input);
    }

    set value(v) {
        this._value = v ?? '';
        if (this._input) this._input.value = this._value;
    }

    get value() { return this._input ? this._input.value : (this._value ?? ''); }
}

customElements.define('mood-picker', MoodPicker);
```

Defina elementos no nível superior do seu script. O navegador atualiza qualquer elemento correspondente que o painel já tenha renderizado assim que `customElements.define` executa, então a ordem de carregamento nunca importa.

O contrato é agnóstico a framework. Qualquer coisa que compile para um elemento personalizado padrão funciona: `defineCustomElement` do Vue, Lit, wrappers React ou Svelte. Autores de plugins não ficam presos aos internos do painel.

## Multi-inquilino

Saddle oferece suporte opcional a multi-inquilino com escopo por URL. Ative apontando `tenancy.model` para qualquer classe Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Quando multi-inquilino está ativo, o painel é montado em `/admin/{tenant}` em vez de `/admin`. O segmento `{tenant}` é resolvido por lookup de chave de rota no modelo configurado. Chaves de inquilino desconhecidas retornam **404**. Usuários autenticados que não são membros do inquilino resolvido são rejeitados com **403**.

### Escopo de recursos

Declare o relacionamento BelongsTo do registro com o inquilino em cada recurso que você quer escopar:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Recursos sem `$tenant` (tabelas de consulta compartilhadas, configuração global) permanecem sem escopo por design.

### Garantias de escopo automático

Todo caminho de dados verifica o inquilino vinculado no servidor:

- **Índice, pesquisa e filtros** passam pela consulta base com escopo (`whereBelongsTo` no relacionamento declarado).
- **Buscas de registros** para editar, atualizar e destruir resolvem pela mesma consulta com escopo, então IDs de outros inquilinos retornam 404 antes de qualquer política executar.
- **Stores** carimbam o inquilino atual no servidor depois de preencher o formulário. Qualquer chave estrangeira de inquilino enviada pelo cliente é sobrescrita.
- **Listas de opções de relacionamento** aplicam o mesmo escopo quando o recurso registrado do modelo relacionado também tem escopo por inquilino.

### Seletor de inquilino

Quando o usuário autenticado pertence a mais de um inquilino, a barra lateral do painel mostra um select que lista todas as suas associações. Alternar navega para o mesmo caminho do painel sob o inquilino selecionado.

### Cuidados

- **Não exponha o relacionamento `$tenant` como campo de formulário em um recurso com escopo.** O controlador store carimba o relacionamento no servidor, mas um campo BelongsTo editável apontando para o relacionamento de inquilino em um formulário de atualização permitiria que um valor enviado reapontasse o registro para outro inquilino.
- **Um rótulo de relacionamento salvo ainda é renderizado no formulário de edição mesmo quando a linha relacionada fica fora do escopo atual.** `BelongsTo` resolve a seleção atual com uma consulta sem escopo para que o rótulo nunca desapareça após uma mudança de escopo. Apenas a lista de opções para novas seleções é filtrada.
- **Alterar a configuração de multi-inquilino exige `php artisan route:clear`**, porque o prefixo `{tenant}` é decidido na inicialização. Servidores de aplicação de longa duração (workers FPM mantidos vivos entre requisições) devem garantir que o estado da requisição seja redefinido entre requisições. O inquilino vinculado vive no singleton Saddle, que é resolvido novamente por requisição sob o ciclo de vida padrão do contêiner. Quando Octane está instalado, o painel redefine automaticamente o inquilino vinculado por meio dos hooks do ciclo de vida de requisições do Octane.

## Configuração

`saddle:install` publica `config/saddle.php`. Chaves disponíveis:

| Chave | Padrão | Descrição |
|---|---|---|
| `path` | `'admin'` | Prefixo URL do painel (por exemplo `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Pilha de middleware aplicada a todas as rotas do painel. |
| `resources.path` | `app_path('Saddle')` | Caminho do sistema de arquivos escaneado para classes de recursos. |
| `resources.namespace` | `'App\\Saddle'` | Namespace PHP correspondente a `resources.path`. |
| `per_page` | `25` | Linhas padrão por página em tabelas de índice. |
| `brand.name` | `'Saddle'` | Nome do painel (barra lateral e aba do navegador). |
| `brand.accent` | `'#d9501f'` | Cor de destaque (botões, estados ativos). |
| `uploads.disk` | `'public'` | Disco padrão do sistema de arquivos usado por campos `FileUpload` quando nenhum `disk()` por campo é definido. |
| `uploads.directory` | `'saddle'` | Diretório padrão de upload dentro do disco quando nenhum `directory()` por campo é definido. |

## Comandos

| Comando | Descrição |
|---|---|
| `saddle:install` | Publica configuração, publica assets do painel, cria `app/Saddle/`. Oferece adicionar `saddle:upgrade` a `composer post-update-cmd` para manter os assets atualizados. |
| `saddle:upgrade` | Republica os assets do painel. Execute após cada atualização de pacote. |
| `saddle:resource NameResource --model=Name` | Gera o scaffold de uma nova classe de recurso. A opção `--model` é opcional. Ela é inferida do nome do recurso quando omitida. |

**Nota de deploy.** Adicione `php artisan saddle:upgrade` ao seu script de deploy depois de `composer install` ou `composer update`. O painel exibe um banner de aviso na UI quando os assets publicados estão fora de sincronia com a versão instalada do pacote.

## Desenvolvimento local

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

O diretório `workbench/` contém uma aplicação host mínima usada pela suíte de testes e para testes manuais. `vendor/bin/testbench serve` a inicializa com `HorseResource` registrado. Observe que as rotas do painel ficam atrás do middleware `auth` e o workbench inclui apenas uma rota stub `/login`, então para navegação interativa defina temporariamente `'middleware' => ['web']` em `config/saddle.php` ou navegue pelos testes de feature. Ainda não há seeder de demonstração.

## Stack

Construído para **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licença

MIT.
