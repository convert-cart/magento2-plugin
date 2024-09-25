# Convert Cart Magento 2 Plugin

![Magento 2](https://img.shields.io/badge/Magento-2-brightgreen.svg)
![Version](https://img.shields.io/badge/version-1.0.8-blue.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)
[![Packagist](https://img.shields.io/packagist/v/convert-cart/analytics.svg)](https://packagist.org/packages/convert-cart/analytics)

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
  - [Composer Installation](#composer-installation)
  - [Manual Installation](#manual-installation)
- [Configure Domain Id](#configure-domain-id)
- [Troubleshooting](#troubleshooting)
- [Contact](#contact)

## Introduction

Welcome to the Magento 2 Plugin by Convert Cart. This plugin integrates seamlessly with Magento 2 ecommerce websites, enabling the tracking of user behavior. Additionally, it synchronizes crucial data such as product catalogs, order histories, customer profiles, and category information to our servers on a regular basis. This synchronization powers our recommendation engine, providing personalized and data-driven insights to enhance your ecommerce operations.

## Features

- Script injection on the frontend for user behavior tracking.
- Token generation for synchronizing product/order/customer/category data to Convert Cart servers for recommendations.
- Product deletion tracking to avoid recommending deleted products to the visitors of the store.

## Installation

### Composer Installation

1. Run the following command in the root folder of your Magento installation when the domain is added in app.convertcart.com (for production):

    ```sh
    composer require convert-cart/analytics
    ```

    If you're intending to setting up a domain in app-beta.convertcart.com (for beta testing), please use tag name followed by the suffix `-beta` like `1.0.8-beta`. The command will be like,

    ```sh
    composer require convert-cart/analytics:1.0.8-beta
    ```

    If you wanted to know the exact changes that's needed to setup a beta server on a production tag, you can take a look into this [commit](https://github.com/convert-cart/magento2-plugin/commit/7fcd6766d00aa0c1f9c24365864a5738bc893252).

2. After installing via Composer, run the following commands from the Magento root directory:

    ```sh
    php bin/magento maintenance:enable
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy -f
    php bin/magento maintenance:disable
    php bin/magento cache:flush
    ```

### Manual Installation

1. Download the latest version of the plugin from the [releases](https://github.com/convert-cart/magento2-plugin/releases) page.
2. Extract the downloaded archive.
3. Upload the contents to the `app/code/convert-cart/analytics` directory of your Magento installation.
4. Run the following commands from the Magento root directory:

    ```sh
    bin/magento module:enable Convertcart_Analytics
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento setup:static-content:deploy -f
    bin/magento cache:clean
    bin/magento cache:flush
    ```

## Configure Domain Id

Please reach out to your Customer Support Manager to Configure your domain with Convert Cart.

## Troubleshooting

If you encounter issues, try the following steps:

1. Ensure the plugin is enabled: `bin/magento module:status Convertcart_Analytics`
2. Clear Magento cache: `bin/magento cache:clean`
3. Check the logs in `var/log` for any error messages.

## Uninstall

Please use the following command to uninstall the plugin and delete all the tables and settings related to the plugin,

      bin/magento module:uninstall Convertcart_Analytics

### Setting up folder & file permissions,

If you encounter folder permission issues on folder such as cache, please use the following commands to set the appropriate permissions for public files and directories:
- Goto magento2 directory

      find . -type f -exec chmod 644 {} \;
      find . -type d -exec chmod 755 {} \;
      find var pub/static pub/media app/etc generated/ -type f -exec chmod g+w {} \;
      find var pub/static pub/media app/etc generated/ -type d -exec chmod g+ws {} \;
      chown -R <magento user>:<web server group> . #(usually by default magento user and we user used to be www-data, check it with your server administrator)
      chmod u+x bin/magento

- 644 sets files to read and write for the owner, and read-only for group and others.
- 755 sets directories to read, write, and execute for the owner, and read and execute for group and others.

## Contact

Please contact [sales@convertcart.com](mailto:sales@convertcart.com) if any issues occur during the integration process.
