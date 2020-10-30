<?php

declare(strict_types=1);

namespace Drupal\degov_common\Twig\Extension;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Template\Attribute;

/**
 * Provides field value filters for Twig templates.
 */
class FieldQuickEditAttributesExtension extends \Twig_Extension {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  public function __construct(ModuleHandlerInterface $module_handler, AccountProxyInterface $current_user) {
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('quickedit_attr', [$this, 'getQuickEdit']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'twig_field_quickedit_attributes';
  }

  /**
   * Twig filter callback: Only return a field's attributes for quick edit.
   *
   * @param array $build
   *   Render array of a field.
   *
   * @return \Drupal\Core\Template\Attribute
   *   Rendered attributes.
   */
  public function getQuickEdit($build) :?Attribute {

    if (!$this->isFieldRenderArray($build)) {
      return NULL;
    }

    if (!$this->moduleHandler->moduleExists('quickedit')) {
      return NULL;
    }

    if (!$this->currentUser->hasPermission('access in-place editing')) {
      return NULL;
    }

    // Quick Edit module only supports view modes, not dynamically defined
    // "display options" (which
    // \Drupal\Core\Field\FieldItemListInterface::view() always names the
    // "_custom" view mode).
    // @see \Drupal\Core\Field\FieldItemListInterface::view()
    // @see https://www.drupal.org/node/2120335
    if ($build['#view_mode'] === '_custom') {
      return NULL;
    }
    /** @var $entity \Drupal\Core\Entity\FieldableEntityInterface */
    $entity = $build['#object'];
    if (!$entity->hasField($build['#field_name'])) {
      return NULL;
    }

    // Fields that are computed fields are not editable.
    $definition = $entity->getFieldDefinition($build['#field_name']);
    if ($definition && !$definition->isComputed()) {
      $attributes = [];
      $attributes['data-quickedit-field-id'] = $entity->getEntityTypeId() . '/' . $entity->id() . '/' . $build['#field_name'] . '/' . $build['#language'] . '/' . $build['#view_mode'];
      return new Attribute($attributes);
    }

    return NULL;
  }

  /**
   * Checks whether the render array is a field's render array.
   *
   * @param array $build
   *   The renderable array.
   *
   * @return bool
   *   True if $build is a field render array.
   */
  protected function isFieldRenderArray($build): bool {

    return is_array($build) && array_key_exists('#theme', $build) && $build['#theme'] === 'field';
  }

}
