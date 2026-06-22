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
  <a href="README.pt-BR.md">Português (BR)</a> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <b>简体中文</b>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>上鞍吧，牛仔，镇上来了一个新的管理面板。</em>
</p>

---

**Saddle** 是面向 Laravel 的开源管理面板框架，以现代方式为 **Inertia and
Vue** 构建。它把你的 Eloquent 模型汇聚成打磨完善的资源面板，配备表单和表格构建器、角色与访问控制、
插件以及多租户。

> **状态: v1.0，CSV 导入/导出和租户增强功能。** 营销网站位于 **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com))。

## 安装

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

服务提供者会自动发现。`saddle:install` 会发布配置文件，发布面板资源，并为你的资源类创建 `app/Saddle/`。访问 `/admin` 查看面板。

## 定义资源

将资源类放在 `app/Saddle/` 中。每个类都扩展 `SaddlePHP\Resource`，并实现 `form()` 和 `table()`。

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

资源会在启动时通过扫描 `app/Saddle/` 自动发现，无需手动注册。

> **保留路由键。** 面板拥有每个资源下的静态路径段 `create`、`options` 和 `actions`，因此如果某条记录的路由键字面上就是这些词之一，就无法通过它的编辑/更新/删除 URL 访问。此类记录请使用整数键或不同的 slug。

## 表单布局

字段可以分组到布局容器中。容器可以自由相互嵌套。

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

| 容器 | 描述 |
|---|---|
| `Section` | 带标签的卡片组。`description(string)` 添加副标题。接受包含字段和嵌套容器的 `schema([...])`。 |
| `Grid` | 将子项排列在 CSS 网格中。`Grid::make(2)` 创建两列网格。Grid 内的字段使用 `columnSpan(int)` 跨越多列。 |
| `Tabs` | 将一个或多个 `Tab` 容器包装在选项卡界面中。 |
| `Tab` | `Tabs` 组内的单个面板。`Tab::make('Label')->schema([...])`。当某个选项卡内的任何字段验证失败时，该选项卡会显示错误指示器，让用户无需切换到每个面板也能定位问题。 |

**扁平 schema 仍然可用。** 将普通字段列表传给 `$form->schema([...])` 而不使用容器是完全支持的，并会生成与以前相同的布局。无论字段位于容器内还是顶层，验证和授权都会以相同方式处理它们。

## 字段

| 字段 | 描述 |
|---|---|
| `Text` | 单行文本输入。修饰符: `required()`, `rules(string\|array)`, `placeholder()`。 |
| `Textarea` | 多行文本输入。修饰符: `rows(int)`。 |
| `Select` | 固定选项下拉框。将关联数组传给 `options(['value' => 'Label'])`。 |
| `Toggle` | 布尔开关。存储 `true`/`false`。 |
| `BelongsTo` | 关系选择器。参数是模型上的 Eloquent 关系方法名 (`BelongsTo::make('rider')` 会读取 `$model->rider()` 并提交外键)。选项标签从 `titleAttribute('name')` 解析，回退到关联模型注册资源的 `$title`，再回退到它的键。如果既没有 `titleAttribute()` 也没有注册资源，选项会按主键标记，因此请设置 `titleAttribute('name')` 以获得可读标签。选项默认最多 100 个，可用 `limit(int)` 覆盖。`searchable()` 切换到异步选择器，通过已认证的 endpoint 在你输入时搜索关联表。编辑时只嵌入当前选择，不加载完整列表。`modifyOptionsQuery(fn ($query) => ...)` 为多租户或可见性限定选项列表范围。它适用于列出的和搜索到的选项，而记录保存的选择即使落在范围外也会继续渲染其标签。 |
| `Number` | 数字输入。修饰符: `min()`, `max()`, `step()`, `integer()`。 |
| `Date` | 日期输入。值渲染为 `Y-m-d`。 |
| `DateTime` | 日期和时间输入 (`datetime-local`)。值通过模型的 datetime cast 存储并读回。`DateTimeInterface` 值会为浏览器格式化为 `Y-m-d\TH:i`。 |
| `Markdown` | 带格式工具栏的 Textarea。以普通字符串存储，限制为 65 535 个字符，相当于 MySQL `TEXT` 列。 |
| `FileUpload` | Multipart 文件上传。修饰符: `disk(string)`, `directory(string)`, `image()` (限制为图片类型), `acceptedTypes(array)` (MIME 扩展名，例如 `['pdf', 'docx']`), `maxSize(int $kilobytes)`。存储值是 `Storage::put` 返回的文件路径。在编辑表单中: 不触碰输入会保留现有文件，清空会存储 `null`，选择新文件会替换路径。被替换或清空的文件不会自动从磁盘删除。 |

## 列

| 列 | 描述 |
|---|---|
| `TextColumn` | 渲染原始属性值。修饰符: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (格式化 DateTime 属性，默认格式 `Y-m-d H:i`)。 |
| `BadgeColumn` | 渲染胶囊徽章。使用 `colors(['value' => 'token'])` 将选项值映射到颜色令牌 (`accent`, `ink`, `muted`)。 |
| `BooleanColumn` | 对 truthy 值渲染对勾，对 falsy 值渲染短横线。 |

