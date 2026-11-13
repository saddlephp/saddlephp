<p align="center">
  <img src="../../.github/og.png" alt="Saddle, there's a new admin panel in town" width="820">
</p>

<h3 align="center">Фреймворк админ-панелей для Laravel с открытым исходным кодом, созданный для Inertia и Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Документация</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Список изменений</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Участие в разработке</b></a>
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
  <a href="README.pt-BR.md">Português (BR)</a> •
  <b>Русский</b> •
  <a href="README.tr.md">Türkçe</a> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

## Что такое Saddle?

Saddle превращает ваши модели Eloquent в аккуратную админ-панель. Вы один раз описываете каждую модель в виде PHP-класса ресурса с формой и таблицей, а Saddle сам строит экраны списка, создания, редактирования и просмотра, а также поиск, фильтры и действия. Интерфейс отрисовывается через Inertia и Vue, использует уже настроенные в Laravel вход и политики и не вмешивается в остальную часть приложения.

<table>
<tr>
<td width="50%" valign="middle">

### Таблицы, которые делают работу за вас

Сортируемые колонки с поиском, бейджами и булевыми значениями, фильтры, действия над строками и массовые действия с подтверждением. Экспортируйте текущее представление в CSV или импортируйте CSV через ту же валидацию, что и в форме.

[Таблицы и колонки →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Таблица ресурса Saddle с фильтрами и панелью массовых действий">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Форма редактирования Saddle с секцией, сеткой в две колонки и вкладками">
</picture>
</td>
<td width="50%" valign="middle">

### Формы с настоящей вёрсткой

Одиннадцать типов полей: от текста и выпадающих списков до выбора связей с поиском, загрузки файлов и Markdown. Раскладывайте их по секциям, сеткам и вкладкам. Валидация, видимость и сохранение опираются на одни и те же описания полей.

[Поля →](../sections/fields.md) · [Вёрстка форм →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Один класс на модель

Ресурс представляет собой обычный PHP-класс. Положите его в `app/Saddle/`, и он будет найден при загрузке: не нужно писать ни маршруты, ни контроллеры, ни Vue-файлы. Доступ определяется вашими политиками Laravel и по умолчанию закрыт.

[Ресурсы →](../sections/resources.md) · [Авторизация →](../sections/authorization.md)

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

## Возможности

<table>
<tr>
<td width="33%" valign="top">

**Ресурсы**
- Автоматическое обнаружение классов ресурсов
- Страницы списка, создания, редактирования и просмотра только для чтения
- Генератор `saddle:resource`
- Жадная загрузка и колонки связей

</td>
<td width="33%" valign="top">

**Формы**
- Текст, пароль, textarea, число, select, переключатель
- Дата, дата и время, Markdown, загрузка файлов
- Выбор `BelongsTo` с поиском
- Секции, сетки и вкладки

</td>
<td width="33%" valign="top">

**Таблицы**
- Текстовые, бейдж- и булевы колонки
- Сортировка, поиск и `formatUsing`
- Фильтры-списки и булевы фильтры
- Пагинация

</td>
</tr>
<tr>
<td valign="top">

**Действия**
- Действия над строками и массовые действия
- Диалоги подтверждения
- Проверка политик для каждой записи
- Массовые операции в транзакции

</td>
<td valign="top">

**Связи**
- Менеджеры связей `HasMany`
- Создание, редактирование и удаление связанных записей прямо на месте
- Ограничение областью родительской записи

</td>
<td valign="top">

**Дашборд**
- Виджеты статистики с мини-графиками
- Виджеты графиков
- Уведомления из базы данных с колокольчиком
- Глобальный поиск по всем ресурсам

</td>
</tr>
<tr>
<td valign="top">

**Доступ**
- Стандартные политики Laravel
- По умолчанию всё закрыто
- Видимость отдельных полей через `canSee`
- Используется ваш существующий вход

</td>
<td valign="top">

**Мультиарендность**
- Арендаторы в URL (`/admin/{tenant}`)
- Автоматическое ограничение запросов и привязка записей к арендатору
- Переключатель арендаторов и самостоятельная регистрация
- Хук проверки доступа для биллинга

</td>
<td valign="top">

**Данные**
- Экспорт текущего представления в CSV
- Импорт CSV с валидацией формы
- Мягкое удаление с восстановлением и окончательным удалением

</td>
</tr>
<tr>
<td valign="top">

**Внешний вид и язык**
- Светлая, тёмная и системная темы
- Название бренда, акцентный цвет и токены темы
- Панель переведена на 14 языков
- Поддержка CSP nonce

</td>
<td valign="top">

**Плагины**
- Обычные пакеты Composer
- Собственные поля и колонки в виде веб-компонентов
- Хуки для общих props, навигации и токенов темы

</td>
<td valign="top">

**Удобство разработки**
- Хелперы для тестов на Pest и PHPUnit
- `saddle:install` и `saddle:upgrade`
- Предупреждение об устаревших ассетах после обновлений
- Laravel 11, 12 и 13

</td>
</tr>
</table>

## Технологии

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Быстрый старт

Требуется PHP 8.4+ и Laravel 11.48+, 12 или 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Войдите через форму входа вашего приложения, зарегистрируйте политику для модели и откройте `/admin`. После каждого обновления пакета запускайте `php artisan saddle:upgrade`. [Руководство по началу работы](../sections/getting-started.md) подробно разбирает каждый шаг.

## Подробнее

| | |
|---|---|
| **Разработка** | [Ресурсы](../sections/resources.md) · [Поля](../sections/fields.md) · [Вёрстка форм](../sections/forms-layout.md) · [Таблицы](../sections/tables.md) · [Фильтры](../sections/filters.md) · [Действия](../sections/actions.md) · [Менеджеры связей](../sections/relations.md) |
| **Панель** | [Виджеты дашборда](../sections/widgets.md) · [Глобальный поиск](../sections/search.md) · [Уведомления](../sections/notifications.md) · [Мягкое удаление](../sections/soft-deletes.md) · [Импорт и экспорт](../sections/import-export.md) |
| **Эксплуатация** | [Авторизация](../sections/authorization.md) · [Мультиарендность](../sections/tenancy.md) · [Дополнительно о мультиарендности](../sections/tenancy-extras.md) · [Конфигурация](../sections/configuration.md) · [Темы оформления](../sections/theming.md) · [Локализация](../sections/i18n.md) |
| **Расширение** | [Плагины](../sections/plugins.md) · [Хелперы для тестов](../sections/testing.md) · [Участие в разработке](../../CONTRIBUTING.md) · [Безопасность](../../SECURITY.md) |

## Лицензия

Saddle является программным обеспечением с открытым исходным кодом и распространяется по [лицензии MIT](../../LICENSE).
