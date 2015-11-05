<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Hoods extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function filterEntries(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale) {
    return !in_array($entry->partnumber, [
          'UXB0600DYS-NAR', 'UXB1200DYS-NAR', 'UXI1200DYS-NAR', 'UXB1200DYS-NAR',
          'UXB0600DYS-NAR'
    ]);
  }

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $util = ServiceLocator::util();

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
        $entryData['islandMount'] = "Island Canopy" == $hoodTypeAttr->value;

        // These are mutually exclusive
        if (!$entryData['islandMount']) {
          $entryData['wallMount'] = "Wall Canopy" == $hoodTypeAttr->value;

          if (!$entryData['wallMount']) {
            $entryData['underCabinet'] = in_array($hoodTypeAttr->value, ["Under Cabinet",
              "Under-the-Cabinet"]);
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
    return 'Hoods';
  }

}
