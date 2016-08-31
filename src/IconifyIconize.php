<?php

namespace Drupal\iconify;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\iconify\IconifyInfoManager;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslationInterface;
// use Drupal\iconify\Entity\Iconify;

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
    'iconOnly' => FALSE,
    'iconPosition' => 'before',
  ];

  /**
   * The system defined icon replacement definition.
   */
  protected $info = [];

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
   * Returns the icon string.
   *
   * @return string
   */
  public function getIcon() {
    if ($info = $this->getIconInfo()) {
      return $info['icon'];
    }
    return '';
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
    if ($icon = $this->getIconMarkup()) {
      if ($this->options['iconOnly']) {
        $output = $icon . '<span class="visually-hidden">' . $output . '</span>';
      }
      else {
        if ($this->options['iconPosition'] == 'after') {
          $output = $output . ' ' . $icon;
        }
        else {
          $output = $icon . ' ' . $output;
        }
      }
    }
    return $output;
  }

}
