<?php

namespace Drupal\Tests\media_file_links\Kernel;

use Drupal\filter\FilterProcessResult;
use Drupal\media_file_links\Plugin\Filter\FilterMediaFileLinks;

/**
 * Class FilterMediaFileLinksTest
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class FilterMediaFileLinksTest extends MediaFileLinksTestBase {

  /**
   * Test input filter outputs resolved url.
   */
  public function testInputFilterOutputsResolvedUrl(): void {
    $language_manager = $this->container->get('language_manager');

    $inputFilter = FilterMediaFileLinks::create($this->container, [], '', ['provider' => '']);
    $formattedText = $inputFilter->process('[media/file/' . $this->supportedMediaId . ']', $language_manager->getCurrentLanguage());
    self::assertInstanceOf(FilterProcessResult::class, $formattedText);
    self::assertContains('dummy.pdf', $formattedText->getProcessedText());
  }

}
