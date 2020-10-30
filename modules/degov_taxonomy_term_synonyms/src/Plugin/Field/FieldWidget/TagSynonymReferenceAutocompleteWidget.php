<?php

namespace Drupal\degov_taxonomy_term_synonyms\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/**
 * Plugin implementation to allow referencing tags by synonym.
 *
 * @FieldWidget(
 *   id = "tag_synonym_reference_autocomplete",
 *   label = @Translation("Tags and synonyms"),
 *   description = @Translation("An autocomplete text field."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class TagSynonymReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * Get synonym list for given tag.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term to get synonyms for.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Compiled list as a string.
   */
  public static function getSynonymList(TermInterface $term) {
    /** @var \Drupal\taxonomy\TermInterface[] $synonyms */
    $synonyms = $term->get('field_synonyms')->referencedEntities();
    if (!$synonyms) {
      return '';
    }

    $synonym_labels = [];
    foreach ($synonyms as $synonym) {
      $synonym_labels[] = $synonym->label();
    }

    return t('Synonyms: @synonyms', ['@synonyms' => implode(', ', $synonym_labels)]);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    $element['target_id']['#type'] = 'tag_and_synonym_reference_autocomplete';
    $id_prefix = implode('-', array_merge($parents, [$field_name]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-tag-synonym-wrapper');
    $element['target_id']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['target_id']['#suffix'] = '</div>';
    $element['target_id']['#ajax'] = [
      'callback' => [$this, 'refreshWidget'],
      'wrapper' => $wrapper_id,
      'event' => 'autocompleteclose change',
    ];

    // New references have to be fetched from user input since they are not yet
    // in the values array.
    if (isset($form_state->getUserInput()['field_tags'][$delta]['target_id'])) {
      $term_id = $form_state->getUserInput()['field_tags'][$delta]['target_id'];
      $pattern_matches = NULL;
      // Record the id part of the "<term> (<id>)" pattern.
      if (preg_match("/^.*\((\d*)\)$/", $term_id, $pattern_matches)) {
        $term = Term::load($pattern_matches[1]);
        if ($term instanceof TermInterface && $term->bundle() === 'tags') {
          $element['target_id']['#description'] = $this->getSynonymList($term);
        }
      }
    }
    elseif ($term = $items->get($delta)->entity) {
      $element['target_id']['#description'] = $this->getSynonymList($term);
    }

    return $element;
  }

  /**
   * Refresh widget.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Triggering element.
   */
  public function refreshWidget(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    $element = NestedArray::getValue($form, $trigger['#array_parents']);
    $term_id = $form_state->getValue([
      'field_tags',
      $element['#delta'],
      'target_id',
    ]);

    if ($term_id && ($term = Term::load($term_id)) && $term instanceof TermInterface) {
      $element['#description'] = self::getSynonymList($term);
    }
    else {
      unset($element['#description']);
    }

    return $element;
  }

}
