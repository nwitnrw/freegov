<?php

declare(strict_types=1);

namespace Drupal\degov_govbot_faq;

use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class FAQAccess.
 */
class FAQAccess {

  /**
   * Paragraph extractor.
   *
   * @var \Drupal\degov_govbot_faq\ParagraphsExtractor
   */
  private $paragraphsExtractor;

  /**
   * FAQAccess constructor.
   */
  public function __construct(ParagraphsExtractor $paragraphsExtractor) {
    $this->paragraphsExtractor = $paragraphsExtractor;
  }

  /**
   * Check if is accessible on  site.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return bool
   *   True if accessible.
   */
  public function isAccessibleOnSite(NodeInterface $node): bool {
    $accessResult = TRUE;
    $faqListParagraphs = $this->paragraphsExtractor->getFaqListParagraphs($node);

    if ($node->getType() === 'faq') {

      foreach ($faqListParagraphs as $faqListParagraph) {
        if ($faqListParagraph instanceof ParagraphInterface && ($fieldFAQListInnerParagraphs = $faqListParagraph->get('field_faq_list_inner_paragraphs')) instanceof EntityReferenceRevisionsFieldItemList) {

          $paragraphFAQItems = $fieldFAQListInnerParagraphs->getValue();

          foreach ($paragraphFAQItems as $paragraphFAQItem) {
            $paragraphFAQItemEntity = Paragraph::load($paragraphFAQItem['target_id']);

            $fieldFAQText = $paragraphFAQItemEntity->get('field_faq_text')->getValue();
            $fieldFAQTitle = $paragraphFAQItemEntity->get('field_faq_title')->getValue();

            if (empty($fieldFAQText['0']['value']) || empty($fieldFAQTitle['0']['value'])) {
              return FALSE;
            }

          }

        }

      }

    }

    return $accessResult;
  }

}
