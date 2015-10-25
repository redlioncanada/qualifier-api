<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Washers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    // Get all the pieces we'll be analysing
    $washerDescriptionDefaultLocale = $entry->getDescription()->getRecord();
    $washerCompareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    
    $util = ServiceLocator::util();

    /*
     * Washer features
     */

    // Will try compare features if this doesn't work -- for combos
    $entryData['capacity'] = $util->getPregMatch('@(\d+(?:\.\d+))\s+cu\. ft\.@i', $washerDescriptionDefaultLocale->name, 1);

    // Init some values to ensure they exist
    $entryData['vibrationControl'] = false;
    $entryData['rapidWash'] = false;
    $entryData['washerWrinkleControl'] = false;
    $entryData['steamEnhanced'] = false;
    $entryData['cycleOptions'] = 0;

    if ($washerCompareFeatureGroup) {
      $capacityAttr = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Washer Capacity (cu. ft.)");
      if ($capacityAttr) {
        $entryData['capacity'] = $capacityAttr->value;
      }
      
      $avcAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => 'Advanced Vibration Control']);
      if ($avcAttr) {
        $entryData['vibrationControl'] = !in_array($avcAttr->value, ["No", "None"]);
      }

      // Store # of cycle options for washer, and increment total cycle options number
      $washerCyclesAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Wash Cycles"]);
      if ($washerCyclesAttr) {
        $entryData['cycleOptions'] += $washerCyclesAttr->value;
      }
    }

    $entryData['frontLoad'] = (
        (false !== stripos($washerDescriptionDefaultLocale->name, 'front load')) ||
        (false !== stripos($washerDescriptionDefaultLocale->longdescription, 'front load'))
        );
    $entryData['topLoad'] = !$entryData['frontLoad'];

    if ($washerSalesFeatureGroup) {
      // Just has to exist
      $entryData['rapidWash'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Rapid Wash Cycle"]);
      $entryData['washerWrinkleControl'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Wrinkle Control Cycle"]);
      $entryData['steamEnhanced'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Steam-Enhanced Cycles"]);
    }

    // Add image for washers
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function postProcess(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale, array &$newOutputData) {
//    return; // FIXME
    /*
     * Each washer has associated dryers. We do their processing here using
     * their own processing classes, and attach the data.
     */
    $newOutputData['dryers'] = [];
    $dryerProcessor = ServiceLocator::catalogEntryProcessor('Whirlpool\\Dryers');
    foreach ($entry->getXSellAssocs() as $xSellAssoc) {
      if ($xSellAssoc->isInGroupId('SC_Laundry_Laundry_Dryers')) {
        // For all x-sell associated products that are dryers, process into
        // a new entry in the dryers array.
        $dryerProcessor->process($xSellAssoc, $entries, $locale, $newOutputData['dryers']);
      }
    }
    
    $newOutputData['numDryers'] = count($newOutputData['dryers']); // REMOVE AFTER DEV
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Washers';
  }

}
