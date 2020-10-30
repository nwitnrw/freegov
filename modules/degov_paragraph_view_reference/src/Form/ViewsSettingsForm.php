<?php

declare(strict_types=1);

namespace Drupal\degov_paragraph_view_reference\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Class ViewsSettingsForm.
 *
 * @package Drupal\degov_paragraph_view_reference
 */
final class ViewsSettingsForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return [
      'degov_paragraph_view_reference.views_helper_settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'degov_views_helper_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the current configuration settings.
    $config = $this->config('degov_paragraph_view_reference.views_helper_settings');

    // Get the list of all view names available in the system.
    $view_list = $this->getAllViewsNames();
    $allowed_views = $config->get('allowed_views');
    $form_ids = $config->get('form_ids');

    $form['allowed_views'] = [
      '#type' => 'checkboxes',
      '#title' => t('Allowed View Options'),
      '#options' => $view_list,
      '#default_value' => \is_array($allowed_views) ? $allowed_views : [],
      '#weight' => 2,
    ];

    $form['form_ids'] = [
      '#type' => 'textarea',
      '#title' => t('Form Ids to apply the filter'),
      '#default_value' => \is_array($form_ids) ? \implode(PHP_EOL, $form_ids) : '',
      '#weight' => 3,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('degov_paragraph_view_reference.views_helper_settings');
    $config->set('allowed_views', $form_state->getValue('allowed_views'))
      ->save();
    $form_ids = $form_state->getValue('form_ids');
    if ($form_ids !== '') {
      $form_ids = \explode(PHP_EOL, $form_ids);
    }
    else {
      $form_ids = [];
    }
    foreach ($form_ids as $key => $value) {
      $form_ids[$key] = \trim($value);
    }
    $config->set('form_ids', $form_ids)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Helper function to get all View Names.
   */
  private function getAllViewsNames() {
    $views = Views::getEnabledViews();
    $options = [];
    foreach ($views as $view) {
      $options[$view->get('id')] = $view->get('label');
    }
    return $options;
  }

}
