<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../art/logo-dark.png">
    <img src="../../art/logo.png" alt="Saddle" width="220">
  </picture>
</p>

<h3 align="center">Laravel için açık kaynaklı yönetim paneli framework'ü; Inertia ve Vue için tasarlandı.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Belgeler</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Değişiklik günlüğü</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Katkıda bulunma</b></a>
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
  <a href="README.ru.md">Русский</a> •
  <b>Türkçe</b> •
  <a href="README.zh-CN.md">简体中文</a>
</p>

<br>

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/dashboard-dark.png">
    <img src="../../.github/screenshots/dashboard-light.png" alt="İstatistik widget'ları ve bir grafik içeren Saddle paneli" width="100%">
  </picture>
</p>

<p align="center"><em>Eyerle atını kovboy, kasabada yeni bir yönetim paneli var.</em></p>

## Saddle nedir?

Saddle, Eloquent modellerinizi özenle hazırlanmış bir yönetim paneline dönüştürür. Her modeli yalnızca bir kez, form ve tablo içeren bir PHP resource sınıfı olarak tanımlarsınız; Saddle da listeleme, oluşturma, düzenleme ve görüntüleme ekranlarını, aramayı, filtreleri ve eylemleri sizin için kurar. Arayüz Inertia ve Vue ile çizilir, mevcut Laravel girişinizi ve policy'lerinizi kullanır ve uygulamanızın geri kalanına karışmaz.

<table>
<tr>
<td width="50%" valign="middle">

### İşi gören tablolar

Rozet ve boolean değerler gösterebilen, sıralanabilir ve aranabilir sütunlar; filtreler, satır eylemleri ve onaylı toplu eylemler. Geçerli görünümü CSV olarak dışa aktarın ya da bir CSV dosyasını formunuzun kullandığı doğrulamadan geçirerek içe aktarın.

