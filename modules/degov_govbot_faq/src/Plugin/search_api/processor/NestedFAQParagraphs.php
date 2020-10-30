<?php

namespace Drupal\degov_govbot_faq\Plugin\search_api\processor;

use Drupal\degov_govbot_faq\GovBotFieldsExtractor;
use Drupal\degov_govbot_faq\GovBotFieldsMerger;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds the item's URL to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "nested_faq_paragraphs",
 *   label = @Translation("Nested FAQ paragraphs"),
 *   description = @Translation("Allows the indexing of FAQ paragraphs, which are related to an node."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class NestedFAQParagraphs extends ProcessorPluginBase {

  /**
   * Bot fields extractor.
   *
   * @var \Drupal\degov_govbot_faq\GovBotFieldsExtractor
   */
  private $govBotFieldsExtractor;

  /**
   * Bot fields merger.
   *
   * @var \Drupal\degov_govbot_faq\GovBotFieldsMerger
   */
  private $govbotFieldsMerger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $processor->setGovBotFieldsExtractor($container->get('degov_govbot_faq.govbot_fields_extractor'));
    $processor->setGovbotFieldsMerger($container->get('degov_govbot_faq.govbot_fields_merger'));

    return $processor;
  }

  /**
   * Get GovBotFieldsExtractor.
   *
   * @return \Drupal\degov_govbot_faq\GovBotFieldsExtractor
   *   GovBotFieldsExtractor.
   */
  public function getGovBotFieldsExtractor(): GovBotFieldsExtractor {
    return $this->govBotFieldsExtractor;
  }

  /**
   * Set setGovBotFieldsExtractor.
   *
   * @param \Drupal\degov_govbot_faq\GovBotFieldsExtractor $govBotFieldsExtractor
   *   GovBotFieldsExtractor.
   */
  public function setGovBotFieldsExtractor(GovBotFieldsExtractor $govBotFieldsExtractor): void {
    $this->govBotFieldsExtractor = $govBotFieldsExtractor;
  }

  /**
   * Get govbotFieldsMerger.
   *
   * @return \Drupal\degov_govbot_faq\GovBotFieldsMerger
   *   GovbotFieldsMerger.
   */
  public function getGovbotFieldsMerger(): GovBotFieldsMerger {
    return $this->govbotFieldsMerger;
  }

  /**
   * Set govbotFieldsMerger.
   *
   * @param \Drupal\degov_govbot_faq\GovBotFieldsMerger $govbotFieldsMerger
   *   GovbotFieldsMerger.
   */
  public function setGovbotFieldsMerger(GovBotFieldsMerger $govbotFieldsMerger): void {
    $this->govbotFieldsMerger = $govbotFieldsMerger;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];
    $definition = [
      'label'        => $this->t('Nested FAQ paragraphs'),
      'description'  => $this->t('Nested FAQ paragraphs field.'),
      'type'         => 'string',
      'processor_id' => $this->getPluginId(),
    ];
    $properties[$this->getPluginId()] = new ProcessorProperty($definition);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $fields = $this->getFieldsHelper()
      ->filterForPropertyPath($item->getFields(FALSE), NULL, $this->getPluginId());
    foreach ($fields as $field) {
      if (($node = $item->getOriginalObject()->getValue()) instanceof NodeInterface && $node->getType() === 'faq') {
        $text = $this->getGovbotFieldsMerger()->computeText($this->getGovBotFieldsExtractor()->compute($node));

        $field->addValue($text);
      }
    }

  }

}
