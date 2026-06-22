<p align="center">
  <b>العربية</b> •
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
  <a href="README.zh-CN.md">简体中文</a>
</p>

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>شدّ السرج يا كاوبوي، وصلت إلى البلدة لوحة إدارة جديدة.</em>
</p>

---

**Saddle** هو إطار لوحة إدارة مفتوح المصدر لـ Laravel، بُني بالطريقة الحديثة من أجل **Inertia and
Vue**. اجمع نماذج Eloquent لديك في لوحات موارد مصقولة، مع منشئات للنماذج والجداول، والأدوار والوصول،
والإضافات وتعدد المستأجرين.

> **الحالة: v1.0، استيراد/تصدير CSV وإضافات للتعددية.** يوجد موقع التسويق على **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## التثبيت

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

يُكتشف مزود الخدمة تلقائياً. ينشر `saddle:install` ملف الإعدادات، وينشر أصول اللوحة، وينشئ `app/Saddle/` لفئات الموارد لديك. زُر `/admin` لرؤية اللوحة.

## تعريف مورد

ضع فئات الموارد في `app/Saddle/`. كل فئة توسع `SaddlePHP\Resource` وتنفذ `form()` و `table()`.

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

تُكتشف الموارد تلقائياً عبر فحص `app/Saddle/` عند الإقلاع، ولا حاجة إلى تسجيل يدوي.

> **مفاتيح مسارات محجوزة.** تمتلك اللوحة مقاطع المسار الثابتة `create` و `options` و `actions` تحت كل مورد، لذلك لا يمكن الوصول إلى سجل يكون مفتاح مساره حرفياً واحداً من هذه الكلمات عبر عناوين URL الخاصة بالتحرير/التحديث/الحذف. استخدم مفتاحاً عددياً صحيحاً أو slug مختلفاً لهذه السجلات.

## تخطيط النموذج

يمكن تجميع الحقول داخل حاويات تخطيط. يمكن للحاويات أن تتداخل بحرية داخل بعضها.

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

| الحاوية | الوصف |
|---|---|
| `Section` | مجموعة بطاقات ذات تسمية. تضيف `description(string)` عنواناً فرعياً. تقبل `schema([...])` من الحقول والحاويات المتداخلة. |
| `Grid` | ترتب أبناءها في شبكة CSS. تنشئ `Grid::make(2)` شبكة بعمودين. تستخدم الحقول داخل Grid الدالة `columnSpan(int)` للامتداد عبر عدة أعمدة. |
| `Tabs` | تغلف حاوية `Tab` واحدة أو أكثر ضمن واجهة مبوبة. |
| `Tab` | لوحة واحدة داخل مجموعة `Tabs`. `Tab::make('Label')->schema([...])`. عندما يفشل أي حقل داخل تبويب في التحقق، يعرض التبويب مؤشراً للخطأ كي يتمكن المستخدمون من تحديد المشكلة من دون الانتقال إلى كل لوحة. |

**ما زالت المخططات المسطحة تعمل.** تمرير قائمة عادية من الحقول إلى `$form->schema([...])` من دون حاويات مدعوم بالكامل وينتج التخطيط نفسه كما في السابق. يتعامل التحقق والتفويض مع الحقول بالطريقة نفسها سواء كانت داخل حاوية أو في المستوى الأعلى.

## الحقول

