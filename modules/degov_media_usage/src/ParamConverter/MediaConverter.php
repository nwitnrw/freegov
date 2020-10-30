<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\ParamConverter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Class MediaConverter.
 *
 * @package Drupal\degov_media_usage\ParamConverter
 */
class MediaConverter implements ParamConverterInterface {

  /**
   * The EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaConverter constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults): ?array {
    $mediaIds = explode(',', $value);

    if (!empty($mediaIds)) {
      return $this->entityTypeManager->getStorage('media')->loadMultiple($mediaIds);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route): bool {
    return !empty($definition['type']) && $definition['type'] === 'media';
  }

}
