<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

/**
 * Trait MessagesTrait.
 */
trait MessagesTrait {

  /**
   * Message no role permission.
   *
   * @var string
   */
  private static $messageNoRolePermission = '`@actionName` action took no effect on node titled `@nodeTitle`, since your user has no role permission.';

  /**
   * Message no permission by term permissions.
   *
   * @var string
   */
  private static $messageNoPermissionByTermPermission = '`@actionName` action took no effect on node titled `@nodeTitle`, due to taxonomy term restrictions regarding your user.';

  /**
   * Message no unpublish permission.
   *
   * @var string
   */
  private static $messageNoUnpublishPermission = 'Node with title `@nodeTitle` is published. The workflow does not allow editors to modify the moderation state of published content.';

  /**
   * Message no moderation states for content entity type.
   *
   * @var string
   */
  private static $messageNoModerationStatesForContentEntityType = 'Content with title `@title` is of content entity type `@contentEntityType`, which does not have any moderation states assigned.';

}
