<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Ranges extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['double'] = stripos($description->name, 'double') !== false;
    $entryData['single'] = !$entryData['double'];
    $entryData['warmingDrawer'] = stripos($description->name, "Warming Drawer") !== false;
    $entryData['induction'] = (stripos($description->name, "induction") !== false) ||
        (stripos($description->longdescription, "induction") !== false);
    $entryData['aquaLift'] = (stripos($description->name, "aqualift") !== false) ||
        (stripos($description->longdescription, "aqualift") !== false);
    $entryData['trueConvection'] = (stripos($description->name, "true convection") !== false) ||
        (stripos($description->longdescription, "true convection") !== false);

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['accuBake'] = false;
    $entryData['rapidPreHeat'] = false;
    $entryData['maxCapacity'] = false;
    $entryData['frozenBake'] = false;

    if ($salesFeatureGroup) {
      $entryData['accuBake'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"AccuBake\u00ae Temperature Management System"'));
      $entryData['rapidPreHeat'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Rapid Preheat");
      $entryData['maxCapacity'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Max Capacity Recessed Rack");
      $entryData['frozenBake'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Frozen Bake\u2122 Technology"'));

      if (!$entryData['warmingDrawer']) {
        // If it wasn't found in title, look for SF
        $entryData['warmingDrawer'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Warming Drawer");
      }

      if (!$entryData['aquaLift']) {
        // Try here if not found in name/descr
        $entryData['aquaLift'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"AquaLift\u00ae Self-Clean technology"'));
      }

      if (!$entryData['trueConvection']) {
        // Try here if not found in name/descr
        $entryData['trueConvection'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("true convection");
      }
    }

    /*
     * Compare-feature-based info
     */

    // Init all to false/null
    $entryData['capacity'] = null;
    $entryData['gas'] = false;
    $entryData['electric'] = false;
    $entryData['frontControl'] = false;
    $entryData['rearControl'] = false;

    if ($compareFeatureGroup) {
      $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Capacity");
      if ($capacityAttr) {
        $capacityNumbers = [];
        preg_match_all('/\d+(?:\.\d+)/', $capacityAttr->value, $capacityNumbers);
        $entryData['capacity'] = array_sum($capacityNumbers[0]); // sum of full pattern matches
      }

      $fuelTypeAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Fuel Type");
      $entryData['gas'] = in_array($fuelTypeAttr->value, ["Gas", "Dual Fuel"]);
      $entryData['electric'] = "Electric" == $fuelTypeAttr->value;

      // Already tried to use SF
      if (!$entryData['maxCapacity']) {
        $ovenRackTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Oven Rack Type"]);
        if ($ovenRackTypeAttr && stripos($ovenRackTypeAttr->value, 'max capacity') !== false) {
          $entryData['maxCapacity'] = true;
        }
      }

      $rangeTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Range Type"]);
      if ($rangeTypeAttr) {
        $entryData['frontControl'] = false !== stripos($rangeTypeAttr->value, 'slide-in');
        $entryData['rearControl'] = false !== stripos($rangeTypeAttr->value, 'freestanding');
      }
    }

    /*
     * Other
     */

    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Ranges';
  }

}
