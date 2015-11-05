<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Dishwashers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['targetClean'] = (false !== stripos($description->name, "TargetClean")) ||
        (false !== stripos($description->longdescription, "TargetClean"));
    $entryData['compactTallTub'] = false !== stripos($description->name, "Compact Tall Tub");

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['totalCoverageArm'] = false;
    $entryData['sensorCycle'] = false;
    $entryData['ez2Lift'] = false;
    $entryData['silverwareSpray'] = false;
    $entryData['accuSense'] = false;
    $entryData['anyWarePlusBasket'] = false;

    if ($salesFeatureGroup) {
      $entryData['totalCoverageArm'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("TotalCoverage Spray Arm");
      $entryData['sensorCycle'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Sensor Cycle");
      $entryData['ez2Lift'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"EZ-2-Lift\u2122 Adjustable Upper Rack"'));
      $entryData['silverwareSpray'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Silverware Spray");
      $entryData['accuSense'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"AccuSense\u00ae Soil Sensor"'));
      $entryData['anyWarePlusBasket'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"AnyWare\u2122 Plus Silverware Basket"'));
    }

    /*
     * Compare-feature-based info
     */

    // Init these to null
    $entryData['decibels'] = null;
    $entryData['placeSettings'] = null;

    if ($compareFeatureGroup) {
      // Decibels
      $decibelsAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Decibel Level");
      if ($decibelsAttr) {
        $entryData['decibels'] = $decibelsAttr->value;
      }

      // Place settings
      $placeSettings = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Capacity");
      if ($placeSettings) {
        $placeSettingsMatches = [];
        preg_match('/\d+(?:\.\d+)?/i', $placeSettings->value, $placeSettingsMatches);
        if (count($placeSettingsMatches)) {
          $entryData['placeSettings'] = $placeSettingsMatches[0];
        }
      }
    }

    /*
     * Catalog group based info
     */

    $allCatalogGroups = $entry->getAllCatalogGroups();
    $allCatalogGroupIds = array_map(function ($grp) {
      return (string) $grp->identifier;
    }, $allCatalogGroups);
    $entryData['FIC'] = in_array('SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Hidden_Control_Console', $allCatalogGroupIds);
    $entryData['FCC'] = in_array('SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Visible_Front_Console', $allCatalogGroupIds);

    // Add image for dishwashers
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Dishwashers';
  }

}
