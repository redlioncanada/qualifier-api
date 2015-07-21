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
   * Keyed first by brand, then sequential
   * 
   * @var FeedEntity\CatalogEntry[][]
   */
  private $feedModelCache = [];

  /**
   * @var array identifier => localized user-facing name
   * 
   * TODO This will actually come from some config generated by the
   * manager control panel where you select categories to include.
   */
  private $applianceGroups = [
    'SC_Kitchen_Cooking' => [
      'en_CA' => 'Cooking',
      'fr_CA' => 'Cooking',
    ],
    'SC_Laundry_Laundry_Appliances_Laundry_Pairs' => [
      'en_CA' => 'Laundry',
      'fr_CA' => 'Laundry',
    ],
    'SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers' => [
      'en_CA' => 'Dishwashers',
      'fr_CA' => 'Dishwashers',
    ],
    'SC_Kitchen_Refrigeration_Refrigerators' => [
      'en_CA' => 'Fridges',
      'fr_CA' => 'Fridges',
    ],
  ];

  /**
   * See above comment
   */
  private $typeGroups = [
    'SC_Kitchen_Cooking_Ranges' => [
      'en_CA' => 'Ranges',
      'fr_CA' => 'Ranges',
    ],
    'SC_Kitchen_Cooking_Wall_Ovens' => [
      'en_CA' => 'Ovens',
      'fr_CA' => 'Ovens',
    ],
  ];
  private $includeOnlyGroups = [
    'SC_Kitchen_Cooking_Ranges',
    'SC_Kitchen_Cooking_Wall_Ovens',
    'SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers',
    'SC_Kitchen_Refrigeration_Refrigerators',
    'SC_Laundry_Laundry_Appliances_Laundry_Pairs',
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
      $this->feedModelCache[$brand] = $this->feedModelBuilder->buildFeedModel($brand, $this->includeOnlyGroups);
    }
    $entries = $this->feedModelCache[$brand];

    $productUrls = $this->getProductUrls($brand);

    $outputData = [];
    $laundryPairs = [];
    foreach ($entries as $entry) {
      if (!$entry->isTopLevel()) {
        continue;
      }

      $newOutputData = [];

      $this->attachGroupData($newOutputData, $entry, $locale);

      if ($newOutputData['appliance'] == $this->applianceGroups['SC_Laundry_Laundry_Appliances_Laundry_Pairs'][$locale]) {
        /*
         * Special logic for building together laundry pairs
         */
        $assocParentSkus = $this->getAssocParentSkus($entry, $entries);
        foreach ($assocParentSkus as $assocParentSku) {
          // Create a key for the pair to make the combination unique, even
          // with the association in reverse (via sort())
          $vPairKey = [$entry->partnumber, $assocParentSku];
          sort($vPairKey);
          $pairKey = implode('|', $vPairKey);
          if (!isset($laundryPairs[$pairKey])) {
            if ($entry->isInGroupId('SC_Laundry_Laundry_Appliances_Washers')) {
              // Current product is washer
              $washerSku = $entry->partnumber;
              $dryerSku = $assocParentSku;
            } else {
              // Current product is dryer
              $washerSku = $assocParentSku;
              $dryerSku = $entry->partnumber;
            }
            $laundryPairs[$pairKey] = [
              'data' => $newOutputData,
              'washerSku' => $washerSku,
              'dryerSku' => $dryerSku,
            ];
          }
        }
      } else {
        /*
         * Standard logic for other, non-paired categories
         */
        $newOutputData['sku'] = $entry->partnumber;

        $this->attachCatalogEntryDescriptionData($newOutputData, $entry, $locale);

        $childEntries = $entry->getChildEntries();
        foreach ($childEntries as $childEntry) {
          $childEntryData = $this->buildChildEntryData($childEntry, $locale);
          $newOutputData['colours'][] = $childEntryData;
        }

        $this->attachFeatureData($newOutputData, $entry, $locale, $brand);

        $newOutputData['url'] = isset($productUrls[$entry->partnumber]) ? $productUrls[$entry->partnumber] : null;

        $outputData[] = $newOutputData;
      }
    }

    /*
     * Now that all laundry pairs are collected, add them to the output data
     */
    foreach ($laundryPairs as $laundryPair) {
      $newOutputData = $this->buildLaundryPairData($laundryPair, $entries, $locale, $brand);
      // Link to washer page
      $newOutputData['url'] = $productUrls[$newOutputData['washerSku']];
      $outputData[] = $newOutputData;
    }

    $json = json_encode(['products' => $outputData], (ServiceLocator::config()->prettyJsonFiles ? JSON_PRETTY_PRINT : 0));
    return $json;
  }

  /**
   * Gets associative array of parentpartnumber => URL
   * 
   * @param string $brand
   * @return void
   */
  private function getProductUrls($brand) {
    $skusToUrls = [];
    // I only have data for maytag, en_CA for now
    // TODO integrate other data
    if ('maytag' == $brand) {
      $filePath = realpath(APPLICATION_PATH . '/../data/source-xml/Maytag_product_feed_en_CA.txt');
      if (!$filePath) {
        return;
      }
      $fileHandle = fopen($filePath, 'r');
      fgetcsv($fileHandle, 0, "\t"); // Skip headers
      while ($row = fgetcsv($fileHandle, 0, "\t")) {
        // NB the file actually has >1 URL per parent sku, cause they're actually
        // for child skus, but also include parent sku. But because of the way this
        // assignment works, we'll still end up with just one URL (doesn't matter
        // which) per parent sku, which is what we want.
        $skusToUrls[$row[11]] = $row[2];
      }
    }
    return $skusToUrls;
  }

  private function getAssocParentSkus(FeedEntity\CatalogEntry $entry,
      array $allEntries) {
    $assocParentSkus = [];
    $assocChildSkus = [];
    $childSkusOfThisEntry = [];

    foreach ($entry->getChildEntries() as $childEntry) {
      $childSkusOfThisEntry[] = $childEntry->partnumber;
      $endecaPropsGroup = $childEntry->getDescriptiveAttributeGroup('EndecaProps');
      if ($endecaPropsGroup) {
        $pairIdAttrs = $endecaPropsGroup->getDescriptiveAttributes(['description' => 'PairId']);
        foreach ($pairIdAttrs as $pairIdAttr) {
          $assocChildSkus = array_merge($assocChildSkus, explode('|', $pairIdAttr->value));
        }
      }
    }

    $assocChildSkus = array_unique(array_diff($assocChildSkus, $childSkusOfThisEntry));
    foreach ($assocChildSkus as $assocChildSku) {
      if (isset($allEntries[$assocChildSku])) {
        $parentPartNumber = $allEntries[$assocChildSku]->parentpartnumber;
        if (isset($allEntries[$parentPartNumber])) {
          $assocParentSkus[] = $parentPartNumber;
        }
      }
    }

    // Make unique and get rid of empties
    $assocParentSkus = array_unique(array_filter($assocParentSkus));

    return $assocParentSkus;
  }

  private function buildLaundryPairData(array $laundryPair, array $entries,
      $locale, $brand) {
    $data = $laundryPair['data'];
    $washer = $entries[$laundryPair['washerSku']];
    $dryer = $entries[$laundryPair['dryerSku']];

    $washerDescription = $washer->getDescription()->getRecord($locale);
    $washerDescriptionDefaultLocale = $washer->getDescription()->getRecord();
    $dryerDescription = $dryer->getDescription()->getRecord($locale);
    $dryerDescriptionDefaultLocale = $dryer->getDescription()->getRecord();

    // Sku/Name/description
    $data['washerSku'] = $laundryPair['washerSku'];
    $data['dryerSku'] = $laundryPair['dryerSku'];
    $data['name'] = $washerDescription->name . ' ' . ServiceLocator::translator()->translate('and_dryer', $locale);
    $data['washerDescription'] = (string) $washerDescription->longdescription;
    $data['dryerDescription'] = (string) $dryerDescription->longdescription;

    /*
     * Combine washer/dryer colours for pairs - all members of pairs seem to
     * come in the same corresponding colours.
     */
    $washerChildEntries = $washer->getChildEntries();
    $dryerChildEntries = $dryer->getChildEntries();
    $washerColours = $dryerColoursByCode = [];
    foreach ($washerChildEntries as $childEntry) {
      $childEntryData = $this->buildChildEntryData($childEntry, $locale);
      $washerColours[] = $childEntryData;
    }
    // Index dryer colours by code so they can be plucked out in the next loop
    foreach ($dryerChildEntries as $childEntry) {
      $childEntryData = $this->buildChildEntryData($childEntry, $locale);
      $dryerColoursByCode[$childEntryData['colourCode']] = $childEntryData;
    }
    // Combine together, assuming codes will match
    $data['colours'] = [];
    foreach ($washerColours as $washerColour) {
      $dryerColour = $dryerColoursByCode[$washerColour['colourCode']];
      $newColour = [
        'name' => $washerColour['colourName'],
        'code' => $washerColour['colourCode'],
        'washerSku' => $washerColour['sku'],
        'dryerSku' => $dryerColour['sku'],
        'washerPrices' => $washerColour['prices'],
        'dryerPrices' => $dryerColour['prices'],
      ];
      $data['colours'][] = $newColour;
    }

    /*
     * Laundry features
     */
    $data['washerCapacity'] = (float) preg_replace('@^.*(\d+(?:\.\d+))\s+cu\. ft\..*$@is', '$1', $washerDescriptionDefaultLocale->name);
    $data['dryerCapacity'] = (float) preg_replace('@^.*(\d+(?:\.\d+))\s+cu\. ft\..*$@is', '$1', $dryerDescriptionDefaultLocale->name);

    // Init some values to ensure they exist
    $data['vibrationControl'] = false;
    $data['rapidWash'] = false;
    $data['stacked'] = false;
    $data['sensorDry'] = false;
    $data['rapidDry'] = false;
    $data['cycleOptions'] = 0;

    /*
     * Washer features
     */
    $washerCompareFeatureGroup = $washer->getDescriptiveAttributeGroup('CompareFeature');
    $dryerCompareFeatureGroup = $dryer->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $washer->getDescriptiveAttributeGroup('SalesFeature');
    $dryerSalesFeatureGroup = $dryer->getDescriptiveAttributeGroup('SalesFeature');

    if ($washerCompareFeatureGroup) {
      $avcAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => 'Advanced Vibration Control']);
      if ($avcAttr) {
        $data['vibrationControl'] = !in_array($avcAttr->value, ["No", "None"]);
      }

      $washerCyclesAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Wash Cycles"]);
      if ($washerCyclesAttr) {
        $data['cycleOptions'] += $washerCyclesAttr->value;
      }
      $dryerCyclesAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Cycles"]);
      if ($dryerCyclesAttr) {
        $data['cycleOptions'] += $dryerCyclesAttr->value;
      }
    }

    $data['frontLoad'] = (
        (false !== stripos($washerDescriptionDefaultLocale->name, 'front load')) ||
        (false !== stripos($washerDescriptionDefaultLocale->longdescription, 'front load'))
        );
    $data['topLoad'] = !$data['frontLoad'];

    if ($washerSalesFeatureGroup) {
      // Just has to exist
      $data['rapidWash'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Rapid Wash Cycle"]);
      $data['washerWrinkleControl'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Wrinkle Control Cycle"]);
      $data['steamEnhanced'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Steam-Enhanced Cycles"]);
    }

    /*
     * Dryer features
     */

    $data['soundGuard'] = (
        (false !== stripos($dryerDescription->name, 'soundguard')) ||
        (false !== stripos($dryerDescription->longdescription, 'soundguard'))
        );

    if ($dryerCompareFeatureGroup) {
      // Stacked is actually a feature of both, but the value we look for is under
      // the dryer attrs.
      $stackableAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Stackable"]);
      if ($stackableAttr) {
        $data['stacked'] = ("Yes" == $stackableAttr->value);
      }

      // Sensor dry
      $moistureSensorAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Moisture Sensor"]);
      if ($moistureSensorAttr) {
        $data['sensorDry'] = ("Yes" == $moistureSensorAttr->value);
      }
    }

    if ($dryerSalesFeatureGroup) {
      // Just has to exist
      $data['rapidDry'] = (bool) $dryerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Rapid Dry Cycle"]);
      $data['dryerWrinkleControl'] = (bool) $dryerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Wrinkle Control Cycle"]);
    }

    /*
     * Pair image
     */
    // Goes before image URLs in feed to make them relative to http://maytag.com
    $imageUrlPrefix = '/digitalassets';
    $galleryGroup = $washer->getDescriptiveAttributeGroup('Gallery');
    if ($galleryGroup) {
      foreach ($galleryGroup->getDescriptiveAttributes(null, $locale) as $attr) {
        // Check if we've found the right attr
        if (false === strpos($attr->image1, 'Pair_244X312_')) {
          continue;
        }
        // Split up urls and find the one of the right dimensions
        $imageUrls = explode('|', $attr->image1);
        foreach ($imageUrls as $imageUrl) {
          if (false !== strpos($imageUrl, 'Pair_244X312_')) {
            $data['image'] = $imageUrlPrefix . $imageUrl;
            break 2;
          }
        }
      }
    }
    // If still not set, use no image image
    if (!isset($data['image'])) {
      $data['image'] = $imageUrlPrefix . '/No Image Available/Standalone_244X312.png';
    }

    /*
     * Attach sales feature data
     */
    $data['salesFeatures'] = [];

    // Washers
    foreach ($washerSalesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $new = [
        // Check if it's a qualified feature and put in the association
        'featureKey' => $this->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, 'Laundry-Washers'),
        'top3' => ($localizedSalesFeature->valuesequence <= 3), // double check using field for this purpose - is it same as sequence?
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $data['salesFeatures'][] = $new;
    }

    // Dryers
    foreach ($dryerSalesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $new = [
        'featureKey' => $this->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, 'Laundry-Dryers'),
        'top3' => ($localizedSalesFeature->valuesequence <= 3),
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $data['salesFeatures'][] = $new;
    }

    return $data;
  }

  private function attachGroupData(array &$data, FeedEntity\CatalogEntry $entry,
      $locale) {
    $allCatalogGroups = $entry->getAllCatalogGroups();
    foreach ($allCatalogGroups as $catalogGroup) {
      $groupId = (string) $catalogGroup->identifier;
      if (isset($this->applianceGroups[$groupId])) {
        $data['appliance'] = $this->applianceGroups[$groupId][$locale];
      }
      if (isset($this->typeGroups[$groupId])) {
        $data['type'] = $this->typeGroups[$groupId][$locale];
      }
    }
  }

  private function buildChildEntryData(FeedEntity\CatalogEntry $childEntry,
      $locale) {
    $variantSku = (string) $childEntry->partnumber;

    $colourDa = $childEntry->getDefiningAttributeValue('Color');
    $newColoursElem = [
      'sku' => $variantSku,
      'colourCode' => (string) $colourDa->valueidentifier,
    ];
    $newColoursElem['colourName'] = (string) $colourDa->getRecord($locale)->value;

    /*
     * Attach price info
     */
    $prices = $childEntry->getPrices();
    foreach ($prices as $price) {
      $newColoursElem['prices'][$price->currency] = $price->listprice;
    }

    return $newColoursElem;
  }

  private function attachCatalogEntryDescriptionData(array &$data,
      FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $localeRecord = $description->getRecord($locale);
    $data['name'] = (string) $localeRecord->name;
    $data['description'] = (string) $localeRecord->longdescription;
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
      case $this->applianceGroups['SC_Kitchen_Cooking'][$locale]:
        switch ($data['type']) {
          case $this->typeGroups['SC_Kitchen_Cooking_Ranges'][$locale]:
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

            if ($salesFeatureGroup) {
              $powerBurnerSearchString = json_decode('"Power\u2122 burner"');
              if (stripos($description->longdescription, $powerBurnerSearchString) !== false) {
                $data['powerBurner'] = true;
              } else {
                $allAttrs = $salesFeatureGroup->getDescriptiveAttributes(null, $locale);
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

          case $this->typeGroups['SC_Kitchen_Cooking_Wall_Ovens'][$locale]:
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

      case $this->applianceGroups['SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers'][$locale]:
        /*
         * Dishwasher features
         */
        $data['placeSettings'] = rand(12, 16);

        if ($compareFeatureGroup) {
          // Decibels
          $decibelLevelAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Decibel Level"]);
          if ($decibelLevelAttr) {
            $data['decibels'] = $decibelLevelAttr->value;
          }
        }

        if ($salesFeatureGroup) {
          // Premium adjusters
          $premiumRackAdjustersAttr = $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Premium Rack Adjusters"]);
          $data['premiumAdjusters'] = (bool) $premiumRackAdjustersAttr; // it just has to exist
        }

        // FID and frontConsole
        $allCatalogGroups = $entry->getAllCatalogGroups();
        $allCatalogGroupIds = array_map(function ($grp) {
          return (string) $grp->identifier;
        }, $allCatalogGroups);
        $data['FID'] = in_array('SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Fully_integrated_Console', $allCatalogGroupIds);
        $data['frontConsole'] = in_array('SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Front_Console', $allCatalogGroupIds);

        // Add image for dishwashers
        $data['image'] = $imageUrlPrefix . $entry->fullimage;

        break;
      case $this->applianceGroups['SC_Kitchen_Refrigeration_Refrigerators'][$locale]:
        /*
         * Fridge features
         */
        // Init these to false
        $data['powerCold'] = false;
        $data['topMount'] = false;
        $data['bottomMount'] = false;
        $data['frenchDoor'] = false;
        $sideBySide = false; // Not part of response, but part of logic
        $data['indoorDispenser'] = false;
        $data['factoryInstalledIceMaker'] = false;

        $data['counterDepth'] = (
            stripos($description->name, 'counter depth') !== false ||
            stripos($description->longdescription, 'counter depth') !== false
            );

        if ($compareFeatureGroup) {
          // Capacity
          $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Total Capacity"]);
          if ($capacityAttr) {
            $data['capacity'] = (float) preg_replace('/^(\d+(?:\.\d+)?).*$/', '$1', $capacityAttr->value);
          }

          // top/bottom mount, french door
          $fridgeTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Refrigerator Type"]);
          if ($fridgeTypeAttr) {
            if ("Top Mount" == $fridgeTypeAttr->value) {
              $data['topMount'] = true;
            } elseif ("French Door" == $fridgeTypeAttr->value) {
              $data['frenchDoor'] = true;
            } elseif ("Side-by-Side" == $fridgeTypeAttr->value) {
              $sideBySide = true;
            }
          }
          $data['bottomMount'] = !($data['topMount'] || $data['frenchDoor'] || $sideBySide);

          // In-door dispenser
          $dispenserTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Dispenser Type"]);
          if ($dispenserTypeAttr) {
            $data['indoorDispenser'] = ('No Dispenser' != $dispenserTypeAttr->value);
          }

          // temp-control pantry
          $tempControlDrawersAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Temperature-Controlled Drawers"]);
          if ($tempControlDrawersAttr) {
            $data['tempControlPantry'] = ('No' != $tempControlDrawersAttr->value);
          }
        }

        if ($salesFeatureGroup) {
          // These just have to exist
          $data['powerCold'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => json_decode('"PowerCold\u2122 Feature"')]);
          $data['freshFlowProducePreserver'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => json_decode('"FreshFlow\u2122 produce preserver"')]);
          $data['dualCool'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => json_decode('"Dual Cool\u00ae Evaporators"')]);
          $data['factoryInstalledIceMaker'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Factory-Installed Ice Maker"]);
        }

        // Add image for fridges
        $data['image'] = $imageUrlPrefix . $entry->fullimage;

        break;
    }

    /*
     * Use the same method of extracting physical dimensions for all these
     * categories
     */
    if (in_array($data['appliance'], [
          $this->applianceGroups['SC_Kitchen_Cooking'][$locale],
          $this->applianceGroups['SC_Kitchen_Refrigeration_Refrigerators'][$locale]
        ]) &&
        $compareFeatureGroup) {
      // Width
      $widthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Width",
      ]);
      if ($widthAttr) {
        $data['width'] = $this->formatPhysicalDimension($widthAttr->value);
      }

      // Height
      $heightAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Height",
      ]);
      if ($heightAttr) {
        $data['height'] = $this->formatPhysicalDimension($heightAttr->value);
      }

      // Depth
      $depthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Depth",
      ]);
      if ($depthAttr) {
        $data['depth'] = $this->formatPhysicalDimension($depthAttr->value);
      }
    }

    /*
     * Attach sales feature data
     */
    $data['salesFeatures'] = [];
    foreach ($salesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $new = [
        // Check if it's a qualified feature and put in the association
        'featureKey' => $this->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, $data['appliance']),
        'top3' => ($localizedSalesFeature->valuesequence <= 3), // double check using field for this purpose - is it same as sequence?
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $data['salesFeatures'][] = $new;
    }
  }

  private function getFeatureKeyForSalesFeature($localizedSalesFeature, $brand,
      $category) {
    $result = null;
    $salesFeatureAssocs = ServiceLocator::salesFeatureAssocs()[$brand][$category];
    foreach ($salesFeatureAssocs as $valueidentifier => $featureKey) {
      if ('/' === $valueidentifier[0]) {
        // Regex
        if (preg_match($valueidentifier, $localizedSalesFeature->valueidentifier)) {
          $result = $featureKey;
          break;
        }
      } else {
        if ($valueidentifier == $localizedSalesFeature->valueidentifier) {
          $result = $featureKey;
          break;
        }
      }
    }
    return $result;
  }

  /**
   * If dimension is expressed as fraction, convert to decimal
   * 
   * @param string $dim
   * @return double
   */
  private function formatPhysicalDimension($dim) {
    $matches = [];
    if (preg_match('@(\d+)\s+(\d+)/(\d+)@', $dim, $matches)) {
      $wholeNumber = $matches[1];
      $numerator = $matches[2];
      $denominator = $matches[3];
      $decimal = $numerator / $denominator;
      $result = $wholeNumber + $decimal;
    } else {
      // Not in fraction format
      $result = $dim;
    }
    return $result;
  }

  /**
   * @param array $source Must be a sequential array
   * @return mixed
   */
  private function getRandomElement(array $source) {
    return $source[rand(0, count($source) - 1)];
  }

}
