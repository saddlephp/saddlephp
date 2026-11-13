<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Otwartoźródłowy framework paneli administracyjnych dla Laravela, stworzony z myślą o Inertia i Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Dokumentacja</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Lista zmian</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Współtworzenie</b></a>
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
  <b>Polski</b> •
  <a href="README.pt-BR.md">Português (BR)</a> •
  <a href="README.ru.md">Русский</a> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="Pulpit Saddle z widżetami statystyk i wykresem" width="100%">
  </picture>
</p>

<p align="center"><em>Siodłaj konia, kowboju: w mieście pojawił się nowy panel administracyjny.</em></p>

## Czym jest Saddle?

Saddle zamienia Twoje modele Eloquent w dopracowany panel administracyjny. Każdy model opisujesz tylko raz, jako klasę zasobu PHP z formularzem i tabelą, a Saddle buduje ekrany listy, tworzenia, edycji i podglądu, a także wyszukiwanie, filtry i akcje. Renderuje wszystko za pomocą Inertia i Vue, korzysta z Twojego istniejącego logowania i polityk Laravela i nie ingeruje w resztę aplikacji.

<table>
<tr>
<td width="50%" valign="middle">

### Tabele, które wykonują pracę za Ciebie

Sortowalne i przeszukiwalne kolumny z odznakami i wartościami logicznymi, filtry, akcje wierszy oraz akcje zbiorcze z potwierdzeniem. Eksportuj bieżący widok do CSV lub importuj plik CSV przez tę samą walidację, której używa formularz.

[Tabele i kolumny →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Tabela zasobu Saddle z filtrami i paskiem akcji zbiorczych">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Formularz edycji Saddle z sekcją, dwukolumnową siatką i kartami">
</picture>
</td>
<td width="50%" valign="middle">

### Formularze z prawdziwym układem

Jedenaście typów pól, od tekstu i list wyboru po przeszukiwalne selektory relacji, przesyłanie plików i Markdown. Układaj je w sekcje, siatki i karty. Walidacja, widoczność i zapis opierają się na tych samych definicjach pól.

[Pola →](../sections/fields.md) · [Układ formularzy →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Jedna klasa na model

Zasób to zwykły PHP. Umieść go w `app/Saddle/`, a zostanie wykryty podczas uruchamiania: bez pisania tras, kontrolerów czy plików Vue. Dostęp opiera się na Twoich politykach Laravela i domyślnie jest zablokowany.

[Zasoby →](../sections/resources.md) · [Autoryzacja →](../sections/authorization.md)

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

## Funkcje

<table>
<tr>
<td width="33%" valign="top">

**Zasoby**
- Automatycznie wykrywane klasy zasobów
- Strony listy, tworzenia, edycji i podglądu tylko do odczytu
- Generator `saddle:resource`
- Eager loading i kolumny relacji

</td>
<td width="33%" valign="top">

**Formularze**
- Tekst, hasło, pole tekstowe, liczba, lista wyboru, przełącznik
- Data, data i godzina, Markdown, przesyłanie plików
- Przeszukiwalny selektor `BelongsTo`
- Sekcje, siatki i karty

</td>
<td width="33%" valign="top">

**Tabele**
- Kolumny tekstowe, z odznakami i logiczne
- Sortowanie, wyszukiwanie i `formatUsing`
- Filtry wyboru i logiczne
- Paginacja

</td>
</tr>
<tr>
<td valign="top">

**Akcje**
- Akcje wierszy i akcje zbiorcze
- Okna potwierdzenia
- Sprawdzanie polityk dla każdego rekordu
- Zbiorcze operacje w transakcji

</td>
<td valign="top">

**Relacje**
- Menedżery relacji `HasMany`
- Tworzenie, edycja i usuwanie powiązanych rekordów na miejscu
- Zawężone do rekordu nadrzędnego

</td>
<td valign="top">

**Pulpit**
- Widżety statystyk z miniwykresami
- Widżety wykresów
- Powiadomienia z bazy danych z dzwonkiem
- Globalne wyszukiwanie we wszystkich zasobach

</td>
</tr>
<tr>
<td valign="top">

**Dostęp**
- Standardowe polityki Laravela
- Domyślnie zablokowany
- Widoczność poszczególnych pól z `canSee`
- Korzysta z Twojego istniejącego logowania

</td>
<td valign="top">

**Wielodostępność**
- Najemcy wyznaczani przez URL (`/admin/{tenant}`)
- Automatyczne zawężanie zapytań i przypisywanie najemcy
- Przełącznik najemców i samodzielna rejestracja
- Hook bramki dostępu do obsługi płatności

</td>
<td valign="top">

**Dane**
- Eksport bieżącego widoku do CSV
- Import CSV przez walidację formularza
- Miękkie usuwanie z przywracaniem i trwałym usuwaniem

</td>
</tr>
<tr>
<td valign="top">

**Wygląd i język**
- Motyw jasny, ciemny i systemowy
- Nazwa marki, kolor akcentu i tokeny motywu
- Panel przetłumaczony na 14 języków
- Obsługa nonce CSP

</td>
<td valign="top">

**Wtyczki**
- Zwykłe pakiety Composera
- Własne pola i kolumny jako komponenty webowe
- Hooki dla współdzielonych props, nawigacji i tokenów motywu

</td>
<td valign="top">

**Wygoda programisty**
- Pomocniki testowe dla Pest i PHPUnit
- `saddle:install` i `saddle:upgrade`
- Ostrzeżenie o nieaktualnych zasobach po aktualizacji
- Laravel 11, 12 i 13

</td>
</tr>
</table>

## Zbudowany z użyciem

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Szybki start

Wymaga PHP 8.4+ oraz Laravela 11.48+, 12 lub 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Zaloguj się przez logowanie swojej aplikacji, zarejestruj politykę dla modelu, a następnie odwiedź `/admin`. Po każdej aktualizacji pakietu uruchom `php artisan saddle:upgrade`. [Przewodnik na start](../sections/getting-started.md) omawia każdy krok.

## Dowiedz się więcej

| | |
|---|---|
| **Budowanie** | [Zasoby](../sections/resources.md) · [Pola](../sections/fields.md) · [Układ formularzy](../sections/forms-layout.md) · [Tabele](../sections/tables.md) · [Filtry](../sections/filters.md) · [Akcje](../sections/actions.md) · [Menedżery relacji](../sections/relations.md) |
| **Panel** | [Widżety pulpitu](../sections/widgets.md) · [Wyszukiwanie globalne](../sections/search.md) · [Powiadomienia](../sections/notifications.md) · [Miękkie usuwanie](../sections/soft-deletes.md) · [Import i eksport](../sections/import-export.md) |
| **Uruchamianie** | [Autoryzacja](../sections/authorization.md) · [Wielodostępność](../sections/tenancy.md) · [Dodatki do wielodostępności](../sections/tenancy-extras.md) · [Konfiguracja](../sections/configuration.md) · [Motywy](../sections/theming.md) · [Lokalizacja](../sections/i18n.md) |
| **Rozszerzanie** | [Wtyczki](../sections/plugins.md) · [Pomocniki testowe](../sections/testing.md) · [Współtworzenie](../../CONTRIBUTING.md) · [Bezpieczeństwo](../../SECURITY.md) |

## Licencja

Saddle to oprogramowanie open source udostępniane na [licencji MIT](../../LICENSE).
