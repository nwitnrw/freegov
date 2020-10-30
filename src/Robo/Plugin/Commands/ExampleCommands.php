<?php

namespace Drupal\degov\Robo\Plugin\Commands;

use Robo\Tasks;
use degov\Scripts\Robo\RunsTrait;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class ExampleCommands extends Tasks {

  use RunsTrait;

  /**
   * Example for your own Robo Tasks.
   *
   * @throws \Exception
   */
  public function exampleCommandsExampleTask(): void {
    // Call the trait's init function to populate rootFolderPath.
    $this->init();

    $rootFolderPath = $this->rootFolderPath;
    $class = __METHOD__;
    $here = __FILE__;

    $composerFile = json_decode(file_get_contents($this->rootFolderPath . '/composer.json'), TRUE);
    $projectName = $composerFile['name'];

    $this->say("Hi, I'm a example project Robo Task.
    
    Tasks using degov\Scripts\Robo\RunsTrait and calling \$this->init() have a \$rootFolderPath.
    
     \$rootFolderPath = '$rootFolderPath'
    
    Find this task here in 
     $class
     $here
    
    Project name in composer.json is '$projectName'.
    ");
  }

}
