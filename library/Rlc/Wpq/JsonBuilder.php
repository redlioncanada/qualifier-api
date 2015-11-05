<?php

namespace Rlc\Wpq;

use Rlc\Wpq\FeedEntity,
    Lrr\ServiceLocator;

class JsonBuilder {

  /**
   * @var FeedModelBuilderInterface
   */
  private $feedModelBuilder;

  /**
   * Will be reused between locales for same brand. Product arrays indexed
   * by brand.
   * 
   * @var FeedEntity\CatalogEntry[][]
   */
  private $feedModelCache = [];

  /**
   * Defines catalog groups to include for each brand, and strategy class to
   * process products from each.
   * 
   * multidimensional:
   * brand => group identifier => strategy class name
   * 
   * (strategy class name is relative to \Rlc\Wpq\CatalogEntryProcessor\)
   * 
   * @var array
   */
  private $catalogGroupsConfig = [
    'maytag' => [
      // The strat class for these first two will return the same 'appliance'
      // field value but diff 'type' field values. Meanwhile, other strat
      // classes will not set 'type' at all.
      'SC_Kitchen_Cooking_Ranges' => 'Maytag\\Ranges',
      'SC_Kitchen_Cooking_Wall_Ovens' => 'Maytag\\WallOvens',
      'SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers' => 'Maytag\\Dishwashers',
      'SC_Kitchen_Refrigeration_Refrigerators' => 'Maytag\\Fridges',
      'SC_Laundry_Laundry_Appliances_Washers' => 'Maytag\\Washers',
    ],
    'whirlpool' => [
      'SC_Laundry_Laundry_Washers' => 'Whirlpool\\Washers',
      'SC_Kitchen_Dishwasher__Cleaning_Dishwashers' => 'Whirlpool\\Dishwashers',
      'SC_Kitchen_Refrigeration_Refrigerators' => 'Whirlpool\\Fridges',
      'SC_Kitchen_Cooking_Ranges' => 'Whirlpool\\Ranges',
      'SC_Kitchen_Cooking_Wall_Ovens' => 'Whirlpool\\WallOvens',
      'SC_Kitchen_Cooking_Cooktops' => 'Whirlpool\\Cooktops',
      'SC_Kitchen_Cooking_Hoods' => 'Whirlpool\\Hoods',
    ],
    'kitchenaid' => [
      'SC_Major_Appliances_Dishwashers_Dishwashers' => 'KitchenAid\\Dishwashers',
      'SC_Major_Appliances_Refrigerators_Refrigerators' => 'KitchenAid\\Fridges',
      'SC_Major_Appliances_Cooktops_Cooktops' => 'KitchenAid\\Cooktops',
      'SC_Major_Appliances_Ranges_Ranges' => 'KitchenAid\\Ranges',
      'SC_Major_Appliances_Wall_Ovens_Wall_Ovens' => 'KitchenAid\\WallOvens',
      'SC_Major_Appliances_Hoods_and_Vents_Hoods_and_Vents' => 'KitchenAid\\Vents',
    ],
  ];

  /**
   * Catalog group IDs of groups that should be included in feedModelBuilder,
   * but not processed into the main result set.
   * 
   * Use case right now is dryers -- they need to be in the overall model
   * so that the Washers processor can delegate them to Dryers processor itself,
   * so they can be nested.
   * 
   * @var array
   */
  private $unprocessedGroups = [
    'maytag' => [
      'SC_Laundry_Laundry_Appliances_Dryers',
    ],
    'kitchenaid' => [],
    'whirlpool' => [
      'SC_Laundry_Laundry_Dryers',
    ],
  ];

  public function __construct(FeedModelBuilderInterface $feedModelBuilder) {
    $this->feedModelBuilder = $feedModelBuilder;
  }

  /**
   * Generate the JSON to store and serve for a a given brand and locale.
   * 
   * @param string $brand
   * @return string JSON
   */
  public function build($brand, $locale) {
    if (!isset($this->feedModelCache[$brand])) {
      $catalogGroupsFilter = array_merge(array_keys($this->catalogGroupsConfig[$brand]), $this->unprocessedGroups[$brand]);
      $this->feedModelCache[$brand] = $this->feedModelBuilder->buildFeedModel($brand, $catalogGroupsFilter);
    }
    $entries = $this->feedModelCache[$brand];

    $outputData = [];

    foreach ($entries as $entry) {
      if (!$entry->isTopLevel()) {
        // Only process parent entries
        continue;
      }

      $allCatalogGroups = $entry->getAllCatalogGroups();
      // Loop through all group IDs the product is in, from most to least specific
      foreach ($allCatalogGroups as $catalogGroup) {
        $groupId = (string) $catalogGroup->identifier;
        if (isset($this->catalogGroupsConfig[$brand][$groupId])) {
          // On finding a group ID that matches one for which we have a processor
          // class, run it through the processor.
          $catalogEntryProcessor = ServiceLocator::catalogEntryProcessor($this->catalogGroupsConfig[$brand][$groupId]);
          $catalogEntryProcessor->process($entry, $entries, $locale, $outputData);
          // Don't look for any other matches.
          break;
        }
      }
      // If no processor matches, product is ignored.
    }

    $json = json_encode(['products' => $outputData], (ServiceLocator::config()->prettyJsonFiles ? JSON_PRETTY_PRINT : 0));
    return $json;
  }

}
