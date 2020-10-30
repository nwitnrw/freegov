<?php

namespace Drupal\degov_search_content_solr\Plugin\facets\widget;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\degov_search_content_solr\InvalidContentBundleMachineNameException;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\DropdownWidget;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The dropdown widget.
 *
 * @FacetsWidget(
 *   id = "content_bundle",
 *   label = @Translation("Content bundles"),
 *   description = @Translation("A configurable widget that shows a dropdown for content bundles."),
 * )
 */
final class ContentBundleWidget extends DropdownWidget implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager): void {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function setLogger(LoggerInterface $logger): void {
    $this->logger = $logger;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setEntityTypeManager($container->get('entity_type.manager'));
    /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory */
    $loggerFactory = $container->get('logger.factory');
    $instance->setLogger($loggerFactory->get('degov_search_content_solr'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);

    foreach ($build['#items'] as &$item) {
      $item = $this->transformBundleIdToName($item);
    }

    return $build;
  }

  /**
   * Transform bundle id to name.
   */
  private function transformBundleIdToName(array $item): array {
    $contentBundleMachineName = $item['#title']['#value'];

    try {

      if ($contentBundleMachineName !== 'document') {

        if (($entity = $this->entityTypeManager->getStorage('node_type')->load($contentBundleMachineName)) && $entity instanceof EntityInterface) {
          $item['#title']['#value'] = $entity->label();
        }
        else {
          throw new InvalidContentBundleMachineNameException($contentBundleMachineName);
        }

      }
      elseif (($entity = $this->entityTypeManager->getStorage('media_type')->load($contentBundleMachineName)) && $entity instanceof EntityInterface) {
        $item['#title']['#value'] = $entity->label();
      }
      else {
        throw new InvalidContentBundleMachineNameException($contentBundleMachineName);
      }

    }
    catch (\Exception $exception) {
      $this->logger->error($exception->getMessage() . ' - Passed content bundle machine name was: ' . $contentBundleMachineName);
    }

    return $item;
  }

}
