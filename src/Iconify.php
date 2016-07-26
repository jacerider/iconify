<?php

namespace Drupal\iconify;

use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an object that holds information about an icon.
 */
class Iconify {

  /**
   * The text to iconify.
   *
   * @var string
   */
  protected $text;

  /**
   * The iconified icon.
   *
   * @var string
   */
  protected $icon = '';

  /**
   * The Icon options.
   *
   * @var array
   */
  protected $options = [
    'iconOnly' => FALSE
  ];

  /**
   * The system defined icon replacement definition.
   */
  protected $info = [];

  /**
   * Constructs a new Iconify object.
   *
   * @param string $text
   *  The string to find an icon for.
   * @param array $options
   */
  public function __construct($text, $options = array()) {
    $this->setText($text);
    $this->setOptions($options);
    $this->info = \Drupal::service('plugin.manager.iconify.info')->getDefinitions();
  }

  /**
   * Creates a new Iconify object for text.
   *
   * @param string $text
   *  The string to find an icon for.
   * @param array $options
   */
  public static function fromText($text, $options = array()) {
    return new static($text, $options);
  }

  /**
   * Set the text
   *
   * @param string $text
   *  The string to find an icon for.
   */
  public function setText($text) {
    if (is_a($text, '\Drupal\Core\StringTranslation\TranslatableMarkup')) {
      $text = $text->getUntranslatedString();
    }
    $this->text = $text;
    return $this;
  }

  /**
   * Returns the text.
   *
   * @return string
   */
  public function getText() {
    return $this->text;
  }

  /**
   * Returns the translated text.
   */
  public function getTextTranslated() {
    return new TranslatableMarkup($this->getText());
  }


  /**
   * Return cleaned and lowercase text.
   */
  public function getMachine() {
    return strtolower($this->getText());
  }


  /**
   * Finds the icon and returns its info array if it can be found.
   */
  public function getIconInfo() {
    $text = $this->getMachine();
    foreach ($this->info as $info) {
      if ($info['text'] && $info['text'] == $text) {
        return $info;
      }
      if ($info['regex'] && !$this->icon && preg_match('!' . $info['regex'] . '!', $text)) {
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
    if (!$this->icon && $info = $this->getIconInfo()) {
      return $info['icon'];
    }
    return '';
  }

  public function getIconMarkup() {
    if ($icon = $this->getIcon()) {
      return '<i class="' . $icon . '"></i>';
    }
    return '';
  }

  /**
   * Set the icon.
   *
   * @return string
   */
  public function setIcon($icon) {
    $this->icon = $icon;
    return $this;
  }

  /**
   * Returns the URL options.
   *
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * Gets a specific option.
   *
   * @param string $name
   *   The name of the option.
   *
   * @return mixed
   *   The value for a specific option, or NULL if it does not exist.
   */
  public function getOption($name) {
    if (!isset($this->options[$name])) {
      return NULL;
    }

    return $this->options[$name];
  }

  /**
   * Sets the URL options.
   *
   * @param array $options
   *
   * @return $this
   */
  public function setOptions($options) {
    $this->options = $options + $this->options;
    return $this;
  }

  /**
   * Sets a specific option.
   *
   * @param string $name
   *   The name of the option.
   * @param mixed $value
   *   The option value.
   *
   * @return $this
   */
  public function setOption($name, $value) {
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Sets the value of the absolute option for this Url.
   *
   * @param bool $absolute
   *   (optional) Whether to hide the text and only show the icon.
   *
   * @return $this
   */
  public function setIconOnly($iconOnly = TRUE) {
    $this->setOption('iconOnly', $iconOnly);
    return $this;
  }

  public function toString() {
    $icon = $this->getIconMarkup();
    $text = $this->getTextTranslated();
    if ($icon) {
      $output = $icon;
      if ($this->options['iconOnly']) {
        $output .= ' ' . $text;
      }
    } else {
      $output = $text;
    }
    return Markup::create($output);
  }

}
