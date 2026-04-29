# Changelog

All notable changes to `contento` will be documented in this file.

## v0.8.0 - 2026-04-29

### v0.8.0 - 2026-04-29

#### What's Changed

This release expands the page API with first-class structured metadata support and adds an opt-in collection mode for selected list endpoints that need every matching record without the package's implicit visibility filtering.

#### Features

- Added nullable page `metadata` support across the package, including validation, model casting, API resource serialization, Bruno examples, README guidance, and an additive `update_pages_add_metadata` migration for existing installations.
- Added `filter=all` support to the menus, modals, and content tags index endpoints so host applications can request every matching record as a non-paginated collection.

#### Improvements

- Made the `filter=all` collection mode remove implicit active and visibility date-range scopes for the affected endpoints while preserving explicit query filtering and sorting behavior.
- Extended the stable page resource contract with the new public `metadata` field.

#### Tooling

- Refreshed the published Bruno requests and README docs for page metadata payloads and the new `filter=all` list behavior.

#### Tests

- Added feature coverage for page metadata persistence and serialization, stable resource contract coverage for the new page field, and regression coverage for `filter=all` responses on menus, modals, and content tags.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.7.0...v0.8.0

## v0.7.0 - 2026-04-23

### What's Changed

This release adds a first-class metadata API to Contento, making metadata records configurable package models with stable JSON resources, slug-based routing, and the same headless package conventions used across the existing CMS endpoints.

### Features

- Added `metadata` package support with configurable model and table bindings, dedicated migrations, validation contracts, resource serialization, factory support, and REST API routes for listing, creating, updating, showing, and deleting metadata records.
- Added slug-based route model binding for metadata so host applications can address records by either ID or slug while keeping the public JSON response shape stable.
- Added created, updated, and deleted metadata events so host applications can extend metadata lifecycle behavior without hard dependencies.

### Improvements

- Extended helper model resolution and service-provider publishing so metadata participates in the same swappable architecture as the other Contento package resources.
- Added metadata list filtering, sorting, and date-range query validation for the public API surface.

### Tooling

- Added a published Bruno request set for metadata endpoints, including list, show, store, update, and delete examples.

### Tests

- Added feature coverage for metadata CRUD flows, exact and text filtering, pagination, unique slug and URI validation, slug route model binding, event dispatching, package architecture wiring, and stable resource contracts.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.6.0...v0.7.0

## v0.6.0 - 2026-04-23

### What's Changed

This release adds first-class image collections to content tags, expands the stable public API contract for those resources, and refreshes the published API tooling for the latest request shapes.

### Features

- Added content tag image collection support with upload handling, typed `thumb` and `cover` slots, generic gallery images, and metadata-only updates on existing images.
- Added an additive `update_content_tags_add_images` migration for existing installations and included the new `images` field in the stable content tag API resource contract.

### Improvements

- Normalized stored content tag image payloads so image IDs, ordering, and typed image uniqueness stay consistent across create and update flows.
- Returned absolute image asset URLs from Contento JSON resources while keeping resource wrapping behavior scoped to Contento resources.

### Tooling

- Refreshed the published Bruno collection with content tag image requests, raw-array bulk upsert examples, additional list filter docs, localized mail form examples, and updated slug and sort-order payload samples.
- Switched the GitHub Actions test workflow to manual `workflow_dispatch` runs only.

### Tests

- Added feature coverage for content tag image uploads, metadata updates without re-uploading, duplicate typed image validation, and resource contract coverage for the new `images` field.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.5.0...v0.6.0

## v0.5.0 - 2026-04-20

### What's Changed

This release expands bulk content workflows, adds more lifecycle extension points across the package, and makes the package friendlier to host applications by removing frontend-style response assumptions and default auth model assumptions.

### Features

- Added `POST /menu-items/bulk/upsert` to create and update menu items in a single request, including multilingual payload support and duplicate or missing target validation.
- Added `sort_order` migrations, validation, persistence, and default ordering for FAQs and menu items, including ordered FAQ category relations and bulk upsert handling.
- Added created, updated, and deleted events for FAQs, FAQ categories, mail forms, modals, and settings.

### Improvements

- Made JSON resource wrapping opt-in and scoped wrapping behavior to Contento resources, with unwrapped responses now enabled by default.
- Made author tracking opt-in and removed the default `User` model assumption so host apps can enable author persistence without hard-coded auth dependencies.
- Moved default settings seeding to an opt-in publishable migration and added configurable default settings records in package config.

### Tooling

- Updated CI, README, Bruno requests, and static analysis config for the latest API surface, including menu-item bulk upserts and default settings publishing.

### Tests

- Added coverage for model lifecycle events, stable resource contracts, slug route model binding, ordered FAQ and menu-item responses, and the unwrapped resource response default.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.4.1...v0.5.0

## v0.4.1 - 2026-03-23

### What's Changed

This patch release adds FAQ bulk upserts, aligns menu-item trees with the same package-backed behavior used in Venditio, and tightens API filtering and serialization consistency across the package.

### Features

- Added `POST /faqs/bulk/upsert` to create and update FAQs in a single request, including multilingual payload support and duplicate or missing target validation.

### Improvements

