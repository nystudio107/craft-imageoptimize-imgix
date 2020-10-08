# ImageOptimize Imgix Image Transform Changelog

## 1.1.3 - 2020.10.07
### Fixed
* Fixed improperly generated `webp` URL for Imgix

## 1.1.2 - 2020.02.11
### Changed
* Updated to `imgix/imgix-php` version `^3.0.0`

## 1.1.1 - 2019.10.03
### Changed
* Changed `clamp` to `clip` for the **fit** transform method

## 1.1.0 - 2019.07.05
### Changed
* Updated to work with the ImageOptimize 1.6.0 `ImageTransformInterface`

## 1.0.3 - 2019.02.22
### Changed
* Fixed an issue where focal points weren't always respected for Imgix

## 1.0.2 - 2019.02.07
### Changed
* Fixed an issue where `.env` vars were not actually parsed

## 1.0.1 - 2019.02.07
### Changed
* If you're using Craft 3.1, ImageOptimize will use Craft [environmental variables](https://docs.craftcms.com/v3/config/environments.html#control-panel-settings) for secrets

## 1.0.0 - 2018.12.28
### Added
- Initial release
