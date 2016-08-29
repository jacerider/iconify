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

  // Helpful $content variable for templates.
  $vars['content'] = [];
  foreach (Element::children($vars['elements']) as $key) {
    $vars['content'][$key] = $vars['elements'][$key];
  }

  // Icons
  $vars['content']['iconify_icons']['title'] = [
    '#type' => 'html_tag',
    '#tag' => 'h2',
    '#value' => t('Available Icons'),
    '#suffix' => '<br><hr><br>',
  ];
  $vars['content']['iconify_icons']['icons'] = [
    '#theme' => 'iconify_icon_list',
    '#iconify' => $iconify,
  ];
  if (!$iconify->isPublished()) {
    $vars['content']['iconify_icons']['icons']['#attached']['library'][] = 'iconify/iconify.' . $iconify->id();
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
  $vars['icons'] = $iconify->getIcons();
}
