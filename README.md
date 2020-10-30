[![Stories in Ready](https://badge.waffle.io/deGov/deGov.png?label=ready&title=Ready)](https://waffle.io/deGov/deGov)

# deGov - Drupal 8 for Government


## Notes

deGov is the first Drupal 8 distribution focussing on the needs of (German) governmental organisations. It uses Acquia Lightning as a basis and extends this it with valueable functions to meet the use cases for different scenarios:

- Websites for governmental organisations from all levels (federal, regional, local) to publish information
- Service-oriented E-Government portals to close the gap between citizens and your administration
- Citizen Engagement portals to discuss and decide online
- Open311 portals for civic issue tracking
- Open data portals to publish and create communities around data
- Intranet/Extranet for government employees

Sounds interesting? Then go for it and install the first Drupal 8 distribution for government!

## Prerequisites

- Webserver (Apache2, Nginx, Hiawatha, Microsoft IIS)
- PHP >= 7.1+ | Memory >= 64MB
- RDMS (MySQL => 5.5.3, MariaDB => 5.5.20, Percona Server => 5.5.8)
- Mailserver (Postfix, exim, etc.)
- [Composer](https://getcomposer.org/download/ "https://getcomposer.org/download/")

## Installing deGov

**BEFORE YOU INSTALL:** please read the [prerequisites](#prerequisites)

First your need to setup the deGov repository.

```
composer create-project degov/degov-project MY_PROJECT_PATH
```

Change your **working directory** into deGov
```
$ cd MY_PROJECT_PATH
```

You need to install deGov. You need to choose one installation type
####**Drush**
You can install your site via drush. Drush will be shipped by deGov
```
bin/drush si degov
```
####**Webinstaller**
Visit the **deGov-Domain** to start the installation process.
Follow the Installer instructions to successfully finish the installation process.
```
http://[YOUR_SITE]/
```

## Usage
Visit the following page to log into your deGov Installation with your previously created User to administer your deGov Installation.
```
http://[YOUR_SITE]/user/login/
```

## Testing

### PHPUnit
Drupal 8 needs PHPUnit 4.8. Therefor it is recommended to download the [PHP phar archive](https://phar.phpunit.de/) in the
appropriate version. Copy the `phpunit.xml.dist` file to `phpunit.xml` for fitting your needs. Afterwards you can execute the
tests via:
```
php ./phpunit-4.8.9.phar
```

### Behat
Needs to be added

Learn more about [testing in Drupal 8](https://www.drupal.org/docs/8/testing).

## License
[GNU General Public License, version 2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html "visit GPLv2 website"). The same as the Drupal core.
