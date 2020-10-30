<?php

declare(strict_types=1);

namespace Drupal\node_action\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node_action\AccessChecker\MessagesTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ModerationState.
 */
final class ModerationState extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  private const DATETIME_STORAGE_FORMAT = 'Y-m-d\TH:i:s';

  use MessagesTrait;

  use ActionFormTrait;

  /**
   * ModerationState constructor.
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

    $form['moderation_state'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Moderation state'),
      '#size'          => 5,
      '#required'      => TRUE,
      '#options'       => $this->getAllModerationStates(),
    ];

    $form['entity_ids'] = [
      '#type'  => 'hidden',
      '#value' => \json_encode($entityIds),
    ];

    $form['date'] = [
      '#type'           => 'datetime',
      '#title'          => $this->t('Scheduled date'),
      '#description'    => $this->t('The date of the scheduled publish state change. If you provide none, the moderation state will be set immediately.'),
      '#default_value'  => NULL,
      '#date_increment' => 1,
      '#date_timezone'  => \date_default_timezone_get(),
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
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    if (!empty($form_state->getValue('date')) && \strtotime((string) $form_state->getValue('date')) < \time()) {
      $form_state->setErrorByName('date', $this->t('The date must be in the future.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $entityIds = \json_decode($form_state->getValue('entity_ids'), TRUE);

    foreach ($entityIds as $entityId => $nodeTitle) {

      if (!empty($form_state->getValue('date'))) {
        $this->scheduleModerationStateChange($form_state, $entityId);
        $this->messenger()->addMessage($this->t('Action was performed successfully.') . ' ' . $this->t('Scheduled moderation state change with state @state on @num nodes for date @date.',
          [
            '@state' => $this->getWorkflowLabelByKey($form_state->getValue('moderation_state')),
            '@num'   => \count($entityIds),
            '@date'  => \date('d.m.Y, h:i', \strtotime((string) $form_state->getValue('date'))),
          ])
        );
      }

      if (empty($form_state->getValue('date'))) {
        $this->setModerationState($form_state, $entityId);
        $this->messenger()->addMessage($this->t('Action was performed successfully.') . ' ' . $this->t('Applied moderation change with state @state on @num nodes.',
          [
            '@state' => $this->getWorkflowLabelByKey($form_state->getValue('moderation_state')),
            '@num'   => \count($entityIds),
          ])
        );
      }

    }

    $this->redirectToContentOverview();
  }

  /**
   * Get workflow label by key.
   */
  private function getWorkflowLabelByKey(string $key) {
    $editorialWorkflow = $this->entityTypeManager->getStorage('workflow')
      ->loadByProperties(['type' => 'content_moderation']);
    $states = $editorialWorkflow['editorial']->get('type_settings')['states'];

    return $states[$key]['label'];
  }

  /**
   * Redirect to content overview.
   */
  private function redirectToContentOverview(): RedirectResponse {
    $redirect_url = new Url('system.admin_content');
    $response = new RedirectResponse($redirect_url->toString());
    $response->send();

    return $response;
  }

  /**
   * Get all moderation states.
   */
  private function getAllModerationStates(): array {
    /**
     * @var \Drupal\workflows\Entity\Workflow $editorialWorkflow
     */
    $editorialWorkflow = $this->entityTypeManager->getStorage('workflow')
      ->loadByProperties(['type' => 'content_moderation']);

    $options = [];

    foreach ($editorialWorkflow['editorial']->get('type_settings')['states'] as $stateKey => $stateArray) {
      $options[$stateKey] = $stateArray['label'];
    }

    return $options;
  }

  /**
   * Schedule moderation state change.
   */
  private function scheduleModerationStateChange(FormStateInterface $form_state, int $entityId): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($entityId);
    $node->set('field_scheduled_publish', [
      'moderation_state' => $form_state->getValue('moderation_state'),
      'value'            => \gmdate(self::DATETIME_STORAGE_FORMAT, \strtotime((string) $form_state->getValue('date'))),
    ]);
    $node->save();
  }

  /**
   * Set moderation state.
   */
  private function setModerationState(FormStateInterface $form_state, int $entityId): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($entityId);
    $node->set('moderation_state', $form_state->getValue('moderation_state'));
    $node->save();
  }

}
