<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Fridges extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function filterEntries(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale) {
    $allCatalogGroups = $entry->getAllCatalogGroups();
    $allCatalogGroupIds = array_map(function ($grp) {
      return (string) $grp->identifier;
    }, $allCatalogGroups);
    
    $excludeGroupIds = [
      // Filter out the non-refrigerators
      'SC_Kitchen_Refrigeration_Refrigerators_Ice_Makers',
      'SC_Kitchen_Refrigeration_Refrigerators_Wine__Beverage_Center',
    ];
    
    return 0 === count(array_intersect($allCatalogGroupIds, $excludeGroupIds));
  }
  
  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription(); // property retrieval will use default locale
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $util = ServiceLocator::util();
    
    /*
     * Name/description-based info
     */

    $entryData['4or5door'] = (false !== stripos($description->name, '4-door')) ||
        (false !== stripos($description->name, "Double Drawer"));
    // If this one's false, will check again in salesfeatures
    $entryData['counterDepth'] = (bool) preg_match('@\bcounter[- ]depth\b@i', $description->name);

    // For capacity, try name/description first
    $entryData['capacity'] = $util->getPregMatch('@(\d+(?:\.\d+)?)\s+cu\. ft\.@i', $description->name, 1);
    if (is_null($entryData['capacity'])) {
      $entryData['capacity'] = $util->getPregMatch('@(\d+(?:\.\d+)?)\s+cu\. ft\.@i', $description->longdescription, 1);
    }

    /*
     * Compare-feature-based info
     */

    // Init these to false/null
    $entryData['energyStar'] = false;
    $entryData['topMount'] = false;
    $entryData['bottomMount'] = false;
    $entryData['frenchDoor'] = false;
    $entryData['sideBySide'] = false; // Part of response for WP
    $entryData['filtered'] = false;
    $entryData['exteriorWater'] = false;
    $entryData['exteriorIce'] = false;
    $entryData['factoryInstalledIce'] = false;

    if ($compareFeatureGroup) {
      // These just have to exist
      $entryData['energyStar'] = $compareFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Energy Star\u00ae Qualified"'));

      // If capacity wasn't found in name/description, try for CF
      if (is_null($entryData['capacity'])) {
        $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Total Capacity Cu. Ft."]);
        if ($capacityAttr) {
          $entryData['capacity'] = (float) preg_replace('/^(\d+(?:\.\d+)?).*$/', '$1', $capacityAttr->value);
        }
      }

      // top/bottom mount, french door
      $fridgeTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Refrigerator Type"]);
      if ($fridgeTypeAttr) {
        if ("Top Mount" == $fridgeTypeAttr->value) {
          $entryData['topMount'] = true;
        } elseif ("French Door" == $fridgeTypeAttr->value) {
          $entryData['frenchDoor'] = true;
        } elseif ("Side-by-Side" == $fridgeTypeAttr->value) {
          $entryData['sideBySide'] = true;
        }
      }
      $entryData['bottomMount'] = !($entryData['topMount'] || $entryData['frenchDoor'] || $entryData['sideBySide']);

      // filtered
      $filteredAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Filtered Water"]);
      if ($filteredAttr) {
        $entryData['filtered'] = ('Yes' == $filteredAttr->value);
      }

      $iceMakerAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Ice Maker");
      if ($iceMakerAttr) {
        $entryData['factoryInstalledIce'] = (false !== stripos($iceMakerAttr->value, 'factory installed'));
      }

      /*
       * Dispenser-related info
       */

      $dispenserTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Dispenser Type"]);
      if ($dispenserTypeAttr) {
        $entryData['exteriorWater'] = (false !== stripos($dispenserTypeAttr->value, 'exterior')) &&
            (false !== stripos($dispenserTypeAttr->value, 'water'));
        $entryData['exteriorIce'] = (false !== stripos($dispenserTypeAttr->value, 'exterior')) &&
            (false !== stripos($dispenserTypeAttr->value, 'ice'));
      }
    }

    /*
     * Sales-feature-based info
     */

    // Init these to false
    $entryData['freshFlowProducePreserver'] = false;
    $entryData['freshStor'] = false;
    $entryData['accuChill'] = false;
    $entryData['accuFresh'] = false;
    $entryData['tripleCrisper'] = false;

    if ($salesFeatureGroup) {
      // If energyStar wasn't found in comparefeatures, try here (yes, it has
      // a different case in the salesfeature group - although since a new
      // change, this func ignores case when scanning)
      if (!$entryData['energyStar']) {
        $entryData['energyStar'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ENERGY STAR\u00ae Qualified"'));
      }
      // If counter depth wasn't present in name, look for salesfeature
      if (!$entryData['counterDepth']) {
        $entryData['counterDepth'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("counter depth styling");
      }

      // These just have to exist
      $entryData['freshFlowProducePreserver'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => json_decode('"FreshFlow\u2122 Produce Preserver"')]);
      $entryData['freshStor'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"FreshStor\u2122 Refrigerated Drawer"'));
      $entryData['accuChill'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Accu-Chill\u2122 Temperature Management System"'));
      $entryData['accuFresh'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"AccuFresh\u2122 dual cooling system"'));
      $entryData['tripleCrisper'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("Triple Crisper system");
    }

    // Add image for fridges
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    // Add info that just depends on other info
    $entryData['standardDepth'] = !$entryData['counterDepth'];

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Fridges';
  }

}
