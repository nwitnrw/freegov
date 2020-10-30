<?php

namespace Drupal\degov_search_content\Plugin\facets\widget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\query_type\SearchApiDate;
use Drupal\facets\Result\Result;
use Drupal\facets\Widget\WidgetPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The date range picker widget.
 *
 * @FacetsWidget(
 *   id = "degov_date_range_picker",
 *   label = @Translation("Date Range Picker"),
 *   description = @Translation("A configurable widget that shows a date range picker."),
 * )
 */
final class DateRangePicker extends WidgetPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFatory
   */
  public function setConfigFactory(ConfigFactoryInterface $config_factory): void {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setConfigFactory($container->get('config.factory'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;
    // Try to load some results.
    $items = array_map(function (Result $result) {
      if (empty($result->getUrl())) {
        return $this->buildResultItem($result);
      }

      return $this->buildListItems($this->facet, $result);
    }, $facet->getResults());

    $startDate = '';
    $endDate = '';
    // Get the active items if present to get start and end date of the filter.
    $activeItems = $this->facet->getActiveItems();
    if ($activeItems) {
      foreach ($activeItems as $value) {
        $value = trim($value, '[]');
        $value = explode(' TO ', $value);
        if (count($value) != 2) {
          continue;
        }
        $startDate = ($value[0] !== '*') ? $value[0] : '';
        if (!strtotime($startDate)) {
          $startDate = '';
        }
        $endDate = ($value[1] !== '*') ? $value[1] : '';
        if (!strtotime($endDate)) {
          $endDate = '';
        }
      }
    }

    // Get the active url of the current search.
    $facetUrl = '';
    if (!empty($items)) {
      foreach ($items as $item) {
        /** @var \Drupal\Core\Url $url */
        $url = $item['#url'];
        $options = $url->getOptions();
        foreach ($options['query']['f'] as $key => $option) {
          if (strpos($option, $this->facet->getUrlAlias()) !== FALSE) {
            unset($options['query']['f'][$key]);
          }
        }
        $options['query']['f'][] = $this->facet->getUrlAlias() . ':[date_min TO date_max]';
        $url->setOptions($options);
        $facetUrl = $url->toString();
      }
    }

    $timezone = $this->configFactory->get('system.date')->get('timezone.default');
    // Prepare the render array for date filters.
    $form = [];
    $form['date_filter_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Date filters'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['date-filter-wrapper']],
    ];
    $form['date_filter_wrapper']['date_from'] = [
      '#type' => 'date',
      '#date_date_format' => 'd.m.Y',
      '#date_date_element' => 'text',
      '#date_time_element' => 'none',
      '#date_increment' => 1,
      '#date_time_format' => '',
      '#date_timezone' => $timezone,
      '#date_year_range' => '-10:+3',
      '#default_value' => (!empty($startDate)) ? DrupalDateTime::createFromTimestamp(strtotime($startDate)) : '',
      '#wrapper_attributes' => [
        'class' => ['date-from-wrapper'],
      ],
      // Set the attributes to get the datepicker.
      '#attributes' => [
        'data-drupal-date-format' => 'd.m.Y',
        'class' => ['date-from'],
        'placeholder' => $this->t('dd.mm.yy'),
        'aria-label' => $this->t('From date'),
      ],
    ];
    $form['date_filter_wrapper']['date_to'] = [
      '#type' => 'date',
      '#date_date_format' => 'd.m.Y',
      '#date_date_element' => 'text',
      '#date_time_element' => 'none',
      '#date_increment' => 1,
      '#date_time_format' => '',
      '#date_timezone' => $timezone,
      '#date_year_range' => '-10:+3',
      '#default_value' => (!empty($endDate)) ? DrupalDateTime::createFromTimestamp(strtotime($endDate)) : '',
      '#wrapper_attributes' => [
        'class' => ['date-to-wrapper'],
      ],
      // Set the attributes to get the datepicker.
      '#attributes' => [
        'data-drupal-date-format' => 'dd.mm.yy',
        'class' => ['date-to'],
        'placeholder' => $this->t('dd.mm.yy'),
        'aria-label' => $this->t('To date'),
      ],
    ];
    // Attach the jquery datepicker library.
    $form['#attached']['library'][] = 'core/drupal.date';
    // Add the settings to JS for facet to properly functioning.
    $form['#attached']['drupalSettings']['dateFilter'] = [
      'urlAlias' => $this->facet->getUrlAlias(),
      'facetUrl' => $facetUrl,
      'min' => $startDate,
      'max' => $endDate,
    ];
    $form['#attached']['library'][] = 'degov_search_content/facet.date_range';

    $form['date_filter_wrapper']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#attributes' => ['class' => ['date-filter']],
    ];

    return $form;
  }

  /**
   * Human readable array of granularity options.
   *
   * @return array
   *   An array of granularity options.
   */
  private function granularityOptions() {
    return [
      SearchApiDate::FACETAPI_DATE_YEAR => $this->t('Year'),
      SearchApiDate::FACETAPI_DATE_MONTH => $this->t('Month'),
      SearchApiDate::FACETAPI_DATE_DAY => $this->t('Day'),
      SearchApiDate::FACETAPI_DATE_HOUR => $this->t('Hour'),
      SearchApiDate::FACETAPI_DATE_MINUTE => $this->t('Minute'),
      SearchApiDate::FACETAPI_DATE_SECOND => $this->t('Second'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_relative' => 0,
      'granularity' => SearchApiDate::FACETAPI_DATE_MONTH,
      'date_display' => '',
      'relative_granularity' => 1,
      'relative_text' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $configuration = $this->getConfiguration();

    $form += parent::buildConfigurationForm($form, $form_state, $facet);

    $form['display_relative'] = [
      '#type' => 'radios',
      '#title' => $this->t('Date display'),
      '#default_value' => $configuration['display_relative'],
      '#options' => [
        0 => $this->t('Actual date with granularity'),
        1 => $this->t('Relative date'),
      ],
    ];

    $form['granularity'] = [
      '#type' => 'radios',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#options' => $this->granularityOptions(),
    ];
    $form['date_display'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#default_value' => $configuration['date_display'],
      '#description' => $this->t('Override default date format used for the displayed filter format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
      '#states' => [
        'visible' => [':input[name="widget_config[display_relative]"]' => ['value' => 0]],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'date';
  }

}
