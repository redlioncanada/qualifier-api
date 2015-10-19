<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Vents extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $util = ServiceLocator::util();

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['easyConversion'] = false;
    $entryData['automaticOn'] = false;
    $entryData['warmingLamps'] = false;

    if ($salesFeatureGroup) {
      $entryData['easyConversion'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Easy In-line Conversion");
      $entryData['automaticOn'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Automatic Turn On");
      $entryData['warmingLamps'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("Warming Lamp");
    }

    /*
     * Compare-feature-based info
     */

    // Init all to false
    $entryData['islandMount'] = false;
    $entryData['wallMount'] = false;
    $entryData['underCabinet'] = false;
    $entryData['cfm'] = null;
    $entryData['exterior'] = false;
    $entryData['nonVented'] = false;
    $entryData['convertible'] = false;

    if ($compareFeatureGroup) {
      // Hood-type-related properties
      $hoodTypeAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Hood Type");
      if ($hoodTypeAttr) {
        $entryData['islandMount'] = "Island Mount" == $hoodTypeAttr->value;
        $entryData['wallMount'] = "Wall Mount" == $hoodTypeAttr->value;
        $entryData['underCabinet'] = "Under-the-Cabinet" == $hoodTypeAttr->value;
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

    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);

    // If that didn't find width in compare features, look for it in name
    if (empty($entryData['width'])) {
      $entryData['width'] = $util->getPregMatch('/\b(\d+)"\s/', $description->name, 1);
    }
  }

  protected function getBrand() {
    return 'kitchenaid';
  }

  protected function getCategory() {
    return 'Vents';
  }

}
