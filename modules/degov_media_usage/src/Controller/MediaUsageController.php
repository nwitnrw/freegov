<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\degov_media_usage\Service\MediaUsageInfo;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MediaUsageController.
 *
 * @package Drupal\degov_media_usage\Controller
 */
final class MediaUsageController implements ContainerInjectionInterface {
  use StringTranslationTrait;
  /**
   * @var \Drupal\degov_media_usage\Service\MediaUsageInfo
   */
  private $referenceInfo;

  public function __construct(MediaUsageInfo $reference_info) {
    $this->referenceInfo = $reference_info;
  }

  /**
   * @{inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('degov_media_usage.reference_info'));
  }

  /**
   * Turns an array of Media into a table of media reference info.
   *
   * @param \Drupal\media\MediaInterface[] $media
   *   An array of Media entities.
   *
   * @return array
   *   A render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function referencesPage(array $media = NULL): array {
    $empty = [
      'description' => [
        '#prefix' => '<h4>',
        '#suffix' => '</h4>',
        '#markup' => $this->t('This media has no references.'),
      ],
    ];

    if (\is_null($media)) {
      return $empty;
    }
    $refs = [];
    /** @var \Drupal\media\MediaInterface $mediaEntity */
    foreach ($media as $mediaEntity) {
      $entityReferences = $this->referenceInfo->getRefsList($mediaEntity);
      foreach ($entityReferences as $entityReference) {
        $refs[] = $entityReference;
      }
    }

    if (\count($refs) === 0) {
      return $empty;
    }

    $table = $this->referenceInfo->buildRefsTable($refs);

    return [
      'description' => [
        '#prefix' => '<h4>',
        '#suffix' => '</h4>',
        '#markup' => $this->t(
          'This media is referenced at @count place(s).',
          ['@count' => \count($refs)]
        ),
      ],
      'table' => $table,
    ];
  }

  /**
   * Get a translated title for one or more Media usages.
   *
   * @param \Drupal\media\MediaInterface[] $media
   *   An array of Media entities.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The references title.
   */
  public function referencesTitle(array $media = NULL): TranslatableMarkup {
    // Breadcrumb also use this title.
    if (\is_null($media) || \count($media) === 0) {
      return $this->t('Browse media references');
    }

    $labels = \array_map(function (MediaInterface $mediaEntity) {
      return $mediaEntity->label();
    }, $media);

    return $this->t('Browse media "@media" references', ['@media' => \implode(', ', $labels)]);
  }

}
