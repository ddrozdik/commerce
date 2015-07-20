<?php

/**
 * @file
 * Definition of \Drupal\commerce_order\Tests\CommerceOrderTestBase.
 */

namespace Drupal\commerce_order\Tests;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_store\Entity\Store;
use Drupal\commerce_store\Tests\StoreTestBase;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\simpletest\WebTestBase;

/**
 * Defines base class for commerce_order test cases.
 */
abstract class CommerceOrderTestBase extends WebTestBase {

  /**
   * The product to test against
   *
   * @var \Drupal\commerce_product\Entity\Product
   */
  protected $product;

  /**
   * The store to test against
   *
   * @var \Drupal\commerce_store\Entity\Store
   */
  protected $store;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce',
    'commerce_order',
    'commerce_price',
    'commerce_line_item',
    'inline_entity_form'
  ];

  /**
   * A user with permission to administer orders.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
      'administer orders',
      'administer order types',
      'access administration pages',
    ));

    // Create a store
    $values = [
      'name' => t('Default store'),
      'uid' => 1,
      'mail' => \Drupal::config('system.site')->get('mail'),
      'type' => 'default',
      'default_currency' => 'USD',
      'address' => [
        'country_code' => 'GB',
        'locality' => 'London',
        'postal_code' => 'NW1 6XE',
        'address_line1' => '221B Baker Street',
      ],
    ];
    $this->store = Store::create($values);
    $this->store->save();

    // Set as default store.
    \Drupal::configFactory()->getEditable('commerce_store.settings')
      ->set('default_store', $this->store->uuid())->save();

    // Create a product
    $values = [
      'sku' => $this->randomMachineName(),
      'title' => $this->randomMachineName(),
      'type' => 'product',
      'store_id' => $this->store->id()
    ];

    $this->product = Product::create($values);
    $this->product->save();

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Creates a new entity
   *
   * @param string $entityType
   * @param array $values
   *   An array of settings.
   *   Example: 'id' => 'foo'.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function createEntity($entityType, $values) {
    $entity = \Drupal::entityManager()
      ->getStorage($entityType)
      ->create($values);
    $status = $entity->save();

    $this->assertEqual(
      $status,
      SAVED_NEW,
      SafeMarkup::format('Created %label entity %type.', [
          '%label' => $entity->getEntityType()->getLabel(),
          '%type' => $entity->id()
        ]
      )
    );

    return $entity;
  }
}
