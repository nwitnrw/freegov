<?php

declare(strict_types=1);

namespace Drupal\styled_google_map\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'styled_google_map_default' formatter.
 *
 * @FieldFormatter(
 *   id = "styled_google_map_default",
 *   label = @Translation("Styled Google Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class StyledGoogleMapDefaultFormatter extends FormatterBase {

  /**
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   */
  public function setLinkGenerator(LinkGeneratorInterface $link_generator): void {
    $this->linkGenerator = $link_generator;
  }

  /**
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   */
  public function setEntityDisplayRepository(EntityDisplayRepositoryInterface $entity_display_repository): void {
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setLinkGenerator($container->get('link_generator'));
    $instance->setEntityDisplayRepository($container->get('entity_display.repository'));

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => STYLED_GOOGLE_MAP_DEFAULT_WIDTH,
      'height' => STYLED_GOOGLE_MAP_DEFAULT_HEIGHT,
      'gestureHandling' => STYLED_GOOGLE_MAP_DEFAULT_GESTURE,
      'style' => [
        'maptype' => STYLED_GOOGLE_MAP_DEFAULT_MAP_TYPE,
        'style' => STYLED_GOOGLE_MAP_DEFAULT_STYLE,
        'pin' => '',
      ],
      'map_center' => [
        'center_coordinates' => NULL,
      ],
      'popup' => [
        'choice' => NULL,
        'text' => NULL,
        'view_mode' => NULL,
        'label' => STYLED_GOOGLE_MAP_DEFAULT_LABEL,
        'shadow_style' => STYLED_GOOGLE_MAP_DEFAULT_SHADOW_STYLE,
        'padding' => STYLED_GOOGLE_MAP_DEFAULT_PADDING,
        'border_radius' => STYLED_GOOGLE_MAP_DEFAULT_BORDER_RADIUS,
        'border_width' => STYLED_GOOGLE_MAP_DEFAULT_BORDER_WIDTH,
        'border_color' => STYLED_GOOGLE_MAP_DEFAULT_BORDER_COLOR,
        'background_color' => STYLED_GOOGLE_MAP_DEFAULT_BACKGROUND_COLOR,
        'min_width' => STYLED_GOOGLE_MAP_DEFAULT_MIN_WIDTH,
        'max_width' => STYLED_GOOGLE_MAP_DEFAULT_MAX_WIDTH,
        'min_height' => STYLED_GOOGLE_MAP_DEFAULT_MIN_HEIGHT,
        'max_height' => STYLED_GOOGLE_MAP_DEFAULT_MAX_HEIGHT,
        'arrow_style' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_STYLE,
        'arrow_size' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_SIZE,
        'arrow_position' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_POSITION,
        'disable_auto_pan' => STYLED_GOOGLE_MAP_DEFAULT_DISABLE_AUTO_PAN,
        'hide_close_button' => STYLED_GOOGLE_MAP_DEFAULT_HIDE_CLOSE_BUTTON,
        'disable_animation' => STYLED_GOOGLE_MAP_DEFAULT_DISABLE_ANIMATION,
        'classes' => [
          'content_container' => STYLED_GOOGLE_MAP_DEFAULT_CONTENT_CONTAINER_CLASS,
          'background' => STYLED_GOOGLE_MAP_DEFAULT_BACKGROUND_CLASS,
          'arrow' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_CLASS,
          'arrow_outer' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_OUTER_CLASS,
          'arrow_inner' => STYLED_GOOGLE_MAP_DEFAULT_ARROW_INNER_CLASS,
        ],
      ],
      'zoom' => [
        'default' => STYLED_GOOGLE_MAP_DEFAULT_ZOOM,
        'max' => STYLED_GOOGLE_MAP_DEFAULT_MAX_ZOOM,
        'min' => STYLED_GOOGLE_MAP_DEFAULT_MIN_ZOOM,
      ],
      'maptypecontrol' => STYLED_GOOGLE_MAP_DEFAULT_MAP_TYPE_CONTROL,
      'scalecontrol' => STYLED_GOOGLE_MAP_DEFAULT_SCALE_CONTROL,
      'rotatecontrol' => STYLED_GOOGLE_MAP_DEFAULT_ROTATE_CONTROL,
      'draggable' => STYLED_GOOGLE_MAP_DEFAULT_DRAGGABLE,
      'mobile_draggable' => STYLED_GOOGLE_MAP_DEFAULT_MOBILE_DRAGGABLE,
      'zoomcontrol' => STYLED_GOOGLE_MAP_DEFAULT_ZOOM_CONTROL,
      'streetviewcontrol' => STYLED_GOOGLE_MAP_DEFAULT_STREET_VIEW_CONTROL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $default_settings = self::defaultSettings();
    // Set all available setting fields for the Styled Google Map.
    $elements['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width size'),
      '#default_value' => $this->getSetting('width'),
      '#description' => $this->t('Map width written in pixels or percentage'),
      '#required' => TRUE,
    ];
    $elements['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height size'),
      '#default_value' => $this->getSetting('height'),
      '#description' => $this->t('Map height written in pixels or percentage'),
      '#required' => TRUE,
    ];
    $elements['gestureHandling'] = [
      '#type' => 'select',
      '#title' => $this->t('Gesture handling'),
      '#description' => $this->t('This setting controls how the API handles gestures on the map. See more <a href="@href">here</a>',
        [
          '@href' => 'https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions.gestureHandling',
        ]
      ),
      '#options' => [
        'cooperative' => $this->t('Scroll events with a ctrl key or ⌘ key pressed zoom the map.'),
        'greedy' => $this->t('All touch gestures and scroll events pan or zoom the map.'),
        'none' => $this->t('The map cannot be panned or zoomed by user gestures.'),
        'auto' => $this->t('(default) Gesture handling is either cooperative or greedy'),
      ],
      '#default_value' => $this->getSetting('gestureHandling'),
    ];
    $elements['style'] = [
      '#type' => 'details',
      '#title' => $this->t('Map style'),
    ];
    $style_settings = $this->getSetting('style');
    $elements['style']['maptype'] = [
      '#type' => 'select',
      '#options' => [
        'ROADMAP' => $this->t('ROADMAP'),
        'SATELLITE' => $this->t('SATELLITE'),
        'HYBRID' => $this->t('HYBRID'),
        'TERRAIN' => $this->t('TERRAIN'),
      ],
      '#title' => $this->t('Map type'),
      '#default_value' => empty($style_settings['maptype']) ? $default_settings['style']['maptype'] : $style_settings['maptype'],
      '#required' => TRUE,
    ];
    $elements['style']['style'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JSON Style'),
      '#default_value' => empty($style_settings['style']) ? $default_settings['style']['style'] : $style_settings['style'],
      '#description' => $this->t('Check out !url for custom styles. Also check out this !project to style and edit Google Map JSON styles.', [
        '!url' => $this->linkGenerator->generate($this->t('Snazzy maps'), Url::fromUri('https://snazzymaps.com/')),
        '!project' => $this->linkGenerator->generate($this->t('Github page'), Url::fromUri('https://instrument.github.io/styled-maps-wizard/')),
      ]
      ),
    ];
    $elements['style']['pin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL to the marker'),
      '#default_value' => empty($style_settings['pin']) ? $default_settings['style']['pin'] : $style_settings['pin'],
      '#description' => $this->t('URL to the marker image. You can use a !wrapper for the url. Ex. !example (not working until !fixed)',
        [
          '!wrapper' => $this->linkGenerator->generate($this->t('Stream wrapper'), Url::fromUri('https://drupal.org/project/system_stream_wrapper')),
          '!example' => STYLED_GOOGLE_MAP_DEFAULT_PIN,
          '!fixed' => $this->linkGenerator->generate('https://www.drupal.org/node/1308152', Url::fromUri('https://www.drupal.org/node/1308152')),
        ]
      ),
    ];
    $map_center_settings = $this->getSetting('map_center');
    $elements['map_center'] = [
      '#type' => 'details',
      '#title' => $this->t('Centering map'),
    ];
    // Retrieve all field names from the current entity bundle.
    $field_options = [];
    $center_options = ['' => $this->t('Center automatically')];
    $fields = $form['#fields'];
    foreach ($fields as $field) {
      $config = FieldConfig::loadByName($form['#entity_type'], $form['#bundle'], $field);
      if (!$config) {
        continue;
      }
      $type = $config->get('field_type');
      $name = $config->get('field_name');
      $field_options[$field] = $config->getLabel();
      if ($type === 'geofield' && $this->fieldDefinition->get('field_name') != $name) {
        $center_options[$field] = $config->getLabel();
      }
    }

    $elements['map_center']['center_coordinates'] = [
      '#type' => 'select',
      '#options' => $center_options,
      '#default_value' => empty($map_center_settings['center_coordinates']) ? $default_settings['map_center']['center_coordinates'] : $map_center_settings['center_coordinates'],
      '#description' => $this->t('To have map centered on other point than location you need to have another GeoField in your content type structure'),
    ];
    $elements['popup'] = [
      '#type' => 'details',
      '#title' => $this->t('Marker popup'),
    ];
    $popup_settings = $this->getSetting('popup');
    $elements['popup']['choice'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('None'),
        1 => $this->t('Field'),
        2 => $this->t('View mode'),
      ],
      '#default_value' => empty($popup_settings['choice']) ? $default_settings['popup']['choice'] : $popup_settings['choice'],
      '#id' => 'edit-popup-choice-field',
    ];
    // Retrieve view mode settings from the current entity bundle.
    $view_modes = $this->entityDisplayRepository->getViewModeOptions($form['#entity_type']);
    $elements['popup']['view_mode'] = [
      '#type' => 'select',
      '#options' => $view_modes,
      '#default_value' => empty($popup_settings['view_mode']) ? $default_settings['popup']['view_mode'] : $popup_settings['view_mode'],
      '#states' => [
        'visible' => [
          ':input[id="edit-popup-choice-field"]' => ['value' => 2],
        ],
      ],
    ];

    $elements['popup']['text'] = [
      '#type' => 'select',
      '#options' => $field_options,
      '#default_value' => empty($popup_settings['text']) ? $default_settings['popup']['text'] : $popup_settings['text'],
      '#states' => [
        'visible' => [
          ':input[id="edit-popup-choice-field"]' => ['value' => 1],
        ],
      ],
    ];
    $elements['popup']['label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show field label'),
      '#default_value' => empty($popup_settings['label']) ? $default_settings['popup']['label'] : $popup_settings['label'],
      '#states' => [
        'visible' => [
          ':input[id="edit-popup-choice-field"]' => ['value' => 1],
        ],
      ],
    ];
    $elements['popup']['shadow_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Shadow style'),
      '#options' => [0, 1, 2],
      '#description' => $this->t('1: shadow behind, 2: shadow below, 0: no shadow'),
      '#default_value' => empty($popup_settings['shadow_style']) ? $default_settings['popup']['shadow_style'] : $popup_settings['shadow_style'],
    ];
    $elements['popup']['padding'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding'),
      '#field_suffix' => 'px',
      '#default_value' => empty($popup_settings['padding']) ? $default_settings['popup']['padding'] : $popup_settings['padding'],
    ];
    $elements['popup']['border_radius'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Border radius'),
      '#field_suffix' => 'px',
      '#default_value' => empty($popup_settings['border_radius']) ? $default_settings['popup']['border_radius'] : $popup_settings['border_radius'],
    ];
    $elements['popup']['border_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Border width'),
      '#field_suffix' => 'px',
      '#default_value' => empty($popup_settings['border_width']) ? $default_settings['popup']['border_width'] : $popup_settings['border_width'],
    ];
    $elements['popup']['border_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Border color'),
      '#field_suffix' => '#hex',
      '#default_value' => empty($popup_settings['border_color']) ? $default_settings['popup']['border_color'] : $popup_settings['border_color'],
    ];
    $elements['popup']['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background color'),
      '#field_suffix' => '#hex',
      '#default_value' => empty($popup_settings['background_color']) ? $default_settings['popup']['background_color'] : $popup_settings['background_color'],
    ];
    $elements['popup']['min_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Min width'),
      '#field_suffix' => 'px (or auto)',
      '#default_value' => empty($popup_settings['min_width']) ? $default_settings['popup']['min_width'] : $popup_settings['min_width'],
    ];
    $elements['popup']['max_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max width'),
      '#field_suffix' => 'px (or auto)',
      '#default_value' => empty($popup_settings['max_width']) ? $default_settings['popup']['max_width'] : $popup_settings['max_width'],
    ];
    $elements['popup']['min_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Min height'),
      '#field_suffix' => 'px (or auto)',
      '#default_value' => empty($popup_settings['min_height']) ? $default_settings['popup']['min_height'] : $popup_settings['min_height'],
    ];
    $elements['popup']['max_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max height'),
      '#field_suffix' => 'px (or auto)',
      '#default_value' => empty($popup_settings['max_height']) ? $default_settings['popup']['max_height'] : $popup_settings['max_height'],
    ];
    $elements['popup']['arrow_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Arrow style'),
      '#options' => [0, 1, 2],
      '#description' => $this->t('1: left side visible, 2: right side visible, 0: both sides visible'),
      '#default_value' => empty($popup_settings['arrow_style']) ? $default_settings['popup']['arrow_style'] : $popup_settings['arrow_style'],
    ];
    $elements['popup']['arrow_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arrow size'),
      '#field_suffix' => 'px',
      '#default_value' => empty($popup_settings['arrow_size']) ? $default_settings['popup']['arrow_size'] : $popup_settings['arrow_size'],
    ];
    $elements['popup']['arrow_position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arrow position'),
      '#field_suffix' => 'px',
      '#default_value' => empty($popup_settings['arrow_position']) ? $default_settings['popup']['arrow_position'] : $popup_settings['arrow_position'],
    ];
    $elements['popup']['disable_auto_pan'] = [
      '#type' => 'select',
      '#title' => $this->t('Auto pan'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#description' => $this->t('Automatically center the pin on click'),
      '#default_value' => empty($popup_settings['disable_auto_pan']) ? $default_settings['popup']['disable_auto_pan'] : $popup_settings['disable_auto_pan'],
    ];
    $elements['popup']['hide_close_button'] = [
      '#type' => 'select',
      '#title' => $this->t('Hide close button'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#description' => $this->t('Hide the popup close button'),
      '#default_value' => empty($popup_settings['hide_close_button']) ? $default_settings['popup']['hide_close_button'] : $popup_settings['hide_close_button'],
    ];
    $elements['popup']['disable_animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Disable animation'),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#description' => $this->t('Disables the popup animation'),
      '#default_value' => empty($popup_settings['disable_animation']) ? $default_settings['popup']['disable_animation'] : $popup_settings['disable_animation'],
    ];
    $popup_classes_settings = !empty($popup_settings['classes']) ? $popup_settings['classes'] : $default_settings['popup']['classes'];
    $elements['popup']['classes']['content_container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper class'),
      '#default_value' => empty($popup_classes_settings['content_container']) ? $default_settings['popup']['classes']['content_container'] : $popup_classes_settings['content_container'],
    ];
    $elements['popup']['classes']['background'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background class'),
      '#default_value' => empty($popup_classes_settings['background']) ? $default_settings['popup']['classes']['background'] : $popup_classes_settings['background'],
    ];
    $elements['popup']['classes']['arrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arrow class'),
      '#default_value' => empty($popup_classes_settings['arrow']) ? $default_settings['popup']['classes']['arrow'] : $popup_classes_settings['arrow'],
    ];
    $elements['popup']['classes']['arrow_outer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arrow outer class'),
      '#default_value' => empty($popup_classes_settings['arrow_outer']) ? $default_settings['popup']['classes']['arrow_outer'] : $popup_classes_settings['arrow_outer'],
    ];
    $elements['popup']['classes']['arrow_inner'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arrow inner class'),
      '#default_value' => empty($popup_classes_settings['arrow_inner']) ? $default_settings['popup']['classes']['arrow_inner'] : $popup_classes_settings['arrow_inner'],
    ];
    $elements['zoom'] = [
      '#type' => 'details',
      '#title' => $this->t('Zoom'),
    ];
    $zoom_settings = $this->getSetting('zoom');
    $elements['zoom']['default'] = [
      '#type' => 'select',
      '#options' => range(1, 23),
      '#title' => $this->t('Default zoom level'),
      '#default_value' => empty($zoom_settings['default']) ? $default_settings['zoom']['default'] : $zoom_settings['default'],
      '#description' => $this->t('Should be between the Min and Max zoom level.'),
      '#required' => TRUE,
    ];
    $elements['zoom']['max'] = [
      '#type' => 'select',
      '#options' => range(1, 23),
      '#title' => $this->t('Max zoom level'),
      '#default_value' => empty($zoom_settings['max']) ? $default_settings['zoom']['max'] : $zoom_settings['max'],
      '#description' => $this->t('Should be greater then the Min zoom level.'),
      '#required' => TRUE,
    ];
    $elements['zoom']['min'] = [
      '#type' => 'select',
      '#options' => range(1, 23),
      '#title' => $this->t('Min zoom level'),
      '#default_value' => empty($zoom_settings['min']) ? $default_settings['zoom']['min'] : $zoom_settings['min'],
      '#description' => $this->t('Should be smaller then the Max zoom level.'),
      '#required' => TRUE,
    ];
    $elements['maptypecontrol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Map Type control'),
      '#default_value' => $this->getSetting('maptypecontrol'),
    ];
    $elements['scalecontrol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable scale control'),
      '#default_value' => $this->getSetting('scalecontrol'),
    ];
    $elements['rotatecontrol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable rotate control'),
      '#default_value' => $this->getSetting('rotatecontrol'),
    ];
    $elements['draggable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable dragging'),
      '#default_value' => $this->getSetting('draggable'),
    ];
    $elements['mobile_draggable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable mobile dragging'),
      '#description' => $this->t('Sometimes when the map covers big part of touch device screen draggable feature can cause inability to scroll the page'),
      '#default_value' => $this->getSetting('mobile_draggable'),
    ];
    $elements['streetviewcontrol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable street view control'),
      '#default_value' => $this->getSetting('streetviewcontrol'),
    ];
    $elements['zoomcontrol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable zoom control'),
      '#default_value' => $this->getSetting('zoomcontrol'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Width: <b>%width</b><br />Height: <b>%height</b>',
      ['%width' => $this->getSetting('width'), '%height' => $this->getSetting('height')]);
    $style_settings = $this->getSetting('style');
    if ($style_settings['style']) {
      $summary[] = $this->t('<br />Map style: <b>Custom</b>');
    }
    if ($style_settings['pin']) {
      $summary[] = $this->t('<br />Pin style: <b>%pin</b>', ['%pin' => $style_settings['pin']]);
    }
    $summary[] = $this->t('<br />Map type: <b>%maptype</b>', ['%maptype' => $style_settings['maptype']]);
    if ($style_settings['pin']) {
      $summary[] = $this->t('<br />Pin location: <b>%pin</b>', ['%pin' => $style_settings['pin']]);
    }
    $popup_settings = $this->getSetting('popup');
    if ($popup_settings['choice'] == 1) {
      $summary[] = $this->t('<br />Popup shows field <b>%field</b>', ['%field' => $popup_settings['text']]);
      $readable = [FALSE => $this->t('without'), TRUE => $this->t('with')];
      $summary[] = $this->t('<b>%label</b> label', ['%label' => $readable[$popup_settings['label']]]);
    }
    if ($popup_settings['choice'] == 2) {
      $summary[] = $this->t('<br />Popup shows view mode <b>%viewmode</b>', ['%viewmode' => $popup_settings['view_mode']]);
    }
    $zoom_settings = $this->getSetting('zoom');
    $summary[] = $this->t('<br />Default zoom: <b>%zoom</b>', ['%zoom' => $zoom_settings['default']]);
    $summary[] = $this->t('<br />Maximum zoom: <b>%maxzoom</b>', ['%maxzoom' => $zoom_settings['max']]);
    $summary[] = $this->t('<br />Minimum zoom: <b>%minzoom</b>', ['%minzoom' => $zoom_settings['min']]);
    $gesture_handling = $this->getSetting('gestureHandling');
    $summary[] = $this->t('<br />Gesture handling:<b>%mode</b>', ['%mode' => $gesture_handling]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => theme_styled_google_map(['location' => $item, 'settings' => $this->getSettings()]),
      ];
    }
    return $elements;
  }

}
