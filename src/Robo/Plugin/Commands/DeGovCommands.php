<?php

namespace Drupal\degov\Robo\Plugin\Commands;

use Robo\Tasks;
use degov\Scripts\Robo\Model\InstallationProfile;
use degov\Scripts\Robo\Model\InstallationProfileCollection;
use degov\Scripts\Robo\RunsTrait;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class DeGovCommands extends Tasks {

  use RunsTrait;

  /**
   * New DeGov issue.
   *
   * @param string $gitBranchName
   *   Git branch name.
   *
   * @command degov:new-issue
   *
   * @throws \Exception
   */
  public function degovNewIssue(string $gitBranchName): void {
    $this->init();
    $degovFolderLocation = $this->rootFolderPath . '/docroot/profiles/contrib/degov';
    $this->ensureGitRepo($degovFolderLocation, 'git@bitbucket.org:publicplan/degov.git', 'degov');
    $this->newGitBranch($degovFolderLocation, $gitBranchName);
  }

  /**
   * Allows you to install or reinstall deGOV.
   *
   * @throws \degov\Scripts\Robo\Exception\NoInstallationProfileProvided
   */
  public function degovSiteInstall(): void {
    $installationProfileCollection = new InstallationProfileCollection();

    $mainInstallationProfile = new InstallationProfile(
      'degov',
      'deGov',
      'deGov - Drupal 8 for Government'
    );

    $installationProfileCollection->setMainInstallationProfile($mainInstallationProfile);
    $this->runDrushProfileInstallation($installationProfileCollection);
  }

}
