<?php

declare(strict_types=1);

namespace Drupal\node_action\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Author.
 */
final class Author extends FormBase {

  use ActionFormTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Author constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'moderation_state_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $this->removeMessageFromDefaultAction();

    $entityIds = $this->getRequest()->get('entityIds');

    $nodesList = $this->putTogetherHtmlList($entityIds);

    $form['entity_info'] = [
      '#markup' => $this->t('Nodes which will be affected by this action:') . $nodesList,
    ];

    $form['author'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Users'),
      '#size'          => 5,
      '#required'      => TRUE,
      '#options'       => $this->getAllUsersAsOptions(),
    ];

    $form['entity_ids'] = [
      '#type'  => 'hidden',
      '#value' => \json_encode($entityIds),
    ];

    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $entityIds = \json_decode($form_state->getValue('entity_ids'), TRUE);

    foreach ($entityIds as $entityId => $nodeTitle) {
      $this->setAuthor($form_state, $entityId);
      $this->messenger()->addMessage($this->t('Action was performed successfully.') . ' ' . $this->t('Applied author change with user display name @userDisplayName on @num nodes.',
        [
          '@userDisplayName' => $this->entityTypeManager->getStorage('user')->load($form_state->getValue('author'))->getDisplayName(),
          '@num'             => \count($entityIds),
        ])
      );
    }

    $this->redirectToContentOverview();
  }

  /**
   * Get all users as options.
   */
  private function getAllUsersAsOptions(): array {
    /**
     * @var \Drupal\user\Entity\User[] $users
     */
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple();

    $options = [];

    foreach ($users as $user) {
      $options[$user->id()] = $user->getDisplayName();
    }

    return $options;
  }

  /**
   * Set author.
   */
  private function setAuthor(FormStateInterface $form_state, int $entityId): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($entityId);
    $node->set('uid', $form_state->getValue('author'));
    $node->save();
  }

}
