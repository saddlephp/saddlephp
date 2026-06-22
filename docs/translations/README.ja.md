<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <b>日本語</b> •
  <a href="README.ko.md">한국어</a> •
  <a href="README.nl.md">Nederlands</a> •
  <a href="README.pl.md">Polski</a> •
  <a href="README.pt-BR.md">Português (BR)</a> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>鞍を締めろ、カウボーイ。街に新しい管理パネルがやってきた。</em>
</p>

---

**Saddle** は Laravel 向けのオープンソース管理パネルフレームワークで、**Inertia and
Vue** のためにモダンな方法で構築されています。Eloquent モデルを、フォームとテーブルのビルダー、ロールとアクセス、
プラグイン、マルチテナントに対応した洗練されたリソースパネルへまとめます。

> **ステータス: v1.0、CSV インポート/エクスポートとテナント機能の追加。** マーケティングサイトは **[saddlephp.com](https://saddlephp.com)** にあります ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com))。

## インストール

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

サービスプロバイダーは自動検出されます。`saddle:install` は設定ファイルを公開し、パネルアセットを公開し、リソースクラス用に `app/Saddle/` を作成します。パネルを見るには `/admin` にアクセスします。

## リソースを定義する

リソースクラスは `app/Saddle/` に置きます。各クラスは `SaddlePHP\Resource` を継承し、`form()` と `table()` を実装します。

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

リソースは起動時に `app/Saddle/` をスキャンして自動的に検出されるため、手動登録は不要です。

> **予約済みルートキー。** パネルは各リソース配下の静的パスセグメント `create`、`options`、`actions` を所有します。そのため、ルートキーが文字どおりこれらの単語のいずれかであるレコードは、編集/更新/削除 URL から到達できません。そのようなレコードには整数キーまたは別の slug を使用してください。

## フォームレイアウト

フィールドはレイアウトコンテナにグループ化できます。コンテナは互いに自由にネストできます。

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

| コンテナ | 説明 |
|---|---|
| `Section` | ラベル付きのカードグループです。`description(string)` はサブタイトルを追加します。フィールドとネストしたコンテナの `schema([...])` を受け取ります。 |
| `Grid` | 子要素を CSS グリッドに配置します。`Grid::make(2)` は 2 カラムのグリッドを作成します。Grid 内のフィールドは `columnSpan(int)` を使って複数カラムにまたがります。 |
| `Tabs` | 1 つ以上の `Tab` コンテナをタブ付きインターフェイスで包みます。 |
| `Tab` | `Tabs` グループ内の単一ペインです。`Tab::make('Label')->schema([...])`。タブ内のいずれかのフィールドがバリデーションに失敗すると、そのタブはエラーインジケーターを表示し、ユーザーがすべてのペインへ移動せずに問題を見つけられるようにします。 |

**フラットなスキーマも引き続き動作します。** コンテナなしでフィールドの単純なリストを `$form->schema([...])` に渡すことは完全にサポートされ、以前と同じレイアウトを生成します。バリデーションと認可は、フィールドがコンテナ内にあってもトップレベルにあっても同じように扱います。

## フィールド

| フィールド | 説明 |
|---|---|
| `Text` | 1 行のテキスト入力です。修飾子: `required()`, `rules(string\|array)`, `placeholder()`。 |
| `Textarea` | 複数行のテキスト入力です。修飾子: `rows(int)`。 |
| `Select` | 固定オプションのドロップダウンです。連想配列を `options(['value' => 'Label'])` に渡します。 |
| `Toggle` | Boolean スイッチです。`true`/`false` を保存します。 |
| `BelongsTo` | リレーション選択です。引数はモデル上の Eloquent リレーションメソッド名です (`BelongsTo::make('rider')` は `$model->rider()` を読み、外部キーを送信します)。オプションラベルは `titleAttribute('name')` から解決され、関連モデルの登録済みリソース `$title`、そのキーの順にフォールバックします。`titleAttribute()` も登録済みリソースも利用できない場合、オプションは主キーでラベル付けされるため、読みやすいラベルには `titleAttribute('name')` を設定してください。オプションはデフォルトで 100 件に制限され、`limit(int)` で上書きできます。`searchable()` は、入力中に認証済み endpoint 経由で関連テーブルを検索する非同期ピッカーに切り替えます。編集時には現在の選択だけが埋め込まれ、完全なリストは読み込まれません。`modifyOptionsQuery(fn ($query) => ...)` はテナントや可視性のためにオプションリストをスコープします。これは一覧表示と検索されたオプションに適用され、レコードの保存済み選択はスコープ外にある場合でもラベルを表示し続けます。 |
| `Number` | 数値入力です。修飾子: `min()`, `max()`, `step()`, `integer()`。 |
| `Date` | 日付入力です。値は `Y-m-d` として描画されます。 |
| `DateTime` | 日付と時刻の入力です (`datetime-local`)。値はモデルの datetime cast 経由で保存および読み戻されます。`DateTimeInterface` 値はブラウザ向けに `Y-m-d\TH:i` としてフォーマットされます。 |
| `Markdown` | フォーマットツールバー付きの Textarea です。プレーンな文字列として保存され、65 535 文字に制限されます。これは MySQL の `TEXT` カラムに相当します。 |
| `FileUpload` | Multipart ファイルアップロードです。修飾子: `disk(string)`, `directory(string)`, `image()` (画像タイプに制限), `acceptedTypes(array)` (MIME 拡張子、例 `['pdf', 'docx']`), `maxSize(int $kilobytes)`。保存値は `Storage::put` が返すファイルパスです。編集フォームでは、入力を触らなければ既存ファイルを保持し、クリアすると `null` を保存し、新しいファイルを選ぶとパスを置き換えます。置き換えられたファイルやクリアされたファイルはディスクから自動削除されません。 |

## カラム

| カラム | 説明 |
|---|---|
| `TextColumn` | 生の属性値を描画します。修飾子: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (DateTime 属性をフォーマット、デフォルト形式 `Y-m-d H:i`)。 |
| `BadgeColumn` | ピル型のバッジを描画します。`colors(['value' => 'token'])` を使い、オプション値をカラートークン (`accent`, `ink`, `muted`) に対応付けます。 |
| `BooleanColumn` | truthy 値にはチェックマーク、falsy 値にはダッシュを描画します。 |

**リレーションカラムと Eager Loading。** `TextColumn::make('rider.name')` のようなドット付き名は、読み込まれたリレーションを通して読みます。インデックスクエリが描画前にリレーションを eager-load するよう、リソースで `public static array $with = ['rider']` を宣言します。リレーションカラムはまだソートや検索に対応していません。

## フィルター

フィルターはテーブル上で `->filters([...])` により宣言します。インデックスでは、パネルが `filter[name]=value` の query string パラメータからフィルターを適用します。要求された値は宣言に対して検証されるため、不明なフィルター名や未宣言のオプション値は静かに無視されます。

| フィルター | 説明 |
|---|---|
| `SelectFilter` | 完全一致のドロップダウンです。`options(['value' => 'Label'])` はドロップダウンの選択肢と受け入れ可能な値の allowlist の両方を定義します。 |
| `BooleanFilter` | Boolean カラムに対する Yes/No ドロップダウンです。 |

## アクション

アクションはインデックステーブルの各行にボタンとして表示されます。1 つ以上の行が選択されると、バルクアクションはツールバーに表示されます。

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

| Fluent | 説明 |
|---|---|
| `label(string)` | ボタンに表示されるラベルです。省略すると、名前は title case に変換されます。 |
| `color(string)` | ボタンのカラートークン: `accent`, `ink`, `muted`。デフォルトは `ink` です。 |
| `requiresConfirmation(?string)` | 実行前に確認ダイアログを表示します。カスタムメッセージを渡すか、省略してデフォルトのプロンプトを使用します。 |
| `authorize(string)` | ハンドラー実行前にレコードごとにチェックされるポリシー能力名を指定します。 |
| `successMessage(string)` | 実行成功後に表示される flash メッセージです。デフォルトは `Done.` です。 |

アクションは保護された endpoint に post します。レコードは他の場所と同じスコープ済みベースクエリで解決されるため、テナント、フィルター、リソースごとのクエリスコープが自動的に適用されます。`authorize('ability')` が宣言されている場合、ハンドラー実行前にポリシーがレコードごとにチェックされます。バルク実行はデータベーストランザクション内で実行され、1 リクエストあたり 100 レコードに制限されます。要求されたレコードがスコープ済み取得に存在しない場合、解決されたサブセットへ静かに適用するのではなく、操作全体が 404 で中止されます。破壊的なアクションには `authorize()` を宣言してください。

`BulkAction::delete()` は事前構築済みプリセットです: 名前 `delete`、ラベル `Delete`、色 `accent`、確認 `Delete the selected records?`、そして `authorize('delete')` がすでに接続されています。

## 認可

Saddle は標準の Laravel ポリシーを使用します。モデルにポリシーを登録すると、パネルはインデックス、フォーム、行アクション、リレーションピッカーのすべてでそれを強制します。ポリシーが登録されていない場合、すべての能力はすべての認証済みユーザーに許可されます。ロールはアプリケーション内に残ります。ポリシーを支えるロールパッケージや自作レイヤーはそのまま動作します。

### パネルをロックダウンする

登録済みポリシーのないリソースは、デフォルトですべての認証済みユーザーを許可します。閉じた状態で失敗させるには `saddle.authorization.require_policy` を `true` に設定します。ポリシーのないリソースは開いたままではなくアクセス不可になります。任意のパネルルートが実行される前に包括的なチェックを行うため、gate middleware を `saddle.middleware` に追加することもできます。web guard をエンドユーザーと管理者で共有している場合、これらの制御のいずれかは必須です。

| 能力 | チェックされる場所 |
|---|---|
| `viewAny` | リソースインデックスページ、サイドバーの可視性 |
| `create` | 作成フォーム、store アクション、リレーションオプション endpoint |
| `update` | 編集フォーム、update アクション、行ごとの Edit リンク、リレーションオプション endpoint (スコープ内にレコードがない場合は新しいモデルに対してチェック) |
| `delete` | destroy アクション、行ごとの Delete ボタン |

### `canSee` によるフィールド可視性

個別のフィールドは `canSee` を使ってリクエストごとに制御できます。`notes` フィールドは `HorseResource` の動作する例です:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

非表示フィールドはフォームペイロードから取り除かれます (保存済み値が frontend にシリアライズされることはありません)。バリデーションルールを追加せず、保存時にも書き込まれず、リレーションオプション endpoint は 404 を返します。callback は 1 リクエストで複数回実行されることがあるため、軽く保ち、実際の boolean を返してください。たとえば `Gate::allows('view-notes', $model)` を使います、`Gate::inspect(...)` ではなく。その `Response` オブジェクトは常に truthy で、フィールドを隠すことはありません。

## プラグイン

プラグインは通常の Composer パッケージです。そのサービスプロバイダーは `Saddle` ファサードを通してリソース、スクリプト、スタイルを登録し、Laravel のパッケージ自動検出によりアプリケーションと一緒に自動的に起動します。

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

プラグインのサービスプロバイダーからコンパイル済みアセットを `public/vendor/{plugin}` に標準の `$this->publishes([...])` 仕組みで公開し、公開されたパスを `Saddle::script()` に指定します。プラグインのスクリプトとスタイルシートは、コアパネルバンドルの後にすべてのパネルページで読み込まれます。

### カスタム要素

プラグインは独自のフィールドおよびカラムレンダラーをカスタム要素として提供できます。PHP 側では:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

パネルはこの契約を満たします。フィールドでは、要素の `value` と `field` DOM プロパティを設定し、`saddle:input` CustomEvent を待ち受けます。その `detail` が新しい値です。カラムでは `value` と `column` DOM プロパティを設定します (読み取り専用で、入力イベントは期待されません)。

フィールド契約を実装する最小の vanilla カスタム要素:

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

要素はスクリプトのトップレベルで定義します。`customElements.define` が実行されるとすぐに、ブラウザはパネルがすでに描画した一致要素をアップグレードするため、読み込み順序は問題になりません。

契約はフレームワーク非依存です。標準のカスタム要素にコンパイルされるものなら何でも動作します: Vue の `defineCustomElement`、Lit、React または Svelte のラッパー。プラグイン作者はパネル内部に縛られません。

## マルチテナント

Saddle はオプトインの URL スコープ付きマルチテナントをサポートします。任意の Eloquent クラスを `tenancy.model` に指定して有効化します:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

テナントが有効な場合、パネルは `/admin/{tenant}` の下にマウントされます、`/admin` ではなく。`{tenant}` セグメントは設定済みモデルのルートキー lookup で解決されます。不明なテナントキーは **404** を返します。解決されたテナントのメンバーではない認証済みユーザーは **403** で拒否されます。

### リソースのスコープ

スコープしたい各リソースで、レコードのテナントへの BelongsTo リレーションを宣言します:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

`$tenant` のないリソース (共有 lookup テーブル、グローバル設定) は設計上スコープされません。

### 自動スコープ保証

すべてのデータパスはサーバー側でバインドされたテナントをチェックします:

- **インデックス、検索、フィルター** はスコープ済みベースクエリ (`whereBelongsTo` を宣言済みリレーションに対して使用) を通ります。
- **レコード lookup** は編集、更新、削除で同じスコープ済みクエリを通して解決されるため、テナントをまたぐ IDs はポリシー実行前に 404 を返します。
- **Stores** はフォームを埋めた後、現在のテナントをサーバー側で付与します。クライアントから送信されたテナント外部キーはすべて上書きされます。
- **リレーションオプションリスト** は、関連モデルの登録済みリソースもテナントスコープ付きである場合、同じスコープを適用します。

### テナントスイッチャー

認証済みユーザーが複数のテナントに属している場合、パネルのサイドバーにはそのすべてのメンバーシップを列挙する select が表示されます。切り替えると、選択したテナントの下の同じパネルパスへ移動します。

### 注意点

- **スコープ済みリソースで `$tenant` リレーションをフォームフィールドとして公開しないでください。** store コントローラーはサーバー側でリレーションを付与しますが、update フォームでテナントリレーションを指す編集可能な BelongsTo フィールドがあると、送信値でレコードを別テナントへ付け替えられてしまいます。
- **関連行が現在のスコープ外にある場合でも、保存済みリレーションラベルは編集フォームで引き続き描画されます。** `BelongsTo` は現在の選択をスコープなしクエリで解決するため、スコープ変更後にラベルが消えることはありません。新しい選択肢のオプションリストだけがフィルターされます。
- **テナント設定を変更するには `php artisan route:clear` が必要です**。`{tenant}` プレフィックスは起動時に決定されるためです。長時間動作するアプリケーションサーバー (リクエスト間で生存し続ける FPM ワーカー) は、リクエスト状態がリクエスト間でリセットされることを保証する必要があります。バインドされたテナントは Saddle シングルトン上にあり、デフォルトのコンテナライフタイムではリクエストごとに新しく解決されます。Octane がインストールされている場合、パネルは Octane のリクエストライフサイクルフックを通してバインドされたテナントを自動的にリセットします。

## 設定

`saddle:install` は `config/saddle.php` を公開します。利用可能なキー:

| キー | デフォルト | 説明 |
|---|---|---|
| `path` | `'admin'` | パネルの URL プレフィックス (例 `'admin'` → `/admin`)。 |
| `middleware` | `['web', 'auth']` | すべてのパネルルートに適用される middleware スタック。 |
| `resources.path` | `app_path('Saddle')` | リソースクラスをスキャンするファイルシステムパス。 |
| `resources.namespace` | `'App\\Saddle'` | `resources.path` に対応する PHP namespace。 |
| `per_page` | `25` | インデックステーブルの 1 ページあたりデフォルト行数。 |
| `brand.name` | `'Saddle'` | パネル名 (サイドバーとブラウザタブ)。 |
| `brand.accent` | `'#d9501f'` | アクセントカラー (ボタン、アクティブ状態)。 |
| `uploads.disk` | `'public'` | `FileUpload` フィールドが使うデフォルトのファイルシステムディスクで、フィールドごとの `disk()` が設定されていない場合に使われます。 |
| `uploads.directory` | `'saddle'` | フィールドごとの `directory()` が設定されていない場合の、ディスク内のデフォルトアップロードディレクトリ。 |

## コマンド

| コマンド | 説明 |
|---|---|
| `saddle:install` | 設定を公開し、パネルアセットを公開し、`app/Saddle/` を作成します。アセットを新しく保つため、`saddle:upgrade` を `composer post-update-cmd` に追加することを提案します。 |
| `saddle:upgrade` | パネルアセットを再公開します。各パッケージ更新後に実行してください。 |
| `saddle:resource NameResource --model=Name` | 新しいリソースクラスを scaffold します。`--model` オプションは任意です。省略時はリソース名から推測されます。 |

**デプロイメモ。** デプロイスクリプトに `php artisan saddle:upgrade` を追加してください、`composer install` または `composer update` の後に。公開済みアセットがインストール済みパッケージバージョンと同期していない場合、パネルは UI に警告バナーを表示します。

## ローカル開発

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

`workbench/` ディレクトリには、テストスイートと手動確認に使う最小ホストアプリケーションがあります。`vendor/bin/testbench serve` は `HorseResource` を登録して起動します。パネルルートは `auth` middleware の背後にあり、workbench にはスタブの `/login` ルートしかない点に注意してください。対話的に閲覧するには、一時的に `'middleware' => ['web']` を `config/saddle.php` で設定するか、代わりに機能テスト経由で確認してください。まだ demo seeder はありません。

## スタック

**Laravel 13+ / PHP 8.4+**、**Inertia 2**、**Vue 3**、**Tailwind CSS 4** 向けに構築されています。

## ライセンス

MIT.
