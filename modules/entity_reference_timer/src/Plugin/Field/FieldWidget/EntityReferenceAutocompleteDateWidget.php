<?php

namespace Drupal\entity_reference_timer\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\entity_reference_timer\InputValidator;
use Drupal\entity_reference_timer\Plugin\Field\Exception\NoStartButEndException;
use Drupal\entity_reference_timer\Plugin\Field\Exception\StartAfterEndException;

/**
 * Class EntityReferenceAutocompleteDateWidget.
 *
 * @FieldWidget(
 *   id = "entity_reference_autocomplete_date",
 *   label = @Translation("Autocomplete with date"),
 *   field_types = {
 *     "entity_reference_date"
 *   }
 * )
 */
class EntityReferenceAutocompleteDateWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $widget = parent::formElement($items, $delta, $element, $form, $form_state);

    $widget = $this->createDate('start_date', t('Start'), $items, $delta, $widget);
    $widget = $this->createDate('end_date', t('End'), $items, $delta, $widget);

    $widget['publish_timer'] = [
      '#type'          => 'checkbox',
      '#weight'        => 19,
      '#title'         => t('publish time scheduled'),
      '#default_value' => (isset($items[$delta]) && !empty($items[$delta]->start_date)) ? 1 : NULL,
      '#attributes'    => [
        'class' => ['publish_timer'],
      ],
    ];

    $widget['#element_validate'][] = [__CLASS__, 'validateElement'];
    $widget['#attached']['library'][] = 'entity_reference_timer/entity_reference_date_widget';

    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $formState): void {
    try {
      InputValidator::handleDateInputException($element);
    }
    catch (NoStartButEndException $exception) {
      $formState->setError($element['start_date'], t('Start date and end date must exist'));
      $formState->setError($element['end_date'], t('Start date and end date must exist'));
    }
    catch (StartAfterEndException $exception) {
      $formState->setError($element['start_date'], t('Start date must be before end date'));
      $formState->setError($element['end_date'], t('End date must be after start date'));
    }

    if ($element['publish_timer']['#value'] !== 1) {
      $formState->setValueForElement($element['start_date'], NULL);
      $formState->setValueForElement($element['end_date'], NULL);
    }
  }

  /**
   * Creates a date object for use as a default value.
   *
   * This will take a default value, apply the proper timezone for display in
   * a widget, and set the default time for date-only fields.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   The UTC default date.
   * @param string $timezone
   *   The timezone to apply.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   A date object for use as a default value in a field widget.
   */
  protected function createDefaultValue(DrupalDateTime $date, string $timezone): DrupalDateTime {
    // The date was created and verified during field_load(), so it is safe to
    // use without further inspection.
    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $date->setDefaultDateTime();
    }
    $date->setTimezone(new \DateTimeZone($timezone));
    return $date;
  }

  /**
   * Create date.
   */
  private function createDate(string $key, string $label, FieldItemListInterface $items, $delta, array $widget): array {
    $cssClasses = [$key];

    if (isset($items[$delta]) && empty($items[$delta]->date)) {
      $cssClasses[] = 'hidden';
    }

    $defaultValue = NULL;
    if ($key === 'start_date') {
      $defaultValue = DrupalDateTime::createFromTimestamp(time());
    }

    $widget[$key] = [
      '#type'           => 'datetime',
      '#default_value'  => isset($items[$delta]) && !empty($items[$delta]->date) ? $items[$delta]->date : $defaultValue,
      '#date_increment' => 1,
      '#date_timezone'  => date_default_timezone_get(),
      '#required'       => FALSE,
      '#title'          => $label,
      '#weight'         => 20,
      '#attributes'     => ['class' => $cssClasses],
    ];

    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      // A date-only field should have no timezone conversion performed, so
      // use the same timezone as for storage.
      $widget[$key]['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;
    }

    if ($items[$delta]->$key) {
      $date = new DrupalDateTime($items[$delta]->$key);
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      $date->setTimezone(new \DateTimeZone($widget[$key]['#date_timezone']));
      $widget[$key]['#default_value'] = $this->createDefaultValue($date, $widget[$key]['#date_timezone']);
    }

    return $widget;
  }

}
