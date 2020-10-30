CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
------------

Provides the [Font Awesome Icon Picker](https://farbelous.io/fontawesome-iconpicker/) for menu items.

REQUIREMENTS
------------

This module requires no Drupal modules outside of Drupal core. Libraries on which this module is based:
* [Font Awesome Icon Picker](https://farbelous.io/fontawesome-iconpicker/): An icon picker, which is based on [Bootstrap Popover Picker](https://farbelous.io/bootstrap-popover-picker/). For supporting Font Awesome in version 4, version 1.4.1 of Font Awesome Icon Picker must be installed.

See JavaScript library dependencies in `js/package.json`.

INSTALLATION
------------

* Install this module as you would normally install a
 contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
 further information.

If you have made any JavaScript code changes, you must re-compile the JavaScript code.
1. Switch to the `js` folder
2. Make sure NPM and NVM are installed
3. Run `nvm use 9.11.1` (its proven, that NPM works with version `9.11.1` - newer versions can possibly work, too)
4. Run `npm install` (installs all JavaScript dependencies)
5. Run `npm run build` (compiles the assets for a production environment, run `npm run build-dev` for a development environment. Then you will have JavaScript source maps for example.)

CONFIGURATION
-------------

This module provides no configuration.
