<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Hoods extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $util = ServiceLocator::util();

    // Override appliance string and set type
    $entryData['appliance'] = "Cooking";
    $entryData['type'] = "Hoods";

    /*
     * Compare-feature-based info
     */

    // Init all to false
    $entryData['islandMount'] = false;
    $entryData['wallMount'] = false;
    $entryData['underCabinet'] = false;
    $entryData['customHoodLiner'] = false;
    $entryData['inLineBlower'] = false;
    $entryData['cfm'] = null;
    $entryData['exterior'] = false;
    $entryData['nonVented'] = false;
    $entryData['convertible'] = false;

    if ($compareFeatureGroup) {
      // Hood-type-related properties
      $hoodTypeAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Hood Type");
      if ($hoodTypeAttr) {
        $entryData['islandMount'] = "Island Canopy" == $hoodTypeAttr->value;

        // These are mutually exclusive
        if (!$entryData['islandMount']) {
          $entryData['wallMount'] = "Wall Canopy" == $hoodTypeAttr->value;

          if (!$entryData['wallMount']) {
            $entryData['underCabinet'] = in_array($hoodTypeAttr->value, ["Under Cabinet",
              "Under-the-Cabinet"]);

            if (!$entryData['underCabinet']) {
              $entryData['customHoodLiner'] = "Custom Hood Liners" == $hoodTypeAttr->value;

              if (!$entryData['customHoodLiner']) {
                $entryData['inLineBlower'] = "In-Line Blower" == $hoodTypeAttr->value;
              }
            }
          }
        }
      }

      // Fan CFM
      $cfmAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Fan CFM");
      if ($cfmAttr) {
        $entryData['cfm'] = $cfmAttr->value;
      }

      // Venting-type-related properties
      $ventingTypeAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Venting Type");
      if ($ventingTypeAttr) {
        $entryData['exterior'] = false !== stripos($ventingTypeAttr->value, "exterior");
        $entryData['nonVented'] = false !== stripos($ventingTypeAttr->value, "recirculating");
        $entryData['convertible'] = "Exterior or Recirculating" == $ventingTypeAttr->value;
      }
    }

    /*
     * Other
     */

    if (is_null($entryData['cfm'])) {
      // If no CompareFeature, try in description
      $entryData['cfm'] = $util->getPregMatch('/(\d+)[\s-]CFM/i', $description->longdescription, 1);
    }
    
    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);

    // If that didn't find width in compare features, look for it in name
    if (empty($entryData['width'])) {
      $entryData['width'] = $util->getPregMatch('/\b(\d+)"\s/', $description->name, 1);
    }
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Cooking-Hoods';
  }

}
