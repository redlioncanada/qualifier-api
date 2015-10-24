<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

class Ranges extends CookingAppliances {

  protected function attachFeatureData(array &$entryData,
      \Rlc\Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    
    // First attach common cooking features
    parent::attachFeatureData($entryData, $entry, $locale);

    $description = $entry->getDescription(); // property retrieval will use default locale
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');

    // Default all booleans to false
    $entryData['gas'] = false;
    $entryData['electric'] = false;
    $entryData['maxCapacity'] = false;
    $entryData['warmingDrawer'] = false;
    $entryData['powerBurner'] = false;
    $entryData['frontControl'] = false;
    $entryData['rearControl'] = false;

    if ($compareFeatureGroup) {
      $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Capacity (cu. ft.)"]);
      if ($capacityAttr) {
        $capacityNumbers = [];
        preg_match_all('/\d+(?:\.\d+)/', $capacityAttr->value, $capacityNumbers);
        $entryData['capacity'] = array_sum($capacityNumbers[0]); // sum of full pattern matches
      }

      $fuelTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Fuel Type"]);
      if ($fuelTypeAttr) {
        switch ($fuelTypeAttr->value) {
          case 'Gas':
            $entryData['gas'] = true;
            break;
          case 'Electric':
            $entryData['electric'] = true;
            break;
        }
      }

      $ovenRackTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Oven Rack Type"]);
      if ($ovenRackTypeAttr && stripos($ovenRackTypeAttr->value, 'max capacity') !== false) {
        $entryData['maxCapacity'] = true;
      }

      $drawerTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Drawer Type"]);
      if ($drawerTypeAttr && 'Warming Drawer' == $drawerTypeAttr->value) {
        $entryData['warmingDrawer'] = true;
      }

      $rangeTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Range Type"]);
      if ($rangeTypeAttr) {
        $entryData['frontControl'] = ('Slide-in' == $rangeTypeAttr->value);
        $entryData['rearControl'] = ('Freestanding' == $rangeTypeAttr->value);
      }
    }

    $powerBurnerSearchString = json_decode('"Power\u2122 burner"');
    if (stripos($description->longdescription, $powerBurnerSearchString) !== false) {
      $entryData['powerBurner'] = true;
    } else {
      if ($salesFeatureGroup) {
        $allAttrs = $salesFeatureGroup->getDescriptiveAttributes(null);
        foreach ($allAttrs as $attr) {
          // Look for an attribute where valueidentifier _contains_
          // search string, ignoring case
          if (stripos($attr->valueidentifier, $powerBurnerSearchString) !== false) {
            $entryData['powerBurner'] = true;
            break;
          }
        }
      }
    }
  }

  protected function getType() {
    return 'Ranges';
  }

}
