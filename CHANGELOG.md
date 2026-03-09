# Changelog

All notable changes to `contento` will be documented in this file.

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
