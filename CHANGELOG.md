# ImageOptimize Imgix Image Transform Changelog

## 1.2.8 - 2023.10.23
### Fixed
* Parse API key environment variable before checking length ([#6](https://github.com/nystudio107/craft-imageoptimize-imgix/pull/6))

## 1.2.7 - 2023.09.29
### Fixed
* Fixed an issue where the url is encoded twice ([#5](https://github.com/nystudio107/craft-imageoptimize-imgix/pull/5))

## 1.2.6 - 2022.03.29
### Changed
* Tighten the `composer.json` `require` constraints to limit it to the Craft 3 version of ImageOptimize

## 1.2.5 - 2022.02.24

### Changed

* Loosen the `composer.json` `require` constraints

## 1.2.4 - 2022.02.23
### Fixed
* Use the new Imgix purge API endpoint properly ([#4](https://github.com/nystudio107/craft-imageoptimize-imgix/issues/4))
* Fixed an issue where corrupted images could result in the Imgix transformer throwing an exception ([#3](https://github.com/nystudio107/craft-imageoptimize-imgix/issues/3)) ([#306](https://github.com/nystudio107/craft-imageoptimize/issues/306))

## 1.2.3 - 2021.09.07
### Fixed
* Fixed an issue where the wrong Imgix API was being used for purging images ([#273](https://github.com/nystudio107/craft-imageoptimize/issues/273))

## 1.2.2 - 2021.04.23
### Added
* Added a setting to control the amount an image needs to be scaled down for automatic sharpening to be applied (https://github.com/nystudio107/craft-imageoptimize/issues/263)

## 1.2.1 - 2021.03.28
### Changed
* Added the **Unsharp Mask (USM)** config setting

## 1.2.0 - 2021.02.23
### Changed
* Updated to use the new Imgix [purging API](https://blog.imgix.com/2020/10/16/api-deprecation)

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
