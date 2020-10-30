<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

use Drupal\content_moderation\Plugin\Field\ModerationStateFieldItemList;
use Drupal\node\NodeInterface;
use Drupal\node_action\UserInteractionFacade;

/**
 * Class PublishedStateChange.
 */
class PublishedStateChange {

  use MessagesTrait;

  /**
   * User interaction facade.
   *
   * @var \Drupal\node_action\UserInteractionFacade
   */
  private $userInteractionFacade;

  /**
   * PublishedStateChange constructor.
   */
  public function __construct(UserInteractionFacade $userInteractionFacade) {

    $this->userInteractionFacade = $userInteractionFacade;
  }

  /**
   * Is allowed.
   */
  public function isAllowed(NodeInterface $node): bool {
    if (!$this->hasModerationState($node)) {
      $this->addMessageForNoModerationState($node);

      return FALSE;
    }

    if ($this->isPublishedModerationState($node) && \in_array('editor', $this->userInteractionFacade->currentUser->getRoles(), TRUE)) {
      $this->addMessageForRestriction($node);

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Has moderation state.
   */
  private function hasModerationState(NodeInterface $node): bool {
    if (($moderationStateList = $node->get('moderation_state')) instanceof ModerationStateFieldItemList && $moderationStateList->count() === 0) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Is published moderation state.
   */
  private function isPublishedModerationState(NodeInterface $node): bool {
    if (!($moderationStateList = $node->get('moderation_state')) instanceof ModerationStateFieldItemList) {
      $this->userInteractionFacade->messenger->addMessage($this->userInteractionFacade->stringTranslationAdapter->t(self::$messageNoUnpublishPermission, ['@nodeTitle' => $node->getTitle()]), 'warning', FALSE);

      return FALSE;
    }

    if (!empty($moderationState = $moderationStateList->first()
      ->getValue()) && $moderationState['value'] === 'published') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Add message for no moderation state.
   */
  private function addMessageForNoModerationState(NodeInterface $node): void {
    $this->userInteractionFacade->messenger->addMessage($this->userInteractionFacade->stringTranslationAdapter->t(self::$messageNoModerationStatesForContentEntityType, [
      '@title'             => $node->getTitle(),
      '@contentEntityType' => $node->getType(),
    ]), 'warning', FALSE);
  }

  /**
   * Add message for restriction.
   */
  private function addMessageForRestriction(NodeInterface $node): void {
    $this->userInteractionFacade->messenger->addMessage($this->userInteractionFacade->stringTranslationAdapter->t(self::$messageNoUnpublishPermission, ['@nodeTitle' => $node->getTitle()]), 'warning', FALSE);
  }

}
