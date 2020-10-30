<?php

namespace degov\Scripts\Robo\Test;

use degov\Scripts\Robo\Exception\ApplicationRequirementFail;
use degov\Scripts\Robo\Utilities;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilitiesTest.
 */
class UtilitiesTest extends TestCase {

  /**
   * Test remove cli line breaks.
   */
  public function testRemoveCliLineBreaks(): void {
    $text = <<<EOT
Somewhat with

 a

 line


 break.
EOT;

    self::assertSame('Somewhat with a line break.', Utilities::removeCliLineBreaks($text));
  }

  /**
   * Test check application requirement fail.
   *
   * @dataProvider wrongNodeVersionDataProvider
   */
  public function testCheckApplicationRequirementFail(string $wrongVersion): void {
    $this->expectException(ApplicationRequirementFail::class);
    Utilities::checkNodeVersion($wrongVersion);
  }

  /**
   * Test checkout no application requirement fail.
   *
   * @dataProvider correctNodeVersionDataProvider
   */
  public function testCheckNoApplicationRequirementFail(string $correctVersion): void {
    Utilities::checkNodeVersion($correctVersion);
    // We do not have assert here, therefore we fake one.
    // $this->doesNotPerformAssertions() is a risky test which is not allowed.
    $this->assertTrue(TRUE);
  }

  /**
   * Wrong node version data provider.
   */
  public function wrongNodeVersionDataProvider(): array {
    return [
      ['v6.11.1'],
      ['vasdfaf6.1'],
      ['5.11.1'],
      ['blub4.11.1'],
    ];
  }

  /**
   * Correct node version data provider.
   */
  public function correctNodeVersionDataProvider(): array {
    return [
      ['v8.22.1'],
      ['9.34.5'],
      ['blub10.34.5'],
      ['v10.34.5'],
      ['vasdf11.34.5'],
    ];
  }

  /**
   * Test determine latest release dev branch.
   *
   * @dataProvider gitBranchesDataProvider
   */
  public function testDetermineLatestReleaseDevBranch(string $gitBranches): void {
    $this->assertSame('remotes/origin/release/7.1.x-dev', Utilities::determineLatestReleaseDevBranch($gitBranches));
  }

