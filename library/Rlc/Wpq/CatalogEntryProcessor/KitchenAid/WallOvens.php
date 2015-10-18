<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class WallOvens extends Wpq\CatalogEntryProcessor\StandardAbstract {

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
    $entryData['combination'] = stripos($description->name, 'combination') !== false;
    $entryData['single'] = !($entryData['double'] || $entryData['combination']);

    $entryData['trueConvection'] = (
        false !== stripos($description->name, "True Convection") ||
        false !== stripos($description->longdescription, "True Convection")
        );

    /*
     * Capacity (complex enough for its own section)
     */

    $entryData['capacity'] = null;

    // First try CF
    if ($compareFeatureGroup) {
      $results = $compareFeatureGroup->getDescriptiveAttributesByValueIdentifierMatch("Oven Capacity", 1);
      if (count($results)) {
        $matches = [];
        preg_match('/\d+(?:\.\d+)?/', $results[0]->value, $matches);
        if (count($matches)) {
          $decimalValue = $matches[0];
          if (!$entryData['single'] && false !== stripos($results[0]->value, 'each oven')) {
            $decimalValue *= 2;
          }
          $entryData['capacity'] = 0 + $decimalValue;
        }
      }
    }

    // Then try common SF
    if (is_null($entryData['capacity']) && $salesFeatureGroup) {
      $results = $salesFeatureGroup->getDescriptiveAttributesByValueIdentifierMatch("Cu. Ft. Capacity");
      if (count($results)) {
        $capacitySum = 0;
        foreach ($results as $result) {
          $matches = [];
          preg_match('/\d+(?:\.\d+)?/', $result->valueidentifier, $matches);
          if (count($matches)) {
            $decimalValue = $matches[0];
            if (!$entryData['single'] && false !== stripos($result->valueidentifier, 'each oven')) {
              $capacitySum = $decimalValue * 2;
              break;
            } else {
              $capacitySum += $decimalValue;
            }
          }
        }
        $entryData['capacity'] = 0 + $capacitySum;
      }
    }

    // Finally, try rare SF
    if (is_null($entryData['capacity']) && $salesFeatureGroup) {
      $results = $salesFeatureGroup->getDescriptiveAttributesByValueIdentifierMatch("Total Capacity", 1);
      if (count($results)) {
        $matches = [];
        preg_match('/(\d+(?:\.\d+)?)\s+cu\.?\s+ft\.?/i', $results[0]->valueidentifier, $matches);
        if (count($matches)) {
          $entryData['capacity'] = 0 + $matches[1];
        }
      }
    }

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['easyConvection'] = false;
    $entryData['temperatureProbe'] = false;

    if ($salesFeatureGroup) {
      $entryData['easyConvection'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"EasyConvect\u2122 Conversion System"'));
      $entryData['temperatureProbe'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Temperature Probe");

      if (!$entryData['trueConvection']) {
        // Try for a matching SF if not found earlier in name/description
        $entryData['trueConvection'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("True Convection");
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
    return 'Wall Ovens';
  }

}
