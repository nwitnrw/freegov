<?php

namespace Drupal\degov_social_media_instagram\Form;

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
    return 'degov_social_media_instagram_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'degov_social_media_instagram.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('degov_social_media_instagram.settings');
    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your user name'),
      '#default_value' => $config->get('user'),
      '#required' => TRUE,
    ];
    $form['number_of_posts'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of listed posts'),
      '#default_value' => $config->get('number_of_posts') ?? 20,
      '#min' => 1
    ];
    $form['number_of_characters'] = [
      '#type' => 'number',
      '#title' => $this->t("Number of characters post's caption"),
      '#default_value' => $config->get('number_of_characters') ?? 100,
      '#min' => 100
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('degov_social_media_instagram.settings');
    $config->set('user', $values['user'])->save();
    $config->set('number_of_posts', $values['number_of_posts'])->save();
    $config->set('number_of_characters', $values['number_of_characters'])->save();
  }

}