| الحقل | الوصف |
|---|---|
| `Text` | إدخال نص بسطر واحد. المعدلات: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | إدخال نص متعدد الأسطر. المعدلات: `rows(int)`. |
| `Select` | قائمة منسدلة بخيارات ثابتة. مرر مصفوفة ترابطية إلى `options(['value' => 'Label'])`. |
| `Toggle` | مفتاح منطقي. يخزن `true`/`false`. |
| `BelongsTo` | اختيار علاقة. الوسيط هو اسم دالة علاقة Eloquent على النموذج (`BelongsTo::make('rider')` يقرأ `$model->rider()` ويرسل المفتاح الأجنبي). تُحل تسميات الخيارات من `titleAttribute('name')`، ثم بالرجوع إلى مورد النموذج المرتبط المسجل `$title`، ثم إلى مفتاحه. إذا لم يتوفر `titleAttribute()` ولا مورد مسجل، تُسمى الخيارات بالمفتاح الأساسي، لذلك عيّن `titleAttribute('name')` للحصول على تسميات مقروءة. تُحد الخيارات افتراضياً عند 100، ويمكن تجاوز ذلك عبر `limit(int)`. تحول `searchable()` الحقل إلى منتقٍ غير متزامن يبحث في الجدول المرتبط أثناء الكتابة عبر endpoint موثق. عند التحرير، يُضمن الاختيار الحالي فقط، ولا تُحمّل القائمة الكاملة. تضبط `modifyOptionsQuery(fn ($query) => ...)` قائمة الخيارات للتعددية أو الرؤية. تنطبق على الخيارات المعروضة والمبحوثة، بينما يستمر اختيار السجل المحفوظ في عرض تسميته حتى عندما يقع خارج النطاق. |
| `Number` | إدخال رقمي. المعدلات: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | إدخال تاريخ. تُعرض القيم بصيغة `Y-m-d`. |
| `DateTime` | إدخال تاريخ ووقت (`datetime-local`). تُخزن القيم وتُقرأ عبر datetime cast في نموذجك. تُنسق قيمة `DateTimeInterface` إلى `Y-m-d\TH:i` للمتصفح. |
| `Markdown` | Textarea مع شريط تنسيق. تُخزن كسلسلة عادية وتُحد إلى 65 535 حرفاً، وهو ما يعادل عمود MySQL `TEXT`. |
| `FileUpload` | رفع ملف multipart. المعدلات: `disk(string)`, `directory(string)`, `image()` (يقصر على أنواع الصور), `acceptedTypes(array)` (امتدادات MIME، مثل `['pdf', 'docx']`), `maxSize(int $kilobytes)`. القيمة المخزنة هي مسار الملف الذي ترجعه `Storage::put`. في نموذج التحرير: ترك الإدخال دون لمس يبقي الملف الحالي، ومسحه يخزن `null`، واختيار ملف جديد يستبدل المسار. لا تُحذف الملفات المستبدلة أو الممسوحة من القرص تلقائياً. |

## الأعمدة

| العمود | الوصف |
|---|---|
| `TextColumn` | يعرض قيمة السمة الخام. المعدلات: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (ينسق سمات DateTime، والصيغة الافتراضية `Y-m-d H:i`). |
| `BadgeColumn` | يعرض شارة بشكل كبسولة. استخدم `colors(['value' => 'token'])` لربط قيم الخيارات برموز الألوان (`accent`, `ink`, `muted`). |
| `BooleanColumn` | يعرض علامة صح للقيم truthy وشرطة للقيم falsy. |

**أعمدة العلاقات والتحميل المسبق.** الأسماء المنقطة مثل `TextColumn::make('rider.name')` تقرأ عبر علاقة محملة. صرّح بـ `public static array $with = ['rider']` على المورد حتى يقوم استعلام الفهرس بالتحميل المسبق للعلاقة قبل العرض. أعمدة العلاقات ليست قابلة للفرز أو البحث بعد.

## الفلاتر

تُعلن الفلاتر على الجدول عبر `->filters([...])`. في الفهرس، تطبقها اللوحة من معاملات query string بصيغة `filter[name]=value`. تُتحقق القيم المطلوبة مقابل الإعلان، لذلك تُتجاهل أسماء الفلاتر غير المعروفة وقيم الخيارات غير المعلنة بصمت.

| الفلتر | الوصف |
|---|---|
| `SelectFilter` | قائمة منسدلة للمطابقة التامة. تحدد `options(['value' => 'Label'])` خيارات القائمة المنسدلة وقائمة القيم المقبولة معاً. |
| `BooleanFilter` | قائمة منسدلة نعم/لا فوق عمود منطقي. |

## الإجراءات

تظهر الإجراءات كأزرار على كل صف في جدول الفهرس. تظهر الإجراءات الجماعية في شريط أدوات عندما يُحدد صف واحد أو أكثر.

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

