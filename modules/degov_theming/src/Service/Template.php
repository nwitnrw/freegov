<?php

declare(strict_types=1);

namespace Drupal\degov_theming\Service;

use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\degov_theming\Facade\ComponentLocation;

/**
 * Class Template.
 */
class Template {

  /**
   * Theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * Library discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  private $libraryDiscovery;

  /**
   * Drupal path.
   *
   * @var DrupalPath
   */
  private $drupalPath;

  /**
   * File system.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $filesystem;

  /**
   * Twig.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  private $twig;

  /**
   * @var \Drupal\Core\Theme\ThemeInitializationInterface
   */
  private $themeInitialization;

  /**
   * Template constructor.
   */
  public function __construct(
    ThemeManagerInterface $themeManager,
  ComponentLocation $componentLocation,
  TwigEnvironment $twig,
    ThemeInitializationInterface $theme_initialization
  ) {
    $this->themeManager = $themeManager;
    $this->libraryDiscovery = $componentLocation->getLibraryDiscovery();
    $this->filesystem = $componentLocation->getFilesystem();
    $this->drupalPath = $componentLocation->getDrupalPath();
    $this->twig = $twig;
    $this->themeInitialization = $theme_initialization;
  }

  /**
   * Get inherited theme.
   */
  private function getInheritedTheme(): ActiveTheme {
    $activeTheme = $this->themeManager->getActiveTheme();
    $baseThemes = $activeTheme->getBaseThemeExtensions();
    $base_theme = reset($baseThemes);
    return $this->themeInitialization->getActiveThemeByName($base_theme->getName());
  }

  /**
   * Suggest.
   */
  public function suggest(array &$variables, $hook, array &$info, array $options) {

    $entity_type = $options['entity_type'];
    $entity_bundles = $options['entity_bundles'];
    $module_name = $options['module_name'];
    $entity_view_modes = $options['entity_view_modes'];

    $add_suggestion = FALSE;

    if ($hook === $entity_type) {
      // Add module overwritten template suggestions for only the entity
      // bundles that are defined.
      if ($entity_bundles) {
        if ($hook === 'media') {
          $entity = $variables['elements']['#media'];
        }
        elseif ($hook === 'taxonomy_term') {
          $entity = $variables['term'];
        }
        else {
          $entity = $variables[$entity_type];
        }
        $entity_bundle = $entity->bundle();
        // Overwrite the core/contrib template with our module template
        // in case no custom theme has overwritten the template.
        if (\in_array($entity_bundle, $entity_bundles, TRUE)) {
          $add_suggestion = TRUE;
        }
      }
      else {
        // In case no entity bundles are defined, we still include the
        // default template override.
        $add_suggestion = TRUE;
      }
    }

    if ($add_suggestion) {
      // substr($info['theme path'], 0, 14);.
      $template_path = $info['theme path'];
      $path_to_active_theme = $this->themeManager->getActiveTheme()->getPath();

      if (strpos($template_path, 'profiles/contrib') === 0 ||
        strpos($template_path, 'themes/contrib') === 0 ||
        (strpos($template_path, $path_to_active_theme) === FALSE && strpos($template_path, $this->getInheritedTheme()->getPath()) === FALSE)) {
        [$variables, $template_filename] = $this->computeTemplateFilename($variables, $entity_view_modes, $entity_type, $entity_bundle ?? NULL);
        // Does the template exist in the active theme?
        $theme_templates_dirname = $this->buildPath($path_to_active_theme, 'templates');
        $template_found = $this->addTemplateToArrayIfFileIsFound($info, 'themes', $template_filename, $theme_templates_dirname);
        if (!$template_found) {
          // no? does the template exist in a base theme?
          $base_themes = $this->themeManager->getActiveTheme()->getBaseThemeExtensions();
          foreach ($base_themes as $base_theme) {
            if ($base_theme->getPath() !== NULL) {
              $theme_templates_dirname = $this->buildPath($base_theme->getPath(), 'templates');
              if ($this->addTemplateToArrayIfFileIsFound($info, 'themes', $template_filename, $theme_templates_dirname)) {
                $template_found = TRUE;
                break;
              }
            }
          }
        }
        if (!$template_found) {
          // no? does the template exist in a module?
          $module_path = $this->drupalPath->getPath('module', $module_name);
          if ($module_path) {
            $module_templates_dirname = $this->buildPath($module_path, 'templates');
            $this->addTemplateToArrayIfFileIsFound($info, "modules", $template_filename, $module_templates_dirname);
          }
        }
      }
    }
    // Include defined entity bundle libraries.
    if (isset($entity_bundle)) {
      $library = $this->libraryDiscovery->getLibraryByName($module_name, $entity_bundle);
      if ($library) {
        $variables['#attached']['library'][] = $module_name . '/' . $entity_bundle;
      }
    }
  }

