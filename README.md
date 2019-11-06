## Description

This is a compatibility module for [Magento2AttributeLanding](https://github.com/EmicoEcommerce/Magento2AttributeLanding) and [Magento2Tweakwise](https://github.com/EmicoEcommerce/Magento2Tweakwise).
It will install the following packages: 
1. emico/tweakwise
2. emico/tweakwise-export
3. emico/m2-attributelanding
4. emico/m2-attributelanding-tweakwise (this package)

Packages emico/tweakwise and emico/tweakwise-export provides magento2 integration with the Tweakwise navigator (https://www.tweakwise.com/)
Package emico/m2-attributelanding provides magento2 support for landingspages based on attributes and categories for example "red-pants", here category is "pants" and the attribute is color with value red.
This package provides the integration between the navigator and the landingspages.


## Installation
Install package using composer
```sh
composer require emico/m2-attributelanding-tweakwise
```

Run installers
```sh
php bin/magento setup:upgrade
```
