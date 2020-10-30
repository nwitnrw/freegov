<?php

namespace Drupal\degov\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class ModuleContext.
 */
class ModuleContext extends RawDrupalContext {

  /**
   * Proof drupal module is installed.
   *
   * @Then /^I proof that Drupal module "([^"]*)" is installed$/
   */
  public function proofDrupalModuleIsInstalled($moduleName): void {
    if (!$this->getModuleHandler()->moduleExists($moduleName)) {
      throw new \Exception("Drupal module $moduleName is not installed.");
    }
  }

  /**
   * Proofs multiple Drupal modules installation.
   *
   * Provide module data in the following format:
   *
   * | webform      |
   * | devel        |
   *
   * @Given I proof that the following Drupal modules are installed:
   */
  public function proofMultipleDrupalModulesAreInstalled(TableNode $modulesTable): void {
    $rowsHash = $modulesTable->getRowsHash();
    $moduleMachineNames = array_keys($rowsHash);

    foreach ($moduleMachineNames as $moduleMachineName) {
      if (!$this->getModuleHandler()->moduleExists($moduleMachineName)) {
        throw new \Exception("Drupal module '$moduleMachineName' is not installed.");
      }
    }
  }

  /**
   * Proofs that multiple Drupal modules are "not" installed.
   *
   * Provide module data in the following format:
   *
   * | webform      |
   * | devel        |
   *
   * @Given I proof that the following Drupal modules are not installed:
   */
  public function proofMultipleDrupalModulesAreNotInstalled(TableNode $modulesTable): void {
    $rowsHash = $modulesTable->getRowsHash();
    $moduleMachineNames = array_keys($rowsHash);

    foreach ($moduleMachineNames as $moduleMachineName) {
      if ($this->getModuleHandler()->moduleExists($moduleMachineName)) {
        throw new TextNotFoundException("Drupal module '$moduleMachineName' is installed.", $this->getSession());
      }
    }
  }

  /**
   * Am installing the module.
   *
   * @Then /^I am installing the "([^"]*)" module$/
   */
  public function iAmInstallingTheModule(string $moduleName): void {
    $this->getModuleInstaller()->install([$moduleName]);
    // Required to import translations or other batch processes which runs after
    // a module is installed. (by default via backend which would runs a batch)
    $batch =& batch_get();
    if (empty($batch)) {
      return;
    }
    $batch['progressive'] = FALSE;
    batch_process();
  }

  /**
   * Uninstall the module.
   *
   * @Then /^I uninstall the "([^"]*)" module$/
   */
  public function iUninstallTheModule(string $moduleName): void {
    $this->getModuleInstaller()->uninstall([$moduleName], TRUE);
  }

  /**
   * Installs multiple Drupal modules.
   *
   * Provide module data in the following format:
   *
   * | webform      |
   * | devel        |
   *
   * @Given I am installing the following Drupal modules:
   */
  public function installMultipleDrupalModules(TableNode $modulesTable): void {
    $rowsHash = $modulesTable->getRowsHash();
    $moduleMachineNames = array_keys($rowsHash);

    foreach ($moduleMachineNames as $moduleMachineName) {
      $this->iAmInstallingTheModule($moduleMachineName);
    }
  }

  /**
   * Get module installer.
   */
  protected function getModuleInstaller(): ModuleInstallerInterface {
    return \Drupal::service('module_installer');
  }

  /**
   * Get module handler.
   */
  protected function getModuleHandler(): ModuleHandlerInterface {
    return \Drupal::service('module_handler');
  }

}
