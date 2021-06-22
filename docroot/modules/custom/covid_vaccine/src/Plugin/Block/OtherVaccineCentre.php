<?php

namespace Drupal\covid_vaccine\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\user\Entity\User;
use Drupal\Core\Cache\Cache;

/**
 * Provides a generic Menu block.
 *
 * @Block(
 *   id = "other_covid_vaccine_centre",
 *   admin_label = @Translation("Other Vaccine Centre"),
 *   category = @Translation("Custom"),
 * )
 */
class OtherVaccineCentre extends BlockBase {

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
    
    $address = $profile->get('field_address')->getValue();
    $city = $address[0]['locality'];
    $output = views_embed_view('all_vaccine_centre', 'block_2', $city);

    return $output;
  }

  public function getCacheTags() {
    $current_user = \Drupal::currentUser();
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $current_user->id()]);
  }

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

}
