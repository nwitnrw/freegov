<?php

namespace Drupal\degov_social_media_youtube\Form;

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
    return 'degov_social_media_youtube_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'degov_social_media_youtube.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('degov_social_media_youtube.settings');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your API key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];
    $form['channel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your channel name or channel Id'),
      '#default_value' => $config->get('channel'),
      '#required' => TRUE,
    ];
    $form['number_of_videos'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of listed videos'),
      '#default_value' => $config->get('number_of_videos') ?? 10,
      '#min' => 1
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('degov_social_media_youtube.settings');
    $config->set('api_key', $values['api_key'])->save();
    $config->set('channel', $values['channel'])->save();
    $config->set('number_of_videos', $values['number_of_videos'])->save();
  }

}
