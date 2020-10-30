<?php

namespace degov\Scripts\Robo\Model;

/**
 * Class InstallationProfile.
 */
class InstallationProfile {

  /**
   * Machine name.
   *
   * @var string
   */
  private $machineName;

  /**
   * Label.
   *
   * @var string
   */
  private $label;

  /**
   * Description.
   *
   * @var string
   */
  private $description;

  /**
   * InstallationProfileDefinitionModel constructor.
   *
   * @param string $machineName
   *   Machine name.
   * @param string $label
   *   Label.
   * @param string $description
   *   Description.
   */
  public function __construct(string $machineName, string $label, string $description) {
    $this->machineName = $machineName;
    $this->label = $label;
    $this->description = $description;
  }

  /**
   * Get machine name.
   *
   * @return string
   *   Machine name.
   */
  public function getMachineName() {
    return $this->machineName;
  }

  /**
   * Set machine name.
   *
   * @param string $machineName
   *   Machine name.
   */
  public function setMachineName(string $machineName): void {
    $this->machineName = $machineName;
  }

  /**
   * Get label.
   *
   * @return string
   *   Label.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Set label.
   *
   * @param string $label
   *   Label.
   */
  public function setLabel(string $label): void {
    $this->label = $label;
  }

  /**
   * Get description.
   *
   * @return string
   *   Description.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Set description.
   *
   * @param string $description
   *   Description.
   */
  public function setDescription(string $description): void {
    $this->description = $description;
  }

}