[Tablolar ve sütunlar →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Filtreler ve toplu eylem çubuğu içeren bir Saddle resource tablosu">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Bir bölüm, iki sütunlu ızgara ve sekmeler içeren bir Saddle düzenleme formu">
</picture>
</td>
<td width="50%" valign="middle">

### Gerçek bir düzene sahip formlar

Metin ve seçim kutularından aranabilir ilişki seçicilere, dosya yüklemeye ve Markdown'a kadar on bir alan türü. Bunları bölümler, ızgaralar ve sekmeler halinde düzenleyin. Doğrulama, görünürlük ve kaydetme aynı alan tanımlarını izler.

[Alanlar →](../sections/fields.md) · [Form düzeni →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Her model için tek sınıf

Bir resource sade bir PHP sınıfıdır. `app/Saddle/` içine koyun, açılışta otomatik olarak bulunsun: yazmanız gereken route, controller ya da Vue dosyası yok. Erişim Laravel policy'lerinizi izler ve varsayılan olarak kapalıdır.

[Resource'lar →](../sections/resources.md) · [Yetkilendirme →](../sections/authorization.md)

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

## Özellikler

<table>
<tr>
<td width="33%" valign="top">

**Resource'lar**
- Otomatik keşfedilen resource sınıfları
- Listeleme, oluşturma, düzenleme ve salt okunur görüntüleme sayfaları
- `saddle:resource` oluşturucusu
- Eager loading ve ilişki sütunları

</td>
<td width="33%" valign="top">

**Formlar**
- Metin, parola, textarea, sayı, select, toggle
- Tarih, tarih-saat, Markdown, dosya yükleme
- Aranabilir `BelongsTo` seçici
- Bölümler, ızgaralar ve sekmeler

</td>
<td width="33%" valign="top">

**Tablolar**
- Metin, rozet ve boolean sütunları
- Sıralama, arama ve `formatUsing`
- Seçim ve boolean filtreleri
- Sayfalama

</td>
</tr>
<tr>
<td valign="top">

**Eylemler**
- Satır eylemleri ve toplu eylemler
- Onay pencereleri
- Kayıt bazında policy kontrolleri
- Transaction içinde toplu işlemler

</td>
<td valign="top">

**İlişkiler**
- `HasMany` ilişki yöneticileri
- İlişkili kayıtları yerinde oluşturma, düzenleme ve silme
- Üst kayıtla sınırlı kapsam

</td>
<td valign="top">

**Dashboard**
- Mini grafikli istatistik widget'ları
- Grafik widget'ları
- Zil simgeli veritabanı bildirimleri
- Tüm resource'larda genel arama

</td>
</tr>
<tr>
<td valign="top">

**Erişim**
- Standart Laravel policy'leri
- Varsayılan olarak kapalı
- `canSee` ile alan bazında görünürlük
- Mevcut girişinizi kullanır

</td>
<td valign="top">

**Çoklu kiracılık**
- URL kapsamlı kiracılar (`/admin/{tenant}`)
- Otomatik sorgu kapsamı ve kiracı damgalama
- Kiracı değiştirici ve kendi kendine kayıt
- Faturalandırma için erişim kontrol hook'u

</td>
<td valign="top">

**Veri**
- Geçerli görünümün CSV olarak dışa aktarımı
- Form doğrulamasından geçen CSV içe aktarımı
- Geri yükleme ve kalıcı silme destekli soft delete

</td>
</tr>
<tr>
<td valign="top">

**Görünüm ve dil**
- Açık, koyu ve sistem temaları
- Marka adı, vurgu rengi ve tema token'ları
- 14 dile çevrilmiş panel
- CSP nonce desteği

</td>
<td valign="top">

**Eklentiler**
- Sıradan Composer paketleri
- Web component olarak özel alanlar ve sütunlar
- Paylaşılan prop'lar, gezinme ve tema token'ları için hook'lar

</td>
<td valign="top">

**Geliştirici deneyimi**
- Pest ve PHPUnit test yardımcıları
- `saddle:install` ve `saddle:upgrade`
- Güncellemelerden sonra eskimiş asset uyarısı
- Laravel 11, 12 ve 13

</td>
</tr>
</table>

## Kullanılan teknolojiler

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Hızlı başlangıç

PHP 8.4+ ve Laravel 11.48+, 12 veya 13 gerektirir.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Uygulamanızın giriş ekranından oturum açın, model için bir policy kaydedin ve ardından `/admin` adresini ziyaret edin. Her paket güncellemesinden sonra `php artisan saddle:upgrade` komutunu çalıştırın. [Başlangıç kılavuzu](../sections/getting-started.md) her adımı tek tek anlatır.

## Daha fazlası

| | |
|---|---|
| **Geliştirme** | [Resource'lar](../sections/resources.md) · [Alanlar](../sections/fields.md) · [Form düzeni](../sections/forms-layout.md) · [Tablolar](../sections/tables.md) · [Filtreler](../sections/filters.md) · [Eylemler](../sections/actions.md) · [İlişki yöneticileri](../sections/relations.md) |
| **Panel** | [Dashboard widget'ları](../sections/widgets.md) · [Genel arama](../sections/search.md) · [Bildirimler](../sections/notifications.md) · [Soft delete](../sections/soft-deletes.md) · [İçe ve dışa aktarma](../sections/import-export.md) |
| **Çalıştırma** | [Yetkilendirme](../sections/authorization.md) · [Çoklu kiracılık](../sections/tenancy.md) · [Çoklu kiracılık ekleri](../sections/tenancy-extras.md) · [Yapılandırma](../sections/configuration.md) · [Temalar](../sections/theming.md) · [Yerelleştirme](../sections/i18n.md) |
| **Genişletme** | [Eklentiler](../sections/plugins.md) · [Test yardımcıları](../sections/testing.md) · [Katkıda bulunma](../../CONTRIBUTING.md) · [Güvenlik](../../SECURITY.md) |

## Lisans

Saddle, [MIT lisansı](../../LICENSE) ile lisanslanmış açık kaynaklı bir yazılımdır.
