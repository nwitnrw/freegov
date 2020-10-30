<?php

namespace Drupal\degov_govbot_faq;

/**
 * Class GovBotFieldsModel.
 */
class GovBotFieldsModel {

  /**
   * Field govbot answer.
   *
   * @var string
   */
  private $fieldGovbotAnswer;

  /**
   * Field govbot question.
   *
   * @var string
   */
  private $fieldGovbotQuestion;

  /**
   * GovBotFieldsModel constructor.
   *
   * @param string $field_govbot_answer
   *   Field govbot answer.
   * @param string $field_govbot_question
   *   Field govbot question.
   */
  public function __construct(string $field_govbot_answer, string $field_govbot_question) {
    $this->fieldGovbotAnswer = $field_govbot_answer;
    $this->fieldGovbotQuestion = $field_govbot_question;
  }

  /**
   * Get field govbot answer.
   *
   * @return string
   *   Govbot answer field.
   */
  public function getFieldGovBotAnswer(): string {
    return $this->fieldGovbotAnswer;
  }

  /**
   * Set field govbot answer.
   *
   * @param string $fieldGovbotAnswer
   *   Field govbot answer.
   */
  public function setFieldGovBotAnswer(string $fieldGovbotAnswer): void {
    $this->fieldGovbotAnswer = $fieldGovbotAnswer;
  }

  /**
   * Get field govbot question.
   *
   * @return string
   *   Field govbot question.
   */
  public function getFieldGovBotQuestion(): string {
    return $this->fieldGovbotQuestion;
  }

  /**
   * Set field govbot question.
   *
   * @param string $fieldGovbotQuestion
   *   Field govbot question.
   */
  public function setFieldGovBotQuestion(string $fieldGovbotQuestion): void {
    $this->fieldGovbotQuestion = $fieldGovbotQuestion;
  }

}
