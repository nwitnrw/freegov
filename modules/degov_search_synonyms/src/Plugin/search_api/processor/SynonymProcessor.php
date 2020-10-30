<?php

namespace Drupal\degov_search_synonyms\Plugin\search_api\processor;

use Drupal;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Query\QueryInterface;

/**
 * Adds synonyms to the search index.
 *
 * @SearchApiProcessor(
 *   id = "term_synonym",
 *   label = @Translation("Term synonyms"),
 *   description = @Translation("Add term synonyms to the index."),
 *   stages = {
 *     "preprocess_query" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class SynonymProcessor extends ProcessorPluginBase {

  /**
   * Check whether or not to ignore synonyms.
   *
   * @return bool
   *   Whether or not to ignore synonyms.
   */
  public static function ignoreSynonyms() {
    return (bool) Drupal::request()->query->get('ignore-synonyms');
  }

  /**
   * Query for all assigned synonyms contained in string.
   *
   * @param string $string
   *   String to parse.
   *
   * @return array
   *   The names of the synonyms found and the  terms they are assigned to.
   */
  public static function getSynonymsFromString(string $string) {
    return Drupal::database()->query("
      SELECT tfd.name, fs.entity_id AS tid
      FROM taxonomy_term_field_data AS tfd
      JOIN taxonomy_term__field_synonyms AS fs ON fs.field_synonyms_target_id = tfd.tid
      WHERE :value LIKE CONCAT('%', name, '%') AND vid = 'synonyms'
    ", [
      ':value' => $string,
    ])->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessSearchQuery(QueryInterface $query) {
    if (self::ignoreSynonyms() || !$query->getOriginalKeys()) {
      return;
    }

    // Query for assigned synonyms by name contained in queried string.
    $synonyms = self::getSynonymsFromString($query->getOriginalKeys());

    if ($synonyms) {
      $keys = $query->getKeys();

      /*
       * Collect all the pieces of found synonyms to a single array to filter
       * out of the query keys array.
       */
      $synonym_fragments = [];
      foreach ($synonyms as $synonym) {
        $synonym_fragments = array_merge($synonym_fragments, explode(' ', $synonym->name));
      }

      /*
       * Get the keys for synonyms on the processed keys array so they can be
       * filtered out since that way we are not dependant on any processing
       * that happens to the keys.
       */
      $original_synonyms_keys = array_intersect(explode(' ', $query->getOriginalKeys()), $synonym_fragments);

      /*
       * Filter out synonyms from the main query keys to be added later as
       * separate conditions.
       */
      $keys_without_synonyms = array_diff_key($keys, $original_synonyms_keys);

      /*
       * If any synonyms detected, the results have to have one of the
       * corresponding tags assigned.
       */
      $tags_field_name = 'field_tags';
      if ($query->getIndex()->getField('field_tags') === NULL && $query->getIndex()->getField('tags') !== NULL) {
        $tags_field_name = 'tags';
      }
      $has_tags = $query->createConditionGroup('OR');
      foreach (array_column($synonyms, 'tid') as $tid) {
        $has_tags->addCondition($tags_field_name, $tid);
      }
      $query->addConditionGroup($has_tags);

      $query->keys($keys_without_synonyms);
    }
  }

}
