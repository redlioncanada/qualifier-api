<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Dishwashers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    $entryData['placeSettings'] = rand(12, 16);

    if ($compareFeatureGroup) {
      // Decibels
      $decibelLevelAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Decibel Level"]);
      if ($decibelLevelAttr) {
        $entryData['decibels'] = $decibelLevelAttr->value;
      }
    }

    if ($salesFeatureGroup) {
      // Premium adjusters
      $premiumRackAdjustersAttr = $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Premium Rack Adjusters"]);
      $entryData['premiumAdjusters'] = (bool) $premiumRackAdjustersAttr; // it just has to exist
    }

    // FID and frontConsole
    $allCatalogGroups = $entry->getAllCatalogGroups();
    $allCatalogGroupIds = array_map(function ($grp) {
      return (string) $grp->identifier;
    }, $allCatalogGroups);
    $entryData['FID'] = in_array('SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Fully_integrated_Console', $allCatalogGroupIds);
    $entryData['frontConsole'] = in_array('SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Front_Console', $allCatalogGroupIds);

    // Add image for dishwashers
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function getBrand() {
    return 'maytag';
  }

}
