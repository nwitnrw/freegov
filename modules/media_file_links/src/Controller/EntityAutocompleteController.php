<?php

declare(strict_types=1);

namespace Drupal\media_file_links\Controller;

use Drupal\system\Controller\EntityAutocompleteController as EntityAutocompleteControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityAutocompleteController.
 */
final class EntityAutocompleteController extends EntityAutocompleteControllerBase {

  /**
   * The autocomplete matcher for entity references.
   *
   * @var \Drupal\media_file_links\EntityAutocompleteMatcher
   */
  protected $matcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media_file_links.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
