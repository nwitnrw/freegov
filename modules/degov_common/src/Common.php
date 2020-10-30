<?php

namespace Drupal\degov_common;

/**
 * Class Common.
 *
 * Contains all common function implementations.
 *
 * @package Drupal\degov_common
 */
class Common {

  /**
   * Adds template suggestions and library implementations.
   *
   * Add this to the HOOK_preprocess() of your module. The first 3 arguments
   * are equal to the ones from the parent HOOK_preprocess().
   *
   * @param array &$variables
   *   Original $variables from the hook_preprocess() function.
   * @param string $hook
   *   Original $hook from the hook_preprocess() function.
   * @param array &$info
   *   Original $info from the hook_preprocess() function.
   * @param array $options
   *   A key named array of options, including:
   *   - module_name: mandatory value with the name of the module implementing
   *   the method.
   *   - entity_type: mandatory value with mostly the entity type created (E.g.
   *   node, paragraph, media, swiftmailer..)
   *   - entity_bundles: optional array of entity bundles created, could be
   *   empty.
   *   - entity_view_modes: optional array of entity view modes that need
   *   templates, could be empty.
   *
   * TODO:
   *   Replace this with hook_theme and hook_theme_suggestions_alter and the derivates.
   * @see \Drupal\degov_theming\Service\Template::suggest()
   */
  public static function addThemeSuggestions(array &$variables, $hook, array &$info, array $options) {
    /**
     * @var \Drupal\degov_theming\Service\Template $template
     */
    $template = \Drupal::service('degov_theming.template');
    $template->suggest($variables, $hook, $info, $options);
  }

  /**
   * Remove content.
   *
   * @param array $options
   *   Options.
   */
  public static function removeContent(array $options): void {
    $entity_type = $options['entity_type'];
    $entity_bundles = $options['entity_bundles'];

    if (\in_array($entity_type, ['paragraph', 'node'])) {
      foreach ($entity_bundles as $entity_bundle) {
        self::removeEntities($entity_type, $entity_bundle, 'type');
      }
    }

    if ($entity_type === 'taxonomy_term') {
      foreach ($entity_bundles as $entity_bundle) {
        self::removeEntities($entity_type, $entity_bundle, 'vid');
      }
    }

    if ($entity_type === 'media') {
      foreach ($entity_bundles as $entity_bundle) {
        self::removeEntities($entity_type, $entity_bundle, 'bundle');
      }
    }
  }

  /**
   * Remove entities.
   *
   * @param int $entity_id
   *   Entity ID.
   * @param string $entity_bundle
   *   Entity bundle.
   * @param string $condition_field
   *   Condition field.
   */
  public static function removeEntities($entity_id, $entity_bundle, $condition_field): void {
    \Drupal::logger($entity_id)
      ->notice('Removing all content of type @type', ['@type' => $entity_bundle]);
    $entity_ids = \Drupal::entityQuery($entity_id)
      ->condition($condition_field, $entity_bundle)
      ->accessCheck(FALSE)
      ->execute();
    $controller = \Drupal::entityTypeManager()->getStorage($entity_id);
    $entities = $controller->loadMultiple($entity_ids);
    $controller->delete($entities);
  }

}
