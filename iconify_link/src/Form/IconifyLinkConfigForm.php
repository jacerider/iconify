<?php

namespace Drupal\iconify_link\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\iconify\IconifyManager;

/**
 * Class IconifyLinkConfigForm.
 *
 * @package Drupal\iconify_link\Form
 */
class IconifyLinkConfigForm extends ConfigFormBase {

  /**
   * Drupal\iconify\IconifyManager definition.
   *
   * @var \Drupal\iconify\IconifyManager
   */
  protected $iconifyManager;

  public function __construct(ConfigFactoryInterface $config_factory, IconifyManager $iconify_manager) {
    parent::__construct($config_factory);
    $this->iconifyManager = $iconify_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('iconify.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'iconify_link.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iconify_link_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iconify_link.config');
    $form['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#description' => $this->t('The icon packages that should be made available to menu items. If no packages are selected, all will be made available.'),
      '#options' => $this->iconifyManager->getActivePackageLabels(),
      '#default_value' => $config->get('packages'),
    ];
    $form['menu_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add icons to rendered menus.'),
      '#default_value' => $config->get('menu_enable'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('iconify_link.config')
      ->set('packages', $form_state->getValue('packages'))
      ->set('menu_enable', $form_state->getValue('menu_enable'))
      ->save();
  }

}
