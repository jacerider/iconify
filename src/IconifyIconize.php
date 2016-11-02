<?php

namespace Drupal\iconify;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class IconifyIconize.
 *
 * @package Drupal\iconify
 */
class IconifyIconize extends TranslatableMarkup {

  /**
   * The Icon options.
   *
   * @var array
   */
  protected $options = [
    'childrenCount' => FALSE,
    'iconOnly' => FALSE,
    'iconPosition' => 'before',
  ];

  /**
   * The system defined icon replacement definition.
   */
  protected $info = [];

  /**
   * The icon.
   */
  protected $icon = NULL;

  /**
   * Constructs a new Iconify object.
   *
   * @param string $string
   *  The string to find an icon for.
   * @param array $options
   */
  public function __construct($string, array $arguments = array(), array $options = array(), TranslationInterface $string_translation = NULL) {
    if (is_a($string, '\Drupal\Core\StringTranslation\TranslatableMarkup')) {
      $string = $string->getUntranslatedString();
    }
    $options = $options + $this->options;
    parent::__construct($string, $arguments, $options, $string_translation);
    $this->info = \Drupal::service('plugin.manager.iconify.info')->getDefinitions();
    $this->manager = \Drupal::service('iconify.manager');
  }

  /**
   * Return a class instance.
   */
  public static function iconize($string, array $arguments = array(), array $options = array(), TranslationInterface $string_translation = NULL) {
    return new static(
      $string,
      $arguments,
      $options,
      $string_translation
    );
  }

  /**
   * Return cleaned and lowercase string.
   */
  public function getCleanString() {
    return strtolower(strip_tags($this->getUntranslatedString()));
  }

  /**
   * Finds the icon and returns its info array if it can be found.
   */
  public function getIconInfo() {
    $string = $this->getCleanString();
    foreach ($this->info as $info) {
      if ($info['text'] && $info['text'] == $string) {
        return $info;
      }
      if ($info['regex'] && preg_match('!' . $info['regex'] . '!', $string)) {
        return $info;
      }
    }
    return [];
  }

  /**
   * Set the icon string.
   */
  public function setIcon($icon = '') {
    if (empty($icon) && ($info = $this->getIconInfo())) {
      $icon = $info['icon'];
    }
    if ($info = $this->manager->getIconInfo($icon)) {
      $this->setIconChildrenCount(count($info['code']));
    }
    else {
      $icon = '';
    }
    $this->icon = $icon;
    return $this;
  }

  /**
   * Returns the icon string.
   *
   * @return string
   */
  public function getIcon() {
    if ($this->icon === NULL) {
      $this->setIcon();
    }
    return $this->icon;
  }

  /**
   * Returns the icon wrapped in markup.
   *
   * @return string
   */
  public function getIconMarkup() {
    if ($icon = $this->getIcon()) {
      return '<i class="' . $icon . '"></i>';
    }
    return '';
  }

  /**
   * Set icon child count.
   *
   * @param integer $count
   *   (optional) Some icons need multiple children selectors. Font glyphs
   *   cannot have more than one color by default. Using CSS, IcoMoon layers
   *   multiple glyphs on top of each other to implement multicolor glyphs.
   *   As a result, these glyphs take more than one character code and cannot
   *   have ligatures.
   *
   * @return $this
   */
  public function setIconChildrenCount($count = 1) {
    $this->options['childrenCount'] = $count;
    return $this;
  }

  /**
   * Only show the icon.
   *
   * @param bool $absolute
   *   (optional) Whether to hide the string and only show the icon.
   *
   * @return $this
   */
  public function setIconOnly($iconOnly = TRUE) {
    $this->options['iconOnly'] = $iconOnly;
    return $this;
  }

  /**
   * Show the icon before the text.
   *
   * @return $this
   */
  public function setIconBefore() {
    $this->options['iconPosition'] = 'before';
    return $this;
  }

  /**
   * Show the icon before the text.
   *
   * @return $this
   */
  public function setIconAfter() {
    $this->options['iconPosition'] = 'after';
    return $this;
  }

  /**
   * Renders the object as a string.
   *
   * @return string
   *   The translated string.
   */
  public function render() {
    $icon = $this->getIconMarkup();
    $output = parent::render();
    if ($icon = $this->getIcon()) {
      $output = [
        '#theme' => 'iconify_icon',
        '#icon' => $icon,
        '#text' => $output,
        '#children_count' => $this->options['childrenCount'],
        '#icon_only' => $this->options['iconOnly'],
        '#position' => $this->options['iconPosition'],
      ];
      $output = drupal_render($output);
    }
    return $output;
  }

}
