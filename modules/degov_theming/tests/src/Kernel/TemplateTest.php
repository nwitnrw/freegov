<?php

namespace Drupal\Tests\degov_theming\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class TemplateTest.
 */
class TemplateTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['degov_theming'];

  /**
   * Render.
   */
  public function testRender() {
    /**
     * @var \Drupal\degov_theming\Service\Template $template
     */
    $template = $this->container->get('degov_theming.template');
    $html = $template->render('degov_theming', 'tests/src/Kernel/Fixture/template.html.twig', ['test' => 'some string']);

    $this->assertSame($html, '<span>some string</span>');
  }

}
