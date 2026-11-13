<p align="center">
  <img src="../../.github/og.png" alt="Saddle, there's a new admin panel in town" width="820">
</p>

<h3 align="center">إطار عمل مفتوح المصدر للوحات الإدارة في Laravel، مبني لـ Inertia وVue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>التوثيق</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>سجل التغييرات</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>المساهمة</b></a>
</p>

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

## ما هو Saddle؟

يحوّل Saddle نماذج Eloquent لديك إلى لوحة إدارة متقنة. تصف كل نموذج مرة واحدة فقط، على شكل صنف موارد PHP يحتوي على نموذج إدخال وجدول، فيبني Saddle شاشات العرض والإنشاء والتعديل والاستعراض، إضافة إلى البحث والمرشحات والإجراءات. يعتمد في العرض على Inertia وVue، ويستخدم تسجيل الدخول والسياسات الموجودة في تطبيق Laravel لديك، ولا يتدخل في بقية تطبيقك.

<table>
<tr>
<td width="50%" valign="middle">

### جداول تنجز العمل

أعمدة قابلة للفرز والبحث مع شارات وقيم منطقية، ومرشحات، وإجراءات على مستوى الصف، وإجراءات جماعية مع التأكيد. صدّر العرض الحالي إلى ملف CSV، أو استورد ملف CSV عبر قواعد التحقق نفسها التي يستخدمها نموذج الإدخال.

