<?php

namespace Drupal\degov_govbot_faq;

/**
 * Class GovBotFieldsMerger.
 */
class GovBotFieldsMerger {

  /**
   * Compute text.
   *
   * @param GovBotFieldsModel[] $govBotFieldModels
   *   Bot field models.
   *
   * @return string
   *   Computed text.
   */
  public function computeText(array $govBotFieldModels): string {
    $text = '';

    foreach ($govBotFieldModels as $govBotFieldModel) {
      $text .= $this->computePlain($govBotFieldModel->getFieldGovBotQuestion()) . ' ' . $this->computePlain($govBotFieldModel->getFieldGovBotAnswer());
    }

    return $text;
  }

  /**
   * Compute plain.
   */
  private function computePlain(string $text) {
    return trim(strip_tags($text));
  }

}
