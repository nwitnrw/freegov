# Entity Reference Timer

## Uninstallation

To uninstall Entity Reference Timer, first migrate all content to the entity reference field and switch back the form field to the former state. Use the following Drush command for this: 

```
drush entity_reference_timer:uninstall_field
```

Drupal and Drush won't allow the un-installation, without the execution of the mentioned command. Any date for start and end dates will be removed afterwards.