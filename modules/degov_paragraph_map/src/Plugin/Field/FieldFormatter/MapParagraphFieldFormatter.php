<?php

namespace Drupal\degov_paragraph_map\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\styled_google_map\Plugin\Field\FieldFormatter\StyledGoogleMapDefaultFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldFormatter(
 *   id = "map_paragraph_field_formatter",
 *   label = @Translation("Map paragraph field formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MapParagraphFieldFormatter extends StyledGoogleMapDefaultFormatter {

  /**
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twigEnvironment;

  /**
   * @param \Drupal\Core\Template\TwigEnvironment $twigEnvironment
   */
  public function setTwigEnvironment(TwigEnvironment $twigEnvironment): void {
    $this->twigEnvironment = $twigEnvironment;
  }

  /**
   * @var int
   */
  protected static $elementCounter = 0;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setTwigEnvironment($container->get('twig'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];

    /**
     * @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $item
     */
    foreach ($items as $item) {
      $addressEntity = $item->getEntity();
      $viewMode = $addressEntity->get('field_map_address_view_mode')->getString();
      $mapId = self::$elementCounter;

      if ($viewMode === 'osm_map') {
        $elementConfig['#attached']['drupalSettings']['degov_media_address'] = NULL;
      }

      foreach ($addressEntity->get('field_map_address_reference')->referencedEntities() as $referencedEntity) {
        if ($viewMode === 'default_map') {
          $viewMode = 'google_map';
        }

        if ($viewMode === 'osm_map') {
          $this->addMarkerToOsm($elementConfig, $mapId, $referencedEntity);
        }

      }

    }

    if ($viewMode === 'google_map') {
      $referencedAddressMediaEntities = $addressEntity->get('field_map_address_reference')->referencedEntities();
      $geofields = $this->collectGeofieldsForGoogleMap($referencedAddressMediaEntities);
      $elements[] = $this->composeElementConfigForGoogleMap($geofields, $referencedAddressMediaEntities);
    }

    if ($viewMode === 'osm_map') {
      $elements[] = $this->composeElementConfigForOsm($elementConfig);
    }

    self::$elementCounter++;

    return $elements;
  }

  private function addMarkerToOsm(array &$elementConfig, int $mapId, FieldableEntityInterface $addressEntity): void {
    $location = $addressEntity->get('field_address_location');

    $marker = [
      'address' => degov_media_address_get_js_fields($addressEntity),
      'lat'     => $location->lat,
      'lon'     => $location->lon,
      'pin'     => '/' . drupal_get_path('module', 'degov_media_address') . '/images/map-icon.png',
      'type'    => 'leaflet',
    ];

    $elementConfig['#attached']['drupalSettings']['degov_media_address'][$mapId][] = $marker;
  }

  private function composeElementConfigForOsm(array $elementConfig): array {
    $elementConfig['#markup'] = $this->twigEnvironment->render(
      drupal_get_path('module', 'degov_media_address') . '/templates/media--address--osm_map.html.twig',
      [
        'map_id' => self::$elementCounter
      ]
    );
    $elementConfig['#attached']['library'][] = 'degov_paragraph_map/osm_map';

    return $elementConfig;
  }

  private function composeElementConfigForGoogleMap(array $geofields, array $addressEntities): array {
    $elementConfig['#markup'] = theme_styled_google_map([
      'locations'       => $geofields,
      'addressEntities' => $addressEntities,
      'settings'        => $this->getSettings(),
    ]);
    $elementConfig['#attached']['library'][] = 'styled_google_map/styled-google-map';

    return $elementConfig;
  }

  private function collectGeofieldsForGoogleMap(array $medias): array {
    $values = [];
    foreach ($medias as $media) {
      $values[] = $media->get('field_address_location');
    }

    return $values;
  }

}
