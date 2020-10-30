<?php

namespace Drupal\degov_devel\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'degov_devel';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'degov_devel.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('degov_devel.settings');
    $form['dev_mode'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Development mode'),
      '#description'   => $this->t('E.g. set fixed content for social media feeds.'),
      '#default_value' => $config->get('dev_mode'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('degov_devel.settings');
    $config->set('dev_mode', $values['dev_mode'])->save();
  }

}