[الجداول والأعمدة →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="جدول موارد في Saddle مع مرشحات وشريط للإجراءات الجماعية">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="نموذج تعديل في Saddle يضم قسمًا وشبكة من عمودين وتبويبات">
</picture>
</td>
<td width="50%" valign="middle">

### نماذج إدخال بتخطيط حقيقي

أحد عشر نوعًا من الحقول، من النصوص والقوائم المنسدلة إلى منتقيات العلاقات القابلة للبحث ورفع الملفات وMarkdown. رتّبها في أقسام وشبكات وتبويبات. يعتمد التحقق والظهور والحفظ كلها على تعريفات الحقول نفسها.

[الحقول →](../sections/fields.md) · [تخطيط النماذج →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### صنف واحد لكل نموذج

المورد مجرد كود PHP عادي. ضعه في `app/Saddle/` وسيُكتشف تلقائيًا عند الإقلاع: لا مسارات ولا متحكمات ولا ملفات Vue تكتبها. يخضع الوصول لسياسات Laravel لديك ويكون مغلقًا افتراضيًا.

[الموارد →](../sections/resources.md) · [التفويض →](../sections/authorization.md)

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

## الميزات

<table>
<tr>
<td width="33%" valign="top">

**الموارد**
- أصناف موارد تُكتشف تلقائيًا
- صفحات للعرض والإنشاء والتعديل والاستعراض للقراءة فقط
- مولّد `saddle:resource`
- التحميل المسبق (eager loading) وأعمدة العلاقات

</td>
<td width="33%" valign="top">

**النماذج**
- نص، كلمة مرور، منطقة نص، رقم، قائمة اختيار، مفتاح تبديل
- تاريخ، تاريخ ووقت، Markdown، رفع ملفات
- منتقي `BelongsTo` قابل للبحث
- أقسام وشبكات وتبويبات

</td>
<td width="33%" valign="top">

**الجداول**
- أعمدة نصية وشارات وقيم منطقية
- الفرز والبحث و`formatUsing`
- مرشحات اختيار ومرشحات منطقية
- ترقيم الصفحات

</td>
</tr>
<tr>
<td valign="top">

**الإجراءات**
- إجراءات على مستوى الصف وإجراءات جماعية
- مربعات حوار للتأكيد
- فحص السياسات لكل سجل
- تنفيذ جماعي داخل معاملة

</td>
<td valign="top">

**العلاقات**
- مديرو علاقات `HasMany`
- إنشاء السجلات المرتبطة وتعديلها وحذفها في مكانها
- محصورة في السجل الأب

</td>
<td valign="top">

**لوحة المعلومات**
- أدوات إحصائية مع رسوم بيانية مصغّرة
- أدوات رسوم بيانية
- إشعارات قاعدة البيانات مع جرس تنبيه
- بحث شامل عبر جميع الموارد

</td>
</tr>
<tr>
<td valign="top">

**الوصول**
- سياسات Laravel القياسية
- مغلق افتراضيًا
- التحكم في ظهور كل حقل عبر `canSee`
- يستخدم تسجيل الدخول الحالي لديك

</td>
<td valign="top">

**تعدد المستأجرين**
- مستأجرون محددون عبر الرابط (`/admin/{tenant}`)
- حصر الاستعلامات تلقائيًا وربط السجلات بالمستأجر
- مبدّل المستأجرين والتسجيل الذاتي
- نقطة ربط للتحكم في الوصول لأغراض الفوترة

</td>
<td valign="top">

**البيانات**
- تصدير العرض الحالي إلى CSV
- استيراد CSV عبر قواعد التحقق في النموذج
- الحذف الناعم مع الاستعادة والحذف النهائي

</td>
</tr>
<tr>
<td valign="top">

**المظهر واللغة**
- سمات فاتحة وداكنة وحسب النظام
- اسم العلامة ولون التمييز ورموز السمة
- لوحة مترجمة إلى 14 لغة
- دعم nonce الخاص بـ CSP

</td>
<td valign="top">

**الإضافات**
- حزم Composer عادية
- حقول وأعمدة مخصصة على شكل web components
- نقاط ربط للخصائص المشتركة والتنقل ورموز السمة

</td>
<td valign="top">

**تجربة المطوّر**
- أدوات اختبار مساعدة لـ Pest وPHPUnit
- `saddle:install` و`saddle:upgrade`
- تحذير من الأصول القديمة بعد الترقية
- Laravel 11 و12 و13

</td>
</tr>
</table>

## مبني باستخدام

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## البدء السريع

يتطلب PHP 8.4+ وLaravel 11.48+ أو 12 أو 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

سجّل الدخول عبر صفحة تسجيل الدخول في تطبيقك، وسجّل سياسة للنموذج، ثم افتح `/admin`. شغّل `php artisan saddle:upgrade` بعد كل تحديث للحزمة. يشرح [دليل البدء](../sections/getting-started.md) كل خطوة بالتفصيل.

## اعرف المزيد

| | |
|---|---|
| **البناء** | [الموارد](../sections/resources.md) · [الحقول](../sections/fields.md) · [تخطيط النماذج](../sections/forms-layout.md) · [الجداول](../sections/tables.md) · [المرشحات](../sections/filters.md) · [الإجراءات](../sections/actions.md) · [مديرو العلاقات](../sections/relations.md) |
| **اللوحة** | [أدوات لوحة المعلومات](../sections/widgets.md) · [البحث الشامل](../sections/search.md) · [الإشعارات](../sections/notifications.md) · [الحذف الناعم](../sections/soft-deletes.md) · [الاستيراد والتصدير](../sections/import-export.md) |
| **التشغيل** | [التفويض](../sections/authorization.md) · [تعدد المستأجرين](../sections/tenancy.md) · [إضافات تعدد المستأجرين](../sections/tenancy-extras.md) · [الإعدادات](../sections/configuration.md) · [السمات](../sections/theming.md) · [الترجمة](../sections/i18n.md) |
| **التوسعة** | [الإضافات](../sections/plugins.md) · [أدوات الاختبار المساعدة](../sections/testing.md) · [المساهمة](../../CONTRIBUTING.md) · [الأمان](../../SECURITY.md) |

## الترخيص

Saddle برنامج مفتوح المصدر مرخّص بموجب [رخصة MIT](../../LICENSE).
