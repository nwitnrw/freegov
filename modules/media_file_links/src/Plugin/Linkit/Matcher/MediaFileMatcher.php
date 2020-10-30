<?php

namespace Drupal\media_file_links\Plugin\Linkit\Matcher;

use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;
use Drupal\media_file_links\Service\MediaFileSuggester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides specific LinkIt matchers for our custom entity type.
 *
 * @Matcher(
 *   id = "entity:media_file_links",
 *   label = @Translation("Media file links"),
 *   target_entity = "media",
 *   provider = "media_file_links"
 * )
 */
class MediaFileMatcher extends EntityMatcher {

  /**
   * @var \Drupal\media_file_links\Service\MediaFileSuggester
   */
  protected $fileSuggester;

  /**
   * @param \Drupal\media_file_links\Service\MediaFileSuggester $file_suggester
   */
  public function setFileSuggester(MediaFileSuggester $file_suggester): void {
    $this->fileSuggester = $file_suggester;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setFileSuggester($container->get('media_file_links.file_suggester'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return parent::defaultConfiguration() + [
      'result_description' => '',
      'group_by_bundle' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary(): array {
    $summary = [];
    $entity_type = $this->entityManager->getDefinition($this->target_type);

    $result_description = $this->configuration['result_description'];
    if (!empty($result_description)) {
      $summary[] = $this->t('Result description: @result_description', [
        '@result_description' => $result_description,
      ]);
    }

    if ($entity_type && $entity_type->hasKey('bundle')) {
      $summary[] = $this->t('Group by bundle: @bundle_grouping', [
        '@bundle_grouping' => $this->configuration['group_by_bundle'] ? $this->t('Yes') : $this->t('No'),
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $entity_type = $this->entityManager->getDefinition($this->target_type);
    $form['result_description'] = [
      '#title' => $this->t('Result description'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['result_description'],
      '#size' => 120,
      '#maxlength' => 255,
      '#weight' => -100,
    ];

    // Filter the possible bundles to use if the entity has bundles.
    if ($entity_type && $entity_type->hasKey('bundle')) {
      // Group the results by bundle.
      $form['group_by_bundle'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Group by bundle'),
        '#default_value' => $this->configuration['group_by_bundle'],
        '#weight' => -50,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getMatches($string): array {
    $mediaEntities = \json_decode($this->fileSuggester->findBySearchString($string), TRUE);
    $returnMatches = [];

    if (!empty($mediaEntities)) {
      foreach ($mediaEntities as $mediaEntity) {
        $returnMatches[$mediaEntity['id']] = [
          'title' => $mediaEntity['title'],
          'description' => sprintf(
            '<i class="%s" /> %s, %s',
            $mediaEntity['iconClass'],
            $mediaEntity['bundleLabel'],
            $mediaEntity['filename']
          ),
          'path' => '[media/file/' . $mediaEntity['id'] . ']',
          'group' => $this->t('Media file links'),
        ];
      }
    }

    return $returnMatches;
  }

}
