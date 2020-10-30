<?php

declare(strict_types=1);

namespace Drupal\filter_disallow\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to remove disallowed HTML tags.
 *
 * @Filter(
 *   id = "filter_html_disallow",
 *   title = @Translation("Remove disallowed HTML tags"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_HTML_RESTRICTOR,
 *   settings = {
 *     "disallowed_html" = "<script>",
 *     "filter_html_disallow_help" = TRUE,
 *   },
 *   weight = -10
 * )
 */
final class FilterHtmlDisallow extends FilterBase implements ContainerFactoryPluginInterface {
  /**
   * The processed HTML restrictions.
   *
   * @var array
   */
  protected $removals;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function setMessenger(MessengerInterface $messenger): void {
    $this->messenger = $messenger;
  }

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   */
  public function setCurrentUser(AccountProxyInterface $currentUser): void {
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setMessenger($container->get('messenger'));
    $instance->setCurrentUser($container->get('current_user'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['disallowed_html'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Disallowed HTML tags'),
      '#default_value' => $this->settings['disallowed_html'],
      '#description'   => $this->t('A list of HTML tags that will be removed.'),
    ];
    $form['filter_html_disallow_help'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Display basic HTML help in long filter tips'),
      '#default_value' => $this->settings['filter_html_disallow_help'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (isset($configuration['settings']['disallowed_html'])) {
      // The javascript in core/modules/filter/filter.filter_html.admin.js
      // removes new lines and double spaces so, for consistency when javascript
      // is disabled, remove them.
      $configuration['settings']['disallowed_html'] = \preg_replace('/\s+/', ' ', $configuration['settings']['disallowed_html']);
    }
    parent::setConfiguration($configuration);
    // Force restrictions to be calculated again.
    $this->removals = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $removals = $this->getHtmlRemovals();
    foreach ($removals['disallowed'] as $removal) {
      $text = $this->stripHtmlTag($text, $removal);
    }
    return new FilterProcessResult($text);
  }

  /**
   * Strip html tag.
   *
   * @param string $htmlContent
   *   Html content.
   * @param string $tag
   *   Tag.
   *
   * @return string
   *   Filtered html.
   */
  public function stripHtmlTag(string $htmlContent, string $tag): string {
    $dom = new \DOMDocument();
    // Prevent warnings on HTML5 elements.
    \libxml_use_internal_errors(TRUE);
    $htmlentities = \htmlentities($htmlContent);
    $decoded_htmlentities = \html_entity_decode($htmlentities);
    $dom->loadHTML(\utf8_decode($htmlContent));
    $xPath = new \DOMXPath($dom);
    $nodes = $xPath->query('//' . $tag);
    if ($nodes->length > 0) {
      if ($this->currentUser->hasPermission('view filter_disallow messages')) {
        $this->messenger->addWarning($this->t('The text you entered contains <code>:element</code> tags. These are not permitted here and will be stripped from the output.', [':element' => $tag]));
      }
    }
    $filteredHtml = \preg_replace('(<' . $tag . '.*?>|</' . $tag . '>)', '', $decoded_htmlentities);
    // saveHTML() will urlencode characters like square brackets,
    // which we need to remain intact for the media_file_links input filter.
    $filteredHtml = \preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $filteredHtml);
    return $filteredHtml;
  }

  /**
   * {@inheritdoc}
   */
  public function getHtmlRemovals() {
    if ($this->removals) {
      return $this->removals;
    }

    // Parse the allowed HTML setting, and gradually make the whitelist more
    // specific.
    $removals = ['disallowed' => []];

    // Make all the tags self-closing, so they will be parsed into direct
    // children of the body tag in the DomDocument.
    $html = \str_replace('>', ' />', $this->settings['disallowed_html']);
    // Protect any trailing * characters in attribute names, since DomDocument
    // strips them as invalid.
    $star_protector = '__zqh6vxfbk3cg__';
    $html = \str_replace('*', $star_protector, $html);
    $body_child_nodes = Html::load($html)
      ->getElementsByTagName('body')
      ->item(0)->childNodes;

    foreach ($body_child_nodes as $node) {
      if ($node->nodeType !== \XML_ELEMENT_NODE) {
        // Skip the empty text nodes inside tags.
        continue;
      }
      $tag = $node->tagName;
      // Mark the tag as allowed, but with no attributes allowed.
      $removals['disallowed'][] = $tag;
    }

    // Save this calculated result for re-use.
    $this->removals = $removals;

    return $removals;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    $disallowed_html = $this->settings['disallowed_html'];

    if (empty($disallowed_html) || $this->settings['filter_html_disallow_help'] === FALSE) {
      return '';
    }

    $output = $this->t('Disallowed HTML tags: <code>@tags</code>', ['@tags' => $disallowed_html]);
    if (!$long) {
      return $output;
    }

    return '<p>' . $output . '</p>';
  }

}