| Fluent | الوصف |
|---|---|
| `label(string)` | تسمية العرض الظاهرة على الزر. عند حذفها، يُحول الاسم إلى title case. |
| `color(string)` | رمز لون للزر: `accent` أو `ink` أو `muted`. الافتراضي هو `ink`. |
| `requiresConfirmation(?string)` | يعرض مربع تأكيد قبل التشغيل. مرر رسالة مخصصة أو احذفها لاستخدام المطالبة الافتراضية. |
| `authorize(string)` | يسمي قدرة سياسة تُفحص لكل سجل قبل تشغيل المعالج. |
| `successMessage(string)` | رسالة flash تُعرض بعد تشغيل ناجح. الافتراضي هو `Done.`. |

ترسل الإجراءات POST إلى endpoint محمي. تُحل السجلات عبر استعلام الأساس ذي النطاق نفسه المستخدم في كل مكان آخر، لذلك تُطبق التعددية والفلاتر ونطاقات الاستعلام الخاصة بكل مورد تلقائياً. عند إعلان `authorize('ability')`، تُفحص السياسة لكل سجل قبل تشغيل المعالج. تُنفذ التشغيلات الجماعية داخل معاملة قاعدة بيانات وتُحد عند 100 سجل لكل طلب. إذا كان أي سجل مطلوب مفقوداً من الجلب ذي النطاق، تُلغى العملية كلها مع 404 بدلاً من تطبيقها بصمت على المجموعة الفرعية المحلولة. أعلن `authorize()` على أي إجراء تدميري.

`BulkAction::delete()` إعداد مسبق جاهز: الاسم `delete`، التسمية `Delete`، اللون `accent`، التأكيد `Delete the selected records?`، و `authorize('delete')` موصلة مسبقاً.

## التفويض

يستخدم Saddle سياسات Laravel القياسية. سجّل سياسة لنموذج، وستفرضها اللوحة في كل مكان: الفهرس، والنماذج، وإجراءات الصفوف، ومنتقيات العلاقات. من دون سياسة مسجلة، تُسمح كل القدرات لكل مستخدم موثق. تبقى الأدوار في تطبيقك: أي حزمة أدوار أو طبقة محلية تدعم سياساتك تعمل من دون تغيير.

### إغلاق اللوحة

تسمح الموارد التي لا تملك سياسة مسجلة لكل مستخدم موثق افتراضياً. اضبط `saddle.authorization.require_policy` على `true` للفشل مغلقاً: الموارد التي لا تملك سياسة تصبح غير قابلة للوصول بدلاً من أن تكون مفتوحة. يمكنك أيضاً إضافة middleware gate إلى `saddle.middleware` لإجراء فحص شامل قبل تشغيل أي مسار للوحة. إذا كان web guard لديك مشتركاً بين المستخدمين النهائيين والمديرين، فإن أحد هذين التحكمين أساسي.

| القدرة | أين تُفحص |
|---|---|
| `viewAny` | صفحة فهرس المورد، رؤية الشريط الجانبي |
| `create` | نموذج الإنشاء، إجراء store، endpoint خيارات العلاقة |
| `update` | نموذج التحرير، إجراء update، رابط Edit لكل صف، endpoint خيارات العلاقة (تُفحص مقابل نموذج جديد عندما لا يكون أي سجل ضمن النطاق) |
| `delete` | إجراء destroy، زر Delete لكل صف |

### رؤية الحقول باستخدام `canSee`

يمكن تقييد الحقول الفردية لكل طلب باستخدام `canSee`. الحقل `notes` في `HorseResource` مثال عملي:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

تُزال الحقول المخفية من حمولة النموذج (القيم المخزنة لا تُسلسل أبداً إلى الواجهة الأمامية)، ولا تضيف قواعد تحقق، ولا تُكتب أبداً عند الحفظ، ويعيد endpoint خيارات العلاقة الخاص بها 404. قد يعمل callback عدة مرات لكل طلب، لذلك اجعله رخيصاً وأرجع قيمة منطقية حقيقية. على سبيل المثال، استخدم `Gate::allows('view-notes', $model)` بدلاً من `Gate::inspect(...)`، إذ إن كائن `Response` الخاص بها truthy دائماً ولن يخفي الحقل أبداً.

## الإضافات

