<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Fridges extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {


foreach ($entry->getDescriptiveAttributeGroups() as $grpName => $grp) {
  if (in_array($grpName, ['Endeca', 'EndecaProps'])) {
    continue;
  }
  foreach ($grp->getDescriptiveAttributes() as $attr) {
    $entryData['descr-attrs'][$grpName][] = [
      'description' => $attr->description,
      'valueidentifier' => $attr->valueidentifier,
      'value' => $attr->value,
      'noteinfo' => $attr->noteinfo,
    ];
  }
}



return;



    $description = $entry->getDescription(); // property retrieval will use default locale
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info
     */

    // If this one's false, will check again in salesfeatures
    $entryData['builtIn'] = (bool) preg_match('@\bbuilt[- ]in\b@i', $description->name);
    $entryData['counterDepth'] = $entryData['builtIn'] ? false : (bool) preg_match('@\bcounter[- ]depth\b@i', $description->name);

    /*
     * Compare-feature-based info
     */

    // Init these to false
    $entryData['energyStar'] = false; // I think this is actually true for all anyway
    $entryData['topMount'] = false;
    $entryData['bottomMount'] = false;
    $entryData['frenchDoor'] = false;
    $sideBySide = false; // Not part of response, but part of logic
    $entryData['indoorDispenser'] = false;
    $entryData['filtered'] = false;
    $entryData['exteriorDispenser'] = false;
    $entryData['indoorIce'] = false;

    if ($compareFeatureGroup) {
      // These just have to exist
      $entryData['energyStar'] = $compareFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Energy Star\u00ae Qualified"'));

      // Capacity
      $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Total Capacity"]);
      if ($capacityAttr) {
        $entryData['capacity'] = (float) preg_replace('/^(\d+(?:\.\d+)?).*$/', '$1', $capacityAttr->value);
      }

      // top/bottom mount, french door
      $fridgeTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Refrigerator Type"]);
      if ($fridgeTypeAttr) {
        if ("Top Mount" == $fridgeTypeAttr->value) {
          $entryData['topMount'] = true;
        } elseif ("French Door" == $fridgeTypeAttr->value) {
          $entryData['frenchDoor'] = true;
        } elseif ("Side-by-Side" == $fridgeTypeAttr->value) {
          $sideBySide = true;
        }
      }
      $entryData['bottomMount'] = !($entryData['topMount'] || $entryData['frenchDoor'] || $sideBySide);

      // filtered
      $filteredAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Filtered Water"]);
      if ($filteredAttr) {
        $entryData['filtered'] = ('Yes' == $filteredAttr->value);
      }

      /*
       * Dispenser-related info
       */

      $dispenserTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Dispenser Type"]);
      if ($dispenserTypeAttr) {
        // In-door dispenser
        $entryData['indoorDispenser'] = ('No Dispenser' != $dispenserTypeAttr->value);
        // Exterior dispenser
        $entryData['exteriorDispenser'] = (false !== stripos($dispenserTypeAttr->value, 'exterior'));
        fwrite(STDOUT, $dispenserTypeAttr->value . "," . var_export(stripos($dispenserTypeAttr->value, 'exterior'), true) . "\n");
        // In-door ice
        $entryData['indoorIce'] = (false !== stripos($dispenserTypeAttr->value, 'ice'));
      }
    }

    /*
     * Sales-feature-based info
     */

    // Init these to false
    $entryData['5door'] = false;
    $entryData['producePreserver'] = false;
    $entryData['extendFresh'] = false;
    $entryData['extendFreshPlus'] = false;
    $entryData['freshChill'] = false;
    $entryData['preservaCare'] = false;
    $entryData['maxCool'] = false;

    if ($salesFeatureGroup) {
      // If energyStar wasn't found in comparefeatures, try here (yes, it has
      // a different case in the salesfeature group)
      if (!$entryData['energyStar']) {
        $entryData['energyStar'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ENERGY STAR\u00ae Qualified"'));
      }
      // If not built-in and counter depth wasn't present in name, look for salesfeature
      if (!$entryData['builtIn'] && !$entryData['counterDepth']) {
        $entryData['counterDepth'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Counter-depth");
      }

      // These just have to exist
      $entryData['5door'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("5-Door Configuration");
      $entryData['producePreserver'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Produce Preserver");
      $entryData['extendFresh'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ExtendFresh\u2122 Temperature Management System"'));
      $entryData['extendFreshPlus'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ExtendFresh\u2122 Plus Temperature Management System"'));
      $entryData['freshChill'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"FreshChill\u2122 Temperature-Controlled Full-Width Pantry"'));
      $entryData['preservaCare'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Preserva\u00ae Food Care System"'));
      $entryData['maxCool'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Max Cool");
    }

    // Add image for fridges
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    // Add info that just depends on other info
    $entryData['standardDepth'] = !($entryData['counterDepth'] || $entryData['builtIn']);

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'kitchenaid';
  }

  protected function getCategory() {
    return 'Fridges';
  }

}