**关系列与 eager loading。** 像 `TextColumn::make('rider.name')` 这样的点号名称会通过已加载的关系读取。在资源上声明 `public static array $with = ['rider']`，让索引查询在渲染前 eager-load 该关系。关系列目前还不能排序或搜索。

## 过滤器

过滤器通过表上的 `->filters([...])` 声明。在索引页，面板会从 `filter[name]=value` query string 参数应用它们。请求值会根据声明验证，因此未知过滤器名称和未声明的选项值会被静默忽略。

| 过滤器 | 描述 |
|---|---|
| `SelectFilter` | 精确匹配下拉框。`options(['value' => 'Label'])` 同时定义下拉选择和可接受值的 allowlist。 |
| `BooleanFilter` | 布尔列上的是/否下拉框。 |

## 操作

操作以按钮形式显示在索引表的每一行上。当选择一行或多行时，批量操作会显示在工具栏中。

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

| Fluent | 描述 |
|---|---|
| `label(string)` | 按钮上显示的标签。省略时，名称会转换为 title case。 |
| `color(string)` | 按钮的颜色令牌: `accent`, `ink` 或 `muted`。默认是 `ink`。 |
| `requiresConfirmation(?string)` | 运行前显示确认对话框。传入自定义消息，或省略以使用默认提示。 |
| `authorize(string)` | 命名一个策略能力，在 handler 运行前按记录检查。 |
| `successMessage(string)` | 成功运行后显示的 flash 消息。默认是 `Done.`。 |

操作会 post 到受保护的 endpoint。记录通过其他地方使用的同一个 scoped 基础查询解析，因此多租户、过滤器和每资源查询 scope 会自动应用。当声明 `authorize('ability')` 时，策略会在 handler 运行前按记录检查。批量运行在数据库事务中执行，并限制为每个请求 100 条记录。如果任何请求的记录在 scoped fetch 中缺失，整个操作会以 404 中止，而不是静默应用到已解析的子集。请在任何破坏性操作上声明 `authorize()`。

`BulkAction::delete()` 是预构建 preset: 名称 `delete`，标签 `Delete`，颜色 `accent`，确认 `Delete the selected records?`，并且 `authorize('delete')` 已经接好。

## 授权

Saddle 使用标准 Laravel 策略。为模型注册策略后，面板会在所有地方强制执行: 索引、表单、行操作和关系选择器。没有注册策略时，所有认证用户都允许所有能力。角色保留在你的应用中: 任何支持你策略的角色包或自建层都无需更改即可工作。

### 锁定面板

没有注册策略的资源默认允许所有认证用户。将 `saddle.authorization.require_policy` 设为 `true` 可关闭式失败: 没有策略的资源会变为不可访问，而不是开放。你也可以向 `saddle.middleware` 添加 gate middleware，在任何面板路由运行前做统一检查。如果你的 web guard 在终端用户和管理员之间共享，这些控制之一是必要的。

| 能力 | 检查位置 |
|---|---|
| `viewAny` | 资源索引页，侧边栏可见性 |
| `create` | 创建表单，store 操作，关系选项 endpoint |
| `update` | 编辑表单，update 操作，每行 Edit 链接，关系选项 endpoint (当范围内没有记录时针对新模型检查) |
| `delete` | destroy 操作，每行 Delete 按钮 |

### 使用 `canSee` 控制字段可见性

单个字段可以使用 `canSee` 按请求控制。`notes` 字段是 `HorseResource` 中的一个可用示例:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

隐藏字段会从表单 payload 中移除 (存储值永远不会序列化到 frontend)，不贡献验证规则，保存时永远不会写入，并且其关系选项 endpoint 返回 404。callback 每个请求可能运行多次，因此要保持轻量并返回真正的 boolean。例如，使用 `Gate::allows('view-notes', $model)` 而不是 `Gate::inspect(...)`，后者的 `Response` 对象始终 truthy，永远不会隐藏字段。

## 插件

插件是普通的 Composer 包。其服务提供者通过 `Saddle` facade 注册资源、脚本和样式，Laravel 的 package auto-discovery 会随你的应用自动启动它。

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

从插件的服务提供者将编译后的资源发布到 `public/vendor/{plugin}`，使用标准 `$this->publishes([...])` 机制，然后将 `Saddle::script()` 指向发布后的路径。插件脚本和样式表会在核心面板 bundle 之后加载到每个面板页面。

### 自定义元素

插件可以将自己的字段和列渲染器作为自定义元素提供。在 PHP 侧:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

面板履行此契约: 对于字段，它设置元素的 `value` 和 `field` DOM 属性，并监听 `saddle:input` CustomEvent，其 `detail` 是新值。对于列，它设置 `value` 和 `column` DOM 属性 (只读，不期待输入事件)。

实现字段契约的最小 vanilla 自定义元素:

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

在脚本顶层定义元素。`customElements.define` 运行后，浏览器会立即升级面板已经渲染的任何匹配元素，因此加载顺序从不重要。

