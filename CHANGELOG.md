# ImageOptimize Imgix Image Transform Changelog

## 4.0.5 - UNRELEASED
### Added
* Add `phpstan` and `ecs` code linting
* Add `code-analysis.yaml` GitHub action

### Changed
* PHPstan code cleanup
* ECS code cleanup

## 4.0.4 - 2024.02.23
### Fixed
* Fixed a purging issue due to checking the unparsed API key variable, and sending the wrong payload key ([#7](https://github.com/nystudio107/craft-imageoptimize-imgix/pull/7))

## 4.0.3 - 2023.10.23
### Fixed
* Parse API key environment variable before checking length ([#6](https://github.com/nystudio107/craft-imageoptimize-imgix/pull/6))

## 4.0.2 - 2023.09.29
### Fixed
* Fixed an issue where the url is encoded twice ([#5](https://github.com/nystudio107/craft-imageoptimize-imgix/pull/5))

## 4.0.1 - 2022.07.06
### Fixed
* Fixed an issue where calling `generateUrl()` would throw an exception ([#342](https://github.com/nystudio107/craft-imageoptimize/issues/342))

## 4.0.0 - 2022.05.25
### Added
* Initial Craft CMS 4 release

## 4.0.0-beta.1 - 2022.03.20

### Added

* Initial Craft CMS 4 compatibility
