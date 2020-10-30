<?php

namespace degov\Scripts\Robo;

use Robo\Contract\VerbosityThresholdInterface;
use Robo\Tasks;
use degov\Scripts\Robo\Exception\WrongFolderLocation;

/**
 * Class ConsoleReturn.
 */
class ConsoleReturn extends Tasks {

  /**
   * Is folder location.
   */
  public function isFolderLocation(string $command): bool {
    $message = $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec($command)
      ->run()
      ->getMessage();
    if (!empty($message)) {
      return TRUE;
    }

    throw new WrongFolderLocation('docroot folder is in wrong location.');
  }

  /**
   * Is application installed.
   */
  public function isApplicationInstalled(string $command): bool {
    exec($command, $output);
    if (!empty($output)) {
      return TRUE;
    }

    return FALSE;
  }

}
