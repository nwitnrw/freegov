<?php

namespace Drupal\degov_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DegovSettingsForm.
 */
class DegovSettingsForm extends ConfigFormBase {

  private const CONFIGNAME = 'degov_common.default_settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIGNAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'degov_default_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIGNAME);

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('This is configuration section of deGov distribution') . '</p>',
    ];
    $form['privacy_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy URL Path'),
      '#description' => $this->t('The path at which the privacy terms can be found.'),
      '#default_value' => $config->get('privacy_url'),
      '#required' => TRUE,
    ];
    $form['netiquette_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Netiquette URL Path'),
      '#description' => $this->t('The path at which the netiquette can be found.'),
      '#default_value' => $config->get('netiquette_url'),
      '#required' => TRUE,
    ];
    $form['youtube_apikey'] = [
      '#type' => 'textfield',
      '#title' => t('Youtube API Key'),
      '#description' => $this->t('Obtain a Youtube API key at <a href="@link">@link</a>', [
        '@link' => 'https://console.developers.google.com/apis/credentials',
      ]),
      '#default_value' => $config->get('youtube_apikey'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIGNAME);
    $config
      ->set('privacy_url', $form_state->getValue('privacy_url'))
      ->set('netiquette_url', $form_state->getValue('netiquette_url'))
      ->set('youtube_apikey', $form_state->getValue('youtube_apikey'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
