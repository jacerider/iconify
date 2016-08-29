<?php

namespace Drupal\iconify\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class IconifyForm.
 *
 * @package Drupal\iconify\Form
 */
class IconifyForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $iconify = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $iconify->label(),
      '#maxlength' => 255,
      '#description' => $this->t('A unique label for this IcoMoon package. This label will be displayed in the interface of modules that integrate with Iconify.'),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $iconify->id(),
      '#disabled' => !$iconify->isNew(),
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this IcoMoon package. It must only contain lowercase letters, numbers and hyphens.'),
      '#machine_name' => array(
        'source' => array('label'),
        'exists' => '\Drupal\iconify\Entity\Iconify::load',
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ),
    );

    $validators = array(
      'file_validate_extensions' => array('zip'),
      'file_validate_size' => array(file_upload_max_size()),
    );
    $form['file'] = array(
      '#type' => 'file',
      '#title' => $iconify->isNew() ? $this->t('IcoMoon Font Package') : $this->t('Replace IcoMoon Font Package'),
      '#description' => array(
        '#theme' => 'file_upload_help',
        '#description' => $this->t('An IcoMoon font package.'),
        '#upload_validators' => $validators,
      ),
      '#size' => 50,
      '#upload_validators' => $validators,
      '#attributes' => array('class' => array('file-import-input')),
    );

    $form['#entity_builders']['update_status'] = [$this, 'updateStatus'];

    return $form;
  }

  /**
   * Entity builder updating the iconify status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\iconify\IconifyInterface $iconify
   *   The iconify updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\iconify\NodeForm::form()
   */
  function updateStatus($entity_type_id, \Drupal\iconify\Entity\Iconify $iconify, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $iconify->setPublished($element['#published_status']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $iconify = $this->entity;

      // Add a "Publish" button.
    $element['publish'] = $element['submit'];
    // If the "Publish" button is clicked, we want to update the status to "published".
    $element['publish']['#published_status'] = TRUE;
    $element['publish']['#dropbutton'] = 'save';
    if ($iconify->isNew()) {
      $element['publish']['#value'] = t('Save and publish');
    }
    else {
      $element['publish']['#value'] = $iconify->isPublished() ? t('Save and keep published') : t('Save and publish');
    }
    $element['publish']['#weight'] = 0;

    // Add a "Unpublish" button.
    $element['unpublish'] = $element['submit'];
    // If the "Unpublish" button is clicked, we want to update the status to "unpublished".
    $element['unpublish']['#published_status'] = FALSE;
    $element['unpublish']['#dropbutton'] = 'save';
    if ($iconify->isNew()) {
      $element['unpublish']['#value'] = t('Save as unpublished');
    }
    else {
      $element['unpublish']['#value'] = !$iconify->isPublished() ? t('Save and keep unpublished') : t('Save and unpublish');
    }
    $element['unpublish']['#weight'] = 10;

    // If already published, the 'publish' button is primary.
    if ($iconify->isPublished()) {
      unset($element['unpublish']['#button_type']);
    }
    // Otherwise, the 'unpublish' button is primary and should come first.
    else {
      unset($element['publish']['#button_type']);
      $element['unpublish']['#weight'] = -10;
    }

    // Remove the "Save" button.
    $element['submit']['#access'] = FALSE;

    $element['delete']['#access'] = $iconify->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $this->file = file_save_upload('file', $form['file']['#upload_validators'], FALSE, 0);

    // Ensure we have the file uploaded.
    if (!$this->file && $this->entity->isNew()) {
      $form_state->setErrorByName('file', $this->t('File to import not found.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $iconify = $this->entity;

    if ($this->file) {
      try {
        $zip_path = $this->file->getFileUri();
        $iconify->setArchive($zip_path);
      }
      catch (Exception $e) {
        $form_state->setErrorByName('file', $e->getMessage());
        return;
      }
    }

    $status = $iconify->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Iconify.', [
          '%label' => $iconify->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Iconify.', [
          '%label' => $iconify->label(),
        ]));
    }
    drupal_flush_all_caches();
    $form_state->setRedirectUrl($iconify->urlInfo('collection'));
  }

}