  /**
   * Git branches  data provider.
   */
  public function gitBranchesDataProvider(): array {
    return [
      ['* feature/DEGOV-276-new-ticket  release/7.1.x-dev  release/7.x-dev  remotes/origin/2984568  remotes/origin/DEGOV-384_degov_breadcrumbs_config  remotes/origin/DEGOV-384_paragraph_downloads_config  remotes/origin/HEAD -> origin/release/7.x-dev  remotes/origin/SaschaHannes/commonphp-edited-online-with-bitbucket-1557998534778  remotes/origin/feature/2984568  remotes/origin/feature/2985934-teaser-long-text  remotes/origin/feature/5.x-dev-into-6.x  remotes/origin/feature/7.x-test  remotes/origin/feature/ADM-fix-quickedit  remotes/origin/feature/DEGOV-208_2  remotes/origin/feature/DEGOV-274-robo-build-tool  remotes/origin/feature/DEGOV-351-wraith-pipeline  remotes/origin/feature/DEGOV-354-drupal-update_863  remotes/origin/feature/DEGOV-359-fix-pipeline  remotes/origin/feature/DEGOV-388-add-tag-updater-service  remotes/origin/feature/DEGOV-394-migrate-lightning_media_image  remotes/origin/feature/DEGOV-411  remotes/origin/feature/DEGOV-412  remotes/origin/feature/DEGOV-422-menu-links-autocomplete  remotes/origin/feature/DEGOV-462-bugfixes  remotes/origin/feature/DEGOV-467-bulk-operations  remotes/origin/feature/DEGOV-477-link-to-media  remotes/origin/feature/DEGOV-483-fontawesome-5  remotes/origin/feature/DEGOV-515-fixes-bulk-operations  remotes/origin/feature/DEGOV-525-volltextsuche-dokumente  remotes/origin/feature/DEGOV-530-social-media-settings  remotes/origin/feature/DEGOV-560-update-menu-links  remotes/origin/feature/DEGOV-589-fix-instalation-theme  remotes/origin/feature/DEGOV-598-homepage-slider-test  remotes/origin/feature/DEGOV-628-social-media-teasers-on-frontpage  remotes/origin/feature/DEGOV-629-citation-paragraph-on-homepage  remotes/origin/feature/DEGOV-631-place-social-media-settings-links-in-the-top-menu  remotes/origin/feature/DEGOV-632-search-box-in-nav-missing  remotes/origin/feature/DEGOV-632-search-box-in-nav-missing_new  remotes/origin/feature/DEGOV-632-search-box-in-nav-missing_new1  remotes/origin/feature/DEGOV-633-place-icons-in-the-user-menu  remotes/origin/feature/DEGOV-640-fix-slider-behat-test-base-single-test  remotes/origin/feature/DEGOV-662  remotes/origin/feature/MHKBGNRW-173_degov  remotes/origin/feature/NRWGOV-1-long-teaser  remotes/origin/feature/NRWGOV-10-blog-teaser-text  remotes/origin/feature/NRWGOV-19-disallow-remote-sources  remotes/origin/feature/NRWGOV-22-gallery-preview  remotes/origin/feature/NRWGOV-7-maps-locations  remotes/origin/feature/added-test-for-missing-fields  remotes/origin/feature/automatische-bildzuschnitte  remotes/origin/feature/behat-more-info-on-failure  remotes/origin/feature/behat-parallel-execution  remotes/origin/feature/bugfix-123  remotes/origin/feature/capitalize-module-namespacing  remotes/origin/feature/check-scheduled-publish  remotes/origin/feature/check_cellular  remotes/origin/feature/dateformat  remotes/origin/feature/degov-547-backstopjs  remotes/origin/feature/degov_facet_theme  remotes/origin/feature/demo-content-fails-on-platform  remotes/origin/feature/demo-content-module  remotes/origin/feature/drupal-8.6.4-update  remotes/origin/feature/drupal-core-8.6.13  remotes/origin/feature/drupal-update_8-6-9  remotes/origin/feature/entity_browser_2  remotes/origin/feature/external-teaser-refactoring  remotes/origin/feature/fix-chrome-downloadable  remotes/origin/feature/fix-transform-script  remotes/origin/feature/issue-2995488-dependency-to-scheduled_updates  remotes/origin/feature/issue-2996977-moved-media-pattern  remotes/origin/feature/linkableTitles  remotes/origin/feature/make-degov-simplenews-references-config-optional-5.1.x  remotes/origin/feature/make-degov-simplenews-references-config-optional-6.x  remotes/origin/feature/manager-with-default-pbt-permissions-5.1.x  remotes/origin/feature/manager-with-default-pbt-permissions-6.x  remotes/origin/feature/media-image-test  remotes/origin/feature/merge-5.x  remotes/origin/feature/merge-latest-release  remotes/origin/feature/module-status  remotes/origin/feature/newsletterNewFieldsFix  remotes/origin/feature/outsourced-degov-installation-profile  remotes/origin/feature/pipeline-march-changes  remotes/origin/feature/remove-dependencies-degov_common  remotes/origin/feature/remove-dev-shm  remotes/origin/feature/remove-lightning_media  remotes/origin/feature/removed-useless-accesscheck  remotes/origin/feature/replace-old-package  remotes/origin/feature/responsive-teaser-image  remotes/origin/feature/simplenews  remotes/origin/feature/test-drupal-8-6-4  remotes/origin/feature/test-php-ini  remotes/origin/feature/testing_media_context  remotes/origin/feature/testing_media_view  remotes/origin/feature/update-translation  remotes/origin/feature/used_hook_enable_for_translation  remotes/origin/feauture/add-slide-class-to-slider-elements  remotes/origin/feauture/fix-chrome-8.6.2.x  remotes/origin/feauture/prevent-twice-loaded-js  remotes/origin/feauture/small-cleanup  remotes/origin/fix/DEGOV-384_translatable-config-breadcrumb  remotes/origin/fix/DEGOV-384_translatable-configuration  remotes/origin/hotfix/SA-CORE-2019-003  remotes/origin/master  remotes/origin/release-7.x-dev-old-state  remotes/origin/release/2.1.x  remotes/origin/release/2.x  remotes/origin/release/3.x  remotes/origin/release/5.1.x  remotes/origin/release/5.x  remotes/origin/release/5.x-dev  remotes/origin/release/6.1.x  remotes/origin/release/6.1.x-dev  remotes/origin/release/6.2.x  remotes/origin/release/6.2.x-dev  remotes/origin/release/6.3.x  remotes/origin/release/6.3.x-dev  remotes/origin/release/6.4.x  remotes/origin/release/6.4.x-dev  remotes/origin/release/6.x  remotes/origin/release/6.x-dev  remotes/origin/release/7.1.x  remotes/origin/release/7.1.x-dev  remotes/origin/release/7.x  remotes/origin/release/7.x-dev  remotes/origin/release/1.18.x-dev'],
    ];
  }

}
