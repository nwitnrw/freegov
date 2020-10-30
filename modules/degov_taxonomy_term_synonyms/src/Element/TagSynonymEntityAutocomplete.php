<?php

namespace Drupal\degov_taxonomy_term_synonyms\Element;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an entity autocomplete for tags and synonyms.
 *
 * The #default_value accepted by this element is either an entity object or an
 * array of entity objects.
 *
 * @FormElement("tag_and_synonym_reference_autocomplete")
 */
class TagSynonymEntityAutocomplete extends EntityAutocomplete {

  /**
   * {@inheritdoc}
   */
  public static function processEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element = parent::processEntityAutocomplete($element, $form_state, $complete_form);
    $element['#autocomplete_route_name'] = 'degov_taxonomy_term_synonyms.autocomplete';
    return $element;
  }

}
