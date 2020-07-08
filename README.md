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
