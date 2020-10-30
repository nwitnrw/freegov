<?php

declare(strict_types=1);

namespace Drupal\degov_auto_crop\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crop\Form\CropTypeForm;

/**
 * Class CropOffsetForm.
 *
 * Adds fields for offset values to the CropTypeForm
 * and stores them in third_party_settings.
 */
class CropOffsetForm extends CropTypeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['offsets'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['offsets']['landscape'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Automatic offset (landscape images)'),
      '#description' => $this->t('<p>On first upload of a file the crop frame will be placed automatically. Use these values to define offsets for the placement.</p><p>Example: if <code>top</code> is <code>2</code> and <code>bottom</code> is <code>1</code>, the crop will be placed twice as far from the top of the image as from the bottom.</p>'),
    ];
    $form['offsets']['portrait'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Automatic offset (portrait images)'),
      '#description' => $this->t('<p>On first upload of a file the crop frame will be placed automatically. Use these values to define offsets for the placement.</p><p>Example: if <code>top</code> is <code>2</code> and <code>bottom</code> is <code>1</code>, the crop will be placed twice as far from the top of the image as from the bottom.</p>'),
    ];
    $form['offsets']['square'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Automatic offset (square images)'),
      '#description' => $this->t('<p>On first upload of a file the crop frame will be placed automatically. Use these values to define offsets for the placement.</p><p>Example: if <code>top</code> is <code>2</code> and <code>bottom</code> is <code>1</code>, the crop will be placed twice as far from the top of the image as from the bottom.</p>'),
    ];

    $crop_type = $this->getEntity();
    $stored_offsets = $crop_type->getThirdPartySetting('degov_auto_crop', 'offsets', []);

    foreach (array_keys($form['offsets']) as $image_type) {
      if (strpos($image_type, '#') === FALSE) {
        $form['offsets'][$image_type]['top'] = [
          '#type'          => 'textfield',
          '#title'         => $this->t('Offset top'),
          '#required'      => TRUE,
          '#default_value' => $stored_offsets[$image_type]['top'] ?? 1,
        ];
        $form['offsets'][$image_type]['bottom'] = [
          '#type'          => 'textfield',
          '#title'         => $this->t('Offset bottom'),
          '#required'      => TRUE,
          '#default_value' => $stored_offsets[$image_type]['bottom'] ?? 1,
        ];
        $form['offsets'][$image_type]['left'] = [
          '#type'          => 'textfield',
          '#title'         => $this->t('Offset left'),
          '#required'      => TRUE,
          '#default_value' => $stored_offsets[$image_type]['left'] ?? 1,
        ];
        $form['offsets'][$image_type]['right'] = [
          '#type'          => 'textfield',
          '#title'         => $this->t('Offset right'),
          '#required'      => TRUE,
          '#default_value' => $stored_offsets[$image_type]['right'] ?? 1,
        ];
      }
    }

    $form['#entity_builders'][] = '::extendEntity';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $offsets = $form_state->getValue('offsets');
    if (!$offsets) {
      $form_state->setError($form['offsets'], $this->t('Could not find offset values in the submitted form.'));
      return;
    }

    foreach ($offsets as $image_type => $offset_values) {
      foreach ($offset_values as $key => $value) {
        if (empty($value) || !preg_match("/^[1-9]\d*$/", $value)) {
          $form_state->setError($form['offsets'][$image_type][$key], $this->t('Offset must be a positive integer.'));
        }
      }
    }
  }

  /**
   * Stores the offset values.
   *
   * Offset values are added to the third_party_settings array of the
   * CropType config entity.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\Core\Entity\ContentEntityForm::form()
   */
  public function extendEntity($entity_type_id, EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $offsets = $form_state->getValue('offsets');

    $entity->setThirdPartySetting('degov_auto_crop', 'offsets', $offsets);
  }

}
