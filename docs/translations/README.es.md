<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <b>Español</b> •
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
  <em>Ensilla, vaquero, hay un nuevo panel de administración en el pueblo.</em>
</p>

---

**Saddle** es el framework de panel de administración de código abierto para Laravel, creado de forma moderna para **Inertia and
Vue**. Reúne tus modelos de Eloquent en paneles de recursos pulidos, con constructores de formularios y tablas, roles y acceso,
plugins y multiinquilino.

> **Estado: v1.0, importación/exportación CSV y extras de multiinquilino.** El sitio de marketing está en **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Instalación

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

El proveedor de servicio se detecta automáticamente. `saddle:install` publica el archivo de configuración, publica los assets del panel y crea `app/Saddle/` para tus clases de recursos. Visita `/admin` para ver el panel.

## Definir un recurso

Coloca las clases de recursos en `app/Saddle/`. Cada clase extiende `SaddlePHP\Resource` e implementa `form()` y `table()`.

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

Los recursos se descubren automáticamente al escanear `app/Saddle/` durante el arranque, sin necesidad de registro manual.

> **Claves de ruta reservadas.** El panel posee los segmentos de ruta estáticos `create`, `options` y `actions` bajo cada recurso, así que un registro cuya clave de ruta sea literalmente una de esas palabras no se puede alcanzar por sus URL de editar/actualizar/eliminar. Usa una clave entera o un slug diferente para esos registros.

## Diseño del formulario

Los campos se pueden agrupar en contenedores de diseño. Los contenedores se pueden anidar libremente entre sí.

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

| Contenedor | Descripción |
|---|---|
| `Section` | Un grupo de tarjetas con etiqueta. `description(string)` agrega un subtítulo. Acepta `schema([...])` de campos y contenedores anidados. |
| `Grid` | Organiza sus hijos en una cuadrícula CSS. `Grid::make(2)` crea una cuadrícula de dos columnas. Los campos dentro de un Grid usan `columnSpan(int)` para ocupar varias columnas. |
| `Tabs` | Envuelve uno o más contenedores `Tab` en una interfaz con pestañas. |
| `Tab` | Un panel individual dentro de un grupo `Tabs`. `Tab::make('Label')->schema([...])`. Cuando cualquier campo dentro de una pestaña falla la validación, la pestaña muestra un indicador de error para que los usuarios localicen el problema sin cambiar a cada panel. |

**Los esquemas planos siguen funcionando.** Pasar una lista simple de campos a `$form->schema([...])` sin contenedores está totalmente soportado y produce el mismo diseño de antes. La validación y la autorización tratan los campos de forma idéntica sin importar si viven dentro de un contenedor o en el nivel superior.

## Campos

