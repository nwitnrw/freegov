<?php

declare(strict_types=1);

namespace Drupal\node_action\Form;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ActionFormTrait.
 */
trait ActionFormTrait {

  /**
   * Put together html list.
   */
  private function putTogetherHtmlList(array $entityIds = []): string {
    $nodesList = '<ul>';

    foreach ($entityIds as $entityId => $entityTitle) {
      $nodesList .= '<li><a href="/node/' . $entityId . '" title="' . $entityTitle . '">' . $entityTitle . '</a></li>';
    }

    $nodesList .= '</ul>';
    return $nodesList;
  }

  /**
   * Remove message from default action.
   */
  private function removeMessageFromDefaultAction(): void {
    $this->messenger()->deleteByType('error');
    $this->messenger()->deleteByType('status');
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

}
