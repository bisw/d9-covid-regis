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
 *   id = "covid_vaccine_city",
 *   admin_label = @Translation("Covid Vaccine City"),
 *   category = @Translation("Custom"),
 * )
 */
class VaccineCity extends BlockBase {

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

    $connection = \Drupal::database();
    $query = $connection->select('node_field_data', 'n');
    $query->join('node__field_address', 'ad', 'n.nid = ad.entity_id');
    $query->fields('ad', array('field_address_locality'))
      ->condition('n.type', 'vaccine_centre')
      ->condition('ad.field_address_locality', $city, '!=')
      ->condition('n.status', '1')
      ->groupBy("ad.field_address_locality")
      ->addExpression("count(ad.field_address_locality)", 'centre_count');

    $result = $query->execute();
    $cities = [];
    foreach ($result as $record) {
      $cities[$record->field_address_locality] = $record->centre_count;
    }

    arsort($cities);

    $items = [];
    foreach ($cities as $city => $count) {
      $items[] = Link::fromTextAndUrl($city, Url::fromUri('internal:/vaccine-centre/' . $city))->toRenderable();
    }

    return $items;
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
