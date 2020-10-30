<?php

declare(strict_types=1);

namespace Drupal\node_action;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectResponseFactory.
 */
class RedirectResponseFactory {

  /**
   * Create.
   */
  public function create(string $url, int $status = 302, array $headers = []): RedirectResponse {
    return new RedirectResponse($url, $status, $headers);
  }

}
