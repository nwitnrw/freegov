<?php

namespace degov\Scripts\Robo\Model;

/**
 * Class InstallationProfileCollection.
 */
class InstallationProfileCollection {

  /**
   * Main installation profile.
   *
   * @var InstallationProfile
   */
  private $mainInstallationProfile;

  /**
   * Sub installation profile.
   *
   * @var InstallationProfile
   */
  private $subInstallationProfile;

  /**
   * Get main installation profile.
   *
   * @return InstallationProfile
   *   Installation profile.
   */
  public function getMainInstallationProfile(): InstallationProfile {
    return $this->mainInstallationProfile;
  }

  /**
   * Get sub installation profile.
   *
   * @return InstallationProfile|null
   *   Installation profile.
   */
  public function getSubInstallationProfile(): ?InstallationProfile {
    return $this->subInstallationProfile;
  }

  /**
   * Set sub installation profile.
   *
   * @param InstallationProfile $subInstallationProfile
   *   Installation profile.
   */
  public function setSubInstallationProfile(InstallationProfile $subInstallationProfile): void {
    $this->subInstallationProfile = $subInstallationProfile;
  }

  /**
   * Set main installation profile.
   *
   * @param InstallationProfile $mainInstallationProfile
   *   Installation profile.
   */
  public function setMainInstallationProfile(InstallationProfile $mainInstallationProfile): void {
    $this->mainInstallationProfile = $mainInstallationProfile;
  }

}
