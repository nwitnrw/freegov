<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Plugin\views\field;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\degov_media_usage\Service\MediaUsageInfo;
use Drupal\media\MediaInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MediaUsageCountField.
 *
 * @package Drupal\degov_media_usage\Plugin\views\field
 *
 * @ingroup views_field_handlers
 * @ViewsField("media_usage")
 */
final class MediaUsageCountField extends FieldPluginBase {
  /**
   * The MediaUsageInfo.
   *
   * @var \Drupal\degov_media_usage\Service\MediaUsageInfo
   */
  protected $referenceInfo;

  /**
   * @param \Drupal\degov_media_usage\Service\MediaUsageInfo $referenceInfo
   */
  public function setReferenceInfo(MediaUsageInfo $referenceInfo): void {
    $this->referenceInfo = $referenceInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setReferenceInfo($container->get('degov_media_usage.reference_info'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): ?array {
    $media = $values->_entity;

    if ($media instanceof MediaInterface) {
      $result = $this->referenceInfo->getRefsCount($media);
      if ($result === 0) {
        return NULL;
      }
      $url = Url::fromRoute(
        'entity.media.degov_media_usage_refs',
        ['media' => $media->id()]
      )->toString();

      return [
        '#markup' => '<span class="media-usage"><a href="' . $url . '">' . $this->formatPlural($result, '@count place', '@count places', ['@count' => $result]) . '</a></span>',
      ];
    }
    return NULL;
  }

}
