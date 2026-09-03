# Changelog

## [2.2.0](https://github.com/mykemeynell/reflection/compare/v2.1.0...v2.2.0) (2026-09-03)


### Features

* array access implementation ([a5a9aa0](https://github.com/mykemeynell/reflection/commit/a5a9aa0009c0da74dcc00a983dd22a4fb9075e6a))
* **container:** implement ArrayAccess support with full test coverage ([41be466](https://github.com/mykemeynell/reflection/commit/41be466577e2e6e4d567f46b29001bdfc0bb4305))


### Miscellaneous Chores

* **ci/cd:** replace CI/CD workflow with modular reusable workflows ([6b7f352](https://github.com/mykemeynell/reflection/commit/6b7f3527f1178612012ddf2432beb7fc02f1f15d))
* **docs:** rename project and update references in README ([c50bff2](https://github.com/mykemeynell/reflection/commit/c50bff240da6224c02a1bc0a353ce92361a0a6ef))
* set license to MIT in composer.json ([62ee08d](https://github.com/mykemeynell/reflection/commit/62ee08d836aba5abaa7c37d467d6bf9789b5c074))
* trigger workflow ([89aeae1](https://github.com/mykemeynell/reflection/commit/89aeae14e11d13dc3107cbd72497b194ef28693e))

## [2.1.0](https://github.com/mykemeynell/Reflection/compare/v2.0.0...v2.1.0) (2026-08-28)


### Features

* **ci/cd:** integrate PHPStan static analysis tool ([0f588ad](https://github.com/mykemeynell/Reflection/commit/0f588ad3cfc1a473f5567808b7370f509c5a101f))
* **container:** support attributes and named make arguments ([ea9dc41](https://github.com/mykemeynell/Reflection/commit/ea9dc41255d97fb1c9ee929751c3b749e01dcebe))


### Miscellaneous Chores

* add MIT license file ([0b0fa4e](https://github.com/mykemeynell/Reflection/commit/0b0fa4e393a2919f956f412bdee9007d3c73bff5))
* **tests:** remove unused imports in `StrictTypesTest.php` ([d551613](https://github.com/mykemeynell/Reflection/commit/d551613364b28857b79ebaf4504ca5253d81fcf0))

## [2.0.0](https://github.com/mykemeynell/Reflection/compare/v1.0.0...v2.0.0) (2026-08-27)


### Features

* add CI/CD workflow with PHP tests, release automation, and branch cleanup ([15184ba](https://github.com/mykemeynell/Reflection/commit/15184ba9a7fa84604898511cf92c3a24dc612f3f))
* add closure support to the container, enhance parameter handling, and include unit tests for app helper ([e52effa](https://github.com/mykemeynell/Reflection/commit/e52effacae8a1abdedfa5d737a8d08860000c5a7))
* add support for named parameter resolution in the container and update dependencies ([fbf1da2](https://github.com/mykemeynell/Reflection/commit/fbf1da2c451535ab1a220a77d6f74e1f0326c480))
* add support for object instances in contextual bindings and resolution methods, with test coverage updates ([2e11134](https://github.com/mykemeynell/Reflection/commit/2e11134b70866228f3808afee171c3e70eda8f3a))
* add support for object instances in contextual bindings and resolution methods, with test coverage updates ([0fde8aa](https://github.com/mykemeynell/Reflection/commit/0fde8aaec44684845972b1e75fcf378585b4767a))
* implement PSR-11 compliance for container with updated exception handling, type hints, and contextual binding validation ([0e40c52](https://github.com/mykemeynell/Reflection/commit/0e40c529fb6f82f177d0c03963892f6bbd177a6a))


### Miscellaneous Chores

* add code style check step to CI/CD workflow ([f91e096](https://github.com/mykemeynell/Reflection/commit/f91e09663ad8607abe9e81da00f196d19ec7d99e))
* added `composer.lock` to `.gitignore` ([f4ea188](https://github.com/mykemeynell/Reflection/commit/f4ea18808fb069d17e08464233ebcba8a8826189))
* prepare 2.0.0 release ([de884f7](https://github.com/mykemeynell/Reflection/commit/de884f74f946eb8b7b707969238fbac2bab84dfe))
* remove index.php ([9c03cd3](https://github.com/mykemeynell/Reflection/commit/9c03cd359b8630734eefee2d256b869b1f333891))
* remove redundant `function_exists` check for `app` helper function in `helpers.php` ([07c84c5](https://github.com/mykemeynell/Reflection/commit/07c84c5eb3e9de6faaa872d468b02c0417592593))
* remove unused PhpStorm `Pure` attribute from `Container` class ([a53e03b](https://github.com/mykemeynell/Reflection/commit/a53e03b230f727d71231824c90e755c552b7275c))
* removed `composer.lock` from vcs ([3863489](https://github.com/mykemeynell/Reflection/commit/386348970cf1d0fc877691f6c5c43f4eeb03576d))
* rename `Reflector` namespace to `Reflection` across project files and update autoload configuration ([fb9e8c5](https://github.com/mykemeynell/Reflection/commit/fb9e8c5817f5c3caed1cb6e0c2f0c3e1e466b45a))
* update `composer.json` to refine autoload settings and add testing and linting scripts ([fbf1da2](https://github.com/mykemeynell/Reflection/commit/fbf1da2c451535ab1a220a77d6f74e1f0326c480))
* update `pestphp/pest` dev dependency version constraint in `composer.json` ([a506571](https://github.com/mykemeynell/Reflection/commit/a5065712a9465f6f7bf0055d214a4ca82f0b4ef9))
* update author email in `composer.json` ([a9f3edc](https://github.com/mykemeynell/Reflection/commit/a9f3edce585979be77bd434dbe6ee968bce071c6))
* update PHP requirement to 8.3 and remove unused dev dependency `symfony/var-dumper` ([ccba740](https://github.com/mykemeynell/Reflection/commit/ccba740febe141b773e0d5d7d5e42c9841c9039b))
