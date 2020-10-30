<?php

namespace Drupal\degov_theme\Preprocess;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ViewsExposedFormSearchPageFulltext.
 */
class ViewsExposedFormSearchPageFulltext {

  use StringTranslationTrait;

  /**
   * Preprocess.
   */
  public function preprocess(array &$vars, $hook): void {
    if ($hook == 'views_exposed_form') {
      if (substr_count($vars['form']['#id'], 'views-exposed-form-search-content') === 1) {
        if (isset($vars['form']['volltext'])) {
          $vars['form']['volltext']['#attributes']['placeholder'] = $this->t('Enter a search term...');
        }
      }
    }

    if (!in_array($hook, ['views_exposed_form__search_content', 'views_exposed_form__search_media'])) {
      return;
    }
    $vars['form']['volltext']['#title_display'] = 'invisible';
    $vars['form']['volltext']['#attributes']['placeholder'] = $this->t('Search term');
    $vars['form']['volltext']['#field_suffix'] = '<i class="fas fa-search d-none d-lg-inline-block d-xl-inline-block d-md-none"></i>';

    $classes = [
      'px-3',
      'mr-md-3',
    ];
    array_walk($classes, function ($class) use (&$vars) {
      $vars['form']['volltext']['#wrapper_attributes']['class'][] = $class;
    });

    $classes = [
      'btn',
      'btn-primary',
      'px-3',
      'px-lg-5',
    ];
    array_walk($classes, function ($class) use (&$vars) {
      $vars["form"]["actions"]['submit']['#attributes']['class'][] = $class;
    });
  }

}
