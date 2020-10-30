# Robo scripts

Robo is a taskrunner, made in PHP. We are using it e.g. for running deGov updates easily. Learn more [about Robo](https://robo.li/getting-started/).


#### Check out robo commands

```
cd <project root>
bin/robo
```


#### Running in verbose mode

The verbose mode shows you, what happens in detail. E.g. Composer output. Otherwise the output it kept minimal.

```
composer degov-update -- -vvv
```

## Upgrade 

### Migrating RoboFile.php

Previously we used a single RoboFile containing all Tasks. 
Now we switched to a [Register command files via PSR-4 autoloading](https://robo.li/extending/#register-command-files-via-psr-4-autoloading) approach, to be able to maintain Robo commands within their (git-)contexts.

The existing Robofile in the project-root is not required anymore to use the provided commands.

* Robofile has no custom robo tasks?
    * Just delete Robofile.php

* Robofile has custom robo tasks?
    * Just delete the not custom robo tasks, which are now provided via own Commands files

#### Migrate autoloading
To find the new Commands files it is necessary to change the autoload-dev section in the composer.json.

```
"autoload-dev": {
  "psr-4": {
    "degov\\Scripts\\Robo\\": "docroot/profiles/contrib/degov/scripts/Robo/",
    "Drupal\\degov\\": "docroot/profiles/contrib/degov/src/",
    "Drupal\\nrwgov\\": "docroot/profiles/contrib/nrwgov/src/",
    "Drupal\\nrw_base_theme\\": "docroot/themes/nrw/nrw_base_theme/src/",
  }
}
```

* `degov\\Scripts\\Robo\\` contains the legacy php files for the Robofile.php
* `Drupal\\degov\\` contains all robo tasks from degov project
* `Drupal\\nrwgov\\` contains all robo tasks from nrwgov project
* `Drupal\\nrw_base_theme\\` contains all robo tasks from nrw_base_theme project

Just add the namespace which are required for your project. When you do not use nrwgov then you do not need to add it.

## Developer
### Providing new robo tasks
To add a new robo task, just extends ./src/Robo/Plugin/Commands/DeGovCommands.php.
In case you want add many new robo tasks, it is better to create a new *Commands.php example ManyCommands.php file inside the same directory.
