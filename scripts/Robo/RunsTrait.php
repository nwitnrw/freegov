<?php

namespace degov\Scripts\Robo;

use DrupalFinder\DrupalFinder;
use degov\Scripts\Robo\Exception\ApplicationRequirementFail;
use degov\Scripts\Robo\Exception\NoInstallationProfileProvided;
use degov\Scripts\Robo\Exception\WrongFolderLocation;
use degov\Scripts\Robo\Model\InstallationProfile;
use degov\Scripts\Robo\Model\InstallationProfileCollection;
use Drupal\Core\Language\LanguageManager;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

/**
 * Trait RunsTrait.
 */
trait RunsTrait {

  /**
   * Root folder path.
   *
   * @var string
   */
  private $rootFolderPath;

  /**
   * Drupal root.
   *
   * @var string
   */
  private $drupalRoot;

  /**
   * The shell's success exit code.
   *
   * @var int
   */
  private $exitCodeSuccess = 0;

  /**
   * Init.
   */
  protected function init(): void {
    $drupalFinder = new DrupalFinder();
    if ($drupalFinder->locateRoot(getcwd())) {
      $this->drupalRoot = $drupalFinder->getDrupalRoot();
      $this->rootFolderPath = $drupalFinder->getComposerRoot();
    }
  }

  /**
   * Run drush profile installation.
   *
   * @param \degov\Scripts\Robo\Model\InstallationProfileCollection $installationProfileCollection
   *   Installation profile collection.
   *
   * @throws \degov\Scripts\Robo\Exception\NoInstallationProfileProvided
   */
  protected function runDrushProfileInstallation(InstallationProfileCollection $installationProfileCollection): void {
    $this->init();
    if (!$installationProfileCollection->getMainInstallationProfile() instanceof InstallationProfile) {
      throw new NoInstallationProfileProvided();
    }

    $locale = $this->askDefault(
      sprintf('In which language (langcode) would you want to install %s (%s)?', $installationProfileCollection->getMainInstallationProfile()->getLabel(), $installationProfileCollection->getMainInstallationProfile()->getDescription()), 'de'
    );
    $languages = LanguageManager::getStandardLanguageList();
    while (!array_key_exists($locale, $languages)) {
      $this->yell('This is not a valid Drupal langcode!', 40, 'red');
      $locale = $this->askDefault('Please provide a valid langcode', 'de');
    }

    $siteName = $this->askDefault('What will be the sitename?', $installationProfileCollection->getMainInstallationProfile()->getLabel());

    $siteMail = $this->askDefault('What will be the site email address?', 'demo@example.com');

    $emailValidator = new EmailValidator();
    while (!$emailValidator->isValid($siteMail, new RFCValidation())) {
      $this->yell('This is not a valid email address!', 40, 'red');
      $siteMail = $this->askDefault('Please provide a valid email address', 'demo@example.com');
    }

    $username = $this->askDefault('The name for first user', 'admin');

    $password = $this->askDefault('The password for first user', 'admin');

    $email = $this->askDefault('The email address for first user', 'demo@example.com');
    while (!$emailValidator->isValid($email, new RFCValidation())) {
      $this->yell('This is not a valid email address!', 40, 'red');
      $email = $this->askDefault('Please provide a valid email address', 'demo@example.com');
    }

    $port = 3306;
    $hostname = '127.0.0.1';

    if (file_exists($pathToDrupalSettings = $this->drupalRoot . '/sites/default/settings.php')) {
      $app_root = $this->drupalRoot;
      $site_path = 'sites/default';
      @include $pathToDrupalSettings;
      $port = $databases['default']['default']['port'] ?? $port;
      $hostname = $databases['default']['default']['host'] ?? $hostname;
    }

    $port = $this->askDefault('Specify the port for the (Maria/My)SQL database, if you do not want to use the provided one.', $port);

    $hostname = $this->askDefault('Specify the hostname for the (Maria/My)SQL database, if you do not want to use the provided one.', $hostname);

    $database = $this->askDefault('What is the (Maria/My)SQL database name?', 'db');

    $databaseUsername = $this->askDefault('What is the (Maria/My)SQL database username?', 'db');

    $databasePassword = $this->askDefault('What is the (Maria/My)SQL database password?', 'db');

    $command = '';
    $command .= 'bin/drush si --yes ' . $installationProfileCollection->getMainInstallationProfile()->getMachineName();
    $command .= " --db-url=mysql://{$databaseUsername}:{$databasePassword}@{$hostname}:{$port}/{$database}";
    $command .= " --site-name='{$siteName}'";
    $command .= " --account-name='{$username}'";
    $command .= " --account-pass='{$password}'";
    $command .= " --locale='{$locale}'";
    $command .= " --account-mail='{$email}'";
    $command .= " --site-mail='{$siteMail}'";

    if (\is_numeric(strpos($installationProfileCollection->getMainInstallationProfile()->getMachineName(), 'gov'))) {
      $optionalModules = $this->askDefault('Would you like to install any additional modules (comma separate each module)?', 'degov_demo_content,degov_devel');
    }
    if ($installationProfileCollection->getSubInstallationProfile() instanceof InstallationProfile) {
      $mainInstallationProfileKey = $installationProfileCollection->getMainInstallationProfile()->getMachineName();
      $subInstallationProfileKey = $installationProfileCollection->getSubInstallationProfile()->getMachineName();

      $questionText = <<<HERE
The main- or sub-installationprofile can be installed. Dou you want to select? Available options: $mainInstallationProfileKey, $subInstallationProfileKey.
HERE;

      $selectedProfile = $this->askDefault($questionText, $mainInstallationProfileKey);
      $command .= ' install_configure_form.custom_profile_selection=' . $selectedProfile;
    }
    else {
      $command .= ' install_configure_form.custom_profile_selection=' . $installationProfileCollection->getMainInstallationProfile()->getMachineName();
    }

    /**
     * @var \Robo\Result $result
     */
    $result = $this->_exec($command);
    if ($result->getExitCode() !== $this->exitCodeSuccess) {
      $this->yell('Could not proceed with the installation due an error. Solve any errors from the console output above and retry.', 40, 'red');
      exit();
    }

    $optionalModules = str_replace(' ', '', $optionalModules);
    if (!empty($optionalModules)) {
      foreach (explode(',', $optionalModules) as $module) {
        $this->say('Enabling optional module: ' . $module);
        $this->_exec("drush en $module -y");
      }
    }
  }

