<p align="center">
  <a href="README.ar.md">العربية</a> •
  <a href="README.de.md">Deutsch</a> •
  <a href="../../README.md">English</a> •
  <a href="README.es.md">Español</a> •
  <b>Français</b> •
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
  <em>En selle, cow-boy, un nouveau panneau d'administration arrive en ville.</em>
</p>

---

**Saddle** est le framework de panneau d'administration open-source pour Laravel, conçu de façon moderne pour **Inertia and
Vue**. Rassemblez vos modèles Eloquent dans des panneaux de ressources soignés, avec des constructeurs de formulaires et de tableaux, des rôles et des accès,
des plugins et la multi-location.

> **Statut : v1.0, import/export CSV et compléments de multi-location.** Le site marketing se trouve sur **[saddlephp.com](https://saddlephp.com)** ([SaddlePHP/saddlephp.com](https://github.com/SaddlePHP/saddlephp.com)).

## Installation

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Le fournisseur de service est découvert automatiquement. `saddle:install` publie le fichier de configuration, publie les assets du panneau et crée `app/Saddle/` pour vos classes de ressources. Visitez `/admin` pour voir le panneau.

## Définir une ressource

Placez les classes de ressources dans `app/Saddle/`. Chaque classe étend `SaddlePHP\Resource` et implémente `form()` et `table()`.

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

Les ressources sont découvertes automatiquement en scannant `app/Saddle/` au démarrage, sans enregistrement manuel nécessaire.

> **Clés de route réservées.** Le panneau possède les segments de chemin statiques `create`, `options` et `actions` sous chaque ressource. Un enregistrement dont la clé de route est littéralement l'un de ces mots n'est donc pas accessible par ses URL d'édition, de mise à jour ou de suppression. Utilisez une clé entière ou un slug différent pour ces enregistrements.

## Mise en page du formulaire

Les champs peuvent être regroupés dans des conteneurs de mise en page. Les conteneurs peuvent s'imbriquer librement les uns dans les autres.

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

| Conteneur | Description |
|---|---|
| `Section` | Un groupe de cartes libellé. `description(string)` ajoute un sous-titre. Accepte `schema([...])` de champs et de conteneurs imbriqués. |
| `Grid` | Dispose ses enfants dans une grille CSS. `Grid::make(2)` crée une grille à deux colonnes. Les champs dans un Grid utilisent `columnSpan(int)` pour s'étendre sur plusieurs colonnes. |
| `Tabs` | Enveloppe un ou plusieurs conteneurs `Tab` dans une interface à onglets. |
| `Tab` | Un panneau unique dans un groupe `Tabs`. `Tab::make('Label')->schema([...])`. Quand un champ dans un onglet échoue à la validation, l'onglet affiche un indicateur d'erreur afin que les utilisateurs localisent le problème sans passer sur chaque panneau. |

**Les schémas plats fonctionnent toujours.** Passer une simple liste de champs à `$form->schema([...])` sans conteneurs est entièrement pris en charge et produit la même mise en page qu'avant. La validation et l'autorisation traitent les champs de manière identique, qu'ils vivent dans un conteneur ou au niveau supérieur.

## Champs

| Champ | Description |
|---|---|
| `Text` | Saisie de texte sur une ligne. Modificateurs : `required()`, `rules(string\|array)`, `placeholder()`. |
| `Textarea` | Saisie de texte multiligne. Modificateurs : `rows(int)`. |
| `Select` | Liste déroulante à options fixes. Passez un tableau associatif à `options(['value' => 'Label'])`. |
| `Toggle` | Interrupteur booléen. Stocke `true`/`false`. |
| `BelongsTo` | Sélecteur de relation. L'argument est le nom de la méthode de relation Eloquent sur le modèle (`BelongsTo::make('rider')` lit `$model->rider()` et soumet la clé étrangère). Les libellés d'option sont résolus depuis `titleAttribute('name')`, avec repli sur la ressource enregistrée `$title` du modèle lié, puis sur sa clé. Si aucun `titleAttribute()` ni ressource enregistrée n'est disponible, les options sont libellées par clé primaire, donc définissez `titleAttribute('name')` pour des libellés lisibles. Les options sont limitées à 100 par défaut, remplacez avec `limit(int)`. `searchable()` bascule vers un sélecteur asynchrone qui recherche dans la table liée pendant la saisie via un endpoint authentifié. En édition, seule la sélection actuelle est intégrée, la liste complète n'est pas chargée. `modifyOptionsQuery(fn ($query) => ...)` restreint la liste d'options pour la multi-location ou la visibilité. Cela s'applique aux options listées et recherchées, tandis que la sélection enregistrée d'un enregistrement continue à afficher son libellé même si elle sort du périmètre. |
| `Number` | Saisie numérique. Modificateurs : `min()`, `max()`, `step()`, `integer()`. |
| `Date` | Saisie de date. Les valeurs s'affichent en `Y-m-d`. |
| `DateTime` | Saisie de date et heure (`datetime-local`). Les valeurs sont stockées et relues via le cast datetime de votre modèle. Une valeur `DateTimeInterface` est formatée en `Y-m-d\TH:i` pour le navigateur. |
| `Markdown` | Textarea avec barre de formatage. Stocké comme simple chaîne et limité à 65 535 caractères, l'équivalent d'une colonne MySQL `TEXT`. |
| `FileUpload` | Téléversement de fichier multipart. Modificateurs : `disk(string)`, `directory(string)`, `image()` (restreint aux types image), `acceptedTypes(array)` (extensions MIME, par ex. `['pdf', 'docx']`), `maxSize(int $kilobytes)`. La valeur stockée est le chemin de fichier renvoyé par `Storage::put`. Sur le formulaire d'édition : laisser l'entrée intacte conserve le fichier existant, la vider stocke `null`, et choisir un nouveau fichier remplace le chemin. Les fichiers remplacés ou vidés ne sont pas supprimés automatiquement du disque. |

## Colonnes

| Colonne | Description |
|---|---|
| `TextColumn` | Rend la valeur brute de l'attribut. Modificateurs : `sortable()`, `searchable()`, `label(string)`, `date(string $format)` (formate les attributs DateTime, format par défaut `Y-m-d H:i`). |
| `BadgeColumn` | Rend un badge en forme de pilule. Utilisez `colors(['value' => 'token'])` pour associer les valeurs d'option aux jetons de couleur (`accent`, `ink`, `muted`). |
| `BooleanColumn` | Rend une coche pour les valeurs truthy et un tiret pour les valeurs falsy. |

**Colonnes de relation et chargement eager.** Les noms pointés comme `TextColumn::make('rider.name')` lisent à travers une relation chargée. Déclarez `public static array $with = ['rider']` sur la ressource afin que la requête d'index charge la relation avant le rendu. Les colonnes de relation ne sont pas encore triables ni recherchables.

## Filtres

Les filtres sont déclarés sur la table via `->filters([...])`. Sur l'index, le panneau les applique depuis les paramètres de query string `filter[name]=value`. Les valeurs demandées sont validées contre la déclaration, donc les noms de filtre inconnus et les valeurs d'option non déclarées sont ignorés silencieusement.

| Filtre | Description |
|---|---|
| `SelectFilter` | Liste déroulante à correspondance exacte. `options(['value' => 'Label'])` définit à la fois les choix de la liste et la liste autorisée des valeurs acceptées. |
| `BooleanFilter` | Liste déroulante Oui/Non sur une colonne booléenne. |

## Actions

Les actions apparaissent comme des boutons sur chaque ligne de la table d'index. Les actions groupées apparaissent dans une barre d'outils quand une ou plusieurs lignes sont sélectionnées.

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

| Fluent | Description |
|---|---|
| `label(string)` | Libellé d'affichage montré sur le bouton. S'il est omis, le nom est converti en title case. |
| `color(string)` | Jeton de couleur pour le bouton : `accent`, `ink` ou `muted`. Par défaut `ink`. |
| `requiresConfirmation(?string)` | Affiche une boîte de confirmation avant l'exécution. Passez un message personnalisé ou omettez-le pour utiliser l'invite par défaut. |
| `authorize(string)` | Nomme une capacité de politique vérifiée par enregistrement avant l'exécution du handler. |
| `successMessage(string)` | Message flash affiché après une exécution réussie. Par défaut `Done.`. |

Les actions publient vers un endpoint protégé. Les enregistrements se résolvent avec la même requête de base à périmètre utilisée partout ailleurs, donc la multi-location, les filtres et les scopes de requête par ressource s'appliquent automatiquement. Quand `authorize('ability')` est déclaré, la politique est vérifiée par enregistrement avant l'exécution du handler. Les exécutions groupées s'effectuent dans une transaction de base de données et sont limitées à 100 enregistrements par requête. Si un enregistrement demandé manque dans la récupération à périmètre, toute l'opération échoue avec 404 plutôt que de s'appliquer silencieusement au sous-ensemble résolu. Déclarez `authorize()` sur toute action destructive.

`BulkAction::delete()` est un préréglage prêt à l'emploi : nom `delete`, libellé `Delete`, couleur `accent`, confirmation `Delete the selected records?`, et `authorize('delete')` déjà câblé.

## Autorisation

Saddle consomme les politiques Laravel standard. Enregistrez une politique pour un modèle et le panneau l'applique partout : index, formulaires, actions de ligne et sélecteurs de relation. Sans politique enregistrée, toutes les capacités sont autorisées pour chaque utilisateur authentifié. Les rôles restent dans votre application : tout paquet de rôles ou couche maison qui alimente vos politiques fonctionne sans changement.

### Verrouiller le panneau

Les ressources sans politique enregistrée autorisent chaque utilisateur authentifié par défaut. Définissez `saddle.authorization.require_policy` à `true` pour échouer fermé : les ressources sans politique deviennent inaccessibles au lieu d'être ouvertes. Vous pouvez aussi ajouter un middleware de gate à `saddle.middleware` pour une vérification globale avant toute route du panneau. Si votre web guard est partagé entre utilisateurs finaux et administrateurs, l'un de ces contrôles est essentiel.

| Capacité | Où elle est vérifiée |
|---|---|
| `viewAny` | Page d'index de la ressource, visibilité de la barre latérale |
| `create` | Formulaire de création, action store, endpoint d'options de relation |
| `update` | Formulaire d'édition, action update, lien Edit par ligne, endpoint d'options de relation (vérifié contre un modèle frais quand aucun enregistrement n'est dans le périmètre) |
| `delete` | Action destroy, bouton Delete par ligne |

### Visibilité des champs avec `canSee`

Les champs individuels peuvent être protégés par requête avec `canSee`. Le champ `notes` dans `HorseResource` est un exemple fonctionnel :

```php
use Illuminate\Http\Request;

Textarea::make('notes')->rows(3)
    ->canSee(fn (Request $request) => (bool) $request->user()?->is_admin),
```

Les champs cachés sont retirés de la payload du formulaire (les valeurs stockées ne sont jamais sérialisées vers le frontend), n'ajoutent aucune règle de validation, ne sont jamais écrits à l'enregistrement, et leur endpoint d'options de relation renvoie 404. Le callback peut s'exécuter plusieurs fois par requête, gardez-le donc peu coûteux et renvoyez un vrai booléen. Par exemple, utilisez `Gate::allows('view-notes', $model)` plutôt que `Gate::inspect(...)`, dont l'objet `Response` est toujours truthy et ne cachera jamais le champ.

## Plugins

Un plugin est un paquet Composer classique. Son fournisseur de service enregistre des ressources, scripts et styles via la façade `Saddle`, et l'auto-découverte des paquets de Laravel le démarre automatiquement avec votre application.

```php
public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Publiez les assets compilés depuis le fournisseur de service du plugin vers `public/vendor/{plugin}` avec le mécanisme standard `$this->publishes([...])`, puis pointez `Saddle::script()` vers le chemin publié. Les scripts et feuilles de style du plugin sont chargés sur chaque page du panneau après le bundle principal du panneau.

### Éléments personnalisés

Les plugins peuvent fournir leurs propres rendus de champs et colonnes comme éléments personnalisés. Côté PHP :

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

Le panneau remplit ce contrat : pour les champs, il définit les propriétés DOM `value` et `field` de l'élément et écoute un CustomEvent `saddle:input` dont `detail` est la nouvelle valeur. Pour les colonnes, il définit les propriétés DOM `value` et `column` (lecture seule, aucun événement de saisie attendu).

Un élément personnalisé vanilla minimal implémentant le contrat de champ :

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

Définissez les éléments au niveau supérieur de votre script. Le navigateur met à niveau tout élément correspondant que le panneau a déjà rendu dès que `customElements.define` s'exécute, donc l'ordre de chargement n'a jamais d'importance.

Le contrat est indépendant du framework. Tout ce qui compile vers un élément personnalisé standard fonctionne : `defineCustomElement` de Vue, Lit, wrappers React ou Svelte. Les auteurs de plugins ne sont pas liés aux internes du panneau.

## Multi-location

Saddle prend en charge une multi-location optionnelle, bornée par URL. Activez-la en pointant `tenancy.model` vers n'importe quelle classe Eloquent :

```php
// config/saddle.php
'tenancy' => [
    'model' => App\Models\Ranch::class, // null disables (default)
    'relationship' => 'users',          // relation that lists the tenant's members
],
```

Quand la multi-location est active, le panneau se monte sous `/admin/{tenant}` au lieu de `/admin`. Le segment `{tenant}` se résout par recherche de clé de route sur le modèle configuré. Les clés de locataire inconnues renvoient **404**. Les utilisateurs authentifiés qui ne sont pas membres du locataire résolu sont rejetés avec **403**.

### Périmètre des ressources

Déclarez la relation BelongsTo de l'enregistrement vers le locataire sur chaque ressource que vous voulez borner :

```php
class HorseResource extends Resource
{
    public static ?string $tenant = 'ranch'; // Eloquent relation name on Horse
}
```

Les ressources sans `$tenant` (tables de recherche partagées, configuration globale) restent hors périmètre par conception.

### Garanties de périmètre automatique

Chaque chemin de données vérifie le locataire lié côté serveur :

- **Index, recherche et filtres** passent par la requête de base à périmètre (`whereBelongsTo` sur la relation déclarée).
- **Recherches d'enregistrements** pour éditer, mettre à jour et détruire se résolvent via la même requête à périmètre, donc les IDs d'autres locataires renvoient 404 avant toute politique.
- **Stores** estampillent le locataire courant côté serveur après le remplissage du formulaire. Toute clé étrangère de locataire soumise par le client est écrasée.
- **Listes d'options de relation** appliquent le même périmètre quand la ressource enregistrée du modèle lié est aussi bornée par locataire.

### Sélecteur de locataire

Quand l'utilisateur authentifié appartient à plusieurs locataires, la barre latérale du panneau affiche un select qui liste toutes ses adhésions. Changer navigue vers le même chemin de panneau sous le locataire sélectionné.

### Mises en garde

- **N'exposez pas la relation `$tenant` comme champ de formulaire sur une ressource à périmètre.** Le contrôleur store estampille la relation côté serveur, mais un champ BelongsTo éditable pointant vers la relation de locataire sur un formulaire de mise à jour permettrait à une valeur soumise de rattacher l'enregistrement à un autre locataire.
- **Un libellé de relation enregistré se rend toujours sur le formulaire d'édition même quand la ligne liée sort du périmètre courant.** `BelongsTo` résout la sélection actuelle avec une requête sans périmètre afin que le libellé ne disparaisse jamais après un changement de périmètre. Seule la liste d'options pour les nouvelles sélections est filtrée.
- **Modifier la configuration de multi-location nécessite `php artisan route:clear`**, car le préfixe `{tenant}` est décidé au démarrage. Les serveurs d'application longue durée (workers FPM maintenus vivants entre les requêtes) doivent garantir que l'état de requête est réinitialisé entre les requêtes. Le locataire lié vit sur le singleton Saddle, qui est résolu à neuf par requête avec la durée de vie par défaut du conteneur. Quand Octane est installé, le panneau réinitialise automatiquement le locataire lié via les hooks du cycle de vie des requêtes d'Octane.

## Configuration

`saddle:install` publie `config/saddle.php`. Clés disponibles :

| Clé | Défaut | Description |
|---|---|---|
| `path` | `'admin'` | Préfixe URL du panneau (par ex. `'admin'` → `/admin`). |
| `middleware` | `['web', 'auth']` | Pile de middleware appliquée à toutes les routes du panneau. |
| `resources.path` | `app_path('Saddle')` | Chemin du système de fichiers scanné pour les classes de ressources. |
| `resources.namespace` | `'App\\Saddle'` | Namespace PHP correspondant à `resources.path`. |
| `per_page` | `25` | Lignes par page par défaut sur les tables d'index. |
| `brand.name` | `'Saddle'` | Nom du panneau (barre latérale et onglet du navigateur). |
| `brand.accent` | `'#d9501f'` | Couleur d'accent (boutons, états actifs). |
| `uploads.disk` | `'public'` | Disque de système de fichiers par défaut utilisé par les champs `FileUpload` quand aucun `disk()` par champ n'est défini. |
| `uploads.directory` | `'saddle'` | Répertoire de téléversement par défaut dans le disque quand aucun `directory()` par champ n'est défini. |

## Commandes

| Commande | Description |
|---|---|
| `saddle:install` | Publie la configuration, publie les assets du panneau, crée `app/Saddle/`. Propose d'ajouter `saddle:upgrade` à `composer post-update-cmd` pour garder les assets à jour. |
| `saddle:upgrade` | Republie les assets du panneau. À lancer après chaque mise à jour du paquet. |
| `saddle:resource NameResource --model=Name` | Génère le squelette d'une nouvelle classe de ressource. L'option `--model` est facultative. Elle est déduite du nom de la ressource quand elle est omise. |

**Note de déploiement.** Ajoutez `php artisan saddle:upgrade` à votre script de déploiement après `composer install` ou `composer update`. Le panneau affiche une bannière d'avertissement dans l'UI quand les assets publiés ne sont pas synchronisés avec la version du paquet installé.

## Développement local

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

Le répertoire `workbench/` contient une application hôte minimale utilisée par la suite de tests et pour les essais manuels. `vendor/bin/testbench serve` la démarre avec `HorseResource` enregistré. Notez que les routes du panneau sont derrière le middleware `auth` et que le workbench ne fournit qu'une route stub `/login`, donc pour naviguer interactivement, définissez temporairement `'middleware' => ['web']` dans `config/saddle.php` ou passez plutôt par les tests de fonctionnalités. Il n'y a pas encore de seeder de démonstration.

## Stack

Conçu pour **Laravel 13+ / PHP 8.4+**, **Inertia 2**, **Vue 3**, **Tailwind CSS 4**.

## Licence

MIT.
