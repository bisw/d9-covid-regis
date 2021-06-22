<?php

namespace Drupal\covid_vaccine\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\user\Entity\User;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;

/**
 * Provides a generic Menu block.
 *
 * @Block(
 *   id = "centre_registration",
 *   admin_label = @Translation("Vaccine Centre Registration"),
 *   category = @Translation("Custom"),
 * )
 */
class CentreRegistration extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();

    if ($current_user->id() == 0) {
      return;
    }
    $user = User::load($current_user->id());

    $profile = \Drupal::getContainer()
      ->get('entity_type.manager')
      ->getStorage('profile')
      ->loadByUser($user, ['personal_information']);

    $current_centre = $profile->get('field_vaccine_centre')->referencedEntities();

    if (empty($current_centre)) {
      $build = Link::fromTextAndUrl('Registration', Url::fromUri('internal:/user/' . $user->id(). '/personal_information'))->toRenderable();
    } else {
      $id = $profile->get('field_vaccine_centre')->getValue()[0]['target_id'];
      $build = [
        '#markup' => $this->t('Your registered vaccine centre: ') . Link::createFromRoute($current_centre[0]->getTitle(), 'entity.node.canonical', ['node' => $current_centre[0]->id()])->toString(),
      ];
    }

    return $build;
  }

  public function getCacheTags() {
    $current_user = \Drupal::currentUser();
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $current_user->id()]);
  }

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), ['url', 'user']);
  }


}
