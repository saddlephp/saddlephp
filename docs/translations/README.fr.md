<p align="center">
  <img src="../../.github/og.png" alt="Saddle, there's a new admin panel in town" width="820">
</p>

<h3 align="center">Le framework open source de panneaux d'administration pour Laravel, conçu pour Inertia et Vue.</h3>

<p align="center">
  <a href="../sections/getting-started.md"><b>Documentation</b></a> ·
  <a href="https://packagist.org/packages/saddlephp/saddlephp"><b>Packagist</b></a> ·
  <a href="https://github.com/SaddlePHP/saddlephp/releases"><b>Journal des modifications</b></a> ·
  <a href="../../CONTRIBUTING.md"><b>Contribuer</b></a>
</p>

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

## Qu'est-ce que Saddle ?

Saddle transforme vos modèles Eloquent en un panneau d'administration soigné. Vous décrivez chaque modèle une seule fois, sous la forme d'une classe de ressource PHP avec un formulaire et un tableau, et Saddle construit les écrans de liste, de création, d'édition et de consultation, ainsi que la recherche, les filtres et les actions. Le rendu passe par Inertia et Vue, Saddle s'appuie sur la connexion et les policies déjà en place dans votre application Laravel, et ne touche pas au reste de votre code.

<table>
<tr>
<td width="50%" valign="middle">

### Des tableaux qui font le travail

Colonnes triables et consultables avec badges et booléens, filtres, actions par ligne et actions groupées avec confirmation. Exportez la vue courante en CSV, ou importez un CSV qui passe par la même validation que votre formulaire.

[Tableaux et colonnes →](../sections/tables.md)

</td>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/table-dark.png">
  <img src="../../.github/screenshots/table-light.png" alt="Un tableau de ressources Saddle avec des filtres et une barre d'actions groupées">
</picture>
</td>
</tr>
<tr>
<td width="50%">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="../../.github/screenshots/form-dark.png">
  <img src="../../.github/screenshots/form-light.png" alt="Un formulaire d'édition Saddle avec une section, une grille à deux colonnes et des onglets">
</picture>
</td>
<td width="50%" valign="middle">

### Des formulaires avec une vraie mise en page

Onze types de champs, du texte et des listes déroulantes jusqu'aux sélecteurs de relations avec recherche, à l'envoi de fichiers et au Markdown. Organisez-les en sections, grilles et onglets. La validation, la visibilité et l'enregistrement reposent tous sur les mêmes définitions de champs.

[Champs →](../sections/fields.md) · [Mise en page des formulaires →](../sections/forms-layout.md)

</td>
</tr>
<tr>
<td width="50%" valign="middle">

### Une classe par modèle

Une ressource, c'est du PHP tout simple. Déposez-la dans `app/Saddle/` et elle est découverte au démarrage : aucune route, aucun contrôleur ni fichier Vue à écrire. L'accès suit vos policies Laravel et reste fermé par défaut.

[Ressources →](../sections/resources.md) · [Autorisation →](../sections/authorization.md)

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

## Fonctionnalités

<table>
<tr>
<td width="33%" valign="top">

**Ressources**
- Classes de ressources découvertes automatiquement
- Pages de liste, de création, d'édition et de consultation en lecture seule
- Générateur `saddle:resource`
- Chargement anticipé (eager loading) et colonnes de relations

</td>
<td width="33%" valign="top">

**Formulaires**
- Texte, mot de passe, zone de texte, nombre, liste déroulante, interrupteur
- Date, date et heure, Markdown, envoi de fichiers
- Sélecteur `BelongsTo` avec recherche
- Sections, grilles et onglets

</td>
<td width="33%" valign="top">

**Tableaux**
- Colonnes texte, badge et booléennes
- Tri, recherche et `formatUsing`
- Filtres par liste et booléens
- Pagination

</td>
</tr>
<tr>
<td valign="top">

**Actions**
- Actions par ligne et actions groupées
- Boîtes de dialogue de confirmation
- Vérification des policies pour chaque enregistrement
- Traitements groupés dans une transaction

</td>
<td valign="top">

**Relations**
- Gestionnaires de relations `HasMany`
- Création, édition et suppression des enregistrements liés sur place
- Limités à l'enregistrement parent

</td>
<td valign="top">

**Tableau de bord**
- Widgets de statistiques avec mini-graphiques
- Widgets de graphiques
- Notifications en base de données avec une cloche
- Recherche globale sur toutes les ressources

</td>
</tr>
<tr>
<td valign="top">

**Accès**
- Policies Laravel standard
- Fermé par défaut
- Visibilité par champ avec `canSee`
- Utilise votre connexion existante

</td>
<td valign="top">

**Multi-tenant**
- Tenants définis par l'URL (`/admin/{tenant}`)
- Filtrage automatique des requêtes et rattachement au tenant
- Sélecteur de tenant et inscription en libre-service
- Point d'accroche d'accès pour la facturation

</td>
<td valign="top">

**Données**
- Export CSV de la vue courante
- Import CSV via la validation du formulaire
- Suppression logique avec restauration et suppression définitive

</td>
</tr>
<tr>
<td valign="top">

**Apparence et langue**
- Thèmes clair, sombre et système
- Nom de marque, couleur d'accent et tokens de thème
- Panneau traduit en 14 langues
- Prise en charge des nonces CSP

</td>
<td valign="top">

**Plugins**
- De simples paquets Composer
- Champs et colonnes personnalisés sous forme de web components
- Hooks pour les props partagées, la navigation et les tokens de thème

</td>
<td valign="top">

**Expérience développeur**
- Outils de test pour Pest et PHPUnit
- `saddle:install` et `saddle:upgrade`
- Avertissement d'assets obsolètes après une mise à jour
- Laravel 11, 12 et 13

</td>
</tr>
</table>

## Construit avec

<p>
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP_8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

## Démarrage rapide

Nécessite PHP 8.4+ et Laravel 11.48+, 12 ou 13.

```bash
composer require saddlephp/saddlephp
php artisan saddle:install
php artisan saddle:resource HorseResource --model=Horse
```

Connectez-vous via la page de connexion de votre application, enregistrez une policy pour le modèle, puis rendez-vous sur `/admin`. Lancez `php artisan saddle:upgrade` après chaque mise à jour du paquet. Le [guide de démarrage](../sections/getting-started.md) détaille chaque étape.

## En savoir plus

| | |
|---|---|
| **Construire** | [Ressources](../sections/resources.md) · [Champs](../sections/fields.md) · [Mise en page des formulaires](../sections/forms-layout.md) · [Tableaux](../sections/tables.md) · [Filtres](../sections/filters.md) · [Actions](../sections/actions.md) · [Gestionnaires de relations](../sections/relations.md) |
| **Panneau** | [Widgets du tableau de bord](../sections/widgets.md) · [Recherche globale](../sections/search.md) · [Notifications](../sections/notifications.md) · [Suppression logique](../sections/soft-deletes.md) · [Import et export](../sections/import-export.md) |
| **Exploitation** | [Autorisation](../sections/authorization.md) · [Multi-tenant](../sections/tenancy.md) · [Compléments multi-tenant](../sections/tenancy-extras.md) · [Configuration](../sections/configuration.md) · [Thèmes](../sections/theming.md) · [Localisation](../sections/i18n.md) |
| **Extension** | [Plugins](../sections/plugins.md) · [Outils de test](../sections/testing.md) · [Contribuer](../../CONTRIBUTING.md) · [Sécurité](../../SECURITY.md) |

## Licence

Saddle est un logiciel open source distribué sous [licence MIT](../../LICENSE).
