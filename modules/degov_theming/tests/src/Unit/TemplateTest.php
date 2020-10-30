<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_theming\Unit;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\degov_theming\Facade\ComponentLocation;
use Drupal\degov_theming\Service\DrupalPath;
use Drupal\degov_theming\Service\Template;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;

/**
 * Class TemplateTest.
 */
class TemplateTest extends UnitTestCase {

  /**
   * Template.
   *
   * @var \Drupal\degov_theming\Service\Template
   */
  private $template;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->template = new Template($this->mockThemeManager(), $this->mockComponentLocation(), $this->mockTwig(), $this->mockThemeInitialization());
  }

  /**
   * Mock drupal path.
   *
   * @return \Drupal\degov_theming\Service\DrupalPath
   *   Drupal path.
   */
  private function mockDrupalPath($bundle) {
    $drupalPath = $this->prophesize(DrupalPath::class);
    switch ($bundle) {
      case 'normal_page':
        $drupalPath->getPath(Argument::type('string'), Argument::type('string'))
          ->willReturn('profiles/contrib/degov/modules/degov_node_normal_page');
        break;

      case 'blog':
        $drupalPath->getPath(Argument::type('string'), Argument::type('string'))
          ->willReturn('profiles/contrib/degov/modules/degov_node_blog');
        break;
    }

    return $drupalPath->reveal();
  }

  /**
   * Mock component location.
   */
  private function mockComponentLocation($bundle = 'normal_page') {
    /**
     * @var \Drupal\degov_theming\Facade\ComponentLocation $componentLocation
     */
    $componentLocation = $this->prophesize(ComponentLocation::class);
    $componentLocation->getDrupalPath()->willReturn($this->mockDrupalPath($bundle));
    $componentLocation->getFilesystem()->willReturn($this->mockFilesystem());
    $componentLocation->getLibraryDiscovery()->willReturn($this->mockLibraryDiscovery());

    return $componentLocation->reveal();
  }

  /**
   * Mock ThemeInitialization service.
   *
   * @return \Prophecy\Prophecy\ProphecySubjectInterface|\Drupal\Core\Theme\ThemeInitializationInterface
   */
  private function mockThemeInitialization() {
    $baseTheme = $this->prophesize(ActiveTheme::class);
    $baseTheme->getPath()->willReturn('themes/custom/base_theme/');
    $baseTheme->reveal();
    $themeInitialization = $this->prophesize(ThemeInitializationInterface::class);
    $themeInitialization->getActiveThemeByName(Argument::type('string'))->willReturn($baseTheme);
    return $themeInitialization->reveal();
  }

  /**
   * Mock theme manager.
   *
   * @return \Drupal\Core\Theme\ThemeManagerInterface
   *   Theme manager.
   */
  private function mockThemeManager() {
    $themeManager = $this->prophesize(ThemeManagerInterface::class);

    $activeThemeStub = $this->prophesize(ActiveTheme::class);

    $baseTheme = $this->prophesize(ActiveTheme::class);
    $baseTheme->getPath()->willReturn('themes/custom/base_theme/');
    $baseTheme->getName()->willReturn('base_theme');
    $projectTheme = $this->prophesize(ActiveTheme::class);
    $projectTheme->getBaseThemeExtensions()->willReturn([
      $baseTheme->reveal(),
      $activeThemeStub->reveal(),
    ]);
    $projectTheme->getPath()->willReturn('themes/custom/project_theme/');
    $projectTheme->reveal();

    $themeManager->getActiveTheme()->willReturn($projectTheme);

    return $themeManager->reveal();
  }

  /**
   * Mock library discovery.
   *
   * @return \Drupal\Core\Asset\LibraryDiscoveryInterface
   *   Library discovery.
   */
  private function mockLibraryDiscovery() {
    $libraryDiscovery = $this->prophesize(LibraryDiscoveryInterface::class);
    $libraryDiscovery->getLibraryByName(Argument::type('string'), Argument::type('string'))
      ->willReturn('any.library');

    return $libraryDiscovery->reveal();
  }

  /**
   * Mock filesystem.
   *
   * @return \org\bovigo\vfs\vfsStreamDirectory
   *   Filesystem factory.
   */
  private function mockFilesystem() {
    return vfsStream::setup(NULL, NULL, [
      'profiles' => [
        'contrib' => [
          'degov' => [
            'modules' => [
              'degov_node_blog' => [],
              'degov_node_normal_page' => [
                'templates' => [
                  'node--normal_page--small_image.html.twig' => 'Foo',
                  'node--normal_page--preview.html.twig'     => 'Foo',
                  'node--normal_page--default.html.twig'     => 'Foo',
                  'node--normal_page--full.html.twig'        => 'Foo',
                ],
              ],
              'degov_taxonomies' => [
                'templates' => [
                  'node--normal_page--full.html.twig'        => 'Foo',
                ],
              ],
            ],
          ],
        ],
      ],
      'themes'   => [
        'custom' => [
          'project_theme' => [
            'templates' => [
              'nodes' => [
                'node--normal_page--preview.html.twig' => 'Foo',
                'node--normal_page--default.html.twig' => 'Foo',
              ],
            ],
          ],
          'base_theme'    => [
            'templates' => [
              'node--normal_page--preview.html.twig' => 'Foo',
              'node--normal_page--full.html.twig'    => 'Foo',
            ],
          ],
        ],
      ],
    ]);
  }

  /**
   * Mock twig.
   *
   * @return \Drupal\Core\Template\TwigEnvironment
   *   Twig environment.
   */
  private function mockTwig() {
    $twig = $this->prophesize(TwigEnvironment::class);

    return $twig->reveal();
  }

  /**
   * Get preprocess.
   */
  public function getPreprocess() {
    $out = [];

    $out[] = [
      'hook'    => 'node',
      'info'    => [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--normal_page--default',
        'path'           => 'themes/base-theme/templates',
      ],
      'options' => [
        'module_name'       => 'degov_node_normal_page',
        'entity_type'       => 'node',
        'entity_bundles'    =>
          [
            0 => 'normal_page',
          ],
        'entity_view_modes' =>
          [
            0 => 'full',
            1 => 'long_text',
            2 => 'preview',
            3 => 'slim',
            4 => 'small_image',
          ],
      ],
    ];

    return $out;
  }

  /**
   * Get client  theme preprocess.
   */
  public function getClientThemePreprocess() {
    $out = [];

    $out[] = [
      'hook'    => 'node',
      'info'    => [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--normal_page--default',
        'path'           => 'themes/client-theme/templates',
      ],
      'options' => [
        'module_name'       => 'degov_node_normal_page',
        'entity_type'       => 'node',
        'entity_bundles'    =>
          [
            0 => 'normal_page',
          ],
        'entity_view_modes' =>
          [
            0 => 'full',
            1 => 'long_text',
            2 => 'preview',
            3 => 'slim',
            4 => 'small_image',
          ],
      ],
    ];

    return $out;
  }

  /**
   * Get no template preprocess.
   */
  public function getNoTemplatePreprocess() {
    $out = [];

    $out[] = [
      'hook'    => 'node',
      'info'    => [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--blog--default',
        'path'           => 'themes/client-theme/templates',
      ],
      'options' => [
        'module_name'       => 'degov_node_blog',
        'entity_type'       => 'node',
        'entity_bundles'    =>
          [
            0 => 'blog',
          ],
        'entity_view_modes' =>
          [
            0 => 'full',
            1 => 'long_text',
            2 => 'preview',
            3 => 'slim',
            4 => 'small_image',
          ],
      ],
    ];

    return $out;
  }

  /**
   * Test suggest template from module.
   *
   * @dataProvider getPreprocess()
   */
  public function testSuggestTemplateFromModule($hook, $info, $options) {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('normal_page');

    $variables = [
      'elements' => [
        '#view_mode' => 'small_image',
      ],
      'node'     => $node->reveal(),
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals(
      [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'modules',
        'template'       => 'node--normal_page--small_image',
        'path'           => 'profiles/contrib/degov/modules/degov_node_normal_page/templates',
      ],
      $info
    );
  }

  /**
   * Test suggest template from base theme.
   *
   * @dataProvider getPreprocess()
   */
  public function testSuggestTemplateFromBaseTheme($hook, $info, $options) {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('normal_page');

    $variables = [
      'elements' => [
        '#view_mode' => 'full',
      ],
      'node'     => $node->reveal(),
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals(
      [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--normal_page--full',
        'path'           => 'themes/custom/base_theme/templates',
      ],
      $info
    );
  }

  /**
   * Test suggest template from project theme in preview viewmode.
   *
   * @dataProvider getClientThemePreprocess()
   */
  public function testSuggestTemplateFromProjectThemeInPreviewViewMode($hook, $info, $options) {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('normal_page');

    $variables = [
      'elements' => [
        '#view_mode' => 'preview',
      ],
      'node'     => $node->reveal(),
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals(
      [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--normal_page--preview',
        'path'           => 'themes/custom/project_theme/templates/nodes',
      ],
      $info
    );
  }

  /**
   * Test suggest template from project  theme in default viewmode.
   *
   * @dataProvider getClientThemePreprocess()
   */
  public function testSuggestTemplateFromProjectThemeInDefaultViewMode($hook, $info, $options) {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('normal_page');

    $variables = [
      'elements' => [
        '#view_mode' => 'default',
      ],
      'node'     => $node->reveal(),
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals(
      [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--normal_page--default',
        'path'           => 'themes/custom/project_theme/templates/nodes',
      ],
      $info
    );
  }

  /**
   * Test do not add suggestion if no template is found.
   *
   * @dataProvider getNoTemplatePreprocess()
   */
  public function testDoNotAddSuggestionIfNoTemplateIsFound($hook, $info, $options) {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('blog');

    $variables = [
      'elements' => [
        '#view_mode' => 'long_text',
      ],
      'node' => $node->reveal(),
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals(
      [
        'render element' => 'elements',
        'type'           => 'base_theme',
        'theme path'     => 'themes',
        'template'       => 'node--blog--default',
        'path'           => 'themes/client-theme/templates',
      ],
      $info
    );
  }

  /**
   * Tests that a second or more call of \Drupal\degov_common\Common::addThemeSuggestions do not fallback
   * to default template, because the module do not provide a template for the given viewmode.
   *
   * Story: Drupal wants to render the entity node with bundle normale_page in viewmode small_image
   * - Drupal calls the hook_preprocess of degov_node_normal_page
   * - degov_node_normal_page provides a template for the entity node with bundle normale_page in viewmode small_image
   * - \Drupal\degov_theming\Service\Template::suggest use this template
   * - Drupal calls the hook_preprocess of degov_taxonomies
   * - degov_taxonomies do not provide this template
   * - project_theme has a fallback template for normal_page
   * - \Drupal\degov_theming\Service\Template::suggest do not fallback to default template
   *   because it caches the previously requested template in static cache
   */
  public function testDoNotFallbackToDefault() {

    $node = $this->prophesize(EntityBase::class);
    $node->bundle()->willReturn('normal_page');
    $hook = 'node';

    $variables = [
      'elements' => [
        '#view_mode' => 'small_image',
      ],
      'node'     => $node->reveal(),
    ];
    $info = [
      'render element' => 'elements',
      'type'           => 'project_theme',
      'theme path'     => 'modules',
      'template'       => 'node--normal_page--small_image',
      'path'           => 'profiles/contrib/degov/modules/degov_node_normal_page/templates',
    ];
    $options = [
      'module_name'       => 'degov_node_normal_page',
      'entity_type'       => 'node',
      'entity_bundles'    =>
        [
          0 => 'normal_page',
        ],
      'entity_view_modes' =>
        [
          0 => 'full',
          1 => 'long_text',
          2 => 'preview',
          3 => 'slim',
          4 => 'small_image',
        ],
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $expected = [
      'render element' => 'elements',
      'type'           => 'project_theme',
      'theme path'     => 'modules',
      'template'       => 'node--normal_page--small_image',
      'path'           => 'profiles/contrib/degov/modules/degov_node_normal_page/templates',
    ];

    $this->assertArrayEquals($expected, $info);

    $options = [
      'module_name'       => 'degov_taxonomies',
      'entity_type'       => 'node',
      'entity_bundles'    =>
        [
          0 => 'normal_page',
        ],
      'entity_view_modes' =>
        [
          0 => 'full',
        ],
    ];

    $this->template->suggest($variables, $hook, $info, $options);

    $this->assertArrayEquals($expected, $info);

  }

}
