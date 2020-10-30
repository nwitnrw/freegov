<?php

namespace Drupal\degov_taxonomy_term_synonyms\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\EntityReferenceSelection\TermSelection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reference selection for tags and synonyms.
 *
 * @EntityReferenceSelection(
 *   id = "tag_and_synonym",
 *   label = @Translation("Tags and synonyms"),
 *   entity_types = {"taxonomy_term"},
 *   weight = 1,
 *   group = "tag_and_synonym"
 * )
 */
final class TagSynonymSelection extends TermSelection {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public function setDatabase(Connection $database): void {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setDatabase($container->get('database'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $config = parent::getConfiguration();
    $config['target_bundles'] = ['tags', 'synonyms'];
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    // Fetch synonyms which have been assigned to tags.
    $synonyms = $this->database->select('taxonomy_term__field_synonyms', 'synonyms');
    $synonyms->addField('synonyms', 'field_synonyms_target_id', 'synonym_id');
    $synonyms->condition('bundle', 'tags');
    $synonyms = $synonyms->execute()->fetchCol();

    if ($synonyms) {
      // Filter out synonyms not assigned to tags.
      $tag_or_synonym = $query->orConditionGroup();
      $tag_or_synonym->condition('vid', 'tags');
      $tag_or_synonym->condition('tid', $synonyms, 'IN');
      $query->condition($tag_or_synonym);
    }
    else {
      // If no synonyms then just show tags.
      $query->condition('vid', 'synonyms', '<>');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    if (!$match && !$limit) {
      parent::getReferenceableEntities($match, $match_operator, $limit);
    }

    $target_type = $this->getConfiguration()['target_type'];

    $query = $this->buildEntityQuery($match, $match_operator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage($target_type)->loadMultiple($result);
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $synonym = NULL;

      // If entity is a synonym, check if it has been referenced by a tag.
      if ($entity->getVocabularyId() === 'synonyms') {
        $synonym_id = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
          ->condition('field_synonyms', $entity->id())
          ->range(0, 1)
          ->execute();

        if ($synonym_id) {
          $synonym = Term::load(reset($synonym_id));
        }
      }

      $label = Html::escape($this->entityRepository->getTranslationFromContext($entity)->label());
      if ($synonym instanceof Term) {
        $key_label = Html::escape($this->entityRepository->getTranslationFromContext($synonym)->label());
        $key_id = $synonym->id();
        $label_append = $this->t('Synonym for @tag', ['@tag' => $key_label]);
        $label .= ' (' . $label_append . ')';
      }
      else {
        $key_label = $label;
        $key_id = $entity_id;
      }

      $options[$bundle][$entity_id] = [
        'label' => $label,
        'key_label' => $key_label,
        'key_id' => $key_id,
      ];
    }

    return $options;
  }

}