- Switched menu items to `nevadskiy/laravel-tree`, added persisted tree paths, and aligned `as_tree` responses and path serialization with the Venditio category tree behavior.
- Preserved nullable casted API fields like `parent_id` instead of coercing them to `0` or `false` in JSON resources.
- Normalized controller filtering so text fields use text matching while exact filters stay exact across FAQs, menus, menu items, content tags, mail forms, modals, pages, and settings.

### Tooling

- Updated the published Bruno requests and README for the latest list query parameters and the new FAQ bulk-upsert endpoint.

### Tests

- Added feature coverage for FAQ bulk upserts, menu-item tree paths, menu item path serialization, and the latest list filtering behavior.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.4.0...v0.4.1

## v0.4.0 - 2026-03-16

### What's Changed

This release adds first-class menu APIs to Contento and expands multilingual payload handling across existing CMS resources.

### Features

- Added configurable menu and menu-item models, migrations, validations, controllers, resources, events, and routes.
- Added menu and menu-item list filters for ids, visibility windows, active status, includes, and tree responses, with slug-based route model binding support.

### Improvements

- Added translatable request/model support for page content payloads, mail form localized fields, and modal localized CTA URLs.
- Extended translatable input handling to accept nested `translations` payload wrappers and serialized localized content structures.

### Tooling

- Added Bruno request sets for menus and menu items and refreshed API docs for the latest query parameters and response structure.
- Updated package configuration and helper resolution so host apps can swap menu and menu-item implementations like the other CMS models.

### Tests

- Added feature coverage for menus, menu items, expanded scope toggles, multilingual payload validation, and localized response behavior.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.3.0...v0.4.0

## v0.3.0 - 2026-03-10

### What's Changed

This release aligns Contento's public-content visibility behavior with the Venditio global-scope pattern and updates the Bruno collection to reflect the latest query surface.

### Features

- Added default public visibility scopes for pages, FAQs, modals, content tags, and FAQ categories.
- Added request-level scope toggles so API consumers can explicitly bypass active, date-range, or publication scopes when needed.
- Added controller-side filter handling so explicit API filters override matching implicit model scopes instead of being masked by them.

### Tooling

- Updated Bruno list requests for pages, content tags, FAQs, modals, FAQ categories, mail forms, and settings with current query params and inline docs.

### Tests

- Added regression coverage for scoped list endpoints and explicit-scope override behavior.
- Normalized content factories so default fixtures remain publicly visible under the new scope rules.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.2.2...v0.3.0

## v0.2.2 - 2026-03-09

### What's Changed

This patch release broadens the supported `pictastudio/translatable` package range to stay compatible with newer stable releases.

### Dependencies

- Relaxed the `pictastudio/translatable` Composer constraint from `^0.1` to `^0`.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.2.1...v0.2.2

## v0.2.1 - 2026-03-09

### What's Changed

This patch release improves package configuration merging and aligns the API tooling with the latest localized request expectations.

### Improvements

- Merge package configuration recursively so host applications can override nested config keys without replacing the whole tree.
- Added architecture coverage to verify recursive config merging in the service provider.

### Tooling

- Added `Locale` header support across the published Bruno API collection and local environment.

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.2.0...v0.2.1

## v0.2.0 - 2026-03-05

v0.2.0

Refactor Contento to a fully config-driven, swappable package architecture aligned with Venditio patterns.

- Added model/query helpers: resolve_model(), query(), get_fresh_model_instance().
- Introduced validation contracts + implementations for all CMS resources.
- Moved validation bindings to config-driven container mappings.
- Reworked service provider boot flow for optional/versioned API routes.
- Centralized route config under contento.routes.api.v1 (prefix, name, middleware, pagination).
- Refactored controllers/actions/models/traits to resolve models via configuration.
- Added architecture coverage tests and updated feature tests to the new config shape.
- Removed legacy compatibility paths; package now uses only the new architecture.

Test status: 97 passed.

## v0.1.2 - 2026-02-26

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.1.1...v0.1.2

## v0.1.1 - 2026-02-26

### What's Changed

- resolve route model binding by id or slug (multilingual)

**Full Changelog**: https://github.com/pictastudio/contento/compare/v0.1.0...v0.1.1

## v0.1.0 - 2026-02-25

### What's in this release

API-only Laravel CMS package for managing dynamic content

#### Features

- **API-only CMS** - Headless content APIs; no Blade views or frontend assets
- **Translations** - Multi-language content with `pictastudio/translatable`; create and update multiple translations in a single request
- **Slug support** - Automatic slug generation via `spatie/laravel-sluggable` with route model binding
- **Authorization** - Policy-based, overridable authorization for content operations
- **Bruno API collection** - Publishable Bruno API requests for testing and documenting the CMS APIs

#### Improvements

- **Request validation** - Form request classes with reusable, overridable validation rules
- **Route model binding** - Slug-based binding for pages and related resources
- **Configuration** - Configurable table names and API behaviour; sensible defaults
- **Migrations** - Consistent migration naming; table names configurable via config
- **Database factories** - Refactored factories for tests and local development
- **Dependencies** - Updated to the new translatable library and current Laravel/Composer stack

#### Fixes

- Slug generation and slug handling in models and tests
- Translations management and authorization logic
- Migration and config alignment

#### Developer experience

- **AGENTS.md** - Package rules and conventions for AI/agent-assisted development
- **Code style** - Laravel Pint formatting; GitHub CI tests disabled by default

**Full Changelog**: https://github.com/pictastudio/contento/commits/v0.1.0
