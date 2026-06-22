<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <a href="README.fr.md">Français</a> •
  <a href="README.it.md">Italiano</a> •
  <a href="README.ja.md">日本語</a> •
  <b>한국어</b> •
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
  <em>안장을 올려라, 카우보이. 이 마을에 새 관리 패널이 왔다.</em>
</p>

---

**Saddle**은 Laravel을 위한 오픈 소스 관리 패널 프레임워크로, **Inertia and
Vue**에 맞게 현대적인 방식으로 만들어졌습니다. Eloquent 모델을 폼 및 테이블 빌더, 역할과 접근 제어,
플러그인과 멀티 테넌시를 갖춘 정돈된 리소스 패널로 모으세요.

> **상태: v1.0, CSV 가져오기/내보내기와 테넌시 추가 기능.** 마케팅 사이트는 **[saddlephp.com](https://saddlephp.com)** 에 있습니다 ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## 설치

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

서비스 프로바이더는 자동으로 발견됩니다. `saddle:install`은 설정 파일을 게시하고, 패널 에셋을 게시하며, 리소스 클래스를 위한 `app/Saddle/`을 만듭니다. 패널을 보려면 `/admin`을 방문하세요.

## 리소스 정의

리소스 클래스는 `app/Saddle/`에 둡니다. 각 클래스는 `SaddlePHP\Resource`를 확장하고 `form()`과 `table()`을 구현합니다.

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

리소스는 부팅 시 `app/Saddle/`을 스캔하여 자동으로 발견되며, 수동 등록은 필요 없습니다.

> **예약된 라우트 키.** 패널은 각 리소스 아래의 정적 경로 세그먼트 `create`, `options`, `actions`를 소유합니다. 따라서 라우트 키가 문자 그대로 이 단어 중 하나인 레코드는 편집/업데이트/삭제 URL로 접근할 수 없습니다. 그런 레코드에는 정수 키나 다른 slug를 사용하세요.

## 폼 레이아웃

필드는 레이아웃 컨테이너로 그룹화할 수 있습니다. 컨테이너는 서로 자유롭게 중첩될 수 있습니다.

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

| 컨테이너 | 설명 |
|---|---|
| `Section` | 라벨이 있는 카드 그룹입니다. `description(string)`은 부제목을 추가합니다. 필드와 중첩 컨테이너의 `schema([...])`를 받습니다. |
| `Grid` | 자식들을 CSS 그리드에 배치합니다. `Grid::make(2)`는 2열 그리드를 만듭니다. Grid 안의 필드는 여러 열을 차지하기 위해 `columnSpan(int)`를 사용합니다. |
| `Tabs` | 하나 이상의 `Tab` 컨테이너를 탭 인터페이스로 감쌉니다. |
| `Tab` | `Tabs` 그룹 안의 단일 패널입니다. `Tab::make('Label')->schema([...])`. 탭 안의 어떤 필드가 검증에 실패하면, 사용자가 모든 패널로 전환하지 않고도 문제를 찾을 수 있도록 탭에 오류 표시가 나타납니다. |

**평평한 스키마도 계속 작동합니다.** 컨테이너 없이 `$form->schema([...])`에 단순한 필드 목록을 전달하는 것은 완전히 지원되며 이전과 같은 레이아웃을 만듭니다. 검증과 권한 부여는 필드가 컨테이너 안에 있든 최상위에 있든 동일하게 처리합니다.

## 필드

| 필드 | 설명 |
|---|---|
| `Text` | 한 줄 텍스트 입력입니다. 수정자: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | 여러 줄 텍스트 입력입니다. 수정자: `rows(int)`. |
| `Select` | 고정 옵션 드롭다운입니다. 연관 배열을 `options(['value' => 'Label'])`에 전달합니다. |
| `Toggle` | 불리언 스위치입니다. `true`/`false`를 저장합니다. |
| `BelongsTo` | 관계 선택입니다. 인자는 모델의 Eloquent 관계 메서드 이름입니다 (`BelongsTo::make('rider')`는 `$model->rider()`를 읽고 외래 키를 제출합니다). 옵션 라벨은 `titleAttribute('name')`에서 해결되고, 관련 모델의 등록된 리소스 `$title`, 그다음 키로 fallback 됩니다. `titleAttribute()`도 등록된 리소스도 없으면 옵션은 기본 키로 라벨링되므로 읽기 쉬운 라벨을 위해 `titleAttribute('name')`을 설정하세요. 옵션은 기본적으로 100개로 제한되며, `limit(int)`로 재정의할 수 있습니다. `searchable()`은 인증된 endpoint를 통해 입력하는 동안 관련 테이블을 검색하는 비동기 피커로 전환합니다. 편집 시에는 현재 선택만 포함되고 전체 목록은 로드되지 않습니다. `modifyOptionsQuery(fn ($query) => ...)`는 테넌시나 가시성을 위해 옵션 목록의 범위를 제한합니다. 나열되고 검색된 옵션에 적용되며, 레코드의 저장된 선택은 범위 밖에 있어도 라벨을 계속 렌더링합니다. |
| `Number` | 숫자 입력입니다. 수정자: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | 날짜 입력입니다. 값은 `Y-m-d`로 렌더링됩니다. |
| `DateTime` | 날짜 및 시간 입력입니다 (`datetime-local`). 값은 모델의 datetime cast를 통해 저장되고 다시 읽힙니다. `DateTimeInterface` 값은 브라우저용으로 `Y-m-d\TH:i` 형식으로 지정됩니다. |
| `Markdown` | 서식 도구 모음이 있는 Textarea입니다. 일반 문자열로 저장되며 MySQL `TEXT` 컬럼과 같은 65 535자로 제한됩니다. |
| `FileUpload` | Multipart 파일 업로드입니다. 수정자: `disk(string)`, `directory(string)`, `image()` (이미지 유형으로 제한), `acceptedTypes(array)` (MIME 확장자, 예: `['pdf', 'docx']`), `maxSize(int $kilobytes)`. 저장된 값은 `Storage::put`이 반환한 파일 경로입니다. 편집 폼에서 입력을 건드리지 않으면 기존 파일을 유지하고, 지우면 `null`을 저장하며, 새 파일을 선택하면 경로를 교체합니다. 교체되거나 지워진 파일은 디스크에서 자동으로 삭제되지 않습니다. |

## 컬럼

| 컬럼 | 설명 |
|---|---|
| `TextColumn` | 원시 속성 값을 렌더링합니다. 수정자: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (DateTime 속성을 형식화, 기본 형식 `Y-m-d H:i`). |
| `BadgeColumn` | 알약 모양 badge를 렌더링합니다. `colors(['value' => 'token'])`을 사용해 옵션 값을 색상 토큰 (`accent`, `ink`, `muted`)에 매핑합니다. |
| `BooleanColumn` | truthy 값에는 체크 표시를, falsy 값에는 대시를 렌더링합니다. |

**관계 컬럼과 eager loading.** `TextColumn::make('rider.name')` 같은 점 표기 이름은 로드된 관계를 통해 읽습니다. 인덱스 쿼리가 렌더링 전에 관계를 eager-load 하도록 리소스에 `public static array $with = ['rider']`를 선언하세요. 관계 컬럼은 아직 정렬이나 검색을 지원하지 않습니다.

## 필터

필터는 테이블에서 `->filters([...])`로 선언됩니다. 인덱스에서 패널은 `filter[name]=value` query string 파라미터로부터 필터를 적용합니다. 요청된 값은 선언에 대해 검증되므로 알 수 없는 필터 이름과 선언되지 않은 옵션 값은 조용히 무시됩니다.

| 필터 | 설명 |
|---|---|
| `SelectFilter` | 정확히 일치하는 드롭다운입니다. `options(['value' => 'Label'])`은 드롭다운 선택지와 허용되는 값의 allowlist를 모두 정의합니다. |
| `BooleanFilter` | 불리언 컬럼 위의 예/아니오 드롭다운입니다. |

## 액션

액션은 인덱스 테이블의 각 행에 버튼으로 나타납니다. 하나 이상의 행이 선택되면 벌크 액션은 도구 모음에 나타납니다.

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

| Fluent | 설명 |
|---|---|
| `label(string)` | 버튼에 표시되는 라벨입니다. 생략하면 이름이 title case로 변환됩니다. |
| `color(string)` | 버튼의 색상 토큰: `accent`, `ink`, `muted`. 기본값은 `ink`입니다. |
| `requiresConfirmation(?string)` | 실행 전에 확인 대화 상자를 표시합니다. 사용자 지정 메시지를 전달하거나 생략하여 기본 프롬프트를 사용합니다. |
| `authorize(string)` | 핸들러가 실행되기 전에 레코드별로 확인되는 정책 능력의 이름을 지정합니다. |
| `successMessage(string)` | 성공적인 실행 후 표시되는 flash 메시지입니다. 기본값은 `Done.`입니다. |

액션은 보호된 endpoint로 post합니다. 레코드는 다른 모든 곳에서 사용되는 동일한 범위 지정 기본 쿼리를 통해 해결되므로, 테넌시, 필터, 리소스별 쿼리 scope가 자동으로 적용됩니다. `authorize('ability')`가 선언되면 핸들러 실행 전에 정책이 레코드별로 확인됩니다. 벌크 실행은 데이터베이스 트랜잭션 안에서 실행되며 요청당 100개 레코드로 제한됩니다. 요청된 레코드가 범위 지정 fetch에서 하나라도 누락되면, 해결된 일부에 조용히 적용되는 대신 전체 작업이 404로 중단됩니다. 파괴적인 액션에는 `authorize()`를 선언하세요.

`BulkAction::delete()`는 미리 만들어진 preset입니다: 이름 `delete`, 라벨 `Delete`, 색상 `accent`, 확인 `Delete the selected records?`, 그리고 `authorize('delete')`가 이미 연결되어 있습니다.

## 권한 부여

Saddle은 표준 Laravel 정책을 사용합니다. 모델에 정책을 등록하면 패널은 인덱스, 폼, 행 액션, 관계 피커 등 모든 곳에서 이를 강제합니다. 등록된 정책이 없으면 모든 인증된 사용자에게 모든 능력이 허용됩니다. 역할은 애플리케이션에 남습니다. 정책을 뒷받침하는 역할 패키지나 자체 레이어는 변경 없이 작동합니다.

### 패널 잠그기

등록된 정책이 없는 리소스는 기본적으로 모든 인증된 사용자를 허용합니다. 닫힌 상태로 실패하게 하려면 `saddle.authorization.require_policy`를 `true`로 설정하세요. 정책이 없는 리소스는 열린 상태가 아니라 접근할 수 없게 됩니다. 어떤 패널 라우트가 실행되기 전에 전체 검사를 수행하도록 `saddle.middleware`에 gate middleware를 추가할 수도 있습니다. web guard가 최종 사용자와 관리자 사이에서 공유된다면, 이 제어 중 하나는 필수입니다.

| 능력 | 확인 위치 |
|---|---|
| `viewAny` | 리소스 인덱스 페이지, 사이드바 가시성 |
| `create` | 생성 폼, store 액션, 관계 옵션 endpoint |
| `update` | 편집 폼, update 액션, 행별 Edit 링크, 관계 옵션 endpoint (범위 안에 레코드가 없을 때 새 모델에 대해 확인) |
| `delete` | destroy 액션, 행별 Delete 버튼 |

### `canSee`로 필드 가시성 제어

개별 필드는 `canSee`를 사용해 요청별로 제어할 수 있습니다. `notes` 필드는 `HorseResource`의 작동하는 예입니다:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

숨겨진 필드는 폼 payload에서 제거되고 (저장된 값은 frontend로 직렬화되지 않음), 검증 규칙을 추가하지 않으며, 저장 시 절대 쓰이지 않고, 관계 옵션 endpoint는 404를 반환합니다. callback은 요청당 여러 번 실행될 수 있으므로 가볍게 유지하고 실제 boolean을 반환하세요. 예를 들어 `Gate::allows('view-notes', $model)`을 사용하세요, `Gate::inspect(...)` 대신. 그 `Response` 객체는 항상 truthy이며 필드를 절대 숨기지 않습니다.

## 플러그인

플러그인은 일반 Composer 패키지입니다. 서비스 프로바이더는 `Saddle` facade를 통해 리소스, 스크립트, 스타일을 등록하고, Laravel의 package auto-discovery가 애플리케이션과 함께 자동으로 부팅합니다.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

플러그인의 서비스 프로바이더에서 컴파일된 에셋을 `public/vendor/{plugin}`에 표준 `$this->publishes([...])` 메커니즘으로 게시한 다음, 게시된 경로를 `Saddle::script()`에 지정하세요. 플러그인 스크립트와 스타일시트는 코어 패널 번들 이후 모든 패널 페이지에 로드됩니다.

### 커스텀 엘리먼트

플러그인은 자체 필드 및 컬럼 렌더러를 커스텀 엘리먼트로 제공할 수 있습니다. PHP 쪽에서는:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

패널은 이 계약을 충족합니다. 필드의 경우 요소의 `value`와 `field` DOM 속성을 설정하고, `saddle:input` CustomEvent를 수신하며, 그 `detail`이 새 값입니다. 컬럼의 경우 `value`와 `column` DOM 속성을 설정합니다 (읽기 전용, 입력 이벤트 없음).

필드 계약을 구현하는 최소 vanilla 커스텀 엘리먼트:

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

스크립트의 최상위 수준에서 엘리먼트를 정의하세요. `customElements.define`이 실행되는 즉시 브라우저는 패널이 이미 렌더링한 일치 엘리먼트를 업그레이드하므로 로드 순서는 중요하지 않습니다.

계약은 프레임워크에 구애받지 않습니다. 표준 커스텀 엘리먼트로 컴파일되는 것은 무엇이든 작동합니다: Vue의 `defineCustomElement`, Lit, React 또는 Svelte wrapper. 플러그인 작성자는 패널 내부에 묶이지 않습니다.

## 멀티 테넌시

Saddle은 선택형 URL 범위 멀티 테넌시를 지원합니다. `tenancy.model`을 임의의 Eloquent 클래스로 지정하여 활성화하세요:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

테넌시가 활성화되면 패널은 `/admin/{tenant}` 아래에 마운트됩니다, `/admin` 대신. `{tenant}` 세그먼트는 설정된 모델의 라우트 키 lookup으로 해결됩니다. 알 수 없는 테넌트 키는 **404**를 반환합니다. 해결된 테넌트의 멤버가 아닌 인증된 사용자는 **403**으로 거부됩니다.

### 리소스 범위 지정

범위를 지정하려는 각 리소스에서 레코드의 테넌트에 대한 BelongsTo 관계를 선언하세요:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

`$tenant`가 없는 리소스 (공유 lookup 테이블, 전역 설정)는 설계상 범위가 지정되지 않습니다.

### 자동 범위 보장

모든 데이터 경로는 서버 측에서 바인딩된 테넌트를 확인합니다:

- **인덱스, 검색, 필터**는 범위 지정 기본 쿼리 (`whereBelongsTo`를 선언된 관계에 적용) 를 통과합니다.
- **레코드 lookup**은 편집, 업데이트, 삭제에서 동일한 범위 지정 쿼리로 해결되므로, 테넌트 간 IDs는 정책이 실행되기 전에 404를 반환합니다.
- **Stores**는 폼을 채운 후 현재 테넌트를 서버 측에서 찍습니다. 클라이언트가 제출한 모든 테넌트 외래 키는 덮어씁니다.
- **관계 옵션 목록**은 관련 모델의 등록된 리소스도 테넌트 범위일 때 같은 범위를 적용합니다.

### 테넌트 전환기

인증된 사용자가 둘 이상의 테넌트에 속하면, 패널 사이드바는 모든 멤버십을 나열하는 select를 표시합니다. 전환하면 선택한 테넌트 아래의 동일한 패널 경로로 이동합니다.

### 주의 사항

- **범위 지정 리소스에서 `$tenant` 관계를 폼 필드로 노출하지 마세요.** store 컨트롤러는 서버 측에서 관계를 찍지만, update 폼에서 테넌트 관계를 가리키는 편집 가능한 BelongsTo 필드는 제출된 값으로 레코드를 다른 테넌트로 다시 지정할 수 있게 합니다.
- **관련 행이 현재 범위 밖에 있어도 저장된 관계 라벨은 편집 폼에 계속 렌더링됩니다.** `BelongsTo`는 현재 선택을 범위 없는 쿼리로 해결하므로 범위 변경 후에도 라벨이 사라지지 않습니다. 새 선택을 위한 옵션 목록만 필터링됩니다.
- **테넌시 설정 변경에는 `php artisan route:clear`가 필요합니다**, `{tenant}` prefix가 부팅 시 결정되기 때문입니다. 장기 실행 애플리케이션 서버 (요청 사이에 살아 있는 FPM worker)는 요청 상태가 요청 사이에 재설정되도록 보장해야 합니다. 바인딩된 테넌트는 Saddle singleton에 있으며, 기본 컨테이너 수명에서는 요청마다 새로 해결됩니다. Octane이 설치되어 있으면 패널은 Octane 요청 라이프사이클 hook을 통해 바인딩된 테넌트를 자동으로 재설정합니다.

## 설정

`saddle:install`은 `config/saddle.php`를 게시합니다. 사용 가능한 키:

| 키 | 기본값 | 설명 |
|---|---|---|
| `path` | `'admin'` | 패널의 URL prefix (예: `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | 모든 패널 라우트에 적용되는 middleware 스택. |
| `resources.path` | `app_path('Saddle')` | 리소스 클래스를 찾기 위해 스캔되는 파일 시스템 경로. |
| `resources.namespace` | `'App\\Saddle'` | `resources.path`에 대응하는 PHP namespace. |
| `per_page` | `25` | 인덱스 테이블의 페이지당 기본 행 수. |
| `brand.name` | `'Saddle'` | 패널 이름 (사이드바와 브라우저 탭). |
| `brand.accent` | `'#d9501f'` | 강조 색상 (버튼, 활성 상태). |
| `uploads.disk` | `'public'` | `FileUpload` 필드가 사용하는 기본 파일 시스템 disk이며, 필드별 `disk()`가 설정되지 않았을 때 적용됩니다. |
| `uploads.directory` | `'saddle'` | 필드별 `directory()`가 설정되지 않았을 때 disk 안의 기본 업로드 디렉터리. |

## 명령

| 명령 | 설명 |
|---|---|
| `saddle:install` | 설정을 게시하고, 패널 에셋을 게시하며, `app/Saddle/`을 만듭니다. 에셋을 최신 상태로 유지하도록 `saddle:upgrade`를 `composer post-update-cmd`에 추가할 것을 제안합니다. |
| `saddle:upgrade` | 패널 에셋을 다시 게시합니다. 모든 패키지 업데이트 후 실행하세요. |
| `saddle:resource NameResource --model=Name` | 새 리소스 클래스를 scaffold합니다. `--model` 옵션은 선택 사항입니다. 생략하면 리소스 이름에서 추론됩니다. |

**배포 참고.** 배포 스크립트에 `php artisan saddle:upgrade`를 추가하세요, `composer install` 또는 `composer update` 후에. 게시된 에셋이 설치된 패키지 버전과 동기화되지 않으면 패널은 UI에 경고 배너를 표시합니다.

## 로컬 개발

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

`workbench/` 디렉터리에는 테스트 스위트와 수동 확인에 사용되는 최소 호스트 애플리케이션이 있습니다. `vendor/bin/testbench serve`는 `HorseResource`가 등록된 상태로 이를 부팅합니다. 패널 라우트는 `auth` middleware 뒤에 있고 workbench는 stub `/login` 라우트만 제공하므로, 대화형 탐색을 하려면 임시로 `'middleware' => ['web']`를 `config/saddle.php`에서 설정하거나 대신 feature 테스트를 통해 탐색하세요. 아직 demo seeder는 없습니다.

## 스택

**Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**용으로 구축되었습니다.

## 라이선스

MIT.
