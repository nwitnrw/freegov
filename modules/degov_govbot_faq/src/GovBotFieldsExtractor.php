<?php

namespace Drupal\degov_govbot_faq;

use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class GovBotFieldsExtractor.
 */
class GovBotFieldsExtractor {

  /**
   * Paragraphs extractor.
   *
   * @var \Drupal\paragraphs\Entity\ParagraphsExtractor
   */
  private $paragraphsExtractor;

  /**
   * GovBotFieldsExtractor constructor.
   */
  public function __construct(ParagraphsExtractor $paragraphsExtractor) {
    $this->paragraphsExtractor = $paragraphsExtractor;
  }

  /**
   * Compute.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return array
   *   Bot fields.
   */
  public function compute(NodeInterface $node): array {
    $faqListParagraphs = $this->paragraphsExtractor->getFaqListParagraphs($node);

    $govBotFields = [];

    foreach ($faqListParagraphs as $faqListParagraph) {
      if ($faqListParagraph instanceof Paragraph) {

        if (($fieldFAQListInnerParagraphs = $faqListParagraph->get('field_faq_list_inner_paragraphs')) instanceof EntityReferenceRevisionsFieldItemList) {

          $paragraphFAQItems = $fieldFAQListInnerParagraphs->getValue();

          foreach ($paragraphFAQItems as $paragraphFAQItem) {
            $paragraphFAQItemEntity = Paragraph::load($paragraphFAQItem['target_id']);

            $fieldGovBotAnswer = $paragraphFAQItemEntity->get('field_govbot_answer')->getValue();
            $fieldGovBotQuestion = $paragraphFAQItemEntity->get('field_govbot_question')->getValue();

            for ($i = 0, $iMax = count($fieldGovBotAnswer) - 1; $i <= $iMax; ++$i) {
              if (!empty($fieldGovBotAnswer[$i]['value']) && !empty($fieldGovBotQuestion[$i]['value'])) {
                $govBotFields[] = new GovBotFieldsModel($fieldGovBotAnswer[$i]['value'], $fieldGovBotQuestion[$i]['value']);
              }
            }

          }
        }
      }

    }

    return $govBotFields;
  }

}