الإضافة هي حزمة Composer عادية. يسجل مزود الخدمة الخاص بها الموارد والسكربتات والأنماط عبر واجهة `Saddle`، ويشغلها اكتشاف حزم Laravel التلقائي تلقائياً مع تطبيقك.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

انشر الأصول المجمعة من مزود خدمة الإضافة إلى `public/vendor/{plugin}` باستخدام آلية `$this->publishes([...])` القياسية، ثم وجّه `Saddle::script()` إلى المسار المنشور. تُحمّل سكربتات الإضافة وملفات الأنماط في كل صفحة لوحة بعد حزمة اللوحة الأساسية.

### عناصر مخصصة

يمكن للإضافات شحن عارضات الحقول والأعمدة الخاصة بها كعناصر مخصصة. من جهة PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

تفي اللوحة بهذا العقد: بالنسبة للحقول، تضبط خصائص DOM للعنصر `value` و `field` وتستمع إلى CustomEvent باسم `saddle:input` يكون `detail` فيه هو القيمة الجديدة. بالنسبة للأعمدة، تضبط خصائص DOM `value` و `column` (للقراءة فقط، ولا يُتوقع حدث إدخال).

عنصر مخصص vanilla بسيط يطبق عقد الحقل:

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

عرّف العناصر في المستوى الأعلى من السكربت لديك. يرقّي المتصفح أي عناصر مطابقة عرضتها اللوحة مسبقاً بمجرد تشغيل `customElements.define`، لذلك لا يهم ترتيب التحميل أبداً.

العقد مستقل عن إطار العمل. أي شيء يُترجم إلى عنصر مخصص قياسي يعمل: `defineCustomElement` في Vue، و Lit، وملفات تغليف React أو Svelte. مؤلفو الإضافات غير مرتبطين بدواخل اللوحة.

## تعدد المستأجرين

يدعم Saddle تعدد مستأجرين اختيارياً ومحدداً عبر URL. فعّله بتوجيه `tenancy.model` إلى أي فئة Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

عندما يكون تعدد المستأجرين نشطاً، تُركب اللوحة تحت `/admin/{tenant}` بدلاً من `/admin`. يُحل المقطع `{tenant}` عبر lookup لمفتاح المسار على النموذج المكوّن. مفاتيح المستأجرين غير المعروفة تعيد **404**. المستخدمون الموثقون الذين ليسوا أعضاء في المستأجر المحلول يُرفضون مع **403**.

### تحديد نطاق الموارد

صرّح بعلاقة BelongsTo الخاصة بالسجل إلى المستأجر على كل مورد تريد تحديد نطاقه:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

الموارد التي لا تحتوي على `$tenant` (جداول lookup المشتركة، الإعدادات العامة) تبقى غير محددة النطاق حسب التصميم.

### ضمانات النطاق التلقائي

يفحص كل مسار بيانات المستأجر المرتبط من جهة الخادم:

- **الفهرس والبحث والفلاتر** تمر عبر استعلام الأساس ذي النطاق (`whereBelongsTo` على العلاقة المعلنة).
- **عمليات lookup للسجلات** للتحرير والتحديث والحذف تُحل عبر الاستعلام ذي النطاق نفسه، لذلك تعيد IDs العابرة للمستأجرين 404 قبل تشغيل أي سياسة.
- **Stores** تختم المستأجر الحالي من جهة الخادم بعد تعبئة النموذج. أي مفتاح أجنبي للمستأجر يرسله العميل يُستبدل.
- **قوائم خيارات العلاقات** تطبق النطاق نفسه عندما يكون المورد المسجل للنموذج المرتبط محدد النطاق بالمستأجر أيضاً.

### مبدل المستأجر

عندما ينتمي المستخدم الموثق إلى أكثر من مستأجر واحد، يعرض الشريط الجانبي للوحة select يسرد كل عضوياته. يؤدي التبديل إلى الانتقال إلى مسار اللوحة نفسه تحت المستأجر المحدد.

### تنبيهات

