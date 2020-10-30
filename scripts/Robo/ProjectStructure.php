<?php

namespace degov\Scripts\Robo;

use Robo\Tasks;
use degov\Scripts\Robo\Exception\WrongFolderLocation;

/**
 * Class ProjectStructure.
 */
class ProjectStructure extends Tasks {

  /**
   * Root folder location.
   *
   * @var string
   */
  private $rootFolderLocation;

  /**
   * ProjectStructure constructor.
   */
  public function __construct(string $rootFolderLocation) {
    $this->rootFolderLocation = $rootFolderLocation;
  }

  /**
   * Check correct project structure.
   */
  public function checkCorrectProjectStructure(string $distro): void {
    $this->checkBaseDistroFolder();
    $this->checkDistroFolder($distro);
    $this->checkDocrootFolder();
  }

  /**
   * Check docroot folder.
   */
  private function checkDocrootFolder(): void {
    $command = 'ls ' . $this->rootFolderLocation . ' | grep docroot';
    exec($command, $output);
    if (empty($output)) {
      throw new WrongFolderLocation('docroot folder is in wrong location.');
    }
  }

  /**
   * Check base distro folder.
   */
  private function checkBaseDistroFolder(): void {
    $this->checkDistroFolder('degov');
  }

  /**
   * Check distro folder.
   */
  private function checkDistroFolder(string $distro): void {
    $command = 'ls ' . $this->rootFolderLocation . '/docroot/profiles/contrib | grep ' . $distro;
    exec($command, $output);
    if (empty($output)) {
      throw new WrongFolderLocation($distro . ' folder is in wrong location.');
    }
  }

}
