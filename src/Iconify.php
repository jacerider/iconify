<?php

namespace Drupal\iconify;

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
   * The Icon options.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Constructs a new Iconify object.
   *
   * @param string $text
   *  The string to find an icon for.
   * @param array $options
   */
  public function __construct($text, $options = array()) {
    $this->text = $text;
    $this->options = $options;
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
   * Returns the text.
   *
   * @return string
   */
  public function getText() {
    return $this->text;
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
    $this->options = $options;
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
    return !empty($this->options['iconOnly']) ? '[icon]' : '[icon] ' . $this->getText();
  }

}