- **لا تعرض علاقة `$tenant` كحقل نموذج على مورد محدد النطاق.** يختم متحكم store العلاقة من جهة الخادم، لكن حقل BelongsTo قابل للتحرير يشير إلى علاقة المستأجر في نموذج update سيسمح لقيمة مرسلة بإعادة توجيه السجل إلى مستأجر مختلف.
- **ما زالت تسمية علاقة محفوظة تُعرض في نموذج التحرير حتى عندما يقع الصف المرتبط خارج النطاق الحالي.** يحل `BelongsTo` الاختيار الحالي باستعلام غير محدد النطاق حتى لا تختفي التسمية بعد تغيير النطاق. تُرشح قائمة الخيارات فقط للاختيارات الجديدة.
- **يتطلب تغيير إعداد تعدد المستأجرين `php artisan route:clear`**، لأن بادئة `{tenant}` تُقرر عند الإقلاع. يجب على خوادم التطبيقات طويلة التشغيل (عمال FPM الباقون أحياء بين الطلبات) ضمان إعادة ضبط حالة الطلب بين الطلبات. يعيش المستأجر المرتبط على singleton الخاص بـ Saddle، والذي يُحل من جديد لكل طلب ضمن عمر الحاوية الافتراضي. عند تثبيت Octane، تعيد اللوحة ضبط المستأجر المرتبط تلقائياً عبر خطافات دورة حياة طلبات Octane.

## الإعدادات

ينشر `saddle:install` الملف `config/saddle.php`. المفاتيح المتاحة:

| المفتاح | الافتراضي | الوصف |
|---|---|---|
| `path` | `'admin'` | بادئة URL للوحة (مثلاً `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | مكدس middleware المطبق على كل مسارات اللوحة. |
| `resources.path` | `app_path('Saddle')` | مسار نظام الملفات الذي يُفحص بحثاً عن فئات الموارد. |
| `resources.namespace` | `'App\\Saddle'` | مساحة أسماء PHP المطابقة لـ `resources.path`. |
| `per_page` | `25` | عدد الصفوف الافتراضي لكل صفحة في جداول الفهرس. |
| `brand.name` | `'Saddle'` | اسم اللوحة (الشريط الجانبي وتبويب المتصفح). |
| `brand.accent` | `'#d9501f'` | لون التمييز (الأزرار، الحالات النشطة). |
| `uploads.disk` | `'public'` | قرص نظام الملفات الافتراضي المستخدم بواسطة حقول `FileUpload` عندما لا يُضبط `disk()` لكل حقل. |
| `uploads.directory` | `'saddle'` | دليل الرفع الافتراضي داخل القرص عندما لا يُضبط `directory()` لكل حقل. |

## الأوامر

| الأمر | الوصف |
|---|---|
| `saddle:install` | ينشر الإعدادات، وينشر أصول اللوحة، وينشئ `app/Saddle/`. يعرض إضافة `saddle:upgrade` إلى `composer post-update-cmd` كي تبقى الأصول حديثة. |
| `saddle:upgrade` | يعيد نشر أصول اللوحة. شغله بعد كل تحديث للحزمة. |
| `saddle:resource NameResource --model=Name` | ينشئ هيكل فئة مورد جديدة. الخيار `--model` اختياري. يُستنتج من اسم المورد عند حذفه. |

**ملاحظة النشر.** أضف `php artisan saddle:upgrade` إلى سكربت النشر لديك بعد `composer install` أو `composer update`. تعرض اللوحة شريط تحذير في UI عندما تكون الأصول المنشورة غير متزامنة مع إصدار الحزمة المثبت.

## التطوير المحلي

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

يحتوي الدليل `workbench/` على تطبيق مضيف بسيط يستخدمه طقم الاختبارات وللتجربة اليدوية. يشغله `vendor/bin/testbench serve` مع تسجيل `HorseResource`. لاحظ أن مسارات اللوحة تقع خلف middleware باسم `auth` وأن workbench لا يوفر إلا مسار `/login` وهمياً، لذلك للتصفح التفاعلي اضبط مؤقتاً `'middleware' => ['web']` في `config/saddle.php` أو تصفح عبر اختبارات الميزات بدلاً من ذلك. لا يوجد demo seeder بعد.

## الحزمة التقنية

مبني من أجل **Laravel 13+ / PHP 8.4+** و **Inertia 2** و **Vue 3** و **Tailwind CSS 4**.

## الترخيص

MIT.
