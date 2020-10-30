<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_auto_crop\Kernel;

use Drupal\Tests\degov_common\Kernel\ModuleInstallationTestAbstract;

/**
 * Class ModuleInstallationTest.
 */
class ModuleInstallationTest extends ModuleInstallationTestAbstract {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'degov_auto_crop',
  ];

}
