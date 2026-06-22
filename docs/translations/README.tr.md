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

<p align="center">
  <a href="https://saddlephp.com"><img src=".github/og.png" alt="Saddle, there's a new admin panel in town" width="820"></a>
</p>

<p align="center">
  <em>Eyerini vur kovboy, kasabada yeni bir yönetim paneli var.</em>
</p>

---

**Saddle**, Laravel için açık kaynaklı yönetim paneli framework'üdür, modern biçimde **Inertia and
Vue** için oluşturulmuştur. Eloquent modellerini form ve tablo oluşturucular, roller ve erişim,
pluginler ve çok kiracılık ile cilalı kaynak panellerinde topla.

> **Durum: v1.0, CSV içe/dışa aktarma ve kiracılık ekleri.** Pazarlama sitesi **[saddlephp.com](https://saddlephp.com)** adresindedir ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Kurulum

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Service provider otomatik olarak keşfedilir. `saddle:install` yapılandırma dosyasını yayınlar, panel assetlerini yayınlar ve kaynak sınıflarınız için `app/Saddle/` oluşturur. Paneli görmek için `/admin` adresini ziyaret edin.

## Bir kaynak tanımlama

Kaynak sınıflarını `app/Saddle/` içine yerleştirin. Her sınıf `SaddlePHP\Resource` sınıfını genişletir ve `form()` ile `table()` uygular.

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

Kaynaklar başlangıçta `app/Saddle/` taranarak otomatik keşfedilir, elle kayıt gerekmez.

> **Ayrılmış rota anahtarları.** Panel, her kaynağın altındaki statik yol segmentleri `create`, `options` ve `actions` üzerinde sahibidir. Bu yüzden rota anahtarı kelimenin tam anlamıyla bu sözcüklerden biri olan bir kayıt, düzenleme/güncelleme/silme URL'leriyle erişilebilir değildir. Böyle kayıtlar için tam sayı anahtar ya da farklı bir slug kullanın.

## Form düzeni

Alanlar düzen konteynerlerinde gruplanabilir. Konteynerler birbirinin içine serbestçe yerleştirilebilir.

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

| Konteyner | Açıklama |
|---|---|
| `Section` | Etiketli bir kart grubu. `description(string)` bir alt başlık ekler. Alanlar ve iç içe konteynerler için `schema([...])` kabul eder. |
| `Grid` | Çocuklarını CSS grid içinde düzenler. `Grid::make(2)` iki sütunlu bir grid oluşturur. Grid içindeki alanlar birden fazla sütuna yayılmak için `columnSpan(int)` kullanır. |
| `Tabs` | Bir veya daha fazla `Tab` konteynerini sekmeli bir arayüz içinde sarar. |
| `Tab` | Bir `Tabs` grubu içinde tek bir panel. `Tab::make('Label')->schema([...])`. Bir sekme içindeki herhangi bir alan doğrulamadan geçemezse sekme, kullanıcıların her panele geçmeden sorunu bulabilmesi için hata göstergesi gösterir. |

**Düz şemalar hâlâ çalışır.** Konteyner olmadan `$form->schema([...])` içine düz bir alan listesi geçirmek tamamen desteklenir ve öncekiyle aynı düzeni üretir. Doğrulama ve yetkilendirme, alanlar ister konteyner içinde ister en üst düzeyde olsun aynı şekilde davranır.

## Alanlar

| Alan | Açıklama |
|---|---|
| `Text` | Tek satırlı metin girişi. Değiştiriciler: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Çok satırlı metin girişi. Değiştiriciler: `rows(int)`. |
| `Select` | Sabit seçenekli dropdown. `options(['value' => 'Label'])` içine ilişkisel bir array geçin. |
| `Toggle` | Boolean anahtar. `true`/`false` saklar. |
| `BelongsTo` | İlişki seçimi. Argüman, model üzerindeki Eloquent ilişki metodu adıdır (`BelongsTo::make('rider')` `$model->rider()` okur ve foreign key gönderir). Seçenek etiketleri `titleAttribute('name')` üzerinden çözülür, ardından ilişkili modelin kayıtlı kaynağı `$title`, sonra da anahtarı kullanılır. Ne `titleAttribute()` ne de kayıtlı kaynak varsa seçenekler primary key ile etiketlenir, bu yüzden okunabilir etiketler için `titleAttribute('name')` ayarlayın. Seçenekler varsayılan olarak 100 ile sınırlıdır; `limit(int)` ile geçersiz kılın. `searchable()` yazdıkça ilgili tabloyu kimliği doğrulanmış bir endpoint üzerinden arayan asenkron bir seçiciye geçer. Düzenlemede yalnızca geçerli seçim gömülür, tam liste yüklenmez. `modifyOptionsQuery(fn ($query) => ...)` seçenek listesini kiracılık veya görünürlük için sınırlar. Listelenen ve aranan seçeneklere uygulanır, bir kaydın kayıtlı seçimi kapsam dışına çıksa bile etiketi render edilmeye devam eder. |
| `Number` | Sayısal giriş. Değiştiriciler: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Tarih girişi. Değerler `Y-m-d` olarak render edilir. |
| `DateTime` | Tarih ve saat girişi (`datetime-local`). Değerler modelinizin datetime cast'i üzerinden saklanır ve geri okunur. Bir `DateTimeInterface` değeri tarayıcı için `Y-m-d\TH:i` olarak biçimlendirilir. |
| `Markdown` | Biçimlendirme toolbar'ı olan textarea. Düz string olarak saklanır ve 65 535 karakterle sınırlıdır, MySQL `TEXT` sütununa denktir. |
| `FileUpload` | Multipart dosya yükleme. Değiştiriciler: `disk(string)`, `directory(string)`, `image()` (görüntü türleriyle sınırlar), `acceptedTypes(array)` (MIME uzantıları, ör. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. Saklanan değer `Storage::put` tarafından döndürülen dosya yoludur. Düzenleme formunda: girişi dokunmadan bırakmak mevcut dosyayı korur, temizlemek `null` saklar ve yeni dosya seçmek yolu değiştirir. Değiştirilen veya temizlenen dosyalar diskten otomatik silinmez. |

## Sütunlar

| Sütun | Açıklama |
|---|---|
| `TextColumn` | Ham attribute değerini render eder. Değiştiriciler: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (DateTime attribute'larını biçimlendirir, varsayılan format `Y-m-d H:i`). |
| `BadgeColumn` | Hap biçimli bir badge render eder. `colors(['value' => 'token'])` kullanarak seçenek değerlerini renk tokenlarına (`accent`, `ink`, `muted`) eşleyin. |
| `BooleanColumn` | Truthy değerler için onay işareti, falsy değerler için tire render eder. |

**İlişki sütunları ve eager loading.** `TextColumn::make('rider.name')` gibi noktalı adlar yüklü bir ilişki üzerinden okur. İndeks sorgusunun render öncesinde ilişkiyi eager-load etmesi için kaynak üzerinde `public static array $with = ['rider']` bildirin. İlişki sütunları henüz sıralanabilir veya aranabilir değildir.

## Filtreler

Filtreler tabloda `->filters([...])` ile bildirilir. İndekste panel bunları `filter[name]=value` query string parametrelerinden uygular. İstenen değerler bildirime göre doğrulanır, bu yüzden bilinmeyen filtre adları ve bildirilmemiş seçenek değerleri sessizce yok sayılır.

| Filtre | Açıklama |
|---|---|
| `SelectFilter` | Tam eşleşme dropdown'ı. `options(['value' => 'Label'])` hem dropdown seçeneklerini hem de kabul edilen değerlerin allowlist'ini tanımlar. |
| `BooleanFilter` | Boolean sütun üzerinde Evet/Hayır dropdown'ı. |

## Aksiyonlar

Aksiyonlar indeks tablosunun her satırında buton olarak görünür. Bir veya daha fazla satır seçildiğinde toplu aksiyonlar toolbar'da görünür.

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

| Fluent | Açıklama |
|---|---|
| `label(string)` | Butonda gösterilen etiket. Atlanırsa ad title case'e dönüştürülür. |
| `color(string)` | Buton için renk tokenı: `accent`, `ink` veya `muted`. Varsayılan `ink`. |
| `requiresConfirmation(?string)` | Çalıştırmadan önce onay diyaloğu gösterir. Özel mesaj geçin veya varsayılan istemi kullanmak için atlayın. |
| `authorize(string)` | Handler çalışmadan önce kayıt başına kontrol edilen bir politika yeteneğini adlandırır. |
| `successMessage(string)` | Başarılı çalıştırmadan sonra gösterilen flash mesajı. Varsayılan `Done.`. |

Aksiyonlar korumalı bir endpoint'e post eder. Kayıtlar her yerde kullanılan aynı kapsamlı temel sorgudan çözülür, böylece kiracılık, filtreler ve kaynak başına query scope'ları otomatik uygulanır. `authorize('ability')` bildirildiğinde, handler çalışmadan önce politika kayıt başına kontrol edilir. Toplu çalıştırmalar bir veritabanı transaction'ı içinde yürütülür ve istek başına 100 kayıtla sınırlıdır. İstenen herhangi bir kayıt kapsamlı çekimde eksikse tüm işlem, çözülen alt kümeye sessizce uygulanmak yerine 404 ile iptal olur. Yıkıcı her aksiyonda `authorize()` bildirin.

`BulkAction::delete()` hazır bir presettir: ad `delete`, etiket `Delete`, renk `accent`, onay `Delete the selected records?` ve `authorize('delete')` zaten bağlıdır.

## Yetkilendirme

Saddle standart Laravel politikalarını kullanır. Bir model için politika kaydedin, panel bunu her yerde uygular: indeks, formlar, satır aksiyonları ve ilişki seçiciler. Kayıtlı politika yoksa tüm yetenekler her kimliği doğrulanmış kullanıcı için izinlidir. Roller uygulamanızda kalır: politikalarınızı destekleyen herhangi bir rol paketi veya ev yapımı katman değişmeden çalışır.

### Paneli kilitleme

Kayıtlı politikası olmayan kaynaklar varsayılan olarak her kimliği doğrulanmış kullanıcıya izin verir. Kapalı başarısız olmak için `saddle.authorization.require_policy` değerini `true` yapın: politikası olmayan kaynaklar açık olmak yerine erişilemez olur. Herhangi bir panel rotası çalışmadan önce genel kontrol için `saddle.middleware` içine bir gate middleware'i de ekleyebilirsiniz. Web guard'ınız son kullanıcılar ve yöneticiler arasında paylaşılıyorsa bu kontrollerden biri zorunludur.

| Yetenek | Nerede kontrol edilir |
|---|---|
| `viewAny` | Kaynak indeks sayfası, sidebar görünürlüğü |
| `create` | Oluşturma formu, store aksiyonu, ilişki seçenekleri endpoint'i |
| `update` | Düzenleme formu, update aksiyonu, satır başına Edit bağlantısı, ilişki seçenekleri endpoint'i (kapsamda kayıt yoksa taze bir modele karşı kontrol edilir) |
| `delete` | Destroy aksiyonu, satır başına Delete butonu |

### `canSee` ile alan görünürlüğü

Tek tek alanlar istek başına `canSee` ile kapatılabilir. `notes` alanı `HorseResource` içinde çalışan bir örnektir:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Gizli alanlar form payload'undan çıkarılır (saklanan değerler frontend'e asla serileştirilmez), doğrulama kuralı katkısı yapmaz, kayıtta asla yazılmaz ve ilişki seçenekleri endpoint'i 404 döndürür. Callback istek başına birkaç kez çalışabilir, bu yüzden ucuz tutun ve gerçek boolean döndürün. Örneğin `Gate::allows('view-notes', $model)` kullanın, `Gate::inspect(...)` yerine, çünkü onun `Response` nesnesi her zaman truthy'dir ve alanı asla gizlemez.

## Pluginler

Plugin normal bir Composer paketidir. Service provider'ı `Saddle` facade'ı üzerinden kaynakları, scriptleri ve stilleri kaydeder, Laravel'in package auto-discovery özelliği de onu uygulamanızla birlikte otomatik başlatır.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Plugin'in service provider'ından derlenmiş assetleri `public/vendor/{plugin}` içine standart `$this->publishes([...])` mekanizmasıyla yayınlayın, ardından `Saddle::script()` ile yayınlanan yolu gösterin. Plugin scriptleri ve stylesheet'leri her panel sayfasında çekirdek panel bundle'ından sonra yüklenir.

### Özel elementler

Pluginler kendi alan ve sütun renderer'larını özel elementler olarak gönderebilir. PHP tarafında:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Panel bu sözleşmeyi yerine getirir: alanlar için elementin `value` ve `field` DOM özelliklerini ayarlar ve bir `saddle:input` CustomEvent'i dinler, bunun `detail` değeri yeni değerdir. Sütunlar için `value` ve `column` DOM özelliklerini ayarlar (salt okunur, giriş event'i beklenmez).

Alan sözleşmesini uygulayan minimal vanilla özel element:

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

Elementleri scriptinizin en üst düzeyinde tanımlayın. `customElements.define` çalışır çalışmaz tarayıcı, panelin zaten render ettiği eşleşen elementleri yükseltir, bu yüzden yükleme sırası hiç önemli değildir.

Sözleşme framework'ten bağımsızdır. Standart bir özel elemente derlenen her şey çalışır: Vue'nun `defineCustomElement`'i, Lit, React veya Svelte wrapper'ları. Plugin yazarları panelin iç işleyişine bağlı değildir.

## Çok kiracılık

Saddle isteğe bağlı, URL kapsamlı çok kiracılığı destekler. `tenancy.model` değerini herhangi bir Eloquent sınıfına göstererek etkinleştirin:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Çok kiracılık aktifken panel `/admin/{tenant}` altında mount edilir, `/admin` yerine. `{tenant}` segmenti yapılandırılan model üzerinde route-key lookup ile çözülür. Bilinmeyen kiracı anahtarları **404** döndürür. Çözülen kiracının üyesi olmayan kimliği doğrulanmış kullanıcılar **403** ile reddedilir.

### Kaynakları kapsamlamak

Kapsamlamak istediğiniz her kaynakta kaydın kiracıya BelongsTo ilişkisini bildirin:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

`$tenant` olmayan kaynaklar (paylaşılan lookup tabloları, global yapılandırma) tasarım gereği kapsam dışı kalır.

### Otomatik kapsam garantileri

Her veri yolu bağlı kiracıyı sunucu tarafında kontrol eder:

- **İndeks, arama ve filtreler** kapsamlı temel sorgudan geçer (`whereBelongsTo` bildirilen ilişki üzerinde).
- **Kayıt lookup'ları** düzenleme, güncelleme ve silme için aynı kapsamlı sorgu üzerinden çözülür, böylece kiracılar arası ID'ler herhangi bir politika çalışmadan önce 404 döndürür.
- **Stores** form doldurulduktan sonra geçerli kiracıyı sunucu tarafında damgalar. İstemci tarafından gönderilen tüm kiracı foreign key'leri üzerine yazılır.
- **İlişki seçenek listeleri**, ilgili modelin kayıtlı kaynağı da kiracı kapsamlı olduğunda aynı kapsamı uygular.

### Kiracı değiştirici

Kimliği doğrulanmış kullanıcı birden fazla kiracıya aitse panel sidebar'ı tüm üyeliklerini listeleyen bir select gösterir. Değiştirmek, seçilen kiracı altında aynı panel yoluna gider.

### Uyarılar

- **Kapsamlı bir kaynakta `$tenant` ilişkisini form alanı olarak göstermeyin.** Store controller ilişkiyi sunucu tarafında damgalar, ancak update formunda kiracı ilişkisini gösteren düzenlenebilir bir BelongsTo alanı, gönderilen bir değerin kaydı farklı bir kiracıya yeniden yönlendirmesine izin verir.
- **Kayıtlı ilişki etiketi, ilgili satır geçerli kapsamın dışına düştüğünde bile düzenleme formunda render edilmeye devam eder.** `BelongsTo` geçerli seçimi kapsam dışı bir sorguyla çözer, böylece kapsam değişikliğinden sonra etiket asla kaybolmaz. Yalnızca yeni seçimler için seçenek listesi filtrelenir.
- **Çok kiracılık yapılandırmasını değiştirmek `php artisan route:clear` gerektirir**, çünkü `{tenant}` öneki başlangıçta kararlaştırılır. Uzun süre çalışan uygulama sunucuları (istekler arasında canlı tutulan FPM worker'ları), istek durumunun istekler arasında sıfırlandığından emin olmalıdır. Bağlı kiracı, varsayılan container ömrü altında istek başına taze çözülen Saddle singleton'ında yaşar. Octane kurulu olduğunda panel, Octane istek yaşam döngüsü hook'larıyla bağlı kiracıyı otomatik sıfırlar.

## Yapılandırma

`saddle:install` `config/saddle.php` dosyasını yayınlar. Kullanılabilir anahtarlar:

| Anahtar | Varsayılan | Açıklama |
|---|---|---|
| `path` | `'admin'` | Panel için URL öneki (ör. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Tüm panel rotalarına uygulanan middleware yığını. |
| `resources.path` | `app_path('Saddle')` | Kaynak sınıfları için taranan dosya sistemi yolu. |
| `resources.namespace` | `'App\\Saddle'` | `resources.path` ile eşleşen PHP namespace'i. |
| `per_page` | `25` | İndeks tablolarında sayfa başına varsayılan satır. |
| `brand.name` | `'Saddle'` | Panel adı (sidebar ve tarayıcı sekmesi). |
| `brand.accent` | `'#d9501f'` | Vurgu rengi (butonlar, aktif durumlar). |
| `uploads.disk` | `'public'` | `FileUpload` alanları tarafından kullanılan, alan başına `disk()` ayarlanmadığında geçerli olan varsayılan dosya sistemi diski. |
| `uploads.directory` | `'saddle'` | Alan başına `directory()` ayarlanmadığında disk içindeki varsayılan upload dizini. |

## Komutlar

| Komut | Açıklama |
|---|---|
| `saddle:install` | Yapılandırmayı yayınlar, panel assetlerini yayınlar, `app/Saddle/` oluşturur. Assetlerin güncel kalması için `saddle:upgrade` komutunu `composer post-update-cmd` içine eklemeyi teklif eder. |
| `saddle:upgrade` | Panel assetlerini yeniden yayınlar. Her paket güncellemesinden sonra çalıştırın. |
| `saddle:resource NameResource --model=Name` | Yeni bir kaynak sınıfı scaffold eder. `--model` seçeneği opsiyoneldir. Atlandığında kaynak adından çıkarılır. |

**Deploy notu.** Deploy scriptinize `php artisan saddle:upgrade` ekleyin, `composer install` veya `composer update` sonrasında. Yayınlanan assetler kurulu paket sürümüyle senkron dışı olduğunda panel UI'da bir uyarı banner'ı gösterir.

## Yerel geliştirme

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

`workbench/` dizini test paketi ve manuel yoklama için kullanılan minimal bir host uygulaması içerir. `vendor/bin/testbench serve` bunu kayıtlı `HorseResource` ile başlatır. Panel rotalarının `auth` middleware'i arkasında olduğunu ve workbench'in yalnızca stub `/login` rotası içerdiğini unutmayın. Bu yüzden etkileşimli gezinme için ya geçici olarak `'middleware' => ['web']` değerini `config/saddle.php` içinde ayarlayın ya da feature testleri üzerinden gezinin. Henüz demo seeder yok.

## Stack

**Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4** için oluşturuldu.

## Lisans

MIT.
