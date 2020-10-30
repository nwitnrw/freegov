<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Plugin\views\field;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\degov_media_usage\Service\MediaUsageInfo;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\media\Entity\Media;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FileUsageCountField.
 *
 * @package Drupal\degov_media_usage\Plugin\views\field
 *
 * @ingroup views_field_handlers
 * @ViewsField("file_usage")
 */
final class FileUsageCountField extends FieldPluginBase {
  use StringTranslationTrait;
  /**
   * The FileUsageInterface.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The MediaUsageInfo.
   *
   * @var \Drupal\degov_media_usage\Service\MediaUsageInfo
   */
  protected $referenceInfo;

  /**
   * FileUsageCountField constructor.
   *
   * @param array $configuration
   *   An array of configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The FileUsageInterface.
   * @param \Drupal\degov_media_usage\Service\MediaUsageInfo $info
   *   The MediaUsageInfo.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, FileUsageInterface $fileUsage, MediaUsageInfo $info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUsage = $fileUsage;
    $this->referenceInfo = $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file.usage'),
      $container->get('degov_media_usage.reference_info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): ?array {
    /** @var \Drupal\file\FileInterface $file */
    $file = $values->_entity;
    $overall = 0;
    $mids = [];

    $fileUsage = $this->fileUsage->listUsage($file);
    if (array_key_exists('file', $fileUsage) && array_key_exists('media', $fileUsage['file'])) {
      foreach ($fileUsage['file']['media'] as $mediaId => $count) {
        /** @var \Drupal\media\MediaInterface $media */
        $media = Media::load($mediaId);

        $overall += $this->referenceInfo->getRefsCount($media);
        $mids[] = $mediaId;
      }
    }

    if ($overall === 0) {
      return NULL;
    }

    $url = Url::fromRoute(
      'entity.media.degov_media_usage_refs',
      ['media' => implode(',', $mids)]
    )->toString();

    return [
      '#markup' => '<span class="media-usage"><a href="' . $url . '">' . $this->formatPlural($overall, '@count place', '@count places', ['@count' => $overall]) . '</a></span>',
    ];
  }

}
