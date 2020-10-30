<?php

namespace Drupal\degov_config_integrity\Command;

use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\degov_config_integrity\DegovModuleIntegrityChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DegovConfigIntegrityCheckConsoleCommand.
 *
 * @DrupalCommand (
 *     extension="degov_config_integrity",
 *     extensionType="module"
 * )
 */
class DegovConfigIntegrityCheckConsoleCommand extends ContainerAwareCommand {

  use StringTranslationTrait;

  /**
   * Module integrity checker.
   *
   * @var \Drupal\degov_config_integrity\DegovModuleIntegrityChecker
   */
  private $moduleIntegrityChecker;

  /**
   * DegovConfigIntegrityCheckConsoleCommand constructor.
   */
  public function __construct(DegovModuleIntegrityChecker $moduleIntegrityChecker) {
    parent::__construct();
    $this->moduleIntegrityChecker = $moduleIntegrityChecker;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->setName('config:diff:installed-modules')
      ->setDescription($this->t('Check for missing configuration.'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): void {
    $this->getIo()->info($this->t('deGov configuration integrity check running…'));
    $configurationIntegrityIntact = TRUE;
    foreach ($this->moduleIntegrityChecker->checkIntegrity() as $module) {
      foreach ($module as $key => $messages) {
        $this->getIo()->newLine();
        $this->getIo()
          ->warningLite($this->t('Module @module: Configuration is missing', ['@module' => $key]));
        foreach ($messages as $message) {
          $this->getIo()->info($message);
        }
        $configurationIntegrityIntact = FALSE;
      }
    }
    if ($configurationIntegrityIntact) {
      $this->getIo()
        ->successLite($this->t('All expected configuration seems to be in place.'));
    }
  }

}
