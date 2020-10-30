<?php

namespace Drupal\media_file_links\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\link_attributes\Plugin\Field\FieldWidget\LinkWithAttributesWidget;

/**
 * Extension of the LinkWidget class.
 *
 * @FieldWidget(
 *   id = "link_mediafilelinks",
 *   label = @Translation("Link (with Media support)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MediaFileLinkWidget extends LinkWithAttributesWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#attached']['library'][] = 'media_file_links/fontawesome';

    $element['uri']['#description']['#items'][] = new TranslatableMarkup('Suggestions may also include files from Media entities you can link to. These will be prefixed with an icon appropriate to their file type.');

    return $element;
  }

  /**
   * Form element validation handler for the 'uri' element.
   *
   * Disallows saving inaccessible or untrusted URLs.
   */
  public static function validateUriElement($element, FormStateInterface $form_state, $form) {
    // 1:1 copied from LinkWidget, except for that final if()
    $uri = static::getUserEnteredStringAsUri($element['#value']);
    $form_state->setValueForElement($element, $uri);

    // If getUserEnteredStringAsUri() mapped the entered value to a 'internal:'
    // URI , ensure the raw value begins with '/', '?' or '#'.
    // @todo '<front>' is valid input for BC reasons, may be removed by
    //   https://www.drupal.org/node/2421941
    if (!self::validateInternalUriFormat($uri, $element['#value'])) {
      $form_state->setError($element, t('Manually entered paths should start with /, ? or #, or match &lt;media/file/ID&gt;.'));
      return;
    }
  }

  /**
   * Validate internal uri format.
   */
  private static function validateInternalUriFormat(string $uri, string $inputElementValue): bool {
    if (parse_url($uri, PHP_URL_SCHEME) === 'internal'
      && strpos($inputElementValue, '<front>') !== 0
      && !\in_array($inputElementValue[0], ['/', '?', '#'], TRUE)
      && !\Drupal::service('media_file_links.placeholder_handler')->isValidMediaFileLinkPlaceholder($inputElementValue)) {
      return FALSE;
    }
    return TRUE;
  }

}