| Campo | Descripción |
|---|---|
| `Text` | Entrada de texto de una sola línea. Modificadores: `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Entrada de texto multilínea. Modificadores: `rows(int)`. |
| `Select` | Desplegable de opciones fijas. Pasa un array asociativo a `options(['value' => 'Label'])`. |
| `Toggle` | Interruptor booleano. Almacena `true`/`false`. |
| `BelongsTo` | Selector de relación. El argumento es el nombre del método de relación de Eloquent en el modelo (`BelongsTo::make('rider')` lee `$model->rider()` y envía la clave foránea). Las etiquetas de opciones se resuelven desde `titleAttribute('name')`, con respaldo en el recurso registrado `$title` del modelo relacionado y luego en su clave. Si no hay `titleAttribute()` ni un recurso registrado disponible, las opciones se etiquetan por clave primaria, así que define `titleAttribute('name')` para etiquetas legibles. Las opciones se limitan a 100 por defecto; sobrescríbelo con `limit(int)`. `searchable()` cambia a un selector asíncrono que busca en la tabla relacionada mientras escribes mediante un endpoint autenticado. Al editar, solo se inserta la selección actual, no se carga la lista completa. `modifyOptionsQuery(fn ($query) => ...)` limita la lista de opciones por multiinquilino o visibilidad. Se aplica a opciones listadas y buscadas, mientras la selección guardada de un registro sigue mostrando su etiqueta incluso cuando queda fuera del alcance. |
| `Number` | Entrada numérica. Modificadores: `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Entrada de fecha. Los valores se muestran como `Y-m-d`. |
| `DateTime` | Entrada de fecha y hora (`datetime-local`). Los valores se almacenan y leen mediante el cast datetime de tu modelo. Un valor `DateTimeInterface` se formatea como `Y-m-d\TH:i` para el navegador. |
| `Markdown` | Textarea con barra de formato. Se almacena como string plano y se limita a 65 535 caracteres, equivalente a una columna MySQL `TEXT`. |
| `FileUpload` | Carga de archivo multipart. Modificadores: `disk(string)`, `directory(string)`, `image()` (restringe a tipos de imagen), `acceptedTypes(array)` (extensiones MIME, p. ej. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. El valor almacenado es la ruta del archivo devuelta por `Storage::put`. En el formulario de edición: dejar la entrada sin tocar conserva el archivo existente, limpiarla almacena `null` y elegir un archivo nuevo reemplaza la ruta. Los archivos reemplazados o limpiados no se eliminan del disco automáticamente. |

## Columnas

| Columna | Descripción |
|---|---|
| `TextColumn` | Renderiza el valor bruto del atributo. Modificadores: `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formatea atributos DateTime, formato predeterminado `Y-m-d H:i`). |
| `BadgeColumn` | Renderiza una insignia tipo píldora. Usa `colors(['value' => 'token'])` para mapear valores de opciones a tokens de color (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Renderiza una marca de verificación para valores verdaderos y un guion para valores falsos. |

**Columnas de relación y carga eager.** Los nombres con punto como `TextColumn::make('rider.name')` leen a través de una relación cargada. Declara `public static array $with = ['rider']` en el recurso para que la consulta del índice cargue la relación antes de renderizar. Las columnas de relación aún no son ordenables ni buscables.

## Filtros

Los filtros se declaran en la tabla mediante `->filters([...])`. En el índice, el panel los aplica desde parámetros de query string `filter[name]=value`. Los valores solicitados se validan contra la declaración, por lo que los nombres de filtro desconocidos y los valores de opción no declarados se ignoran silenciosamente.

| Filtro | Descripción |
|---|---|
| `SelectFilter` | Desplegable de coincidencia exacta. `options(['value' => 'Label'])` define tanto las opciones del desplegable como la lista permitida de valores aceptados. |
| `BooleanFilter` | Desplegable Sí/No sobre una columna booleana. |

## Acciones

Las acciones aparecen como botones en cada fila de la tabla de índice. Las acciones masivas aparecen en una barra de herramientas cuando se selecciona una o más filas.

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

| Fluido | Descripción |
|---|---|
| `label(string)` | Etiqueta visible en el botón. Si se omite, el nombre se convierte a title case. |
| `color(string)` | Token de color para el botón: `accent`, `ink` o `muted`. Por defecto es `ink`. |
| `requiresConfirmation(?string)` | Muestra un diálogo de confirmación antes de ejecutar. Pasa un mensaje personalizado u omítelo para usar el aviso predeterminado. |
| `authorize(string)` | Nombra una habilidad de política que se comprueba por registro antes de que se ejecute el handler. |
| `successMessage(string)` | Mensaje flash mostrado después de una ejecución correcta. Por defecto es `Done.`. |

Las acciones hacen POST a un endpoint protegido. Los registros se resuelven mediante la misma consulta base con alcance usada en todas partes, así que el multiinquilino, los filtros y los alcances de consulta por recurso se aplican automáticamente. Cuando se declara `authorize('ability')`, la política se comprueba por registro antes de que se ejecute el handler. Las ejecuciones masivas corren dentro de una transacción de base de datos y tienen un límite de 100 registros por solicitud. Si falta cualquier registro solicitado en la consulta con alcance, toda la operación aborta con 404 en lugar de aplicarse silenciosamente al subconjunto resuelto. Declara `authorize()` en cualquier acción destructiva.

`BulkAction::delete()` es un preset ya construido: nombre `delete`, etiqueta `Delete`, color `accent`, confirmación `Delete the selected records?` y `authorize('delete')` ya conectado.

## Autorización

Saddle consume políticas estándar de Laravel. Registra una política para un modelo y el panel la aplica en todas partes: índice, formularios, acciones de fila y selectores de relación. Sin una política registrada, todas las habilidades se permiten para cada usuario autenticado. Los roles permanecen en tu aplicación: cualquier paquete de roles o capa casera que respalde tus políticas funciona sin cambios.

### Bloquear el panel

Los recursos sin una política registrada permiten a todos los usuarios autenticados por defecto. Define `saddle.authorization.require_policy` como `true` para fallar cerrado: los recursos sin política se vuelven inaccesibles en lugar de abiertos. También puedes agregar un middleware de gate a `saddle.middleware` para una comprobación general antes de que se ejecute cualquier ruta del panel. Si tu web guard se comparte entre usuarios finales y administradores, uno de estos controles es esencial.

| Habilidad | Dónde se comprueba |
|---|---|
| `viewAny` | Página de índice del recurso, visibilidad de la barra lateral |
| `create` | Formulario de creación, acción store, endpoint de opciones de relación |
| `update` | Formulario de edición, acción update, enlace Edit por fila, endpoint de opciones de relación (comprobado contra un modelo nuevo cuando no hay ningún registro en alcance) |
| `delete` | Acción destroy, botón Delete por fila |

### Visibilidad de campos con `canSee`

Los campos individuales se pueden restringir por solicitud usando `canSee`. El campo `notes` en `HorseResource` es un ejemplo funcional:

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Los campos ocultos se eliminan del payload del formulario (los valores almacenados nunca se serializan al frontend), no aportan reglas de validación, nunca se escriben al guardar y su endpoint de opciones de relación devuelve 404. El callback puede ejecutarse varias veces por solicitud, así que mantenlo barato y devuelve un booleano real. Por ejemplo, usa `Gate::allows('view-notes', $model)` en lugar de `Gate::inspect(...)`, cuyo objeto `Response` siempre es truthy y nunca ocultará el campo.

## Plugins

Un plugin es un paquete Composer normal. Su proveedor de servicio registra recursos, scripts y estilos mediante la fachada `Saddle`, y el auto-descubrimiento de paquetes de Laravel lo arranca automáticamente junto con tu aplicación.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Publica los assets compilados desde el proveedor de servicio del plugin en `public/vendor/{plugin}` usando el mecanismo estándar `$this->publishes([...])`, y luego apunta `Saddle::script()` a la ruta publicada. Los scripts y hojas de estilo del plugin se cargan en cada página del panel después del bundle principal del panel.

### Elementos personalizados

Los plugins pueden incluir sus propios renderizadores de campos y columnas como elementos personalizados. En el lado PHP:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

El panel cumple este contrato: para campos, establece las propiedades DOM `value` y `field` del elemento y escucha un CustomEvent `saddle:input` cuyo `detail` es el nuevo valor. Para columnas, establece las propiedades DOM `value` y `column` (solo lectura, no se espera evento de entrada).

Un elemento personalizado vanilla mínimo que implementa el contrato de campo:

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

Define los elementos en el nivel superior de tu script. El navegador actualiza cualquier elemento coincidente que el panel ya haya renderizado en cuanto se ejecuta `customElements.define`, así que el orden de carga no importa.

El contrato es agnóstico al framework. Cualquier cosa que compile a un elemento personalizado estándar funciona: `defineCustomElement` de Vue, Lit, wrappers de React o Svelte. Los autores de plugins no están atados a los internos del panel.

## Multiinquilino

Saddle soporta multiinquilino opcional con alcance por URL. Actívalo apuntando `tenancy.model` a cualquier clase Eloquent:

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Cuando el multiinquilino está activo, el panel se monta bajo `/admin/{tenant}` en lugar de `/admin`. El segmento `{tenant}` se resuelve mediante búsqueda por clave de ruta en el modelo configurado. Las claves de inquilino desconocidas devuelven **404**. Los usuarios autenticados que no sean miembros del inquilino resuelto se rechazan con **403**.

### Alcance de recursos

Declara la relación BelongsTo del registro con el inquilino en cada recurso que quieras acotar:

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Los recursos sin `$tenant` (tablas de búsqueda compartidas, configuración global) permanecen sin alcance por diseño.

### Garantías de alcance automático

Cada ruta de datos comprueba el inquilino enlazado del lado del servidor:

- **Índice, búsqueda y filtros** pasan por la consulta base con alcance (`whereBelongsTo` sobre la relación declarada).
- **Búsquedas de registros** para editar, actualizar y destruir se resuelven mediante la misma consulta con alcance, así que los IDs de otros inquilinos devuelven 404 antes de que se ejecute cualquier política.
- **Stores** sellan el inquilino actual del lado del servidor después de rellenar el formulario. Cualquier clave foránea de inquilino enviada por el cliente se sobrescribe.
- **Listas de opciones de relación** aplican el mismo alcance cuando el recurso registrado del modelo relacionado también tiene alcance de inquilino.

### Selector de inquilino

Cuando el usuario autenticado pertenece a más de un inquilino, la barra lateral del panel muestra un select que lista todas sus membresías. Cambiar navega a la misma ruta del panel bajo el inquilino seleccionado.

### Advertencias

- **No expongas la relación `$tenant` como campo de formulario en un recurso con alcance.** El controlador store sella la relación del lado del servidor, pero un campo BelongsTo editable que apunte a la relación del inquilino en un formulario de actualización permitiría que un valor enviado reasigne el registro a otro inquilino.
- **Una etiqueta de relación guardada sigue renderizándose en el formulario de edición incluso cuando la fila relacionada queda fuera del alcance actual.** `BelongsTo` resuelve la selección actual con una consulta sin alcance para que la etiqueta nunca desaparezca tras un cambio de alcance. Solo se filtra la lista de opciones para nuevas selecciones.
- **Cambiar la configuración de multiinquilino requiere `php artisan route:clear`**, porque el prefijo `{tenant}` se decide durante el arranque. Los servidores de aplicación de larga vida (workers FPM mantenidos vivos entre solicitudes) deben asegurar que el estado de la solicitud se reinicie entre solicitudes. El inquilino enlazado vive en el singleton de Saddle, que se resuelve de nuevo por solicitud bajo el ciclo de vida predeterminado del contenedor. Cuando Octane está instalado, el panel reinicia automáticamente el inquilino enlazado mediante los hooks del ciclo de vida de solicitudes de Octane.

## Configuración

`saddle:install` publica `config/saddle.php`. Claves disponibles:

| Clave | Predeterminado | Descripción |
|---|---|---|
| `path` | `'admin'` | Prefijo URL del panel (p. ej. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Stack de middleware aplicado a todas las rutas del panel. |
| `resources.path` | `app_path('Saddle')` | Ruta del sistema de archivos escaneada para clases de recursos. |
| `resources.namespace` | `'App\\Saddle'` | Namespace PHP correspondiente a `resources.path`. |
| `per_page` | `25` | Filas predeterminadas por página en tablas de índice. |
| `brand.name` | `'Saddle'` | Nombre del panel (barra lateral y pestaña del navegador). |
| `brand.accent` | `'#d9501f'` | Color de acento (botones, estados activos). |
| `uploads.disk` | `'public'` | Disco de sistema de archivos predeterminado usado por campos `FileUpload` cuando no se define un `disk()` por campo. |
| `uploads.directory` | `'saddle'` | Directorio de carga predeterminado dentro del disco cuando no se define un `directory()` por campo. |

## Comandos

| Comando | Descripción |
|---|---|
| `saddle:install` | Publica la configuración, publica los assets del panel, crea `app/Saddle/`. Ofrece agregar `saddle:upgrade` a `composer post-update-cmd` para mantener los assets frescos. |
| `saddle:upgrade` | Vuelve a publicar los assets del panel. Ejecútalo después de cada actualización del paquete. |
| `saddle:resource NameResource --model=Name` | Genera el esqueleto de una nueva clase de recurso. La opción `--model` es opcional. Se infiere del nombre del recurso cuando se omite. |

**Nota de despliegue.** Agrega `php artisan saddle:upgrade` a tu script de despliegue después de `composer install` o `composer update`. El panel muestra un banner de advertencia en la UI cuando los assets publicados no están sincronizados con la versión instalada del paquete.

## Desarrollo local

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

El directorio `workbench/` contiene una aplicación anfitriona mínima usada por la suite de pruebas y para pruebas manuales. `vendor/bin/testbench serve` la arranca con `HorseResource` registrado. Ten en cuenta que las rutas del panel están detrás del middleware `auth` y que el workbench solo incluye una ruta stub `/login`, así que para navegar de forma interactiva define temporalmente `'middleware' => ['web']` en `config/saddle.php` o navega mediante las pruebas de características. Todavía no hay seeder de demostración.

## Stack

Creado para **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licencia

MIT.
