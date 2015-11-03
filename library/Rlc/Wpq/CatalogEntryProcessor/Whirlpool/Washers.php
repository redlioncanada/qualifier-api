<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Washers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function filterEntries(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale) {
    $washerDescription = $entry->getDescription();
    return false === stripos($washerDescription->name, 'combination');
  }

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    // Get all the pieces we'll be analysing
    $washerDescription = $entry->getDescription();
    $washerCompareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');

    $util = ServiceLocator::util();

    /*
     * Washer features
     */

    // Init some values to ensure they exist
    $entryData['vibrationControl'] = false;
    $entryData['energyStar'] = false;
    $entryData['ecoBoost'] = false;
    $entryData['fanFresh'] = false;
    $entryData['adaptiveWash'] = false;
    $entryData['colorLast'] = false;
    $entryData['smoothWave'] = false;

    // Name/description based fields
    $entryData['capacity'] = $util->getPregMatch('@(\d+(?:\.\d+))\s+cu\. ft\.@i', $washerDescription->name, 1);
    $entryData['quickWash'] = false !== stripos($washerDescription->name, "Quick Wash"); // will try CF as backup
    $entryData['quietWash'] = false !== stripos($washerDescription->longdescription, "Quiet Wash"); // will try CF as backup
    $entryData['frontLoad'] = (
        (false !== stripos($washerDescription->name, 'front load')) ||
        (false !== stripos($washerDescription->longdescription, 'front load'))
        );
    $entryData['topLoad'] = !$entryData['frontLoad'];


    // Compare feature based fields
    if ($washerCompareFeatureGroup) {
      $avcAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => 'Advanced Vibration Control']);
      if ($avcAttr) {
        $entryData['vibrationControl'] = !in_array($avcAttr->value, ["No", "None"]);
      }

      $energyStarAttr = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier(json_decode('"Energy Star\u00ae Qualified"'));
      if ($energyStarAttr) {
        $entryData['energyStar'] = "No" != $energyStarAttr->value;
      }

      $optionsSelAttr = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Option Selections");
      if ($optionsSelAttr) {
        $entryData['ecoBoost'] = false !== stripos($optionsSelAttr->value, "EcoBoost");
      }

      if (!$entryData['quickWash']) {
        $washerCycleSelAttr = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Washer Cycle Selections");
        if ($washerCycleSelAttr) {
          $entryData['quickWash'] = false !== stripos($washerCycleSelAttr->value, "Quick Wash");
        }
      }

      if (!$entryData['quietWash']) {
        $soundPkgAttr = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Sound Package");
        if ($soundPkgAttr) {
          $entryData['quietWash'] = false !== stripos($soundPkgAttr->value, "Quiet Wash");
        }
      }

      $fanFresh = $washerCompareFeatureGroup->getDescriptiveAttributeByValueIdentifier(json_decode('"Fan Fresh\u00ae-Fresh Hold\u00ae"'));
      if ($fanFresh) {
        $entryData['fanFresh'] = "No" != $fanFresh->value;
      }
    }

    // Sales feature based fields
    if ($washerSalesFeatureGroup) {
      $entryData['adaptiveWash'] = $washerSalesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Adaptive Wash Technology");
      $entryData['colorLast'] = $washerSalesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ColorLast\u2122 Option"'));
      $entryData['smoothWave'] = $washerSalesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Smooth Wave Stainless Steel Wash Basket");
    }

    // Add image for washers
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function postProcess(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale, array &$newOutputData) {
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

//    $newOutputData['numDryers'] = count($newOutputData['dryers']); // REMOVE AFTER DEV
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Washers';
  }

}
