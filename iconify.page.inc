<?php

/**
 * @file
 * Contains iconify.page.inc.
 *
 * Page callback for Iconify package entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Iconify package templates.
 *
 * Default template: iconify.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_iconify(array &$vars) {
  // Fetch Iconify Entity Object.
  $iconify = $vars['elements']['#iconify'];
  $info = $iconify->getInfo();

  // Helpful $content variable for templates.
  $vars['content'] = [];
  foreach (Element::children($vars['elements']) as $key) {
    $vars['content'][$key] = $vars['elements'][$key];
  }

  // Use
  $vars['content']['use'] = [
    '#theme' => 'fieldset',
    '#title' => t('Use'),
  ];

  $vars['content']['use']['#children']['details'] = [
    '#markup' => '
<textarea rows="13" class="iconify-use">
/* use !important to prevent issues with browser extensions that change fonts */
font-family: \'' . $info['metadata']['name'] . '\' !important;
speak: none;
font-style: normal;
font-weight: normal;
font-variant: normal;
text-transform: none;
line-height: 1;

/* Better Font Rendering =========== */
-webkit-font-smoothing: antialiased;
-moz-osx-font-smoothing: grayscale;
</textarea>',
    '#allowed_tags' => ['textarea'],
  ];

  // Icons
  $vars['content']['icons'] = [
    '#theme' => 'fieldset',
    '#title' => t('Available Icons'),
  ];
  $vars['content']['icons']['#children']['icons'] = [
    '#theme' => 'iconify_icon_list',
    '#iconify' => $iconify,
  ];
  if (!$iconify->isPublished()) {
    $vars['content']['icons']['icons']['#attached']['library'][] = 'iconify/iconify.' . $iconify->id();
  }
}

/**
 * Prepares variables for the Iconify icon list template.
 *
 * Default template: iconify-icon-list.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_iconify_icon_list(array &$vars) {
  // Attach UI CSS.
  $vars['#attached'] = ['library' => ['iconify/iconify.admin.ui']];
  $iconify = $vars['iconify']['#iconify'];
  $vars['icons'] = $iconify->getIconsWithHex();
}
