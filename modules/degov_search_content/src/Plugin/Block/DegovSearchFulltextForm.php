<?php

namespace Drupal\degov_search_content\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Form\ViewsExposedForm;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to filter the media search.
 *
 * @Block(
 *   id = "degov_search_content_full_text_search_block",
 *   admin_label = @Translation("DeGov full text search block")
 * )
 */
final class DegovSearchFulltextForm extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   */
  public function setFormBuilder(FormBuilderInterface $form_builder): void {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setFormBuilder($container->get('form_builder'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view = Views::getView('search_content');
    $view->setDisplay('page_1');
    $view->initHandlers();
    $form_state = (new FormState())
      ->setStorage([
        'view' => $view,
        'display' => &$view->display_handler->display,
        'rerender' => TRUE,
      ])
      ->setMethod('get')
      ->setAlwaysProcess()
      ->disableRedirect();
    $form_state->set('rerender', NULL);
    $form = $this->formBuilder
      ->buildForm(ViewsExposedForm::class, $form_state);
    $form['#theme'] = ['views_exposed_form'];
    return $form;
  }

}
