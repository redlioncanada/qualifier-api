<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Fridges extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription(); // property retrieval will use default locale
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    // Init these to false
    $entryData['powerCold'] = false;
    $entryData['topMount'] = false;
    $entryData['bottomMount'] = false;
    $entryData['frenchDoor'] = false;
    $sideBySide = false; // Not part of response, but part of logic
    $entryData['indoorDispenser'] = false;
    $entryData['factoryInstalledIceMaker'] = false;
    $entryData['tempControlPantry'] = false;

    $entryData['counterDepth'] = (
        stripos($description->name, 'counter depth') !== false ||
        stripos($description->longdescription, 'counter depth') !== false
        );

    if ($compareFeatureGroup) {
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

      // In-door dispenser
      $dispenserTypeAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Dispenser Type"]);
      if ($dispenserTypeAttr) {
        $entryData['indoorDispenser'] = ('No Dispenser' != $dispenserTypeAttr->value);
      }

      // temp-control pantry
      $tempControlDrawersAttr = $compareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Temperature-Controlled Drawers"]);
      if ($tempControlDrawersAttr) {
        $entryData['tempControlPantry'] = ('No' != $tempControlDrawersAttr->value);
      }
    }

    if ($salesFeatureGroup) {
      // These just have to exist
      $entryData['powerCold'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => json_decode('"PowerCold\u2122 Feature"')]);
      $entryData['freshFlowProducePreserver'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => json_decode('"FreshFlow\u2122 produce preserver"')]);
      $entryData['dualCool'] = (bool) $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => json_decode('"Dual Cool\u00ae Evaporators"')]);
      $entryData['factoryInstalledIceMaker'] = (
          $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Factory-Installed Ice Maker"]) ||
          $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Factory Installed Ice Maker"])
          );
    }

    // Add image for fridges
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
    
    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'maytag';
  }

  protected function getCategory() {
    return 'Fridges';
  }

}
