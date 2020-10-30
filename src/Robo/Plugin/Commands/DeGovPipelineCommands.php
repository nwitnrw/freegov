<?php

namespace Drupal\degov\Robo\Plugin\Commands;

use degov\Scripts\Robo\Exception\ApplicationRequirementFail;
use degov\Scripts\Robo\RunsTrait;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class DeGovPipelineCommands extends Tasks {
  use RunsTrait;

  /**
   * Create a Admin login Cookie for Backstop testing.
   *
   * @param int $uid
   *   Uid of the user to get a Cookie for.
   *
   * @command degov:create-admin-cookie
   *
   * @throws \Exception
   */
  public function degovCreateAdminCookie($uid = 1): void {
    $this->init();
    $cookieTemplate = $this->rootFolderPath . '/docroot/profiles/contrib/degov/testing/backstopjs/backstop_data/engine_scripts/cookiesAdmin--template.json';

    $cookieDestinationFolders = [
      $this->rootFolderPath . '/docroot/profiles/contrib/degov/testing/backstopjs/tmp',
      $this->rootFolderPath . '/docroot/profiles/contrib/nrwgov/testing/backstopjs/tmp',
    ];

    if (!file_exists($cookieTemplate)) {
      throw new ApplicationRequirementFail('Missing cookie Template at ' . $cookieTemplate);
    }

    try {
      $cookie = $this->taskExecStack()
        ->stopOnFail()
        ->printOutput(FALSE)
        ->exec($this->rootFolderPath . '/bin/drush  uli --uid=' . $uid . ' --uri=http://host.docker.internal | xargs curl -sLIXGET | grep Set-Cookie.*SESS')
        ->run();
      if (!$cookie->wasSuccessful()) {
        throw new ApplicationRequirementFail('Could not get cookie from drush uli');
      }

      $re = '/Set-Cookie: (SESS[0-9a-fA-F]+)=(.+?);.*/m';
      // @see: https://regex101.com/r/N6i2vj/3
      preg_match_all($re, $cookie->getMessage(), $matches, PREG_SET_ORDER, 0);

      // Arbitrary value. Actually depends on webserver ini_get('session.cookie_lifetime').
      $expireIn = time() + (30 * 60);

      foreach ($cookieDestinationFolders as $cookieDest) {
        if (is_dir($cookieDest)) {
          $this->taskFilesystemStack()
            ->copy($cookieTemplate, "$cookieDest/cookiesAdmin.json", TRUE)
            ->run();

          $this->taskReplaceInFile("$cookieDest/cookiesAdmin.json")
            ->from([
              '<name_placeholder>',
              '<value_placeholder>',
              '<expiration_placeholder>',
            ])
            ->to([
              $matches[0][1],
              $matches[0][2],
              $expireIn,
            ])
            ->run();
        }
      }
    }
    catch (ApplicationRequirementFail $exception) {
      $this->yell($exception->getMessage());
    }
  }

}
