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
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_iconify(array &$variables) {
  // Fetch Iconify Entity Object.
  $iconify = $variables['elements']['#iconify'];

  // Helpful $content variable for templates.
  $variables['content'] = [];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Icons
  $variables['content']['iconify_icons']['title'] = [
    '#type' => 'html_tag',
    '#tag' => 'h2',
    '#value' => t('Available Icons'),
    '#suffix' => '<br><hr><br>',
  ];
  $variables['content']['iconify_icons']['icons'] = [
    '#theme' => 'iconify_icon_list',
    '#iconify' => $iconify,
  ];
}

/**
 * Prepares variables for the Iconify icon list template.
 *
 * Default template: iconify-icon-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_iconify_icon_list(array &$variables) {
  // Attach UI CSS.
  $variables['#attached'] = ['library' => ['iconify/iconify.ui']];

  $iconify = $variables['iconify']['#iconify'];
  $variables['content'] = [];
  $variables['icons'] = $iconify->getIcons();
  // $items = [];
  // foreach ($iconify->getIcons() as $name => $selector) {
  //   $items[$name]['#markup'] = '<i class="'.$selector.'"></i><br>';
  // }
  // $variables['content'] = [];
  // $variables['content']['list'] = array(
  //   '#theme' => 'item_list',
  //   '#items' => $items,
  // );
}
