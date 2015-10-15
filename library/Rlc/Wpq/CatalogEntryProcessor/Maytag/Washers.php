<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Washers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    // Get all the pieces we'll be analysing
    $washerDescriptionDefaultLocale = $entry->getDescription()->getRecord();
    $washerCompareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');

    /*
     * Washer features
     */

    $entryData['capacity'] = (float) preg_replace('@^.*(\d+(?:\.\d+))\s+cu\. ft\..*$@is', '$1', $washerDescriptionDefaultLocale->name);

    // Init some values to ensure they exist
    $entryData['vibrationControl'] = false;
    $entryData['rapidWash'] = false;
    $entryData['washerWrinkleControl'] = false;
    $entryData['steamEnhanced'] = false;
    $entryData['cycleOptions'] = 0;

    if ($washerCompareFeatureGroup) {
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

    /*
     * Pair image
     */
    // Goes before image URLs in feed to make them relative to http://maytag.com
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $galleryGroup = $entry->getDescriptiveAttributeGroup('Gallery');
    if ($galleryGroup) {
      foreach ($galleryGroup->getDescriptiveAttributes(null, $locale) as $attr) {
        // Check if we've found the right attr
        if (false === strpos($attr->image1, 'Pair_244X312_')) {
          continue;
        }
        // Split up urls and find the one of the right dimensions
        $imageUrls = explode('|', $attr->image1);
        foreach ($imageUrls as $imageUrl) {
          if (false !== strpos($imageUrl, 'Pair_244X312_')) {
            $entryData['image'] = $imageUrlPrefix . $imageUrl;
            break 2;
          }
        }
      }
    }
    // If still not set, use no image image
    if (!isset($entryData['image'])) {
      $entryData['image'] = $imageUrlPrefix . '/No Image Available/Standalone_244X312.png';
    }
  }

  protected function postProcess(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale, array &$newOutputData) {
    /*
     * Each washer has associated dryers. We do their processing here using
     * their own processing classes, and attach the data.
     */
    $newOutputData['dryers'] = [];
    $dryerProcessor = ServiceLocator::catalogEntryProcessor('Maytag\\Dryers');
    foreach ($entry->getXSellAssocs() as $xSellAssoc) {
      if ($xSellAssoc->isInGroupId('SC_Laundry_Laundry_Appliances_Dryers')) {
        // For all x-sell associated products that are dryers, process into
        // a new entry in the dryers array.
        $dryerProcessor->process($xSellAssoc, $entries, $locale, $newOutputData['dryers']);
      }
    }
  }

  protected function getBrand() {
    return 'maytag';
  }

  protected function getCategory() {
    return 'Washers';
  }

}
