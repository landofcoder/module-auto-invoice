# Magento 2 Auto Invoice
Magento 2 procedure to automatically invoice orders in a given status.

## System requirements
This extension supports the following versions of Magento:

*	Community Edition (CE) versions 2.2.x and 2.3.x
*	Enterprise Edition (EE) versions 2.2.x and 2.3.x

## Installation
1. Require the module via Composer
```bash
$ composer require landofcoder/module-autoinvoice
```

2. Enable the module
```bash
$ bin/magento module:enable Lof_AutoInvoice
$ bin/magento setup:upgrade
```

## Configuration
The configuration of this module is under _Stores > Configuration > Sales > Auto Invoice_.
There, you will be able to activate processing via cron job, and choose the behaviour of the procedure.

The configuration matrix will allow you to set on for which combinations of status and payment method the extension should invoice the orders, as well as the destination status and capture mode.
A configuration example follows.


## Usage
The module supports two different usage methods.

### Command line
The following command will execute the procedure:

```bash
$ bin/magento lof:autoinvoice:process
```

A dry run mode is also available, just to see what orders would be affected by the procedure:
```bash
$ bin/magento lof:autoinvoice:process --dry-run=1
```

### Cron
By activating the cron, the procedure will be automatically executed every hour.

1. Login to the admin
2. Go to Stores > Configuration > Sales > Auto Invoice
3. Set _Schedule procedure_ to yes
4. Specify a custom cron expression, if needed
5. Clean the cache

## Authors, contributors and maintainers

Author:
- [Landofcoder](https://github.com/landofcoder)

## License
Licensed under the Open Software License version 3.0

## Donation

If this project help you reduce time to develop, you can give me a cup of coffee :) 

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/paypalme/allorderdesk)


**Our Magento 2 Extensions List**
* [Megamenu for Magento 2](https://landofcoder.com/magento-2-mega-menu-pro.html/)

* [Page Builder for Magento 2](https://landofcoder.com/magento-2-page-builder.html/)

* [Magento 2 Marketplace - Multi Vendor Extension](https://landofcoder.com/magento-2-marketplace-extension.html/)

* [Magento 2 Multi Vendor Mobile App Builder](https://landofcoder.com/magento-2-multi-vendor-mobile-app.html/)

* [Magento 2 Form Builder](https://landofcoder.com/magento-2-form-builder.html/)

* [Magento 2 Reward Points](https://landofcoder.com/magento-2-reward-points.html/)

* [Magento 2 Flash Sales - Private Sales](https://landofcoder.com/magento-2-flash-sale.html)

* [Magento 2 B2B Packages](https://landofcoder.com/magento-2-b2b-extension-package.html)

* [Magento 2 One Step Checkout](https://landofcoder.com/magento-2-one-step-checkout.html/)

* [Magento 2 Customer Membership](https://landofcoder.com/magento-2-membership-extension.html/)

* [Magento 2 Checkout Success Page](https://landofcoder.com/magento-2-checkout-success-page.html/)


**Featured Magento Services**

* [Customization Service](https://landofcoder.com/magento-2-create-online-store/)

* [Magento 2 Support Ticket Service](https://landofcoder.com/magento-support-ticket.html/)

* [Magento 2 Multi Vendor Development](https://landofcoder.com/magento-2-create-marketplace/)

* [Magento Website Maintenance Service](https://landofcoder.com/magento-2-customization-service/)

* [Magento Professional Installation Service](https://landofcoder.com/magento-2-installation-service.html)

* [Customization Service](https://landofcoder.com/magento-customization-service.html)

