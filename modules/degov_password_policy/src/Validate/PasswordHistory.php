<?php

declare(strict_types = 1);

namespace Drupal\degov_password_policy\Validate;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class PasswordHistory.
 *
 * @package Drupal\degov_password_policy\Validate
 */
class PasswordHistory implements ValidateInterface {

  /**
   * @inheritdoc
   */
  public static function validate(array $form, FormStateInterface $formState): void {
    $uid = (int) $formState->getFormObject()->getEntity()->id();
    $newPass = $formState->getValue('pass');

    /** @var Drupal\degov_password_policy\Service\PasswordHistoryService $historyService */
    $historyService = \Drupal::service('degov_password_policy.service.password_history');
    $isPasswordInHistory = $historyService->isPasswordInHistory($uid, $newPass);

    if ($isPasswordInHistory) {
      $formState->setError(
        $form['account']['pass'],
        new TranslatableMarkup('Password has been used already. Choose a different password.')
      );
    }
  }

}