  /**
   * Run composer update.
   */
  protected function runComposerUpdate(): void {
    $this->say('Proceeding with update.');
    $this->say('Updating dependencies via Composer..');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& composer update nrwgov/nrwgov --with-dependencies')
      ->run();

    $this->say('Finished Composer update.');
  }

  /**
   * Run translations update.
   */
  protected function runTranslationsUpdate(): void {
    $this->say('Checking translation updates..');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& drush locale-check')
      ->run();

    $this->say('Finished translation updates check..');

    $this->say('Updating translations..');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& drush locale-update')
      ->run();

    $this->say('Finished translation update.');
  }

  /**
   * Run drupal update hooks.
   */
  protected function runDrupalUpdateHooks(): void {
    $this->say('Running Drupal update hooks.');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& drush updb')
      ->run();

    $this->say('Finished running Drupal update hooks.');
  }

  /**
   * Run configuration export into filesystem.
   */
  protected function runConfigurationExportIntoFilesystem(): void {
    $this->say('Exporting configuration from storage into filesystem.');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& drush cex')
      ->run();

    $this->say('Finished exporting configuration from storage into filesystem.');
  }

  /**
   * Run base theme npm package update.
   */
  protected function runBaseThemeNpmPackageUpdate(): void {
    $this->say('Installing NPM packages in NRW base theme.');

    $pathToNpm = $this->getCommandOutput('which npm');

    $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $this->rootFolderPath . '&& cd docroot/themes/nrw/nrw_base_theme && ' . $pathToNpm . ' i')
      ->run();

