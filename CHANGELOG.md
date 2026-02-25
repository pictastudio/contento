# Changelog

All notable changes to `contento` will be documented in this file.

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
