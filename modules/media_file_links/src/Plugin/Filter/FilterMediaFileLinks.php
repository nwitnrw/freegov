<?php

namespace Drupal\media_file_links\Plugin\Filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\media_file_links\Service\MediaFileLinkPlaceholderHandler;
use Drupal\media_file_links\Service\MediaFileLinkResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Transforms placeholders with Media IDs to the corresponding file links.
 *
 * @Filter(
 *   id = "filter_mediafilelinks",
 *   title = @Translation("Resolve links to media files"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
final class FilterMediaFileLinks extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\media_file_links\Service\MediaFileLinkResolver
   */
  protected $fileLinkResolver;

  /**
   * @var \Drupal\media_file_links\Service\MediaFileLinkPlaceholderHandler
   */
  protected $placeholderHandler;

  /**
   * @param \Drupal\media_file_links\Service\MediaFileLinkResolver $file_link_resolver
   */
  public function setFileLinkResolver(MediaFileLinkResolver $file_link_resolver): void {
    $this->fileLinkResolver = $file_link_resolver;
  }

  /**
   * @param \Drupal\media_file_links\Service\MediaFileLinkPlaceholderHandler $placeholder_handler
   */
  public function setPlaceholderHandler(MediaFileLinkPlaceholderHandler $placeholder_handler): void {
    $this->placeholderHandler = $placeholder_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setFileLinkResolver($container->get('media_file_links.file_link_resolver'));
    $instance->setPlaceholderHandler($container->get('media_file_links.placeholder_handler'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    while (($mediaId = $this->placeholderHandler->getMediaIdFromPlaceholder($text)) !== NULL) {
      $fileUrl = $this->fileLinkResolver->getFileUrlString($mediaId);
      $text = str_replace($this->placeholderHandler->getPlaceholderForMediaId($mediaId), $fileUrl, $text);
    }
    return new FilterProcessResult($text);
  }

}
