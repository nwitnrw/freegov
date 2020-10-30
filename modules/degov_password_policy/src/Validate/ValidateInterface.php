<?php

declare(strict_types = 1);

namespace Drupal\degov_password_policy\Validate;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface ValidateInterface.
 *
 * @package Drupal\degov_password_policy\Validate
 */
interface ValidateInterface {

  /**
   * Validates a form.
   *
   * @param array $form
   *   The complete form structure.
   * @param Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public static function validate(array $form, FormStateInterface $formState): void;

}
