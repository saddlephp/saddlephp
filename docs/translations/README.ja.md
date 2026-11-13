<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Inertia と Vue のために作られた、Laravel 向けオープンソース管理画面フレームワーク。</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>ドキュメント</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>変更履歴</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>コントリビュート</b></a>
</p>

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

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="統計ウィジェットとチャートが並ぶ Saddle のダッシュボード" width="100%">
  </picture>
</p>

<p align="center"><em>鞍を締めろ、カウボーイ。この町に新しい管理画面がやってきた。</em></p>

## Saddle とは？

Saddle は Eloquent モデルを洗練された管理画面に変えます。各モデルはフォームとテーブルを持つ PHP のリソースクラスとして一度記述するだけ。一覧・作成・編集・詳細の各画面、検索、フィルター、アクションは Saddle が組み立てます。描画には Inertia と Vue を使い、既存の Laravel のログインとポリシーをそのまま利用し、アプリの他の部分には干渉しません。

<table>
<tr>
<td width="50%" valign="middle">

### しっかり働くテーブル

並べ替えと検索ができるカラム、バッジや真偽値の表示、フィルター、行アクション、確認付きの一括アクションを備えています。現在の表示を CSV にエクスポートしたり、フォームと同じバリデーションを通して CSV をインポートしたりできます。

[テーブルとカラム →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="フィルターと一括アクションバーを備えた Saddle のリソーステーブル">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="セクション、2 カラムのグリッド、タブを使った Saddle の編集フォーム">
</picture>
</td>
<td width="50%" valign="middle">

### 本格的なレイアウトのフォーム

テキストやセレクトから、検索可能なリレーション選択、ファイルアップロード、Markdown まで、11 種類のフィールドを用意しています。セクション、グリッド、タブで自由に配置できます。バリデーション、表示制御、保存はすべて同じフィールド定義に従います。

[フィールド →](../sections/fields.md) · [フォームレイアウト →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### モデルごとにクラスひとつ

リソースはただの PHP です。`app/Saddle/` に置けば起動時に自動検出されるので、ルート、コントローラー、Vue ファイルを書く必要はありません。アクセスは Laravel のポリシーに従い、デフォルトでは拒否されます。

[リソース →](../sections/resources.md) · [認可 →](../sections/authorization.md)

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

## 機能

<table>
<tr>
<td width="33%" valign="top">

**リソース**
- リソースクラスの自動検出
- 一覧・作成・編集・読み取り専用の詳細ページ
- `saddle:resource` ジェネレーター
- Eager Loading とリレーションカラム

</td>
<td width="33%" valign="top">

**フォーム**
- テキスト、パスワード、テキストエリア、数値、セレクト、トグル
- 日付、日時、Markdown、ファイルアップロード
- 検索可能な `BelongsTo` ピッカー
- セクション、グリッド、タブ

</td>
<td width="33%" valign="top">

**テーブル**
- テキスト、バッジ、真偽値のカラム
- 並べ替え、検索、`formatUsing`
- セレクトと真偽値のフィルター
- ページネーション

</td>
</tr>
<tr>
<td valign="top">

**アクション**
- 行アクションと一括アクション
- 確認ダイアログ
- レコード単位のポリシーチェック
- トランザクション内での一括実行

</td>
<td valign="top">

**リレーション**
- `HasMany` リレーションマネージャー
- 関連レコードをその場で作成・編集・削除
- 親レコードにスコープを限定

</td>
<td valign="top">

**ダッシュボード**
- ミニチャート付きの統計ウィジェット
- チャートウィジェット
- ベルアイコン付きのデータベース通知
- リソースを横断するグローバル検索

</td>
</tr>
<tr>
<td valign="top">

**アクセス**
- 標準的な Laravel ポリシー
- デフォルトで拒否
- `canSee` によるフィールド単位の表示制御
- 既存のログインをそのまま利用

</td>
<td valign="top">

**マルチテナンシー**
- URL でスコープされたテナント（`/admin/{tenant}`）
- クエリの自動スコープとテナントの自動付与
- テナント切り替えとセルフ登録
- 課金向けのアクセスゲートフック

</td>
<td valign="top">

**データ**
- 現在の表示の CSV エクスポート
- フォームのバリデーションを通した CSV インポート
- 復元と完全削除に対応したソフトデリート

</td>
</tr>
<tr>
<td valign="top">

**外観と言語**
- ライト、ダーク、システム連動のテーマ
- ブランド名、アクセントカラー、テーマトークン
- 14 言語に翻訳済みのパネル
- CSP nonce に対応

</td>
<td valign="top">

**プラグイン**
- 通常の Composer パッケージ
- Web Components によるカスタムフィールドとカラム
- 共有 props、ナビゲーション、テーマトークン用のフック

</td>
<td valign="top">

**開発者体験**
- Pest と PHPUnit 向けのテストヘルパー
- `saddle:install` と `saddle:upgrade`
- アップグレード後の古いアセットの警告
- Laravel 11、12、13

</td>
</tr>
</table>

## 使用技術

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## クイックスタート

PHP 8.4 以上と、Laravel 11.48 以上、12、または 13 が必要です。

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

アプリのログインからサインインし、モデルのポリシーを登録してから `/admin` にアクセスしてください。パッケージを更新するたびに `php artisan saddle:upgrade` を実行します。各ステップは[スタートガイド](../sections/getting-started.md)で詳しく説明しています。

## さらに詳しく

| | |
|---|---|
| **構築** | [リソース](../sections/resources.md) · [フィールド](../sections/fields.md) · [フォームレイアウト](../sections/forms-layout.md) · [テーブル](../sections/tables.md) · [フィルター](../sections/filters.md) · [アクション](../sections/actions.md) · [リレーションマネージャー](../sections/relations.md) |
| **パネル** | [ダッシュボードウィジェット](../sections/widgets.md) · [グローバル検索](../sections/search.md) · [通知](../sections/notifications.md) · [ソフトデリート](../sections/soft-deletes.md) · [インポートとエクスポート](../sections/import-export.md) |
| **運用** | [認可](../sections/authorization.md) · [マルチテナンシー](../sections/tenancy.md) · [テナンシーの追加機能](../sections/tenancy-extras.md) · [設定](../sections/configuration.md) · [テーマ](../sections/theming.md) · [多言語化](../sections/i18n.md) |
| **拡張** | [プラグイン](../sections/plugins.md) · [テストヘルパー](../sections/testing.md) · [コントリビュート](../../CONTRIBUTING.md) · [セキュリティ](../../SECURITY.md) |

## ライセンス

Saddle は [MIT ライセンス](../../LICENSE)で提供されるオープンソースソフトウェアです。
