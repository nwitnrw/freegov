<?php

declare(strict_types=1);

namespace Drupal\node_action;

use Drupal\Core\Url;

/**
 * Class UrlFactory.
 */
class UrlFactory {

  /**
   * Create.
   */
  public function create($route_name, $route_parameters = [], $options = []): Url {
    return new Url($route_name, $route_parameters, $options);
  }

}
