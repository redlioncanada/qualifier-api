<?php

namespace Rlc\Wpq;

use Rlc\Wpq\FeedEntity,
    Lrr\ServiceLocator,
    Rlc\Wpq\Util;

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
   * @var array
   */
  private $catalogGroupsConfig = [
    'maytag' => [
      // The strat class for these first two will return the same 'appliance'
      // field value but diff 'type' field values. Meanwhile, other strat
      // classes will return null for 'type'.
//      'SC_Kitchen_Cooking_Ranges' => 'Maytag\\Ranges',
//      'SC_Kitchen_Cooking_Wall_Ovens' => 'Maytag\\WallOvens',
//      'SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers' => 'Maytag\\Dishwashers',
      'SC_Kitchen_Refrigeration_Refrigerators' => 'Maytag\\Fridges',
//      'SC_Laundry_Laundry_Appliances_Laundry_Pairs' => 'Maytag\\LaundryPairs',
    ],
    'whirlpool' => [
      'SC_Kitchen_Dishwasher__Cleaning_Dishwashers' => 'Whirlpool\\Dishwashers',
      'SC_Kitchen_Refrigeration_Refrigerators' => 'Whirlpool\\Fridges',
      'SC_Kitchen_Cooking_Ranges' => 'Whirlpool\\Ranges',
      'SC_Kitchen_Cooking_Wall_Ovens' => 'Whirlpool\\WallOvens',
      'SC_Kitchen_Cooking_Cooktops' => 'Whirlpool\\Cooktops',
      'SC_Kitchen_Cooking_Hoods' => 'Whirlpool\\Hoods',
      'SC_Laundry_Laundry_Laundry_Pairs' => 'Whirlpool\\LaundryPairs',
    ],
    'kitchenaid' => [
      'SC_Major_Appliances_Cooktops_Cooktops' => 'KitchenAid\\Cooktops',
      'SC_Major_Appliances_Ranges_Ranges' => 'KitchenAid\\Ranges',
      'SC_Major_Appliances_Hoods_and_Vents_Hoods_and_Vents' => 'KitchenAid\\HoodsVents',
      'SC_Major_Appliances_Wall_Ovens_Wall_Ovens' => 'KitchenAid\\WallOvens',
      'SC_Major_Appliances_Dishwashers_Dishwashers' => 'KitchenAid\\Dishwashers',
      'SC_Major_Appliances_Refrigerators_Refrigerators' => 'KitchenAid\\Fridges',
    ],
  ];

  /**
   * @var Util
   */
  private $util;

  public function __construct(FeedModelBuilderInterface $feedModelBuilder) {
    $this->feedModelBuilder = $feedModelBuilder;
    $this->util = ServiceLocator::util();
  }

  /**
   * Generate the JSON to store and serve for a a given brand and locale.
   * 
   * @param string $brand
   * @return string JSON
   */
  public function build($brand, $locale) {
    if (!isset($this->feedModelCache[$brand])) {
      $catalogGroupsFilter = array_keys($this->catalogGroupsConfig[$brand]);
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
      foreach ($allCatalogGroups as $catalogGroup) {
        $groupId = (string) $catalogGroup->identifier;
        if (isset($this->catalogGroupsConfig[$brand][$groupId])) {
          $catalogEntryProcessor = ServiceLocator::catalogEntryProcessor($this->catalogGroupsConfig[$brand][$groupId]);
          $catalogEntryProcessor->process($entry, $entries, $locale, $outputData);
        }
      }
    }

    $json = json_encode(['products' => $outputData], (ServiceLocator::config()->prettyJsonFiles ? JSON_PRETTY_PRINT : 0));
    return $json;
  }

  /**
   * @todo use strategy pattern or something instead of switch
   * 
   * @param array &$data
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param string $locale
   * @param string $brand
   */
  private function attachFeatureData(array &$data,
      FeedEntity\CatalogEntry $entry, $locale, $brand) {
    // Get some stuff that's used for all/most categories
    $description = $entry->getDescription(); // property retrieval will use default locale
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');

    // Goes before image URLs in feed to make them relative to http://maytag.com
    $imageUrlPrefix = '/digitalassets';

    switch ($data['appliance']) {
      case $this->catalogGroupsConfig[$brand]['SC_Kitchen_Cooking']: // arr, but the ids are different anyway. need a smarter way.
        switch ($data['type']) {
          case $this->typeGroups[$brand]['SC_Kitchen_Cooking_Ranges']:
            /*
             * Range features
             */
            // Default all booleans to false
            $data['gas'] = false;
            $data['electric'] = false;
            $data['maxCapacity'] = false;
            $data['warmingDrawer'] = false;
            $data['powerBurner'] = false;

            if ($compareFeatureGroup) {
              $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Capacity (cu. ft.)"]);
              if ($capacityAttr) {
                $capacityNumbers = [];
                preg_match_all('/\d+(?:\.\d+)/', $capacityAttr->value, $capacityNumbers);
                $data['capacity'] = array_sum($capacityNumbers[0]); // sum of full pattern matches
              }

              $fuelTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Fuel Type"]);
              if ($fuelTypeAttr) {
                switch ($fuelTypeAttr->value) {
                  case 'Gas':
                    $data['gas'] = true;
                    break;
                  case 'Electric':
                    $data['electric'] = true;
                    break;
                }
              }

              $ovenRackTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Oven Rack Type"]);
              if ($ovenRackTypeAttr && stripos($ovenRackTypeAttr->value, 'max capacity') !== false) {
                $data['maxCapacity'] = true;
              }

              $drawerTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Drawer Type"]);
              if ($drawerTypeAttr && 'Warming Drawer' == $drawerTypeAttr->value) {
                $data['warmingDrawer'] = true;
              }
            }

            $powerBurnerSearchString = json_decode('"Power\u2122 burner"');
            if (stripos($description->longdescription, $powerBurnerSearchString) !== false) {
              $data['powerBurner'] = true;
            } else {
              if ($salesFeatureGroup) {
                $allAttrs = $salesFeatureGroup->getDescriptiveAttributes(null);
                foreach ($allAttrs as $attr) {
                  // Look for an attribute where valueidentifier _contains_
                  // search string, ignoring case
                  if (stripos($attr->valueidentifier, $powerBurnerSearchString) !== false) {
                    $data['powerBurner'] = true;
                    break;
                  }
                }
              }
            }

          // break intentionally omitted: all wall oven features also
          // apply to ranges.

          case $this->typeGroups[$brand]['SC_Kitchen_Cooking_Wall_Ovens']:
            /*
             * Wall Oven features
             */
            // Default bools to false
            $data['powerPreheat'] = false;

            $data['combination'] = stripos($description->name, 'combination') !== false;
            $data['double'] = stripos($description->name, 'double') !== false;
            $data['single'] = !$data['double'];
            $data['trueConvection'] = (
                stripos($description->name, 'evenair') !== false ||
                stripos($description->longdescription, 'evenair') !== false ||
                stripos($description->name, 'true convection') !== false ||
                stripos($description->longdescription, 'true convection') !== false
                );

            if ($salesFeatureGroup) {
              if (stripos($description->longdescription, "power preheat") !== false) {
                $data['powerPreheat'] = true;
              } else {
                $powerPreheatAttr = $salesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Power Preheat"]);
                $data['powerPreheat'] = (bool) $powerPreheatAttr;
              }
            }
            break;
        }

        // Add image for cooking
        $data['image'] = $imageUrlPrefix . $entry->fullimage;

        break;

    }

    /*
     * Use the same method of extracting physical dimensions for all these
     * categories
     */
    if (in_array($data['appliance'], [
          $this->catalogGroupsConfig['SC_Kitchen_Cooking'][$locale],
          $this->catalogGroupsConfig['SC_Kitchen_Refrigeration_Refrigerators'][$locale]
        ]) &&
        $compareFeatureGroup) {

      // Width
      $widthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Width",
      ]);
      if ($widthAttr) {
        $data['width'] = $this->util->formatPhysicalDimension($widthAttr->value);
      }

      // Height
      $heightAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Height",
      ]);
      if ($heightAttr) {
        $data['height'] = $this->util->formatPhysicalDimension($heightAttr->value);
      }

      // Depth
      $depthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Depth",
      ]);
      if ($depthAttr) {
        $data['depth'] = $this->util->formatPhysicalDimension($depthAttr->value);
      }
    }

  }

}
