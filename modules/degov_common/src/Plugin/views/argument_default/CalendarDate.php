<?php

namespace Drupal\degov_common\Plugin\views\argument_default;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\views\Plugin\views\argument_default\Raw;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default argument plugin to use the raw value from the URL.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "calendar_event_raw",
 *   title = @Translation("Raw value from URL or current date")
 * )
 */
final class CalendarDate extends Raw {

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
    // Don't trim the leading slash since getAliasByPath() requires it.
    $path = rtrim($this->currentPath->getPath($this->view->getRequest()), '/');
    if ($this->options['use_alias']) {
      $path = $this->aliasManager->getAliasByPath($path);
    }
    $args = explode('/', $path);
    // Drop the empty first element created by the leading slash since the path
    // component index doesn't take it into account.
    array_shift($args);
    if (isset($args[$this->options['index']]) && $this->isValidDateFromArgument($args[$this->options['index']])) {
      return $args[$this->options['index']];
    }

    $request_time = $this->request->server->get('REQUEST_TIME');

    return $this->dateFormatter->format($request_time, 'custom', $this->argFormat);
  }

  /**
   * Check if the string is correct date from format.
   *
   * @param string $date_string
   *   Date string.
   *
   * @return bool
   *   True if valid date.
   */
  private function isValidDateFromArgument(string $date_string): bool {
    $date = \DateTime::createFromFormat($this->argFormat, $date_string);
    return $date instanceof \DateTime;
  }

}
