<?php

namespace Rlc\Wpq;

use Rlc\Wpq\FeedEntity;

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
    'SC_Laundry_Laundry_Appliances_Laundry_Pairs',
    'SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers',
    'SC_Kitchen_Refrigeration_Refrigerators',
  ];

  public function __construct(FeedModelBuilderInterface $feedModelBuilder) {
    $this->feedModelBuilder = $feedModelBuilder;
  }

  /**
   * 
   * @param string $brand
   * @return string JSON
   */
  public function build($brand, $locale) {
    if (!isset($this->feedModelCache[$brand])) {
      $this->feedModelCache[$brand] = $this->feedModelBuilder->buildFeedModel($brand, $this->includeOnlyGroups);
    }
    $topLevelEntries = $this->feedModelCache[$brand];

    /*
     * From here on is code that could actually be used for production,
     * not just for testing...
     */

    $outputData = [];
    foreach ($topLevelEntries as $entry) {
      $newOutputData = [
        'sku' => (string) $entry->partnumber,
      ];

      $this->attachGroupData($newOutputData, $entry, $locale);
      $this->attachCatalogEntryDescriptionData($newOutputData, $entry, $locale);

      $childEntries = $entry->getChildEntries();
      foreach ($childEntries as $childEntry) {
        $this->attachChildEntryData($newOutputData, $childEntry, $locale);
      }

      $this->attachFeatureData($newOutputData, $entry, $locale);

      $outputData[] = $newOutputData;
    }
//    
    // Just testing output
//    ini_set('xdebug.var_display_max_depth', 5);
//    var_dump($catalogEntries['MEW6527DDQ']->getParentEntry());
//    die;

    $json = json_encode($outputData, JSON_PRETTY_PRINT);
//    die($json);
    return $json;
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

  private function attachChildEntryData(array &$data,
      FeedEntity\CatalogEntry $childEntry, $locale) {
    $variantPartNumber = (string) $childEntry->partnumber;

    $colourDa = $childEntry->getDefiningAttributeValue('Color');
    $newColoursElem = [
      'sku' => $variantPartNumber,
      'colourCode' => (string) $colourDa->valueidentifier,
    ];
    $newColoursElem['colourName'] = (string) $colourDa->getRecord($locale)->value;

    /*
     * Attach price info
     */
    $prices = $childEntry->getPrices();
    foreach ($prices as $price) {
      $newColoursElem['prices'][$price->currency] = [
        'list' => $price->listprice,
        'sale' => $price->saleprice,
      ];
    }

    $data['colours'][] = $newColoursElem;
  }

  private function attachCatalogEntryDescriptionData(array &$data,
      FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $localeRecord = $description->getRecord($locale);
    $data['name'] = (string) $localeRecord->name;
    $data['description'] = (string) $localeRecord->londescription;
  }

  /**
   * @todo use strategy pattern or something instead of switch
   * 
   * @param array &$data
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param string $locale
   */
  private function attachFeatureData(array &$data,
      FeedEntity\CatalogEntry $entry, $locale) {
    switch ($data['appliance']) {
      case $this->applianceGroups['SC_Kitchen_Cooking'][$locale]:
        switch ($data['type']) {
          case $this->typeGroups['SC_Kitchen_Cooking_Ranges'][$locale]:
            /*
             * Range features
             */
            // Default all to false
            $data['gas'] = false;
            $data['maxCapacity'] = false;
            $data['warmingDrawer'] = false;
            
            $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
            if ($compareFeatureGroup) {
              $fuelTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Fuel Type"]);
              if ($fuelTypeAttr && 'Gas' == $fuelTypeAttr->value) {
                $data['gas'] = true;
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
            
            
          // break intentionally omitted: all wall oven features also
          // apply to ranges.
          case $this->typeGroups['SC_Kitchen_Cooking_Wall_Ovens'][$locale]:
            /*
             * Wall Oven features
             */
            $description = $entry->getDescription(); // property retrieval will use default locale
            $data['combination'] = stripos($description->name, 'combination') !== false;
            // TODO should single just be the default, i.e. true iff double is false?
            $data['single'] = stripos($description->name, 'single') !== false;
            $data['double'] = stripos($description->name, 'double') !== false;
            $data['trueConvection'] = (
                stripos($description->name, 'evenair') !== false ||
                stripos($description->londescription, 'evenair') !== false ||
                stripos($description->name, 'true convection') !== false ||
                stripos($description->londescription, 'true convection') !== false
                );

//            foreach ($entry->getDescriptiveAttributeGroups() as $groupName => $group) {
//              foreach ($group->getDescriptiveAttributes($locale) as $attr) {
//                $data['descriptive_attrs'][$groupName][] = [
//                  'valueidentifier' => $attr->valueidentifier,
//                  'value' => $attr->value,
//                  'description' => $attr->description,
//                  'noteinfo' => $attr->noteinfo,
//                ];
//              }
//            }
            break;
        }
        break;
    }
  }

}
