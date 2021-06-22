<?php
namespace Drupal\covid_vaccine\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class RegistrationController extends ControllerBase {

  public function getContent() {
    $entity = \Drupal::entityTypeManager()->getStorage('user')->create(array());
    $formObject = \Drupal::entityTypeManager()
      ->getFormObject('user')
      ->setEntity($entity);
    $form = \Drupal::formBuilder()->getForm($formObject);

    return ['#markup' => \Drupal::service('renderer')->render($form)];
  }

}