  /**
   * Build path.
   */
  private function buildPath(string $base, string $directory): string {
    if (!preg_match("/\/$/", $base)) {
      $base .= '/';
    }
    return $base . $directory;
  }

  /**
   * Add template to array if file is found.
   */
  private function addTemplateToArrayIfFileIsFound(array &$original_array, string $theme_path, string $template_filename, string $directory_name): bool {
    $template_filename_with_suffix = $template_filename . '.html.twig';
    if ($directory_iterator = $this->getFileSystemIteratorForDirectory($directory_name)) {
      foreach ($directory_iterator as $file_in_directory) {
        if ($file_in_directory->getFilename() === $template_filename_with_suffix) {
          $original_array = array_merge($original_array, [
            'template'   => $template_filename,
            'theme path' => $theme_path,
            'path'       => $this->getDirnameWithoutVfsProtocol($file_in_directory->getPathName()),
          ]);
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Get directory name without vfs protocol.
   */
  private function getDirnameWithoutVfsProtocol(string $path): string {
    $path = dirname($path);
    if (preg_match('/^vfs:\/\/\//', $path)) {
      $path = str_replace('vfs:///', '', $path);
    }
    return $path;
  }

  /**
   * Get filesystem interator for directory.
   */
  private function getFileSystemIteratorForDirectory(string $directory_name) {
    $directory_path = $directory_name;
    if (preg_match('/vfsStreamDirectory$/', get_class($this->filesystem))) {
      $directory_path = $this->filesystem->url() . '/' . $directory_name;
    }
    if (file_exists($directory_path) && is_dir($directory_path)) {
      return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory_path));
    }
    return NULL;
  }

  /**
   * Compute template filename.
   */
  private function computeTemplateFilename(array &$variables, $entity_view_modes, $entity_type, $entity_bundle): array {

    /*
     * A second call of \Drupal\degov_common\Common::addThemeSuggestions in hook_preprocess could override a working template
     * therefore we store here which non default template was processed.
     */
    static $processed;

    if (isset($variables['elements']['#view_mode']) && in_array($variables['elements']['#view_mode'],
        $entity_view_modes)) {
      $template_filename = $entity_type . '--' . $entity_bundle . '--' . $variables['elements']['#view_mode'];
      $processed[$template_filename] = TRUE;
    }
    else {
      if (isset($variables['elements']['#view_mode']) && $entity_bundle !== NULL) {
        $template_filename = $entity_type . '--' . $entity_bundle . '--' . $variables['elements']['#view_mode'];
        if (isset($processed[$template_filename])) {
          return [
            $variables,
            $template_filename,
          ];
        }
      }

      if (isset($entity_bundle)) {
        $template_filename = $entity_type . '--' . $entity_bundle . '--default';
      }
      else {
        $template_filename = $entity_type . '--default';
      }
    }
    return [
      $variables,
      $template_filename,
    ];
  }

  /**
   * Render.
   */
  public function render(string $module, string $templatePath, array $variables = []) {
    $path = $this->drupalPath->getPath('module', $module) . '/' . $templatePath;
    $twigTemplate = $this->twig->load($path);

    return $twigTemplate->render($variables);
  }

}