    $this->say('Finished installation of NPM packages in NRW base theme.');
  }

  /**
   * Run custom theme update.
   */
  protected function runCustomThemeUpdate(): void {
    $this->say('Installing NPM packages and re-compiling JS/CSS assets in custom theme.');

    $pathToCustomThemeConfig = 'config' . DIRECTORY_SEPARATOR . 'sync' . DIRECTORY_SEPARATOR . 'system.theme.yml';
    if (file_exists($pathToCustomThemeConfig)) {
      $themeConfig = Yaml::parseFile($pathToCustomThemeConfig);
      if ($themeConfig['default'] !== 'nrw_base_theme') {
        $pathToNpm = $this->getCommandOutput('which npm');

        $this->_exec('cd ' . $this->rootFolderPath . '&& cd docroot/themes/custom/' . $themeConfig['default'] . ' && ' . $pathToNpm . ' i');
      }
      $this->say('Finished installation of NPM packages and re-compilation of JS/CSS assets in custom theme.');
    }
    else {
      $this->say('No custom theme detected. Bypassing installation of NPM packages and re-compilation of JS/CSS assets in custom theme.');
    }

  }

  /**
   * Check requirements.
   */
  protected function checkRequirements(string $distro = 'degov'): void {
    $this->checkNodeVersion();

    $projectStructure = new ProjectStructure($this->rootFolderPath);

    try {
      if ($projectStructure->checkCorrectProjectStructure($distro)) {
        $this->say('Project structure is correct.');
      }
    }
    catch (WrongFolderLocation $exception) {
      $this->say($exception->getMessage());
      throw new \Exception('Aborting update.');
    }

    $requiredApplications = new RequiredApplications();

    try {
      if ($requiredApplications->checkApplicationRequirementFulfilled()) {
        $this->say('Applications requirement is fulfilled.');
      }
    }
    catch (ApplicationRequirementFail $exception) {
      $this->say($exception->getMessage());
      throw new \Exception('Aborting update.');
    }
  }

  /**
   * Get command output.
   */
  private function getCommandOutput(string $command, $customLocation = NULL): string {
    $taskExecStack = $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);

    if ($customLocation) {
      $taskExecStack = $taskExecStack
        ->exec('cd ' . $customLocation);
    }

    $commandOutput = $taskExecStack
      ->exec($command)
      ->printOutput(FALSE)
      ->run()
      ->getMessage();

    return Utilities::removeCliLineBreaks($commandOutput);
  }

  /**
   * New git branch.
   */
  protected function newGitBranch($gitBranchLocation, $gitBranchName, $latestReleaseDevBranch = NULL): void {
    if (empty($latestReleaseDevBranch)) {
      $allBranches = $this->getCommandOutput('git branch --all', $gitBranchLocation);
      $latestReleaseDevBranch = Utilities::determineLatestReleaseDevBranch($allBranches);
    }

    $this->ensureCleanGitTree($gitBranchLocation);

    $taskGitStack = $this->taskExecStack();

    if ($gitBranchLocation) {
      $taskGitStack
        ->exec('cd ' . $gitBranchLocation);
    }

    $taskGitStack
      ->exec('git fetch')
      ->exec('git checkout -b ' . $gitBranchName . ' ' . $latestReleaseDevBranch)
      ->run();
  }

  /**
   * Is git remote origin.
   */
  private function isGitRemoteOrigin(string $folderLocation, string $gitRemoteOrigin): bool {
    $remoteInfo = $this->taskExecStack()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
      ->exec('cd ' . $folderLocation)
      ->exec('git remote show origin')
      ->printOutput(FALSE)
      ->run()
      ->getMessage();

    if (!is_numeric(strpos($remoteInfo, $gitRemoteOrigin))) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Ensure git repo.
   */
  protected function ensureGitRepo(string $projectFolderLocation, string $gitRemoteOrigin, string $projectFolderName): void {
    if (!$this->isGitRemoteOrigin($projectFolderLocation, $gitRemoteOrigin)) {
      $this->say('Could not find remote origin. Cloning repository from ' . $gitRemoteOrigin . '.');

      $this->taskExecStack()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
        ->exec('cd ' . $projectFolderLocation . '/..')
        ->exec('rm -rf ' . $projectFolderName)
        ->exec('git clone ' . $gitRemoteOrigin)
        ->run();

      if (!$this->isGitRemoteOrigin($projectFolderLocation, $gitRemoteOrigin)) {
        throw new \Exception('Could not find ' . $gitRemoteOrigin . ' remote origin, after Git clone try. Exiting.');
      }
      else {
        $this->say('Repository clone from ' . $gitRemoteOrigin . ' has been successful.');
      }
    }
  }

  /**
   * Ensure clean git tree.
   */
  private function ensureCleanGitTree($gitBranchLocation): void {
    $this->say('Check Git branch state.');
    $gitStatus = $this->getCommandOutput('git status', $gitBranchLocation);
    if (!is_numeric(strpos($gitStatus, 'working tree clean'))) {
      if (!$this->confirm('Your Git working tree is not clean. If you have no important changes, I can wipe the unsaved changes for you. Shall I do that?')) {
        throw new \Exception('Exiting because of not clean Git working tree. Commit the changes via Git or try "git clean -f" for wiping uncommited files or wipe staged files via "git reset HEAD --hard".');
      }

      $this->say('Resetting Git branch.');

      $taskExecStack = $this->taskExecStack()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);

      if ($gitBranchLocation) {
        $taskExecStack
          ->exec('cd ' . $gitBranchLocation);
      }

      $taskExecStack
        ->exec('cd `git rev-parse --show-toplevel`')
        ->exec('git clean -f')
        ->exec('git reset HEAD --hard')
        ->run();

      $gitStatus = $this->getCommandOutput('git status', $gitBranchLocation);
      if (!is_numeric(strpos($gitStatus, 'working tree clean'))) {
        throw new \Exception('Git branch still not clean.');
      }
    }

    $this->say('Git branch is clean. Checking out new Git branch from latest release dev branch.');
  }

}
