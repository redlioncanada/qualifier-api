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
    $data['powerCold'] = false;
    $data['topMount'] = false;
    $data['bottomMount'] = false;
    $data['frenchDoor'] = false;
    $sideBySide = false; // Not part of response, but part of logic
    $data['indoorDispenser'] = false;
    $data['factoryInstalledIceMaker'] = false;
    $data['tempControlPantry'] = false;

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
      $data['factoryInstalledIceMaker'] = (
          $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Factory-Installed Ice Maker"]) ||
          $salesFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Factory Installed Ice Maker"])
          );
    }

    // Add image for fridges
    $data['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function getBrand() {
    return 'maytag';
  }

}
