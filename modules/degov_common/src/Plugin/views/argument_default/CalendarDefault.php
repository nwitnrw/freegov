<?php

namespace Drupal\degov_common\Plugin\views\argument_default;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default argument plugin to use the raw value from the URL.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "calendar_default",
 *   title = @Translation("Current month of the year (exposed excluded)")
 * )
 */
final class CalendarDefault extends ArgumentDefaultPluginBase {

  /**
   * {@inheritdoc}
   */
  protected $argFormat = 'Ym';

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   */
  public function setDateFormatter(DateFormatterInterface $dateFormatter): void {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function setRequest(Request $request): void {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setDateFormatter($container->get('date.formatter'));
    $instance->setRequest($container->get('request_stack')->getCurrentRequest());
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // Check if there is exposed filter in the query with the same name,
    // if yes - do not apply argument.
    $exposed_filter_active = FALSE;
    // Check the real field name for the argument.
    $argument_real_field = $this->argument->realField;
    // Get exposed input.
    $exposed_data = $this->view->getExposedInput();
    if (!empty($exposed_data)) {
      $view_filters = [];
      foreach ($this->view->filter as $field => $view_filter) {
        $view_filters[$field] = $field;
        if (!empty($view_filter->options['expose']['identifier'])) {
          $view_filters[$field] = $view_filter->options['expose']['identifier'];
        }
      }
      // Try to find in the exposed input the field with the same real name.
      foreach ($exposed_data as $key => $filter) {
        $id = array_search($key, $view_filters);
        if ($id && $filter != '' && !empty($this->view->filter[$id])) {
          $exposed_filter = $this->view->filter[$id];
          if ($exposed_filter->realField == $argument_real_field) {
            $exposed_filter_active = TRUE;
            break;
          }
        }
      }
    }
    // If there is query parameter for the real field,
    // don't apply default value.
    if (!$exposed_filter_active) {
      $request_time = $this->request->server->get('REQUEST_TIME');
      return $this->dateFormatter->format($request_time, 'custom', $this->argFormat);
    }

    // Return all results for argument (exposed filters are limiting
    // the results instead).
    return $this->argument->options['exception']['value'];
  }

}