该契约与框架无关。任何能编译为标准自定义元素的东西都可以工作: Vue 的 `defineCustomElement`、Lit、React 或 Svelte wrappers。插件作者不受面板内部机制绑定。

## 多租户

Saddle 支持可选的 URL 范围多租户。通过将 `tenancy.model` 指向任意 Eloquent 类来启用:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

启用租户后，面板会挂载在 `/admin/{tenant}` 下，而不是 `/admin`。`{tenant}` 段通过配置模型上的 route-key lookup 解析。未知租户键返回 **404**。不是已解析租户成员的认证用户会以 **403** 拒绝。

### 限定资源范围

在每个要限定范围的资源上声明记录到租户的 BelongsTo 关系:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

没有 `$tenant` 的资源 (共享 lookup 表，全局配置) 会按设计保持未限定范围。

### 自动范围保证

每条数据路径都会在服务器端检查绑定的租户:

- **索引、搜索和过滤器** 会通过 scoped 基础查询 (`whereBelongsTo` 作用于声明的关系) 运行。
- **记录 lookup** 用于编辑、更新和销毁时通过同一 scoped 查询解析，因此跨租户 IDs 会在任何策略运行前返回 404。
- **Stores** 在填充表单后在服务器端标记当前租户。客户端提交的任何租户外键都会被覆盖。
- **关系选项列表** 在关联模型的注册资源也限定租户范围时应用相同范围。

### 租户切换器

当认证用户属于多个租户时，面板侧边栏会显示一个列出其所有成员关系的 select。切换会导航到所选租户下的同一面板路径。

### 注意事项

- **不要在 scoped 资源上将 `$tenant` 关系暴露为表单字段。** store 控制器会在服务器端标记关系，但 update 表单上指向租户关系的可编辑 BelongsTo 字段会允许提交值把记录重新指向其他租户。
- **即使关联行落在当前范围之外，保存的关系标签仍会在编辑表单上渲染。** `BelongsTo` 使用未限定范围的查询解析当前选择，因此范围变化后标签不会消失。只有新选择的选项列表会被过滤。
- **更改租户配置需要 `php artisan route:clear`**，因为 `{tenant}` 前缀在启动时决定。长时间运行的应用服务器 (在请求之间保持存活的 FPM workers) 必须确保请求状态在请求之间重置。绑定的租户存在于 Saddle singleton 上，在默认容器生命周期下会按请求重新解析。安装 Octane 时，面板会通过 Octane 请求生命周期 hooks 自动重置绑定的租户。

## 配置

`saddle:install` 发布 `config/saddle.php`。可用键:

| 键 | 默认值 | 描述 |
|---|---|---|
| `path` | `'admin'` | 面板的 URL 前缀 (例如 `'admin'` → `/admin`)。 |
| `middleware` | `['web', 'auth']` | 应用于所有面板路由的 middleware 栈。 |
| `resources.path` | `app_path('Saddle')` | 扫描资源类的文件系统路径。 |
| `resources.namespace` | `'App\\Saddle'` | 对应 `resources.path` 的 PHP namespace。 |
| `per_page` | `25` | 索引表每页默认行数。 |
| `brand.name` | `'Saddle'` | 面板名称 (侧边栏和浏览器标签)。 |
| `brand.accent` | `'#d9501f'` | 强调色 (按钮，活动状态)。 |
| `uploads.disk` | `'public'` | `FileUpload` 字段使用的默认文件系统 disk，在未设置每字段 `disk()` 时使用。 |
| `uploads.directory` | `'saddle'` | 未设置每字段 `directory()` 时 disk 内的默认上传目录。 |

## 命令

| 命令 | 描述 |
|---|---|
| `saddle:install` | 发布配置，发布面板资源，创建 `app/Saddle/`。会提示将 `saddle:upgrade` 添加到 `composer post-update-cmd`，以保持资源最新。 |
| `saddle:upgrade` | 重新发布面板资源。每次包更新后运行。 |
| `saddle:resource NameResource --model=Name` | 生成新的资源类脚手架。`--model` 选项是可选的。省略时会从资源名称推断。 |

**部署说明。** 将 `php artisan saddle:upgrade` 添加到部署脚本，在 `composer install` 或 `composer update` 后运行。发布资源与已安装包版本不同步时，面板会在 UI 中显示警告横幅。

## 本地开发

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

`workbench/` 目录包含一个最小宿主应用，供测试套件和手动试用使用。`vendor/bin/testbench serve` 会在注册 `HorseResource` 后启动它。请注意，面板路由位于 `auth` middleware 后面，而 workbench 只提供一个 stub `/login` 路由，因此要进行交互式浏览，请临时设置 `'middleware' => ['web']` 于 `config/saddle.php` 中，或改为通过功能测试浏览。目前还没有 demo seeder。

## 技术栈

为 **Laravel 13+ / PHP 8.4+**、**Inertia 2**、**Vue 3**、**Tailwind CSS 4** 构建。

## 许可证

MIT.
