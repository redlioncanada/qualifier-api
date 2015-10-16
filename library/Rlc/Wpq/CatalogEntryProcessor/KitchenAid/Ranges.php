<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

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
    $entryData['warmingDrawer'] = stripos($description->name, "Warming Drawer") !== false;


    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['aquaLift'] = false;
    $entryData['trueConvection'] = false;
    $entryData['wirelessProbe'] = false;
    $entryData['steamRack'] = false;
    $entryData['bakingDrawer'] = false;
    $entryData['evenHeat'] = false;
    $entryData['5KBTUSimmerMelt'] = false;
    $entryData['20KBTUDual'] = false;

    if ($salesFeatureGroup) {
      if (!$entryData['warmingDrawer']) {
        // If it wasn't found in title, look for SF
        $entryData['warmingDrawer'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Warming Drawer");
      }

      $entryData['aquaLift'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Aqualift\u00ae"'));
      $entryData['trueConvection'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Even-Heat\u2122 True Convection"'));
      $entryData['wirelessProbe'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Wireless Probe");
      $entryData['steamRack'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Steam Rack");
      $entryData['bakingDrawer'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Baking drawer");
      $entryData['evenHeat'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch(json_decode('"Even-Heat\u2122 Ultra Element"'));
      $entryData['5KBTUSimmerMelt'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("5K BTU Simmer/Melt Burner - Reduces to 500 BTUs");
      $entryData['20KBTUDual'] = (
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("20K BTU Professional Dual Ring Burner") ||
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"20K BTU Ultra Power\u2122 Dual-Flame Burner"'))
          );
    }

    /*
     * Compare-feature-based info
     */

    // Init all to false/null
    $entryData['5Burners'] = false;
    $entryData['6Burners'] = false;
    $entryData['capacity'] = null;
    $entryData['temperatureProbe'] = false;
    $entryData['gas'] = false;
    $entryData['electric'] = false;
    $entryData['15KBTU'] = false;

    if ($compareFeatureGroup) {
      $numElementsFeature = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Number of Cooking Element-Burners");
      $entryData['5Burners'] = 5 <= $numElementsFeature->value;
      $entryData['6Burners'] = 6 <= $numElementsFeature->value;

      $capacityAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Capacity");
      if ($capacityAttr) {
        $capacityNumbers = [];
        preg_match_all('/\d+(?:\.\d+)/', $capacityAttr->value, $capacityNumbers);
        $entryData['capacity'] = array_sum($capacityNumbers[0]); // sum of full pattern matches
      }

      $controlsSelectionsAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        "description" => "Controls",
        "valueidentifier" => "Selections",
      ]);
      if ($controlsSelectionsAttr) {
        $entryData['temperatureProbe'] = false !== stripos($controlsSelectionsAttr->value, "Temperature Probe");
      }

      $fuelTypeAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Fuel Type");
      $entryData['gas'] = in_array($fuelTypeAttr->value, ["Gas", "Dual Fuel"]);
      $entryData['electric'] = "Electric" == $fuelTypeAttr->value;

      if ($entryData['gas']) {
        // If not gas, leave this at false. BTU ratings are gas-only
        $cooktopFeatureAttrs = $compareFeatureGroup->getDescriptiveAttributes(["description" => "Cooktop Features"]);
        foreach ($cooktopFeatureAttrs as $cooktopFeatureAttr) {
          if (
              (" Element-Burner Power" === substr($cooktopFeatureAttr->valueidentifier, -21)) &&
              (" BTU" === substr($cooktopFeatureAttr->value, -4))
          ) {
            // Trim whitespace
            $elementBtus = trim($cooktopFeatureAttr->value);
            // Remove comma (thousands separator)
            $elementBtus = str_replace(',', '', $elementBtus);
            // If it's in "K" format, convert to full
            $elementBtus = preg_replace('/\b(\d+)K\b/', '${1}000', $elementBtus);
            // Convert to number: http://php.net/manual/en/language.types.string.php#language.types.string.conversion
            $elementBtus = 0 + $elementBtus;

            if ($elementBtus >= 15000) {
              // Yes, there is >=1 15K BTU burner. Set to true and stop searching.
              $entryData['15KBTU'] = true;
              break;
            }
          }
        }
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
    return 'kitchenaid';
  }

  protected function getCategory() {
    return 'Ranges';
  }

}
