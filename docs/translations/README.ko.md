<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Inertia와 Vue를 위해 만든 Laravel용 오픈 소스 관리자 패널 프레임워크.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>문서</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>변경 내역</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>기여하기</b></a>
</p>

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

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="통계 위젯과 차트가 있는 Saddle 대시보드" width="100%">
  </picture>
</p>

<p align="center"><em>안장을 얹어라, 카우보이. 마을에 새로운 관리자 패널이 등장했다.</em></p>

## Saddle이란?

Saddle은 Eloquent 모델을 완성도 높은 관리자 패널로 바꿔 줍니다. 각 모델을 폼과 테이블을 갖춘 PHP 리소스 클래스로 한 번만 정의하면, 목록, 생성, 수정, 상세 화면과 검색, 필터, 액션을 Saddle이 만들어 줍니다. 렌더링은 Inertia와 Vue로 처리하고, 기존 Laravel 로그인과 정책을 그대로 사용하며, 앱의 나머지 부분에는 관여하지 않습니다.

<table>
<tr>
<td width="50%" valign="middle">

### 알아서 일하는 테이블

정렬과 검색이 가능한 컬럼, 배지와 불리언 표시, 필터, 행 액션, 확인 절차가 있는 일괄 액션을 제공합니다. 현재 보기를 CSV로 내보내거나, 폼과 동일한 유효성 검사를 거쳐 CSV를 가져올 수 있습니다.

[테이블과 컬럼 →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="필터와 일괄 액션 바가 있는 Saddle 리소스 테이블">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="섹션, 2열 그리드, 탭으로 구성된 Saddle 수정 폼">
</picture>
</td>
<td width="50%" valign="middle">

### 제대로 된 레이아웃의 폼

텍스트와 셀렉트부터 검색 가능한 관계 선택기, 파일 업로드, Markdown까지 11가지 필드 타입을 제공합니다. 섹션, 그리드, 탭으로 배치할 수 있습니다. 유효성 검사, 표시 여부, 저장은 모두 같은 필드 정의를 따릅니다.

[필드 →](../sections/fields.md) · [폼 레이아웃 →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### 모델당 클래스 하나

리소스는 평범한 PHP입니다. `app/Saddle/`에 넣으면 부팅 시 자동으로 발견되므로 라우트, 컨트롤러, Vue 파일을 따로 작성할 필요가 없습니다. 접근 권한은 Laravel 정책을 따르며 기본값은 차단입니다.

[리소스 →](../sections/resources.md) · [권한 부여 →](../sections/authorization.md)

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

## 기능

<table>
<tr>
<td width="33%" valign="top">

**리소스**
- 리소스 클래스 자동 발견
- 목록, 생성, 수정, 읽기 전용 보기 페이지
- `saddle:resource` 생성기
- Eager 로딩과 관계 컬럼

</td>
<td width="33%" valign="top">

**폼**
- 텍스트, 비밀번호, 텍스트 영역, 숫자, 셀렉트, 토글
- 날짜, 날짜 및 시간, Markdown, 파일 업로드
- 검색 가능한 `BelongsTo` 선택기
- 섹션, 그리드, 탭

</td>
<td width="33%" valign="top">

**테이블**
- 텍스트, 배지, 불리언 컬럼
- 정렬, 검색, `formatUsing`
- 셀렉트 및 불리언 필터
- 페이지네이션

</td>
</tr>
<tr>
<td valign="top">

**액션**
- 행 액션과 일괄 액션
- 확인 대화상자
- 레코드별 정책 검사
- 트랜잭션으로 묶인 일괄 실행

</td>
<td valign="top">

**관계**
- `HasMany` 관계 매니저
- 관련 레코드를 그 자리에서 생성, 수정, 삭제
- 상위 레코드 범위로 제한

</td>
<td valign="top">

**대시보드**
- 미니 차트가 포함된 통계 위젯
- 차트 위젯
- 알림 벨이 있는 데이터베이스 알림
- 모든 리소스를 아우르는 전역 검색

</td>
</tr>
<tr>
<td valign="top">

**접근 제어**
- 표준 Laravel 정책
- 기본값은 차단
- `canSee`를 이용한 필드별 표시 여부
- 기존 로그인 그대로 사용

</td>
<td valign="top">

**멀티 테넌시**
- URL 기반 테넌트 범위 (`/admin/{tenant}`)
- 쿼리 자동 범위 지정과 테넌트 자동 기록
- 테넌트 전환기와 셀프 등록
- 결제 연동을 위한 접근 게이트 훅

</td>
<td valign="top">

**데이터**
- 현재 보기의 CSV 내보내기
- 폼 유효성 검사를 거치는 CSV 가져오기
- 복원과 영구 삭제를 지원하는 소프트 삭제

</td>
</tr>
<tr>
<td valign="top">

**디자인과 언어**
- 라이트, 다크, 시스템 테마
- 브랜드 이름, 강조 색상, 테마 토큰
- 14개 언어로 번역된 패널
- CSP nonce 지원

</td>
<td valign="top">

**플러그인**
- 일반 Composer 패키지
- 웹 컴포넌트로 만드는 커스텀 필드와 컬럼
- 공유 props, 내비게이션, 테마 토큰용 훅

</td>
<td valign="top">

**개발자 경험**
- Pest 및 PHPUnit 테스트 헬퍼
- `saddle:install`과 `saddle:upgrade`
- 업그레이드 후 오래된 에셋 경고
- Laravel 11, 12, 13

</td>
</tr>
</table>

## 사용 기술

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## 빠른 시작

PHP 8.4 이상과 Laravel 11.48 이상, 12 또는 13이 필요합니다.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

앱의 로그인으로 로그인하고, 모델에 대한 정책을 등록한 다음 `/admin`에 접속하세요. 패키지를 업데이트할 때마다 `php artisan saddle:upgrade`를 실행하세요. 각 단계는 [시작 가이드](../sections/getting-started.md)에서 자세히 안내합니다.

## 더 알아보기

| | |
|---|---|
| **구축** | [리소스](../sections/resources.md) · [필드](../sections/fields.md) · [폼 레이아웃](../sections/forms-layout.md) · [테이블](../sections/tables.md) · [필터](../sections/filters.md) · [액션](../sections/actions.md) · [관계 매니저](../sections/relations.md) |
| **패널** | [대시보드 위젯](../sections/widgets.md) · [전역 검색](../sections/search.md) · [알림](../sections/notifications.md) · [소프트 삭제](../sections/soft-deletes.md) · [가져오기와 내보내기](../sections/import-export.md) |
| **운영** | [권한 부여](../sections/authorization.md) · [멀티 테넌시](../sections/tenancy.md) · [테넌시 추가 기능](../sections/tenancy-extras.md) · [설정](../sections/configuration.md) · [테마](../sections/theming.md) · [현지화](../sections/i18n.md) |
| **확장** | [플러그인](../sections/plugins.md) · [테스트 헬퍼](../sections/testing.md) · [기여하기](../../CONTRIBUTING.md) · [보안](../../SECURITY.md) |

## 라이선스

Saddle은 [MIT 라이선스](../../LICENSE)로 배포되는 오픈 소스 소프트웨어입니다.
